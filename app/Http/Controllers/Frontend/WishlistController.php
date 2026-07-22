<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Display the wishlist page with all wished products
     */
    public function index()
    {
        $wishlistItems = Wishlist::where('user_id', Auth::id())
            ->with(['product.category', 'product.variants.color', 'product.variants.size'])
            ->get()
            ->map(function ($item) {
                if (!$item->product) return null;

                $product   = $item->product;
                $imagePath = $product->thumb_image;
                $imageUrl  = (strpos($imagePath, 'http') === 0)
                    ? $imagePath
                    : (file_exists(public_path($imagePath))
                        ? asset($imagePath)
                        : asset('storage/' . $imagePath));

                // Format variants
                $variants = $product->variants ? $product->variants->map(function ($v) use ($product) {
                    $colorRelation = $v->getRelation('color');
                    $sizeRelation = $v->getRelation('size');
                    $colorName = trim((string) (is_object($colorRelation) ? ($colorRelation->name ?? '') : ($v->color ?? '')));
                    $sizeName = trim((string) (is_object($sizeRelation) ? ($sizeRelation->name ?? '') : ($v->size ?? '')));
                    $label = trim((string) ($v->name ?? ''));
                    if ($label === '') {
                        $label = trim(implode(' ', array_filter([$colorName, $sizeName])));
                    }
                    if ($label === '') {
                        $label = 'Variant #' . $v->id;
                    }

                    return [
                        'id'              => $v->id,
                        'product_id'      => $v->product_id,
                        'name'            => $label,
                        'color'           => $colorName,
                        'size'            => $sizeName,
                        'stock'           => (int)$v->inventory_stock,
                        'price'           => (float) ($v->price ?: $product->price ?: 0),
                        'outlet_price'    => (float) ($v->outlet_price ?: $v->price ?: $product->outlet_price ?: $product->price ?: 0),
                    ];
                })->toArray() : [];

                return [
                    'id'         => $product->id,
                    'name'       => $product->name,
                    'slug'       => $product->slug,
                    'price'      => $product->price,
                    'outlet_price' => $product->outlet_price,
                    'minimum_order_qty' => (int) ($product->minimum_order_qty ?? 1),
                    'image'      => $imageUrl,
                    'category'   => $product->category->name ?? 'General',
                    'variants'   => $variants,
                ];
            })
            ->filter()
            ->values();

        $settings = \App\Models\GeneralSetting::first() ?? (object)['currency_icon' => '$'];

        return view('frontend.pages.wishlist', compact('wishlistItems', 'settings'));
    }

    /**
     * Toggle a product in the wishlist (add/remove)
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = $validated['product_id'];

        $existing = Wishlist::where('user_id', Auth::id())
                            ->where('product_id', $productId)
                            ->first();

        if ($existing) {
            $existing->delete();
            $wishlisted = false;
            $message    = 'Removed from wishlist';
        } else {
            Wishlist::create([
                'user_id'    => Auth::id(),
                'product_id' => $productId,
            ]);
            $wishlisted = true;
            $message    = 'Added to wishlist';
        }

        $count = Wishlist::where('user_id', Auth::id())->count();

        return response()->json([
            'success'    => true,
            'wishlisted' => $wishlisted,
            'count'      => $count,
            'message'    => $message,
        ]);
    }

    /**
     * Get all wishlisted product IDs for the current user
     */
    public function getIds()
    {
        $ids   = Wishlist::where('user_id', Auth::id())->pluck('product_id')->toArray();
        $count = count($ids);

        return response()->json([
            'ids'   => $ids,
            'count' => $count,
        ]);
    }

    /**
     * Clear all wishlist items for the current user
     */
    public function clearAll()
    {
        Wishlist::where('user_id', Auth::id())->delete();
        
        $count = Wishlist::where('user_id', Auth::id())->count();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist cleared',
            'count'   => $count,
        ]);
    }
}
