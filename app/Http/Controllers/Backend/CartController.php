<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Get cart count for current user
     */
    public function getCount(Request $request)
    {
        $cartType = $request->get('cart_type', 'booking'); // booking or request
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
                    ->with(['product.category', 'vendor'])
                    ->get();
        
        // Normalize image paths for frontend
        $items->each(function($item) {
            if ($item->product) {
                $imagePath = $item->product->thumb_image;
                $item->product->thumb_image = (strpos($imagePath, 'http') === 0) 
                    ? $imagePath 
                    : (file_exists(public_path($imagePath)) 
                        ? asset($imagePath) 
                        : asset('storage/' . $imagePath));
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
     * Add or Remove product to/from cart (Toggle)
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'cart_type' => 'required|in:booking,request'
        ]);

        $cartType = $validated['cart_type'];
        $productId = $validated['product_id'];
        $forceClear = $request->get('force_clear', false);

        // Get the product with its vendor
        $product = Product::find($productId);
        $productVendorId = $product->vendor_id;

        // Check if product already in cart
        $cartItem = Cart::where('user_id', Auth::id())
                       ->where('product_id', $productId)
                       ->where('cart_type', $cartType)
                       ->first();

        if ($cartItem) {
            // Product already in cart, remove it
            $cartItem->delete();
            
            $count = Cart::where('user_id', Auth::id())
                        ->where('cart_type', $cartType)
                        ->count();

            return response()->json([
                'success' => true,
                'message' => 'Removed from ' . ucfirst($cartType) . ' basket',
                'count' => $count,
                'action' => 'removed'
            ]);
        } else {
            // Check for vendor conflict if cart has items and force_clear is not set
            $existingCartItems = Cart::where('user_id', Auth::id())
                                    ->where('cart_type', $cartType)
                                    ->get();

            if ($existingCartItems->count() > 0 && !$forceClear) {
                $existingVendorId = $existingCartItems->first()->vendor_id;
                
                // If vendor is different, return warning
                if ($existingVendorId != $productVendorId) {
                    $existingVendor = $existingCartItems->first()->vendor;
                    $newVendor = $product->vendor;
                    
                    return response()->json([
                        'success' => false,
                        'vendor_conflict' => true,
                        'message' => 'This will replace products from "' . ($existingVendor->shop_name ?? 'Unknown Vendor') . '" with products from "' . ($newVendor->shop_name ?? 'No Vendor') . '". Continue?',
                        'existing_vendor_id' => $existingVendorId,
                        'new_vendor_id' => $productVendorId,
                        'existing_vendor_name' => $existingVendor->shop_name ?? 'Unknown Vendor',
                        'new_vendor_name' => $newVendor->shop_name ?? 'No Vendor'
                    ]);
                }
            }

            // Clear existing cart items only if force_clear is true (vendor conflict confirmation)
            if ($forceClear && $existingCartItems->count() > 0) {
                Cart::where('user_id', Auth::id())
                   ->where('cart_type', $cartType)
                   ->delete();
            }

            // Add new item to cart with vendor_id
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $productId,
                'cart_type' => $cartType,
                'vendor_id' => $productVendorId
            ]);
            
            $count = Cart::where('user_id', Auth::id())
                        ->where('cart_type', $cartType)
                        ->count();

            return response()->json([
                'success' => true,
                'message' => 'Added to ' . ucfirst($cartType) . ' basket',
                'count' => $count,
                'action' => 'added',
                'vendor_id' => $productVendorId
            ]);
        }
    }

    /**
     * Remove product from cart
     */
    public function remove(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'cart_type' => 'required|in:booking,request'
        ]);

        Cart::where('user_id', Auth::id())
           ->where('product_id', $validated['product_id'])
           ->where('cart_type', $validated['cart_type'])
           ->delete();

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
     * Get cart items as product IDs (for booking/request creation)
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
     * Get vendor from cart items (for booking page)
     */
    public function getVendor(Request $request)
    {
        $cartType = $request->get('cart_type', 'booking');
        
        $cartItem = Cart::where('user_id', Auth::id())
                       ->where('cart_type', $cartType)
                       ->first();

        return response()->json([
            'vendor_id' => $cartItem ? $cartItem->vendor_id : null,
            'vendor_name' => $cartItem && $cartItem->vendor ? $cartItem->vendor->shop_name : null
        ]);
    }
}
