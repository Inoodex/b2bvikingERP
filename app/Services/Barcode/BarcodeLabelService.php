<?php

declare(strict_types=1);

namespace App\Services\Barcode;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use Illuminate\Support\Collection;

/**
 * Enterprise Product & Variant Barcode Label Service.
 * Service handling product barcode resolution, SVG rendering, and print formatting.
 */
class BarcodeLabelService
{
    public function __construct(
        protected NativeBarcodeGenerator $generator
    ) {}

    /**
     * Get all supported enterprise label presets for retail products and bulk lots.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getPresets(): array
    {
        return [
            'thermal_50x30' => [
                'name' => '50mm × 30mm (Thermal Roll)',
                'badge' => 'Standard Apparel & Retail',
                'category' => 'thermal',
                'width_mm' => 50,
                'height_mm' => 30,
                'description' => 'Most popular format for clothing, t-shirt tags, souvenirs & gifts. Compatible with Zebra, Xprinter, TSC, Dymo.',
                'css_class' => 'label-thermal-50x30',
                'columns' => 1,
            ],
            'thermal_38x25' => [
                'name' => '38mm × 25mm (Compact Roll)',
                'badge' => 'Compact / Jewelry',
                'category' => 'thermal',
                'width_mm' => 38,
                'height_mm' => 25,
                'description' => 'Compact mini sticker for keyrings, magnets, jewelry and small travel souvenirs.',
                'css_class' => 'label-thermal-38x25',
                'columns' => 1,
            ],
            'a4_3x8_sheet' => [
                'name' => 'A4 Sheet: 3 × 8 (24 Labels per Page)',
                'badge' => 'Standard Laser / Inkjet',
                'category' => 'a4',
                'width_mm' => 63.5,
                'height_mm' => 33.9,
                'description' => 'Avery 7160 standard sticker sheet layout for office laser and inkjet printers (24 labels/sheet).',
                'css_class' => 'label-a4-3x8',
                'columns' => 3,
                'rows' => 8,
            ],
            'a4_2x7_sheet' => [
                'name' => 'A4 Sheet: 2 × 7 (14 Labels per Page)',
                'badge' => 'Large Format Laser Sheet',
                'category' => 'a4',
                'width_mm' => 99.1,
                'height_mm' => 38.1,
                'description' => 'Large format A4 sticker sheet for prominent product displays and cartons (14 labels/sheet).',
                'css_class' => 'label-a4-2x7',
                'columns' => 2,
                'rows' => 7,
            ],
        ];
    }

    /**
     * Load initial dataset for the Search & Queue workspace.
     *
     * @return array<string, mixed>
     */
    public function getWorkspaceData(array $filters = []): array
    {
        $categories = Category::where('status', 1)
            ->withCount('products')
            ->orderBy('name')
            ->get(['id', 'name']);

        $units = Unit::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        $preloadedProductId = ! empty($filters['product_id']) ? (int) $filters['product_id'] : null;
        $preloadedProduct = null;

        if ($preloadedProductId) {
            $prod = Product::with([
                'category',
                'brand',
                'inventoryStocks',
                'stockBatches',
                'variants.color',
                'variants.size',
                'variants.inventoryStocks',
                'variants.stockBatches',
            ])->find($preloadedProductId);

            if ($prod) {
                $preloadedProduct = [
                    'id' => $prod->id,
                    'name' => $prod->name,
                    'sku' => $prod->sku ?? $prod->product_number ?? ('PRD-'.$prod->id),
                    'barcode' => $prod->barcode,
                    'price' => (float) $prod->price,
                    'category' => $prod->category?->name ?? 'General',
                    'brand' => $prod->brand?->name ?? '',
                    'stock' => (int) $prod->inventory_stock,
                    'image' => $prod->thumb_image ? asset('storage/'.$prod->thumb_image) : null,
                    'has_variants' => $prod->variants->isNotEmpty(),
                    'variant_count' => $prod->variants->count(),
                    'batches' => $prod->stockBatches->pluck('batch_no')->unique()->filter()->values()->all(),
                    'variants' => $this->mapProductVariants($prod),
                ];
            }
        }

        $totalProductsCount = Product::count();
        $initialProducts = $this->searchProducts('', null, 60);

        return [
            'presets' => $this->getPresets(),
            'categories' => $categories,
            'units' => $units,
            'totalProductsCount' => $totalProductsCount,
            'initialProducts' => $initialProducts,
            'preloadedProduct' => $preloadedProduct,
        ];
    }

    /**
     * Generate or resolve a collision-free barcode for a parent product.
     */
    public function ensureProductBarcode(Product $product, bool $forceNew = false): string
    {
        if (! $forceNew && ! empty($product->barcode) && ! str_contains($product->barcode, '{')) {
            return $product->barcode;
        }

        $catId = $product->category_id ?? 0;
        $hash = strtoupper(substr(md5($product->id.'-'.($product->sku ?? $product->name)), 0, 4));
        $generatedBarcode = "PRD-{$catId}-{$product->id}-{$hash}";

        $product->barcode = $generatedBarcode;
        $product->saveQuietly();

        return $generatedBarcode;
    }

    /**
     * Explicitly generate and save barcode for a parent product on demand.
     */
    public function generateBarcodeForProduct(int $productId): ?string
    {
        $product = Product::find($productId);
        if (! $product) {
            return null;
        }

        return $this->ensureProductBarcode($product, forceNew: true);
    }

    /**
     * Generate or resolve a collision-free barcode for a product variant.
     */
    public function ensureVariantBarcode(ProductVariant $variant, bool $forceNew = false): string
    {
        if (! $forceNew && ! empty($variant->barcode)) {
            return $variant->barcode;
        }

        if (! $variant->relationLoaded('product')) {
            $variant->load('product.category');
        }

        $catId = $variant->product?->category_id ?? 0;
        $productId = $variant->product_id;
        $hash = strtoupper(substr(md5($variant->id.'-'.($variant->name ?? 'VAR')), 0, 4));
        $generatedBarcode = "PRD-{$catId}-{$productId}-V{$variant->id}-{$hash}";

        $variant->barcode = $generatedBarcode;
        $variant->saveQuietly();

        return $generatedBarcode;
    }

    /**
     * Explicitly generate and save barcode for a variant on demand.
     */
    public function generateBarcodeForVariant(int $variantId): ?string
    {
        $variant = ProductVariant::find($variantId);
        if (! $variant) {
            return null;
        }

        return $this->ensureVariantBarcode($variant, forceNew: true);
    }

    /**
     * Fast AJAX search endpoint returning mapped items without mutating database.
     *
     * @return array<int, array<string, mixed>>
     */
    public function searchProducts(string $query = '', ?int $categoryId = null, int $limit = 60): array
    {
        $productsQuery = Product::query()
            ->select([
                'id',
                'name',
                'sku',
                'product_number',
                'barcode',
                'price',
                'category_id',
                'brand_id',
                'thumb_image',
                'qty',
            ])
            ->withSum('inventoryStocks as total_stock', 'quantity')
            ->with([
                'category:id,name',
                'brand:id,name',
                'variants' => function ($q) {
                    $q->select([
                        'id',
                        'product_id',
                        'name',
                        'color_id',
                        'size_id',
                        'barcode',
                        'price',
                        'qty',
                    ])
                    ->withSum('inventoryStocks as total_stock', 'quantity')
                    ->with(['color:id,name', 'size:id,name']);
                },
            ])
            ->latest('id');

        if (! empty($query)) {
            $productsQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%")
                    ->orWhere('product_number', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%")
                    ->orWhereHas('variants', function ($vq) use ($query) {
                        $vq->where('name', 'like', "%{$query}%")
                            ->orWhere('color', 'like', "%{$query}%")
                            ->orWhere('size', 'like', "%{$query}%")
                            ->orWhere('barcode', 'like', "%{$query}%");
                    });
            });
        }

        if ($categoryId) {
            $productsQuery->where('category_id', $categoryId);
        }

        if ($limit > 0) {
            $productsQuery->limit($limit);
        }

        return $productsQuery->get()->map(function (Product $product) {
            $variants = $this->mapProductVariants($product);
            $stock = (int) ($product->total_stock ?? $product->qty);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku ?? $product->product_number ?? ('PRD-'.$product->id),
                'barcode' => $product->barcode,
                'has_barcode' => ! empty($product->barcode),
                'price' => (float) $product->price,
                'category' => $product->category?->name ?? 'General',
                'brand' => $product->brand?->name ?? '',
                'stock' => $stock,
                'image' => $product->thumb_image ? asset('storage/'.$product->thumb_image) : null,
                'has_variants' => count($variants) > 0,
                'variant_count' => count($variants),
                'variants' => $variants,
            ];
        })->all();
    }

    /**
     * Map variant models of a product into a clean payload array.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function mapProductVariants(Product $product): array
    {
        if (! $product->relationLoaded('variants') || $product->variants->isEmpty()) {
            return [];
        }

        return $product->variants->map(function (ProductVariant $variant) use ($product) {
            $colorName = $variant->color?->name ?? $variant->color ?? '';
            $sizeName = $variant->size?->name ?? $variant->size ?? '';
            $specParts = array_filter([$variant->name, $colorName, $sizeName]);
            $spec = implode(' - ', array_unique($specParts));
            $variantStock = (int) ($variant->total_stock ?? $variant->qty);

            return [
                'id' => $variant->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'name' => $variant->name,
                'color' => $colorName,
                'size' => $sizeName,
                'spec' => $spec ?: ('Variant #'.$variant->id),
                'sku' => ($product->sku ?? ('PRD-'.$product->id)).'-V'.$variant->id,
                'barcode' => $variant->barcode,
                'has_barcode' => ! empty($variant->barcode),
                'price' => (float) ($variant->price > 0 ? $variant->price : $product->price),
                'stock' => $variantStock,
            ];
        })->values()->all();
    }

    /**
     * Parse items payload from JSON string or array safely.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parseItems(mixed $rawItems): array
    {
        if (is_string($rawItems)) {
            $decoded = json_decode($rawItems, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($rawItems) ? $rawItems : [];
    }

    /**
     * Parse content visibility toggles with standard defaults.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, bool>
     */
    public function parseToggles(array $input): array
    {
        return [
            'show_price' => filter_var($input['show_price'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'show_name' => filter_var($input['show_name'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'show_sku' => filter_var($input['show_sku'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'show_brand' => filter_var($input['show_brand'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'show_barcode_text' => filter_var($input['show_barcode_text'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'show_variant_spec' => filter_var($input['show_variant_spec'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * Resolve product & variant label items with centered SVG barcodes and calculated copy quantities.
     *
     * @param  array<int, array<string, mixed>>  $itemSelections
     * @param  array<string, mixed>  $options
     */
    public function resolveProductLabels(array $itemSelections, array $options = [], bool $persistMissing = false): Collection
    {
        $companyName = config('settings.company_name') ?? 'Copenhagen Tourist Point';

        // Segregate product IDs and variant IDs
        $productSelections = [];
        $variantSelections = [];

        foreach ($itemSelections as $item) {
            $type = $item['target_type'] ?? 'product';
            if ($type === 'variant') {
                $variantSelections[] = $item;
            } else {
                $productSelections[] = $item;
            }
        }

        $productIds = array_filter(array_column($productSelections, 'id'));
        $variantIds = array_filter(array_column($variantSelections, 'id'));

        $products = Product::whereIn('id', $productIds)
            ->with(['category', 'brand', 'inventoryStocks'])
            ->get()
            ->keyBy('id');

        $variants = ProductVariant::whereIn('id', $variantIds)
            ->with(['product.category', 'product.brand', 'color', 'size', 'inventoryStocks'])
            ->get()
            ->keyBy('id');

        $labels = collect();

        foreach ($itemSelections as $idx => $item) {
            $type = $item['target_type'] ?? 'product';
            $id = (int) ($item['id'] ?? 0);
            $copies = max(1, (int) ($item['qty'] ?? 1));
            $useStock = ! empty($item['use_stock']);

            if ($type === 'variant') {
                /** @var ProductVariant|null $variant */
                $variant = $variants->get($id);
                if (! $variant) {
                    continue;
                }

                $parent = $variant->product;
                $hasBarcode = ! empty($variant->barcode);
                $barcode = $variant->barcode;
                $isPending = false;

                if (! $hasBarcode) {
                    if ($persistMissing) {
                        $barcode = $this->ensureVariantBarcode($variant);
                        $hasBarcode = true;
                    } else {
                        // Exclude from preview: only items with ready barcodes are displayed
                        continue;
                    }
                }

                if ($useStock) {
                    $variantStock = (int) $variant->inventory_stock;
                    $copies = max(1, $variantStock > 0 ? $variantStock : (int) $variant->qty);
                }

                $colorName = $variant->color?->name ?? $variant->color ?? '';
                $sizeName = $variant->size?->name ?? $variant->size ?? '';
                $specParts = array_filter([$variant->name, $colorName, $sizeName]);
                $spec = implode(' - ', array_unique($specParts)) ?: ('Variant #'.$variant->id);

                $itemSku = ($parent?->sku ?? ('PRD-'.$variant->product_id)).'-V'.$variant->id;
                $itemPrice = (float) ($variant->price > 0 ? $variant->price : ($parent?->price ?? 0));

                $svg = $barcode
                    ? $this->generator->getBarcodeSvg($barcode, 45, 1.4, 8)
                    : null;

                $labels->push([
                    'type' => 'variant',
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'company' => $companyName,
                    'parent_title' => $parent?->name ?? 'Product',
                    'title' => $parent?->name ?? 'Product',
                    'variant_spec' => $spec,
                    'sku' => $itemSku,
                    'barcode' => $barcode,
                    'original_barcode' => $barcode,
                    'has_barcode' => true,
                    'is_pending' => $isPending,
                    'barcode_svg' => $svg,
                    'price' => $itemPrice,
                    'currency' => 'kr.',
                    'copies' => $copies,
                    'category' => $parent?->category?->name ?? 'General',
                    'brand' => $parent?->brand?->name ?? '',
                    'stock' => (int) $variant->inventory_stock,
                ]);

            } else {
                /** @var Product|null $product */
                $product = $products->get($id);
                if (! $product) {
                    continue;
                }

                $hasBarcode = ! empty($product->barcode);
                $barcode = $product->barcode;
                $isPending = false;

                if (! $hasBarcode) {
                    if ($persistMissing) {
                        $barcode = $this->ensureProductBarcode($product);
                        $hasBarcode = true;
                    } else {
                        // Exclude from preview: only items with ready barcodes are displayed
                        continue;
                    }
                }

                if ($useStock) {
                    $copies = max(1, (int) $product->inventory_stock);
                }

                $svg = $barcode
                    ? $this->generator->getBarcodeSvg($barcode, 45, 1.4, 8)
                    : null;

                $labels->push([
                    'type' => 'product',
                    'id' => $product->id,
                    'product_id' => $product->id,
                    'company' => $companyName,
                    'parent_title' => $product->name,
                    'title' => $product->name,
                    'variant_spec' => null,
                    'sku' => $product->sku ?? $product->product_number ?? ('PRD-'.$product->id),
                    'barcode' => $barcode,
                    'original_barcode' => $barcode,
                    'has_barcode' => true,
                    'is_pending' => $isPending,
                    'barcode_svg' => $svg,
                    'price' => (float) $product->price,
                    'currency' => 'kr.',
                    'copies' => $copies,
                    'category' => $product->category?->name ?? 'General',
                    'brand' => $product->brand?->name ?? '',
                    'stock' => (int) $product->inventory_stock,
                ]);
            }
        }

        return $labels;
    }
}
