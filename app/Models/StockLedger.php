<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\ProductVariant;

class StockLedger extends Model
{
    protected $fillable = [
        'product_id',
        'variant_id',
        'batch_id',
        'outlet_id',
        'bin_id',
        'reference_type',
        'reference_id',
        'in_qty',
        'out_qty',
        'balance_qty',
        'date'
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
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    public function bin()
    {
        return $this->belongsTo(WarehouseBin::class, 'bin_id');
    }

    public function batch()
    {
        return $this->belongsTo(StockBatch::class, 'batch_id');
    }
}
