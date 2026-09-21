<?php

declare(strict_types=1);

namespace App\Services\Barcode;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

class Gs1BarcodeService
{
    public function __construct(
        protected ?Gs1TaxonomyResolver $taxonomyResolver = null
    ) {
        $this->taxonomyResolver = $taxonomyResolver ?? app(Gs1TaxonomyResolver::class);
    }

    /**
     * Resolve 8-digit GS1 GPC Brick Code for a product category.
     */
    public function resolveGpcCode(?Category $category): string
    {
        if ($category) {
            $resolved = $this->taxonomyResolver->resolve($category);
            return $resolved['code'];
        }

        return '10000000'; // GS1 Standard Brick Fallback for General Merchandise
    }

    /**
     * Generate collision-free GS1 GPC Barcode for simple product:
     * Formula: [GPC(8 digits)]-[PROD_ID(4+ digits)]-[CHECKSUM_HASH(4 chars)]
     * Example: 10001363-0045-8A1C
     */
    public function generateProductBarcode(Product $product): string
    {
        if (!$product->relationLoaded('category')) {
            $product->load('category');
        }

        $gpc = $this->resolveGpcCode($product->category);
        $paddedId = str_pad((string) $product->id, 4, '0', STR_PAD_LEFT);
        $salt = 0;

        do {
            $seed = "PROD-{$product->id}-" . ($product->sku ?? $product->name ?? 'ITEM') . ($salt > 0 ? "-{$salt}" : '');
            $hash = strtoupper(substr(md5($seed), 0, 4));
            $barcode = "{$gpc}-{$paddedId}-{$hash}";
            $salt++;
            $collision = Product::where('barcode', $barcode)->where('id', '!=', $product->id)->exists()
                || ProductVariant::where('barcode', $barcode)->exists();
        } while ($collision && $salt < 100);

        return $barcode;
    }

    /**
     * Generate collision-free GS1 GPC Barcode for product variant:
     * Formula: [GPC(8 digits)]-[PROD_ID(4+ digits)]-V[VARIANT_ID]-[CHECKSUM_HASH(4 chars)]
     * Example: 10001363-0045-V12-9F2D
     */
    public function generateVariantBarcode(ProductVariant $variant): string
    {
        if (!$variant->relationLoaded('product')) {
            $variant->load('product.category');
        }

        $product = $variant->product;
        $category = $product?->category;
        $gpc = $this->resolveGpcCode($category);
        $paddedProdId = str_pad((string) ($variant->product_id ?? 0), 4, '0', STR_PAD_LEFT);
        $salt = 0;

        do {
            $seed = "VAR-{$variant->id}-" . ($variant->name ?? 'VAR') . ($salt > 0 ? "-{$salt}" : '');
            $hash = strtoupper(substr(md5($seed), 0, 4));
            $barcode = "{$gpc}-{$paddedProdId}-V{$variant->id}-{$hash}";
            $salt++;
            $collision = ProductVariant::where('barcode', $barcode)->where('id', '!=', $variant->id)->exists()
                || Product::where('barcode', $barcode)->exists();
        } while ($collision && $salt < 100);

        return $barcode;
    }

    /**
     * Generate Handling Unit (Carton, Box, Pallet, Container) Barcode:
     * Formula: [TYPE_CODE]-[GPC(8 digits)]-[YEAR]-[RANDOM(5 chars)]
     * Example: CTN-10001363-2026-X8K9P
     */
    public function generateHandlingUnitBarcode(string $typeCode, ?string $gpcCode = null): string
    {
        $gpc = !empty($gpcCode) ? preg_replace('/[^0-9]/', '', $gpcCode) : '10000000';
        $gpc = str_pad($gpc, 8, '0', STR_PAD_LEFT);
        $year = date('Y');
        $random = strtoupper(Str::random(5));
        $prefix = strtoupper(trim($typeCode));

        return "{$prefix}-{$gpc}-{$year}-{$random}";
    }
}
