<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'comment',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Review belongs to Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relationship: Review belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Only published / active reviews
     */
    public function scopePublished($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope: Filter by rating
     */
    public function scopeRating($query, $rating)
    {
        if ($rating) {
            return $query->where('rating', (int) $rating);
        }
        return $query;
    }

    /**
     * Scope: Get reviews for a specific product
     */
    public function scopeForProduct($query, $productId, bool $onlyPublished = false)
    {
        $query->where('product_id', $productId);
        if ($onlyPublished) {
            $query->where('status', 1);
        }
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Get reviews by a specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId)->orderBy('created_at', 'desc');
    }
}
