<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $fillable = ['user_id', 'product_id'];

    /**
     * Get the user that owns this wishlist item
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product in the wishlist
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
