<?php

namespace App\Services\Pricing;

use App\Models\Pricelist;
use App\Models\PricelistItem;
use App\Models\Product;
use App\Models\User;

class PricelistResolverService
{
    /**
     * Resolve effective price for a customer and product.
     *
     * @param int|null $customerId
     * @param int $productId
     * @param int|null $variantId
     * @return array
     */
    public function resolvePrice(?int $customerId, int $productId, ?int $variantId = null): array
    {
        $product = Product::with('variants')->find($productId);
        if (!$product) {
            return [
                'price' => 0.00,
                'source' => 'not_found',
                'pricelist_name' => null,
                'customer_segment' => null,
            ];
        }

        $basePrice = (float) $product->price;

        if (!$customerId) {
            return [
                'price' => $basePrice,
                'source' => 'base_mrp',
                'pricelist_name' => 'Default Base MRP',
                'customer_segment' => 'retail',
            ];
        }

        $customer = User::find($customerId);
        if (!$customer) {
            return [
                'price' => $basePrice,
                'source' => 'base_mrp',
                'pricelist_name' => 'Default Base MRP',
                'customer_segment' => 'retail',
            ];
        }

        $segment = $customer->customer_segment ?? 'retail';
        $today = date('Y-m-d');

        $activePricelist = Pricelist::where('status', 1)
            ->where('customer_segment', $segment)
            ->where(function ($query) use ($today) {
                $query->whereNull('valid_from')->orWhere('valid_from', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('valid_to')->orWhere('valid_to', '>=', $today);
            })
            ->latest()
            ->first();

        if (!$activePricelist) {
            return [
                'price' => $basePrice,
                'source' => 'base_mrp',
                'pricelist_name' => 'Default Base MRP',
                'customer_segment' => $segment,
            ];
        }

        $itemQuery = PricelistItem::where('pricelist_id', $activePricelist->id)
            ->where('product_id', $productId);

        if ($variantId) {
            $variantItem = (clone $itemQuery)->where('variant_id', $variantId)->first();
            if ($variantItem) {
                return [
                    'price' => (float) $variantItem->price,
                    'source' => 'pricelist_tier',
                    'pricelist_name' => $activePricelist->name,
                    'customer_segment' => $segment,
                ];
            }
        }

        $item = $itemQuery->whereNull('variant_id')->first();
        if ($item) {
            return [
                'price' => (float) $item->price,
                'source' => 'pricelist_tier',
                'pricelist_name' => $activePricelist->name,
                'customer_segment' => $segment,
            ];
        }

        return [
            'price' => $basePrice,
            'source' => 'base_mrp_fallback',
            'pricelist_name' => $activePricelist->name . ' (Fallback)',
            'customer_segment' => $segment,
        ];
    }
}
