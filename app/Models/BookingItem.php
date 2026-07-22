<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Booking;
use App\Models\Product;
use App\Models\ProductVariant;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id',
        'product_id',
        'variant_id',
        'quantity',
        'unit_price',
        'total_price'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
