<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'variant_id',
        'qty',
        'unit_cost',
        'total',
        'variant_info'
    ];

    protected $casts = [
        'variant_info' => 'array'
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
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