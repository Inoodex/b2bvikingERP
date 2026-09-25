<?php

namespace App\Services;

use App\Models\Product;
use App\Models\CustomerProductVisibility;
use App\Models\User;

class B2bProductVisibilityService
{
    /**
     * Determine effective stock status and visibility for a given customer or company.
     *
     * @param Product $product
     * @param User|null $customer
     * @return array
     */
    public static function resolveAvailability(Product $product, ?User $customer = null): array
    {
        $realStock = (float) ($product->inventory_stock ?? $product->inventoryStocks()->sum('quantity'));

        if (!$customer) {
            return [
                'is_visible' => true,
                'stock_status' => $realStock > 0 ? 'in_stock' : 'out_of_stock',
                'effective_stock' => $realStock,
                'is_overridden' => false,
                'override_rule' => null
            ];
        }

        // Check for specific override
        $override = CustomerProductVisibility::where('product_id', $product->id)
            ->where(function ($q) use ($customer) {
                $q->where('user_id', $customer->id)
                  ->when($customer->company_id, fn($sq) => $sq->orWhere('company_id', $customer->company_id))
                  ->when($customer->outlet_id, fn($sq) => $sq->orWhere('outlet_id', $customer->outlet_id))
                  ->when($customer->phone, fn($sq) => $sq->orWhere('phone_number', $customer->phone));
            })
            ->first();

        if ($override) {
            if ($override->visibility_mode === 'hide_product') {
                return [
                    'is_visible' => false,
                    'stock_status' => 'hidden',
                    'effective_stock' => 0,
                    'is_overridden' => true,
                    'override_rule' => 'hide_product',
                    'notes' => $override->notes
                ];
            }
            if ($override->visibility_mode === 'force_in_stock') {
                return [
                    'is_visible' => true,
                    'stock_status' => 'in_stock',
                    'effective_stock' => $override->reserved_qty ?: max(1.0, $realStock),
                    'is_overridden' => true,
                    'override_rule' => 'force_in_stock',
                    'notes' => $override->notes
                ];
            }
            if ($override->visibility_mode === 'force_out_of_stock') {
                return [
                    'is_visible' => true,
                    'stock_status' => 'out_of_stock',
                    'effective_stock' => 0,
                    'is_overridden' => true,
                    'override_rule' => 'force_out_of_stock',
                    'notes' => $override->notes
                ];
            }
        }

        return [
            'is_visible' => true,
            'stock_status' => $realStock > 0 ? 'in_stock' : 'out_of_stock',
            'effective_stock' => $realStock,
            'is_overridden' => false,
            'override_rule' => null
        ];
    }
}
