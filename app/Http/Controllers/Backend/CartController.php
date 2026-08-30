<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Get unified cart state (both booking and request IDs + counts + full items with variants)
     */
    public function getAllState()
    {
        $userId = Auth::id();
        $allCarts = Cart::where('user_id', $userId)
            ->whereIn('cart_type', ['booking', 'request'])
            ->with(['product.category', 'vendor', 'variant.color', 'variant.size'])
            ->get();

        $bookingItems = $allCarts->where('cart_type', 'booking')->values();
        $requestItems = $allCarts->where('cart_type', 'request')->values();

        $formatter = function($item) {
            $p = $item->product;
            $image = asset('uploads/no-image.svg');
            if ($p && $p->thumb_image) {
                $imagePath = $p->thumb_image;
                if (strpos($imagePath, 'http') === 0) {
                    $image = $imagePath;
                } elseif (file_exists(public_path($imagePath))) {
                    $image = asset($imagePath);
                } elseif (file_exists(storage_path('app/public/' . $imagePath))) {
                    $image = asset('storage/' . $imagePath);
                } else {
                    $image = asset('uploads/no-image.svg');
                }
            }

            $variantName = '';
            if ($item->variant) {
                $parts = [];
                if ($item->variant->name) {
                    $parts[] = $item->variant->name;
                } else {
                    if (optional($item->variant->color)->name) $parts[] = $item->variant->color->name;
                    if (optional($item->variant->size)->name) $parts[] = $item->variant->size->name;
                }
                $variantName = implode(' - ', $parts);
            }

            $unitPrice = $item->variant 
                ? (float) ($item->variant->price ?: ($p ? ($p->purchase_price ?: $p->price) : 0))
                : (float) ($p ? ($p->purchase_price ?: $p->price) : 0);

            return [
                'id' => $item->id,
                'cart_id' => $item->id,
                'product_id' => (int) $item->product_id,
                'variant_id' => $item->variant_id ? (int) $item->variant_id : null,
                'variant_name' => $variantName,
                'product_name' => $p ? $p->name : 'Product',
                'thumb_image' => $image,
                'vendor_name' => $item->vendor ? $item->vendor->shop_name : 'Primary Supplier',
                'sku' => $p ? ($p->sku ?? '') : '',
                'price' => $unitPrice,
                'quantity' => (float) ($item->quantity ?: 1)
            ];
        };

        $bookingData = $bookingItems->map($formatter)->values();
        $requestData = $requestItems->map($formatter)->values();

        return response()->json([
            'booking' => [
                'ids' => $bookingItems->pluck('product_id')->unique()->map(fn($id) => (int)$id)->values()->toArray(),
                'count' => $bookingItems->count(),
                'items' => $bookingData
            ],
            'request' => [
                'ids' => $requestItems->pluck('product_id')->unique()->map(fn($id) => (int)$id)->values()->toArray(),
                'count' => $requestItems->count(),
                'items' => $requestData
            ]
        ]);
    }

    /**
     * Get cart count for current user
     */
    public function getCount(Request $request)
    {
        $cartType = $request->get('cart_type', 'booking');
        $count = Cart::where('user_id', Auth::id())
                    ->where('cart_type', $cartType)
                    ->count();
        
        return response()->json(['count' => $count, 'cart_type' => $cartType]);
    }

    /**
     * Get all cart items for current user
     */
    public function getItems(Request $request)
    {
        $cartType = $request->get('cart_type', 'booking');
        $items = Cart::where('user_id', Auth::id())
                    ->where('cart_type', $cartType)
                    ->with(['product.category', 'vendor', 'variant.color', 'variant.size'])
                    ->get();
        
        // Normalize image paths for frontend
        $items->each(function($item) {
            if ($item->product) {
                $imagePath = $item->product->thumb_image;
                if (empty($imagePath)) {
                    $item->product->thumb_image = asset('uploads/no-image.svg');
                } elseif (strpos($imagePath, 'http') === 0) {
                    $item->product->thumb_image = $imagePath;
                } elseif (file_exists(public_path($imagePath))) {
                    $item->product->thumb_image = asset($imagePath);
                } elseif (file_exists(storage_path('app/public/' . $imagePath))) {
                    $item->product->thumb_image = asset('storage/' . $imagePath);
                } else {
                    $item->product->thumb_image = asset('uploads/no-image.svg');
                }
            }
        });
        
        return response()->json([
            'items' => $items,
            'count' => $items->count(),
            'product_ids' => $items->pluck('product_id')->toArray(),
            'vendor_id' => $items->first()->vendor_id ?? null
        ]);
    }

    /**
     * Add, Remove, Toggle, or Bulk Add products / variants to cart with explicit action intent
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'cart_type' => 'required|in:booking,request',
            'action' => 'nullable|in:add,remove,toggle,bulk_variants',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'nullable|numeric|min:0.01',
            'is_bulk_variants' => 'nullable|boolean',
            'variants' => 'nullable|array',
            'variants.*.variant_id' => 'required_with:variants|exists:product_variants,id',
            'variants.*.quantity' => 'required_with:variants|numeric|min:0.01'
        ]);

        $cartType = $validated['cart_type'];
        $productId = (int) $validated['product_id'];
        $variantId = !empty($validated['variant_id']) ? (int) $validated['variant_id'] : null;
        $action = $request->input('action', 'toggle');
        $isBulkVariants = $request->boolean('is_bulk_variants', false) || $action === 'bulk_variants' || $request->has('variants');

        // Get product and vendor
        $product = Product::with(['variants.color', 'variants.size', 'vendor'])->findOrFail($productId);
        $productVendorId = $product->vendor_id;

        // 1. Handle Bulk Variants Addition (from Variant Modal)
        if ($isBulkVariants) {
            // Remove previous variants for this product to prevent stale / duplicate entries
            Cart::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->where('cart_type', $cartType)
                ->delete();

            $addedItems = [];
            $variantsInput = $request->input('variants', []);
            if (is_array($variantsInput)) {
                foreach ($variantsInput as $vItem) {
                    $vId = isset($vItem['variant_id']) ? (int) $vItem['variant_id'] : 0;
                    $vQty = isset($vItem['quantity']) ? (float) $vItem['quantity'] : 0;

                    if ($vId > 0 && $vQty > 0) {
                        $cartRow = Cart::create([
                            'user_id' => Auth::id(),
                            'product_id' => $productId,
                            'variant_id' => $vId,
                            'cart_type' => $cartType,
                            'vendor_id' => $productVendorId,
                            'quantity' => $vQty
                        ]);
                        $addedItems[] = $cartRow;
                    }
                }
            }

            $count = Cart::where('user_id', Auth::id())->where('cart_type', $cartType)->count();

            return response()->json([
                'success' => true,
                'message' => count($addedItems) > 0 ? 'Updated ' . count($addedItems) . ' variant(s) in ' . ucfirst($cartType) . ' basket' : 'Removed from ' . ucfirst($cartType) . ' basket',
                'count' => $count,
                'action' => count($addedItems) > 0 ? 'bulk_added' : 'removed',
                'in_cart' => count($addedItems) > 0,
                'cart_type' => $cartType,
                'product_id' => $productId
            ]);
        }

        // 2. Single variant or simple product handling
        $query = Cart::where('user_id', Auth::id())
                    ->where('product_id', $productId)
                    ->where('cart_type', $cartType);

        if ($variantId) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }

        $cartItem = $query->first();

        // Determine final operation based on explicit action or toggle
        $shouldRemove = ($action === 'remove') || ($action === 'toggle' && $cartItem);

        if ($shouldRemove) {
            if ($cartItem) {
                $cartItem->delete();
            }
            
            $count = Cart::where('user_id', Auth::id())
                        ->where('cart_type', $cartType)
                        ->count();

            $remainingProductItems = Cart::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->where('cart_type', $cartType)
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Removed from ' . ucfirst($cartType) . ' basket',
                'count' => $count,
                'action' => 'removed',
                'in_cart' => ($remainingProductItems > 0),
                'cart_type' => $cartType,
                'product_id' => $productId,
                'variant_id' => $variantId
            ]);
        } else {
            // Add or Ensure present
            $quantity = !empty($validated['quantity']) ? (float) $validated['quantity'] : 1;

            if ($cartItem) {
                // If it already existed and action is 'add', ensure quantity
                $cartItem->quantity = $quantity;
                $cartItem->vendor_id = $productVendorId;
                $cartItem->save();
                $finalCart = $cartItem;
            } else {
                $finalCart = Cart::create([
                    'user_id' => Auth::id(),
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'cart_type' => $cartType,
                    'vendor_id' => $productVendorId,
                    'quantity' => $quantity
                ]);
            }
            
            $count = Cart::where('user_id', Auth::id())
                        ->where('cart_type', $cartType)
                        ->count();

            $imagePath = $product->thumb_image;
            if (empty($imagePath)) {
                $image = asset('uploads/no-image.svg');
            } elseif (strpos($imagePath, 'http') === 0) {
                $image = $imagePath;
            } elseif (file_exists(public_path($imagePath))) {
                $image = asset($imagePath);
            } elseif (file_exists(storage_path('app/public/' . $imagePath))) {
                $image = asset('storage/' . $imagePath);
            } else {
                $image = asset('uploads/no-image.svg');
            }

            $variantObj = $variantId ? ProductVariant::with(['color', 'size'])->find($variantId) : null;
            $variantName = '';
            if ($variantObj) {
                $parts = [];
                if ($variantObj->name) {
                    $parts[] = $variantObj->name;
                } else {
                    if (optional($variantObj->color)->name) $parts[] = $variantObj->color->name;
                    if (optional($variantObj->size)->name) $parts[] = $variantObj->size->name;
                }
                $variantName = implode(' - ', $parts);
            }

            return response()->json([
                'success' => true,
                'message' => 'Added to ' . ucfirst($cartType) . ' basket',
                'count' => $count,
                'action' => 'added',
                'in_cart' => true,
                'cart_type' => $cartType,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'vendor_id' => $productVendorId,
                'item' => [
                    'id' => $finalCart->id,
                    'cart_id' => $finalCart->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'variant_name' => $variantName,
                    'product_name' => $product->name,
                    'thumb_image' => $image,
                    'vendor_name' => $product->vendor ? $product->vendor->shop_name : 'Primary Supplier',
                    'sku' => $product->sku ?? '',
                    'price' => (float) ($variantObj ? ($variantObj->price ?: ($product->purchase_price ?: $product->price)) : ($product->purchase_price ?: $product->price)),
                    'quantity' => $quantity
                ]
            ]);
        }
    }

    /**
     * Update Quantity for a Cart Item (by cart_id or product_id + variant_id)
     */
    public function updateQuantity(Request $request)
    {
        $validated = $request->validate([
            'cart_id' => 'nullable|exists:carts,id',
            'product_id' => 'required_without:cart_id|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'cart_type' => 'required|in:booking,request',
            'quantity' => 'required|numeric|min:0.01'
        ]);

        $query = Cart::where('user_id', Auth::id())
            ->where('cart_type', $validated['cart_type']);

        if (!empty($validated['cart_id'])) {
            $query->where('id', (int) $validated['cart_id']);
        } else {
            $query->where('product_id', (int) $validated['product_id']);
            if (!empty($validated['variant_id'])) {
                $query->where('variant_id', (int) $validated['variant_id']);
            } else {
                $query->whereNull('variant_id');
            }
        }

        $query->update(['quantity' => (float) $validated['quantity']]);

        return response()->json([
            'success' => true,
            'quantity' => (float) $validated['quantity']
        ]);
    }

    /**
     * Remove product / variant from cart
     */
    public function remove(Request $request)
    {
        $validated = $request->validate([
            'cart_id' => 'nullable|exists:carts,id',
            'product_id' => 'required_without:cart_id|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'cart_type' => 'required|in:booking,request'
        ]);

        $query = Cart::where('user_id', Auth::id())
            ->where('cart_type', $validated['cart_type']);

        if (!empty($validated['cart_id'])) {
            $query->where('id', (int) $validated['cart_id']);
        } else {
            $query->where('product_id', (int) $validated['product_id']);
            if (!empty($validated['variant_id'])) {
                $query->where('variant_id', (int) $validated['variant_id']);
            } else {
                $query->whereNull('variant_id');
            }
        }

        $query->delete();

        $count = Cart::where('user_id', Auth::id())
                    ->where('cart_type', $validated['cart_type'])
                    ->count();

        return response()->json([
            'success' => true,
            'message' => 'Removed from ' . ucfirst($validated['cart_type']) . ' basket',
            'count' => $count
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clear(Request $request)
    {
        $cartType = $request->get('cart_type', 'booking');
        
        Cart::where('user_id', Auth::id())
           ->where('cart_type', $cartType)
           ->delete();

        return response()->json([
            'success' => true,
            'message' => ucfirst($cartType) . ' basket cleared',
            'count' => 0
        ]);
    }

    /**
     * Get cart items as product IDs
     */
    public function getProductIds(Request $request)
    {
        $cartType = $request->get('cart_type', 'booking');
        
        $items = Cart::where('user_id', Auth::id())
                  ->where('cart_type', $cartType)
                  ->get();

        return response()->json([
            'ids' => $items->pluck('product_id')->toArray(),
            'count' => $items->count(),
            'vendor_id' => $items->first()->vendor_id ?? null
        ]);
    }

    /**
     * Bulk Add Multiple Products by IDs
     */
    public function bulkAddProducts(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|integer|exists:products,id',
            'cart_type' => 'required|in:booking,request'
        ]);

        $cartType = $validated['cart_type'];
        $productIds = array_unique(array_map('intval', $validated['product_ids']));
        $userId = Auth::id();

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $addedCount = 0;
        foreach ($productIds as $pId) {
            $p = $products->get($pId);
            if (!$p) continue;

            $exists = Cart::where('user_id', $userId)
                ->where('product_id', $pId)
                ->where('cart_type', $cartType)
                ->exists();

            if (!$exists) {
                Cart::create([
                    'user_id' => $userId,
                    'product_id' => $pId,
                    'variant_id' => null,
                    'cart_type' => $cartType,
                    'vendor_id' => $p->vendor_id,
                    'quantity' => 1
                ]);
                $addedCount++;
            }
        }

        $totalCount = Cart::where('user_id', $userId)->where('cart_type', $cartType)->count();
        $allProductIds = Cart::where('user_id', $userId)->where('cart_type', $cartType)->pluck('product_id')->unique()->map(fn($id) => (int)$id)->values()->toArray();

        return response()->json([
            'success' => true,
            'message' => "Added {$addedCount} product(s) to " . ucfirst($cartType) . " basket",
            'count' => $totalCount,
            'product_ids' => $allProductIds
        ]);
    }
}
