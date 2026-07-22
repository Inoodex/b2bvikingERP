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
    ];

    protected $casts = [
        'rating' => 'integer',
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
     * Scope: Get reviews for a specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId)->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Get reviews by a specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId)->orderBy('created_at', 'desc');
    }
}
