<?php

namespace App\Services;

use App\Models\Product;
use App\Models\CustomerProductVisibility;
use App\Models\User;
use Illuminate\Support\Collection;

class B2bProductVisibilityService
{
    /**
     * Determine effective stock status and visibility for a given customer or company.
     *
     * Priority: User ID > Phone Number > Outlet ID > Company ID
     *
     * @param Product $product
     * @param User|null $customer
     * @return array
     */
    public static function resolveAvailability(Product $product, ?User $customer = null): array
    {
        $customer = $customer ?? (auth()->check() ? auth()->user() : null);

        $realStock = isset($product->scoped_stock_qty)
            ? (float) $product->scoped_stock_qty
            : (float) ($product->inventory_stock ?? ($product->relationLoaded('inventoryStocks') ? $product->inventoryStocks->sum('quantity') : $product->inventoryStocks()->sum('quantity')));

        if (!$customer) {
            return [
                'is_visible' => true,
                'stock_status' => $realStock > 0 ? 'in_stock' : 'out_of_stock',
                'effective_stock' => $realStock,
                'is_overridden' => false,
                'override_rule' => null,
                'reserved_qty' => null,
                'notes' => null,
            ];
        }

        // Check for specific override ordered by specificity: User > Phone > Outlet > Company
        $override = CustomerProductVisibility::where('product_id', $product->id)
            ->where(function ($q) use ($customer) {
                $q->where('user_id', $customer->id)
                  ->when($customer->phone, fn($sq) => $sq->orWhere('phone_number', $customer->phone))
                  ->when($customer->outlet_id, fn($sq) => $sq->orWhere('outlet_id', $customer->outlet_id))
                  ->when($customer->company_id, fn($sq) => $sq->orWhere('company_id', $customer->company_id));
            })
            ->orderByRaw("
                CASE 
                    WHEN user_id IS NOT NULL THEN 1
                    WHEN phone_number IS NOT NULL THEN 2
                    WHEN outlet_id IS NOT NULL THEN 3
                    WHEN company_id IS NOT NULL THEN 4
                    ELSE 5
                END ASC
            ")
            ->first();

        if ($override) {
            if ($override->visibility_mode === 'hide_product') {
                return [
                    'is_visible' => false,
                    'stock_status' => 'hidden',
                    'effective_stock' => 0,
                    'is_overridden' => true,
                    'override_rule' => 'hide_product',
                    'reserved_qty' => null,
                    'notes' => $override->notes,
                ];
            }
            if ($override->visibility_mode === 'force_in_stock') {
                $effective = $override->reserved_qty !== null ? (float) $override->reserved_qty : max(999.0, $realStock);
                return [
                    'is_visible' => true,
                    'stock_status' => 'in_stock',
                    'effective_stock' => $effective,
                    'is_overridden' => true,
                    'override_rule' => 'force_in_stock',
                    'reserved_qty' => $override->reserved_qty,
                    'notes' => $override->notes,
                ];
            }
            if ($override->visibility_mode === 'force_out_of_stock') {
                return [
                    'is_visible' => true,
                    'stock_status' => 'out_of_stock',
                    'effective_stock' => 0,
                    'is_overridden' => true,
                    'override_rule' => 'force_out_of_stock',
                    'reserved_qty' => null,
                    'notes' => $override->notes,
                ];
            }
        }

        return [
            'is_visible' => true,
            'stock_status' => $realStock > 0 ? 'in_stock' : 'out_of_stock',
            'effective_stock' => $realStock,
            'is_overridden' => false,
            'override_rule' => null,
            'reserved_qty' => null,
            'notes' => null,
        ];
    }

    /**
     * Batch resolve availability overrides for a list/collection of products in 1 single query.
     *
     * @param iterable $products
     * @param User|null $customer
     * @return Collection Keyed by product_id
     */
    public static function resolveAvailabilityMap($products, ?User $customer = null): Collection
    {
        $customer = $customer ?? (auth()->check() ? auth()->user() : null);
        $productsCollection = collect($products);
        $productIds = $productsCollection->pluck('id')->filter()->unique()->values()->all();

        if (empty($productIds) || !$customer) {
            return $productsCollection->mapWithKeys(function ($prod) use ($customer) {
                return [$prod->id => self::resolveAvailability($prod, $customer)];
            });
        }

        $overrides = CustomerProductVisibility::whereIn('product_id', $productIds)
            ->where(function ($q) use ($customer) {
                $q->where('user_id', $customer->id)
                  ->when($customer->phone, fn($sq) => $sq->orWhere('phone_number', $customer->phone))
                  ->when($customer->outlet_id, fn($sq) => $sq->orWhere('outlet_id', $customer->outlet_id))
                  ->when($customer->company_id, fn($sq) => $sq->orWhere('company_id', $customer->company_id));
            })
            ->orderByRaw("
                CASE 
                    WHEN user_id IS NOT NULL THEN 1
                    WHEN phone_number IS NOT NULL THEN 2
                    WHEN outlet_id IS NOT NULL THEN 3
                    WHEN company_id IS NOT NULL THEN 4
                    ELSE 5
                END ASC
            ")
            ->get()
            ->groupBy('product_id')
            ->map(fn($group) => $group->first());

        return $productsCollection->mapWithKeys(function ($product) use ($overrides, $customer) {
            $realStock = isset($product->scoped_stock_qty)
                ? (float) $product->scoped_stock_qty
                : (float) ($product->inventory_stock ?? ($product->relationLoaded('inventoryStocks') ? $product->inventoryStocks->sum('quantity') : 0));

            $override = $overrides->get($product->id);

            if ($override) {
                if ($override->visibility_mode === 'hide_product') {
                    return [$product->id => [
                        'is_visible' => false,
                        'stock_status' => 'hidden',
                        'effective_stock' => 0,
                        'is_overridden' => true,
                        'override_rule' => 'hide_product',
                        'reserved_qty' => null,
                        'notes' => $override->notes,
                    ]];
                }
                if ($override->visibility_mode === 'force_in_stock') {
                    $effective = $override->reserved_qty !== null ? (float) $override->reserved_qty : max(999.0, $realStock);
                    return [$product->id => [
                        'is_visible' => true,
                        'stock_status' => 'in_stock',
                        'effective_stock' => $effective,
                        'is_overridden' => true,
                        'override_rule' => 'force_in_stock',
                        'reserved_qty' => $override->reserved_qty,
                        'notes' => $override->notes,
                    ]];
                }
                if ($override->visibility_mode === 'force_out_of_stock') {
                    return [$product->id => [
                        'is_visible' => true,
                        'stock_status' => 'out_of_stock',
                        'effective_stock' => 0,
                        'is_overridden' => true,
                        'override_rule' => 'force_out_of_stock',
                        'reserved_qty' => null,
                        'notes' => $override->notes,
                    ]];
                }
            }

            return [$product->id => [
                'is_visible' => true,
                'stock_status' => $realStock > 0 ? 'in_stock' : 'out_of_stock',
                'effective_stock' => $realStock,
                'is_overridden' => false,
                'override_rule' => null,
                'reserved_qty' => null,
                'notes' => null,
            ]];
        });
    }

    /**
     * Get IDs of products hidden for this customer/outlet/company.
     */
    public static function getHiddenProductIds(?User $customer = null): array
    {
        $customer = $customer ?? (auth()->check() ? auth()->user() : null);
        if (!$customer) {
            return [];
        }

        return CustomerProductVisibility::where('visibility_mode', 'hide_product')
            ->where(function ($q) use ($customer) {
                $q->where('user_id', $customer->id)
                  ->when($customer->phone, fn($sq) => $sq->orWhere('phone_number', $customer->phone))
                  ->when($customer->outlet_id, fn($sq) => $sq->orWhere('outlet_id', $customer->outlet_id))
                  ->when($customer->company_id, fn($sq) => $sq->orWhere('company_id', $customer->company_id));
            })
            ->pluck('product_id')
            ->unique()
            ->all();
    }
}
