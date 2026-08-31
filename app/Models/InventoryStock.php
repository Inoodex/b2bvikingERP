<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\ProductVariant;

class InventoryStock extends Model
{
    protected $fillable = [
        'product_id',
        'variant_id',
        'outlet_id',
        'bin_id',
        'quantity'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function bin()
    {
        return $this->belongsTo(WarehouseBin::class, 'bin_id');
    }
}
