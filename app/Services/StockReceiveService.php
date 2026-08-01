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

                // 2. Write stock_ledgers entry
                StockLedger::create([
                    'outlet_id'        => $outletId,
                    'product_id'       => $grnItem->product_id,
                    'variant_id'       => $grnItem->variant_id,
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
}
