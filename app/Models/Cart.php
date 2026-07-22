<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'product_id', 'variant_id', 'cart_type', 'vendor_id', 'quantity'];

    /**
     * Get the user that owns the cart item
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product in the cart
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the selected variant in the cart item (if any)
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Get the vendor of the cart item
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
