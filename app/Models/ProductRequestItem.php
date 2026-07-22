<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_request_id',
        'product_id',
        'variant_id',
        'qty',
        'unit_price',
        'subtotal'
    ];

    public function productRequest()
    {
        return $this->belongsTo(ProductRequest::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
