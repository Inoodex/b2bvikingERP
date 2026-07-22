<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_no',
        'vendor_id',
        'product_id',
        'category_id',
        'sub_category_id',
        'child_category_id',
        'unit_id',
        'qty',
        'unit_price',
        'extra_cost',
        'total_cost',
        'sale_price',
        'description',
        'min_inventory_qty',
        'min_sale_qty',
        'min_purchase_price',
        'variant_info',
        'barcode',
        'custom_fields',
        'status',
        'shipping_method'
    ];

    protected $casts = [
        'variant_info' => 'array',
        'custom_fields' => 'array'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function childCategory()
    {
        return $this->belongsTo(ChildCategory::class);
    }
    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }
}
