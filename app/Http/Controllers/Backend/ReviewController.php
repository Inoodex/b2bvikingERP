<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class ReviewController extends Controller
{
    /**
     * Check if reviews table exists
     */
    private function reviewsTableExists()
    {
        try {
            return Schema::hasTable('reviews');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * View all reviews page
     */
    public function index()
    {
        if (!$this->reviewsTableExists()) {
            return redirect()->route('admin.dashboard')->with('error', 'Reviews table not found. Please run migration.');
        }
        return view('backend.reviews.index');
    }

    /**
     * Store a new review/rating
     */
    public function store(Request $request)
    {
        if (!$this->reviewsTableExists()) {
            return response()->json(['status' => 'error', 'message' => 'Reviews system not initialized. Please run migration.'], 503);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();

        try {
            // Check if user already reviewed this product
            $existingReview = Review::where('product_id', $validated['product_id'])
                ->where('user_id', $userId)
                ->first();

            if ($existingReview) {
                // Update existing review
                $existingReview->update([
                    'rating' => $validated['rating'],
                    'comment' => $validated['comment'] ?? null,
                ]);
                return response()->json(['status' => 'success', 'message' => 'Review updated successfully', 'data' => $existingReview], 200);
            }

            // Create new review
            $review = Review::create([
                'product_id' => $validated['product_id'],
                'user_id' => $userId,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Review added successfully', 'data' => $review], 201);
        } catch (QueryException $e) {
            return response()->json(['status' => 'error', 'message' => 'Database error. Please check if reviews table exists.'], 503);
        }
    }

    /**
     * Get all reviews for a product
     */
    public function getProductReviews($productId)
    {
        if (!$this->reviewsTableExists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reviews system not initialized.',
                'reviews' => []
            ], 503);
        }

        try {
            $product = Product::findOrFail($productId);
            
            $reviews = Review::forProduct($productId)
                ->with('user')
                ->get()
                ->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'user' => $review->user->name,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at->diffForHumans(),
                    ];
                });

            // Calculate average rating
            $averageRating = Review::forProduct($productId)->avg('rating') ?? 0;
            $totalReviews = Review::forProduct($productId)->count();

            return response()->json([
                'status' => 'success',
                'product' => ['id' => $product->id, 'name' => $product->name],
                'average_rating' => round($averageRating, 2),
                'total_reviews' => $totalReviews,
                'reviews' => $reviews,
            ]);
        } catch (QueryException $e) {
            return response()->json(['status' => 'error', 'message' => 'Database error.'], 503);
        }
    }

    /**
     * Get current user's review for a product
     */
    public function getUserProductReview($productId)
    {
        if (!$this->reviewsTableExists()) {
            return response()->json(['status' => 'success', 'review' => null], 200);
        }

        try {
            $userId = Auth::id();
            
            $review = Review::where('product_id', $productId)
                ->where('user_id', $userId)
                ->first();

            if (!$review) {
                return response()->json(['status' => 'success', 'review' => null], 200);
            }

            return response()->json([
                'status' => 'success',
                'review' => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                ],
            ]);
        } catch (QueryException $e) {
            return response()->json(['status' => 'success', 'review' => null], 200);
        }
    }

    /**
     * Delete a review
     */
    public function destroy($reviewId)
    {
        if (!$this->reviewsTableExists()) {
            return response()->json(['status' => 'error', 'message' => 'Reviews system not initialized.'], 503);
        }

        try {
            $review = Review::findOrFail($reviewId);

            // Ensure user can only delete their own review
            if ($review->user_id !== Auth::id()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            $review->delete();
            return response()->json(['status' => 'success', 'message' => 'Review deleted successfully']);
        } catch (QueryException $e) {
            return response()->json(['status' => 'error', 'message' => 'Database error.'], 503);
        }
    }

    /**
     * Get best rated products (for report)
     */
    public function bestRatedProducts()
    {
        if (!$this->reviewsTableExists()) {
            return response()->json(['status' => 'success', 'products' => []], 200);
        }

        try {
            $products = Product::where('status', 1)
                ->with(['reviews' => function ($query) {
                    $query->select('product_id', 'rating');
                }])
                ->get()
                ->map(function ($product) {
                    $averageRating = $product->reviews->avg('rating') ?? 0;
                    $reviewCount = $product->reviews->count();
                    
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'category' => $product->category->name ?? 'N/A',
                        'average_rating' => round($averageRating, 2),
                        'total_reviews' => $reviewCount,
                    ];
                })
                ->filter(fn($p) => $p['total_reviews'] > 0) // Only products with reviews
                ->sortByDesc('average_rating')
                ->values()
                ->take(20); // Top 20

            return response()->json(['status' => 'success', 'products' => $products], 200);
        } catch (QueryException $e) {
            return response()->json(['status' => 'success', 'products' => []], 200);
        }
    }
}
