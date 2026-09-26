<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\DataTables\ReviewDataTable;
use App\Models\Review;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class ReviewController extends Controller
{
    /**
     * Check if reviews table exists
     */
    private function reviewsTableExists(): bool
    {
        try {
            return Schema::hasTable('reviews');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * View all reviews management page for admin
     */
    public function index(ReviewDataTable $dataTable)
    {
        if (!$this->reviewsTableExists()) {
            return redirect()->route('admin.dashboard')->with('error', 'Reviews table not found. Please run migration.');
        }

        $totalReviews = Review::count();
        $publishedReviews = Review::where('status', 1)->count();
        $hiddenReviews = Review::where('status', 0)->count();
        $averageRating = round((float) (Review::where('status', 1)->avg('rating') ?? 0), 2);
        $fiveStarCount = Review::where('rating', 5)->count();

        // Categories that have reviews, ordered by latest review activity
        $categories = Category::whereHas('products.reviews')
            ->select('categories.id', 'categories.name')
            ->join('products', 'products.category_id', '=', 'categories.id')
            ->join('reviews', 'reviews.product_id', '=', 'products.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc(\Illuminate\Support\Facades\DB::raw('MAX(reviews.created_at)'))
            ->get();

        // Products that have reviews, ordered by latest review activity
        $productsWithReviews = Product::select('products.id', 'products.name', 'products.sku', 'products.product_number', 'products.category_id')
            ->whereHas('reviews')
            ->withMax('reviews', 'created_at')
            ->orderByDesc('reviews_max_created_at')
            ->get();

        return $dataTable->render('backend.reviews.index', compact(
            'totalReviews',
            'publishedReviews',
            'hiddenReviews',
            'averageRating',
            'fiveStarCount',
            'categories',
            'productsWithReviews'
        ));
    }

    /**
     * Get single review details for modal
     */
    public function show(Review $review)
    {
        $review->load(['product.category', 'user']);

        return response()->json([
            'status' => 'success',
            'review' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment ?: 'No written comment provided.',
                'status' => (bool) $review->status,
                'created_at' => $review->created_at->format('M d, Y h:i A'),
                'created_ago' => $review->created_at->diffForHumans(),
                'product' => [
                    'id' => $review->product?->id,
                    'name' => $review->product?->name ?? 'Product Deleted',
                    'sku' => $review->product?->sku ?? $review->product?->product_number ?? 'N/A',
                    'category' => $review->product?->category?->name ?? 'N/A',
                    'thumb_image' => (!empty($review->product?->thumb_image) && (str_starts_with($review->product->thumb_image, 'http') || file_exists(public_path($review->product->thumb_image))))
                        ? (str_starts_with($review->product->thumb_image, 'http') ? $review->product->thumb_image : asset($review->product->thumb_image))
                        : asset('uploads/no-image.svg'),
                    'url' => $review->product ? route('product.details', $review->product->slug) : '#',
                ],
                'user' => [
                    'id' => $review->user?->id,
                    'name' => $review->user?->name ?? 'Deleted User',
                    'email' => $review->user?->email ?? 'N/A',
                    'outlet_name' => $review->user?->outlet_name ?? 'Standard Customer',
                    'phone' => $review->user?->phone ?? 'N/A',
                ],
            ]
        ]);
    }

    /**
     * Change review publication status (Published / Hidden)
     */
    public function changeStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:reviews,id',
            'status' => 'required',
        ]);

        $review = Review::findOrFail($request->id);
        $review->status = filter_var($request->status, FILTER_VALIDATE_BOOLEAN);
        $review->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Review status updated successfully!')
        ]);
    }

    /**
     * Store or update a review/rating (Customer)
     */
    public function store(Request $request)
    {
        if (!$this->reviewsTableExists()) {
            return response()->json(['status' => 'error', 'message' => 'Reviews system not initialized.'], 503);
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
                $existingReview->update([
                    'rating' => $validated['rating'],
                    'comment' => $validated['comment'] ?? null,
                    'status' => 1,
                ]);
                return response()->json(['status' => 'success', 'message' => 'Review updated successfully', 'data' => $existingReview], 200);
            }

            $review = Review::create([
                'product_id' => $validated['product_id'],
                'user_id' => $userId,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'status' => 1,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Review added successfully', 'data' => $review], 201);
        } catch (QueryException $e) {
            return response()->json(['status' => 'error', 'message' => 'Database error saving review.'], 503);
        }
    }

    /**
     * Get all published reviews for a product with rating-wise breakdown
     */
    public function getProductReviews($productId, Request $request)
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
            
            // Base query: published reviews for this product
            $baseQuery = Review::where('product_id', $productId)->where('status', 1);

            $totalPublished = (clone $baseQuery)->count();
            $averageRating = round((float) ((clone $baseQuery)->avg('rating') ?? 0), 1);

            // Rating-wise breakdown calculation (5, 4, 3, 2, 1 stars)
            $counts = (clone $baseQuery)
                ->selectRaw('rating, count(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray();

            $breakdown = [];
            for ($star = 5; $star >= 1; $star--) {
                $starCount = (int) ($counts[$star] ?? 0);
                $percentage = $totalPublished > 0 ? round(($starCount / $totalPublished) * 100, 1) : 0;
                $breakdown[$star] = [
                    'star' => $star,
                    'count' => $starCount,
                    'percentage' => $percentage,
                ];
            }

            // Apply optional rating filter if supplied
            $reviewQuery = (clone $baseQuery)->with('user');
            if ($request->filled('rating') && in_array((int) $request->rating, [1, 2, 3, 4, 5], true)) {
                $reviewQuery->where('rating', (int) $request->rating);
            }

            $reviews = $reviewQuery->latest('created_at')
                ->get()
                ->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'user' => $review->user?->name ?? 'Verified Buyer',
                        'outlet' => $review->user?->outlet_name ?? null,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at->diffForHumans(),
                        'created_date' => $review->created_at->format('M d, Y'),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'product' => ['id' => $product->id, 'name' => $product->name],
                'average_rating' => $averageRating,
                'total_reviews' => $totalPublished,
                'breakdown' => $breakdown,
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
     * Delete a review (Admin can delete any review, User can delete own review)
     */
    public function destroy($reviewId)
    {
        if (!$this->reviewsTableExists()) {
            return response()->json(['status' => 'error', 'message' => 'Reviews system not initialized.'], 503);
        }

        try {
            $review = Review::findOrFail($reviewId);
            $currentUser = Auth::user();

            $isStaffOrAdmin = $currentUser && ($currentUser->isStaff() || $currentUser->hasRole(['Admin', 'Super Admin']));

            // Ensure non-staff user can only delete their own review
            if (!$isStaffOrAdmin && $review->user_id !== Auth::id()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            $review->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('Deleted Successfully!')
            ]);
        } catch (QueryException $e) {
            return response()->json(['status' => 'error', 'message' => 'Database error.'], 503);
        }
    }

    /**
     * Get best rated products (for reporting)
     */
    public function bestRatedProducts()
    {
        if (!$this->reviewsTableExists()) {
            return response()->json(['status' => 'success', 'products' => []], 200);
        }

        try {
            $products = Product::where('status', 1)
                ->with(['reviews' => function ($query) {
                    $query->where('status', 1)->select('product_id', 'rating');
                }, 'category:id,name'])
                ->get()
                ->map(function ($product) {
                    $averageRating = $product->reviews->avg('rating') ?? 0;
                    $reviewCount = $product->reviews->count();
                    
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku ?? $product->product_number,
                        'category' => $product->category->name ?? 'N/A',
                        'average_rating' => round($averageRating, 2),
                        'total_reviews' => $reviewCount,
                    ];
                })
                ->filter(fn($p) => $p['total_reviews'] > 0)
                ->sortByDesc('average_rating')
                ->values()
                ->take(20);

            return response()->json(['status' => 'success', 'products' => $products], 200);
        } catch (QueryException $e) {
            return response()->json(['status' => 'success', 'products' => []], 200);
        }
    }
}
