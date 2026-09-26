<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\HandlingUnit;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BarcodeScanController extends Controller
{
    /**
     * Resolve a public scan of a 1D/2D Barcode or QR Code.
     * Supports products, product variants, and handling units (cartons/pallets).
     */
    public function resolveScan(Request $request, string $code): View|RedirectResponse
    {
        $cleanCode = trim($code);

        // 1. Check Product Barcode / SKU / Product Number
        $product = Product::where('barcode', $cleanCode)
            ->orWhere('sku', $cleanCode)
            ->orWhere('product_number', $cleanCode)
            ->first();

        if ($product) {
            if (auth()->check() && !empty($product->slug)) {
                return redirect()->route('product.details', $product->slug);
            }

            $product->loadMissing(['category', 'brand', 'variants']);

            return view('frontend.products.public_scan', [
                'product' => $product,
                'selectedVariant' => null,
            ]);
        }

        // 2. Check Product Variant Barcode
        $variant = ProductVariant::with('product')
            ->where('barcode', $cleanCode)
            ->first();

        if ($variant && $variant->product) {
            if (auth()->check() && !empty($variant->product->slug)) {
                return redirect()->route('product.details', [
                    'slug' => $variant->product->slug,
                    'variant' => $variant->id,
                ]);
            }

            $variant->product->loadMissing(['category', 'brand', 'variants']);

            return view('frontend.products.public_scan', [
                'product' => $variant->product,
                'selectedVariant' => $variant,
            ]);
        }

        // 3. Check Handling Unit (Carton / Box / Pallet)
        $handlingUnit = HandlingUnit::with([
            'packagingType',
            'parentUnit.packagingType',
            'childUnits.packagingType',
            'childUnits.items.product',
            'items.product.category',
            'items.variant',
        ])->where('hu_code', $cleanCode)->first();

        if ($handlingUnit) {
            return view('frontend.packaging_units.public_scan', compact('handlingUnit'));
        }

        // 4. Not Found Fallback
        return view('frontend.packaging_units.not_found', [
            'scannedCode' => $cleanCode,
        ]);
    }
}
