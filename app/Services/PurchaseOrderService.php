<?php

namespace App\Services;

use App\Models\ComparisonStatement;
use App\Models\ComparisonStatementItem;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    /**
     * Generate Purchase Order(s) from an approved Comparison Statement.
     *
     * @return Purchase[]
     */
    public function generateFromComparisonStatement(ComparisonStatement $cs, int $userId): array
    {
        if ($cs->approval_status !== 'approved') {
            throw new \DomainException('Comparison Statement must be Approved before generating Purchase Orders.');
        }

        return DB::transaction(function () use ($cs, $userId) {
            $itemsByVendor = [];

            if ($cs->recommended_vendor_id) {
                $vendorId = $cs->recommended_vendor_id;
                foreach ($cs->items as $csItem) {
                    $itemsByVendor[$vendorId][] = $csItem;
                }
            } else {
                foreach ($cs->items as $csItem) {
                    $vendorId = $csItem->recommended_vendor_id;
                    if ($vendorId) {
                        $itemsByVendor[$vendorId][] = $csItem;
                    }
                }
            }

            if (empty($itemsByVendor)) {
                throw new \DomainException('No winning vendors found on this Comparison Statement.');
            }

            $generatedPos = [];

            foreach ($itemsByVendor as $vendorId => $csItems) {
                $poNo = OrderNumberService::generate('PO', Purchase::class, 'purchases', 'po_no');
                $invoiceNo = OrderNumberService::generate('INV', Purchase::class, 'purchases', 'invoice_no');

                $subtotal = 0;
                $lineItemsToCreate = [];

                foreach ($csItems as $csItem) {
                    $quoteItem = $csItem->selectedQuotationItem;
                    $qty = $quoteItem ? (float)$quoteItem->qty : 1;
                    $unitCost = $quoteItem ? (float)$quoteItem->unit_price : 0;
                    $lineTotal = $qty * $unitCost;
                    $subtotal += $lineTotal;

                    $rfqItem = $csItem->rfqItem;
                    $productId = $rfqItem ? $rfqItem->product_id : ($csItem->product_id ?? 1);
                    $variantId = $rfqItem ? $rfqItem->variant_id : ($csItem->variant_id ?? null);

                    $lineItemsToCreate[] = [
                        'product_id'       => $productId,
                        'variant_id'       => $variantId,
                        'qty'              => $qty,
                        'unit_cost'        => $unitCost,
                        'total'            => $lineTotal,
                        'landed_cost'      => $unitCost,
                        'vendor_unit_cost' => $unitCost,
                    ];
                }

                $baseCurrency = \App\Models\Currency::base()->first() ?? \App\Models\Currency::where('is_base', true)->first();
                $vendorObj = Vendor::with('currency')->find($vendorId);

                $isForeign = false;
                if ($vendorObj && $vendorObj->currency && $baseCurrency) {
                    if ((int)$vendorObj->currency_id !== (int)$baseCurrency->id && strtoupper($vendorObj->currency->code) !== strtoupper($baseCurrency->code)) {
                        $isForeign = true;
                    }
                }
                $purchaseType = $isForeign ? 'foreign' : 'local';

                $purchase = Purchase::create([
                    'po_no'            => $poNo,
                    'invoice_no'       => $invoiceNo,
                    'vendor_id'        => $vendorId,
                    'outlet_id'        => 1,
                    'purchase_type'    => $purchaseType,
                    'date'             => now()->toDateString(),
                    'total_amount'     => $subtotal,
                    'grand_total'      => $subtotal,
                    'paid_amount'      => 0,
                    'due_amount'       => $subtotal,
                    'status'           => 1,
                    'milestone_status' => 'approved',
                    'created_by'       => $userId,
                ]);

                foreach ($lineItemsToCreate as $item) {
                    $item['purchase_id'] = $purchase->id;
                    PurchaseDetail::create($item);
                }

                $generatedPos[] = $purchase;
            }

            return $generatedPos;
        });
    }
}
