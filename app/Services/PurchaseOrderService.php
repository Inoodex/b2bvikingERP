<?php

namespace App\Services;

use App\Models\ComparisonStatement;
use App\Models\ComparisonStatementItem;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Vendor;
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
                    $vendorId = $csItem->selectedQuotationItem?->quotation?->vendor_id;
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
                        'product_id'  => $productId,
                        'variant_id'  => $variantId,
                        'qty'         => $qty,
                        'unit_cost'   => $unitCost,
                        'total'       => $lineTotal,
                        'landed_cost' => $unitCost,
                    ];
                }

                $baseCurrency = \App\Models\Currency::base()->first() ?? \App\Models\Currency::where('is_base', true)->first();
                $vendorObj = Vendor::with('currency')->find($vendorId);

                $quoteCurrency = $csItems[0]->selectedQuotationItem?->quotation?->currency ?? $vendorObj?->currency ?? $baseCurrency;
                $currencyId = $quoteCurrency?->id ?? $vendorObj?->currency_id ?? $baseCurrency?->id;

                $isForeign = false;
                if ($quoteCurrency && $baseCurrency) {
                    if ((int)$quoteCurrency->id !== (int)$baseCurrency->id && strtoupper($quoteCurrency->code) !== strtoupper($baseCurrency->code)) {
                        $isForeign = true;
                    }
                }
                $purchaseType = $isForeign ? 'foreign' : 'local';

                if ($isForeign && $quoteCurrency) {
                    $exchangeRate = (float)($quoteCurrency->exchange_rate > 0 ? $quoteCurrency->exchange_rate : 1.0);
                    $foreignAmount = $subtotal;
                    $baseAmount = round($subtotal * $exchangeRate, 2);
                    $totalAmount = $baseAmount;
                } else {
                    $exchangeRate = 1.0;
                    $foreignAmount = null;
                    $baseAmount = $subtotal;
                    $totalAmount = $subtotal;
                }

                $purchase = Purchase::create([
                    'po_no'                   => $poNo,
                    'invoice_no'              => $invoiceNo,
                    'vendor_id'               => $vendorId,
                    'rfq_id'                  => $cs->rfq_id,
                    'comparison_statement_id' => $cs->id,
                    'currency_id'             => $currencyId,
                    'purchase_type'           => $purchaseType,
                    'exchange_rate_used'      => $exchangeRate,
                    'foreign_amount'          => $foreignAmount,
                    'base_amount'             => $baseAmount,
                    'outlet_id'               => 1,
                    'date'                    => now()->toDateString(),
                    'total_amount'            => $totalAmount,
                    'grand_total'             => $totalAmount,
                    'paid_amount'             => 0,
                    'due_amount'              => $totalAmount,
                    'status'                  => 1,
                    'milestone_status'        => 'approved',
                    'approval_status'         => 'approved',
                    'created_by'              => $userId,
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
