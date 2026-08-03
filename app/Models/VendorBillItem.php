<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorBillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_bill_id',
        'product_id',
        'variant_id',
        'description',
        'qty',
        'unit_price',
        'landed_cost',
        'line_total',
    ];

    protected $casts = [
        'qty' => 'float',
        'unit_price' => 'float',
        'landed_cost' => 'float',
        'line_total' => 'float',
    ];

    public function vendorBill()
    {
        return $this->belongsTo(VendorBill::class);
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
