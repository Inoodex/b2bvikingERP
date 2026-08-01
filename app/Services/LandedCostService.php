<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\LandedCostAllocation;
use Illuminate\Support\Facades\DB;

class LandedCostService
{
    /**
     * Calculate and allocate LC Overhead Expenses across Purchase Details
     * using Weighted Average Allocation based on Item Foreign Line Total.
     *
     * Formula:
     *   Item Weight Ratio = (Item Foreign Total) / (PO Foreign Total)
     *   Item Allocated Overhead = (Total LC Expenses Base Amount) * Item Weight Ratio
     *   True Landed Unit Cost = (Item Line Base Total + Item Allocated Overhead) / (Accepted Qty)
     *
     * @param Purchase $purchase
     * @return array Matrix of allocations per purchase detail
     */
    public function calculateLandedCosts(Purchase $purchase): array
    {
        return DB::transaction(function () use ($purchase) {
            $purchase->load(['items.product', 'items.variant', 'letterOfCredit.expenses']);

            $lc = $purchase->letterOfCredit;

            // If no LC attached or no expenses recorded, return base unit costs
            if (!$lc || $lc->expenses->isEmpty()) {
                $matrix = [];
                foreach ($purchase->items as $item) {
                    $acceptedQty = $this->getAcceptedQtyForItem($purchase, $item->product_id, $item->variant_id);
                    $qtyToUse = $acceptedQty > 0 ? $acceptedQty : (float) $item->qty;
                    $unitCost = (float) $item->unit_cost;

                    $item->update(['landed_cost' => $unitCost]);

                    $matrix[] = [
                        'purchase_detail_id' => $item->id,
                        'product_name'       => $item->product?->name ?? 'N/A',
                        'variant_name'       => $item->variant?->name ?? '',
                        'base_unit_cost'     => (float) $item->unit_cost,
                        'allocated_overhead' => 0.00,
                        'accepted_qty'       => $qtyToUse,
                        'landed_unit_cost'   => $unitCost,
                    ];
                }
                return $matrix;
            }

            // Calculate total LC overhead expenses in Base Currency where goes_to_unit_cost = 1
            $totalLcExpensesBase = $lc->expenses
                ->where('goes_to_unit_cost', 1)
                ->sum(function ($exp) {
                    return (float) ($exp->amount ?? 0);
                });

            // PO Foreign Total (or Base Total if foreign is 0)
            $poTotalForeign = (float) ($purchase->foreign_amount > 0 ? $purchase->foreign_amount : $purchase->total_amount);

            $matrix = [];

            // Clear previous allocations for this purchase's details
            $itemIds = $purchase->items->pluck('id');
            LandedCostAllocation::whereIn('purchase_detail_id', $itemIds)->delete();

            foreach ($purchase->items as $item) {
                $lineForeignTotal = (float) ($purchase->foreign_amount > 0 ? $item->subtotal : $item->subtotal);

                // Weight ratio
                $weightRatio = $poTotalForeign > 0 ? ($lineForeignTotal / $poTotalForeign) : (1 / count($purchase->items));

                // Allocated overhead for this line
                $lineAllocatedOverhead = $totalLcExpensesBase * $weightRatio;

                // Total Base Cost for this line
                $lineBaseTotal = (float) $item->subtotal;

                // Get Total Accepted Qty across all GRNs for this PO line
                $acceptedQty = $this->getAcceptedQtyForItem($purchase, $item->product_id, $item->variant_id);
                $qtyToUse = $acceptedQty > 0 ? $acceptedQty : (float) $item->qty;

                // True Landed Unit Cost
                $landedUnitCost = ($lineBaseTotal + $lineAllocatedOverhead) / $qtyToUse;

                // Update purchase_details table
                $item->update(['landed_cost' => round($landedUnitCost, 2)]);

                // Save per-expense allocations in landed_cost_allocations table
                foreach ($lc->expenses->where('goes_to_unit_cost', 1) as $expense) {
                    $expAmount = (float) ($expense->amount ?? 0);
                    $expAllocated = $expAmount * $weightRatio;

                    LandedCostAllocation::create([
                        'purchase_detail_id' => $item->id,
                        'lc_expense_id'      => $expense->id,
                        'allocated_amount'   => round($expAllocated, 2),
                        'landed_unit_cost'   => round($landedUnitCost, 2),
                    ]);
                }

                $matrix[] = [
                    'purchase_detail_id' => $item->id,
                    'product_name'       => $item->product?->name ?? 'N/A',
                    'variant_name'       => $item->variant?->name ?? '',
                    'base_unit_cost'     => (float) $item->unit_cost,
                    'allocated_overhead' => round($lineAllocatedOverhead, 2),
                    'accepted_qty'       => $qtyToUse,
                    'landed_unit_cost'   => round($landedUnitCost, 2),
                ];
            }

            return $matrix;
        });
    }

    /**
     * Helper to get total accepted qty for an item across all GRNs of a Purchase
     */
    private function getAcceptedQtyForItem(Purchase $purchase, int $productId, ?int $variantId): float
    {
        return (float) DB::table('goods_receipt_items')
            ->join('goods_receipts', 'goods_receipt_items.goods_receipt_id', '=', 'goods_receipts.id')
            ->where('goods_receipts.purchase_id', $purchase->id)
            ->where('goods_receipts.qc_status', '!=', 'failed')
            ->where('goods_receipt_items.product_id', $productId)
            ->when($variantId, fn($q) => $q->where('goods_receipt_items.variant_id', $variantId))
            ->sum('goods_receipt_items.accepted_qty');
    }
}
