<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\InventoryStock;
use App\Models\StockLedger;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Exception;

class StockReceiveService
{
    /**
     * Process GRN Approval and Stock Receipt into Inventory
     *
     * 1. Updates inventory_stocks (adds accepted_qty).
     * 2. Writes stock_ledgers entry with transaction_type = purchase_grn, reference_no = grn_no, and Landed Cost.
     * 3. Calculates total received across all GRNs for this PO.
     * 4. Updates PO milestone_status:
     *    - 'goods_received' if fully received
     *    - 'goods_partial' if partially received
     *
     * @param GoodsReceipt $grn
     * @return void
     * @throws Exception
     */
    public function processStockReceive(GoodsReceipt $grn): void
    {
        DB::transaction(function () use ($grn) {
            $grn->load(['items.product', 'items.variant', 'purchase.items']);

            $purchase = $grn->purchase;
            $outletId = $grn->outlet_id;

            // Recalculate Landed Costs for the purchase
            $landedCostService = app(LandedCostService::class);
            $landedCostService->calculateLandedCosts($purchase);
            $purchase->load('items');

            foreach ($grn->items as $grnItem) {
                $acceptedQty = (float) $grnItem->accepted_qty;
                if ($acceptedQty <= 0) {
                    continue;
                }

                $binId = $grnItem->bin_id ?? $grn->bin_id ?? null;

                // Find corresponding purchase_detail line to get Landed Unit Cost
                $poDetail = $purchase->items
                    ->where('product_id', $grnItem->product_id)
                    ->where('variant_id', $grnItem->variant_id)
                    ->first();

                $unitLandedCost = $poDetail ? (float) ($poDetail->landed_cost ?? $poDetail->unit_cost) : 0.00;

                // 1. Update or Create inventory_stocks
                $stock = InventoryStock::firstOrCreate(
                    [
                        'outlet_id'  => $outletId,
                        'bin_id'     => $binId,
                        'product_id' => $grnItem->product_id,
                        'variant_id' => $grnItem->variant_id,
                    ],
                    [
                        'quantity' => 0,
                    ]
                );

                $previousQty = (float) $stock->quantity;
                $newQty = $previousQty + $acceptedQty;
                $stock->update(['quantity' => $newQty]);

                // Also sync main products catalog table quantity
                \App\Models\Product::where('id', $grnItem->product_id)->increment('qty', $acceptedQty);
                if ($grnItem->variant_id && class_exists('\App\Models\ProductVariant')) {
                    \App\Models\ProductVariant::where('id', $grnItem->variant_id)->increment('qty', $acceptedQty);
                }

                // 2. Create StockBatch for FIFO Costing
                $batchNo = 'BCH-' . date('Ymd') . '-' . str_pad($grn->id, 4, '0', STR_PAD_LEFT) . '-' . str_pad($grnItem->id, 4, '0', STR_PAD_LEFT);

                $batch = \App\Models\StockBatch::create([
                    'product_id'         => $grnItem->product_id,
                    'variant_id'         => $grnItem->variant_id,
                    'outlet_id'          => $outletId,
                    'bin_id'             => $binId,
                    'goods_receipt_id'   => $grn->id,
                    'purchase_detail_id' => $poDetail ? $poDetail->id : null,
                    'batch_no'           => $batchNo,
                    'qty_received'       => $acceptedQty,
                    'qty_remaining'      => $acceptedQty,
                    'unit_cost'          => $unitLandedCost,
                    'received_date'      => now()->format('Y-m-d'),
                ]);

                // 3. Write stock_ledgers entry
                StockLedger::create([
                    'outlet_id'        => $outletId,
                    'bin_id'           => $binId,
                    'product_id'       => $grnItem->product_id,
                    'variant_id'       => $grnItem->variant_id,
                    'batch_id'         => $batch->id,
                    'reference_type'   => 'goods_receipt',
                    'reference_id'     => $grn->id,
                    'in_qty'           => $acceptedQty,
                    'out_qty'          => 0,
                    'balance_qty'      => $newQty,
                    'date'             => now()->format('Y-m-d'),
                ]);
            }

            // 3. Check PO Completion across ALL GRNs
            $totalOrderedQty = (float) $purchase->items->sum('qty');

            $totalAcceptedAcrossGrns = (float) DB::table('goods_receipt_items')
                ->join('goods_receipts', 'goods_receipt_items.goods_receipt_id', '=', 'goods_receipts.id')
                ->where('goods_receipts.purchase_id', $purchase->id)
                ->where('goods_receipts.qc_status', '!=', 'failed')
                ->sum('goods_receipt_items.accepted_qty');

            if ($totalAcceptedAcrossGrns >= $totalOrderedQty) {
                $purchase->update(['milestone_status' => 'goods_received']);
            } else if ($totalAcceptedAcrossGrns > 0) {
                $purchase->update(['milestone_status' => 'goods_partial']);
            }
        });
    }

    /**
     * Create Goods Receipt from validated data and trigger stock receive.
     */
    public function createGoodsReceipt(array $data, int $receivedBy): GoodsReceipt
    {
        return DB::transaction(function () use ($data, $receivedBy) {
            $purchase = Purchase::with(['items', 'shipments'])->findOrFail($data['purchase_id']);

            if (strtolower($purchase->purchase_type ?? 'local') === 'foreign') {
                $hasClearedShipment = $purchase->shipments()->where('status', 'cleared')->exists();
                if (!$hasClearedShipment) {
                    throw new \DomainException('Enterprise Rule Violation: Foreign Purchase goods cannot be received until shipment status is Customs Cleared!');
                }
            }

            $seq = GoodsReceipt::whereDate('created_at', now()->toDateString())->lockForUpdate()->count() + 1;
            $grnNo = 'GRN-' . date('Ymd') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $totalAccepted = 0;
            $totalRejected = 0;

            foreach ($data['items'] as $itemData) {
                $totalAccepted += (float)($itemData['accepted_qty'] ?? 0);
                $totalRejected += (float)($itemData['rejected_qty'] ?? 0);
            }

            $qcStatus = $data['qc_status'] ?? 'passed';
            if ($totalAccepted > 0 && $totalRejected > 0) {
                $qcStatus = 'partial';
            } elseif ($totalAccepted == 0 && $totalRejected > 0) {
                $qcStatus = 'failed';
            }

            $physical = !empty($data['qc_physical_check']) ? 'Passed' : 'Pending/Failed';
            $spec = !empty($data['qc_spec_check']) ? 'Verified' : 'Unverified';
            $doc = !empty($data['qc_doc_check']) ? 'Verified' : 'Unverified';
            $qcSummary = "QC Check: Physical Check [{$physical}], Spec & Brand [{$spec}], Invoice & Waybill [{$doc}].";

            $userRemarks = $data['remarks'] ?? '';
            $finalRemarks = trim($qcSummary . ($userRemarks ? " Notes: " . $userRemarks : ''));

            $grn = GoodsReceipt::create([
                'grn_no'      => $grnNo,
                'purchase_id' => $purchase->id,
                'outlet_id'   => $data['outlet_id'],
                'bin_id'      => $data['bin_id'] ?? null,
                'received_by' => $receivedBy,
                'qc_status'   => $qcStatus,
                'remarks'     => $finalRemarks,
            ]);

            foreach ($data['items'] as $itemData) {
                $accepted = (float)($itemData['accepted_qty'] ?? 0);
                $rejected = (float)($itemData['rejected_qty'] ?? 0);

                if ($accepted > 0 || $rejected > 0) {
                    \App\Models\GoodsReceiptItem::create([
                        'goods_receipt_id' => $grn->id,
                        'product_id'       => $itemData['product_id'],
                        'variant_id'       => $itemData['variant_id'] ?? null,
                        'bin_id'           => $itemData['bin_id'] ?? ($data['bin_id'] ?? null),
                        'accepted_qty'     => $accepted,
                        'rejected_qty'     => $rejected,
                        'rejection_reason' => $itemData['rejection_reason'] ?? ($itemData['remarks'] ?? null),
                    ]);
                }
            }

            if (in_array($qcStatus, ['passed', 'partial'])) {
                $this->processStockReceive($grn);
            }

            return $grn;
        });
    }
}
