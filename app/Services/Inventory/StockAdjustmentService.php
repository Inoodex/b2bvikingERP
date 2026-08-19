<?php

namespace App\Services\Inventory;

use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockLedger;
use App\Services\OrderNumberService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    /**
     * Create a new Stock Adjustment in Draft state.
     */
    public function createAdjustment(array $data, array $items): StockAdjustment
    {
        return DB::transaction(function () use ($data, $items) {
            $adjustmentNo = OrderNumberService::generate('ADJ', StockAdjustment::class, 'stock_adjustments');

            $totalItems = 0;
            $totalCost = 0.00;

            $adjustment = StockAdjustment::create([
                'adjustment_no' => $adjustmentNo,
                'outlet_id' => $data['outlet_id'] ?? 1,
                'adjusted_by' => Auth::id() ?? 1,
                'adjustment_type' => $data['adjustment_type'] ?? 'decrease',
                'reason_code' => $data['reason_code'] ?? 'physical_count',
                'reason' => $data['reason'] ?? ($data['reason_code'] ?? 'Adjustment'),
                'status' => 'draft',
                'note' => $data['note'] ?? null,
                'attachment' => $data['attachment'] ?? null,
            ]);

            foreach ($items as $item) {
                $qtyChange = (float) ($item['adjusted_qty'] ?? $item['qty_change'] ?? 0);
                if ($qtyChange == 0) {
                    continue;
                }

                $productId = $item['product_id'];
                $variantId = !empty($item['variant_id']) ? $item['variant_id'] : null;

                // Resolve system current stock
                $currentStock = InventoryStock::where([
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'outlet_id' => $adjustment->outlet_id,
                ])->first();

                $systemQty = $currentStock ? (float) $currentStock->quantity : 0.00;
                $countedQty = (float) ($item['counted_qty'] ?? ($systemQty + ($adjustment->adjustment_type === 'increase' ? $qtyChange : -$qtyChange)));

                // Resolve unit cost
                $product = Product::find($productId);
                $unitCost = (float) ($item['unit_cost'] ?? ($product->purchase_price ?? $product->price ?? 0.00));
                $lineTotalCost = round(abs($qtyChange) * $unitCost, 2);

                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'qty_change' => $adjustment->adjustment_type === 'decrease' ? -abs($qtyChange) : abs($qtyChange),
                    'system_qty' => $systemQty,
                    'counted_qty' => $countedQty,
                    'adjusted_qty' => abs($qtyChange),
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineTotalCost,
                    'item_note' => $item['item_note'] ?? null,
                ]);

                $totalItems++;
                $totalCost += $lineTotalCost;
            }

            $adjustment->update([
                'total_items_count' => $totalItems,
                'total_adjusted_cost' => $totalCost,
            ]);

            return $adjustment->fresh(['items.product', 'items.variant', 'outlet', 'requestedByUser']);
        });
    }

    /**
     * Approve a Stock Adjustment and apply stock changes to inventory and ledger.
     */
    public function approveAdjustment(StockAdjustment $adjustment): StockAdjustment
    {
        if ($adjustment->status !== 'draft') {
            throw new \Exception("Only draft adjustments can be approved.");
        }

        return DB::transaction(function () use ($adjustment) {
            $adjustment->loadMissing('items');

            foreach ($adjustment->items as $item) {
                $stock = InventoryStock::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'outlet_id' => $adjustment->outlet_id,
                    ],
                    ['quantity' => 0]
                );

                if ($adjustment->adjustment_type === 'decrease') {
                    // Check stock sufficiency
                    if ((float) $stock->quantity < (float) $item->adjusted_qty) {
                        $productName = $item->product ? $item->product->name : "Product #{$item->product_id}";
                        throw new \Exception("Insufficient stock for '{$productName}'. Available: {$stock->quantity}, Required Adjustment: {$item->adjusted_qty}");
                    }

                    $stock->decrement('quantity', $item->adjusted_qty);

                    StockLedger::create([
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'outlet_id' => $adjustment->outlet_id,
                        'reference_type' => 'stock_adjustment',
                        'reference_id' => $adjustment->id,
                        'in_qty' => 0,
                        'out_qty' => $item->adjusted_qty,
                        'balance_qty' => $stock->quantity,
                        'date' => now(),
                    ]);
                } else {
                    // Increase or found stock
                    $stock->increment('quantity', $item->adjusted_qty);

                    StockLedger::create([
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'outlet_id' => $adjustment->outlet_id,
                        'reference_type' => 'stock_adjustment',
                        'reference_id' => $adjustment->id,
                        'in_qty' => $item->adjusted_qty,
                        'out_qty' => 0,
                        'balance_qty' => $stock->quantity,
                        'date' => now(),
                    ]);
                }
            }

            $adjustment->update([
                'status' => 'approved',
                'approved_by' => Auth::id() ?? 1,
            ]);

            return $adjustment->fresh();
        });
    }

    /**
     * Cancel a draft adjustment.
     */
    public function cancelAdjustment(StockAdjustment $adjustment): StockAdjustment
    {
        if ($adjustment->status !== 'draft') {
            throw new \Exception("Only draft adjustments can be cancelled.");
        }

        $adjustment->update([
            'status' => 'cancelled',
        ]);

        return $adjustment->fresh();
    }
}
