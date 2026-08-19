<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'variant_id',
        'qty_change',
        'system_qty',
        'counted_qty',
        'adjusted_qty',
        'unit_cost',
        'total_cost',
        'item_note',
    ];

    protected $casts = [
        'qty_change' => 'decimal:2',
        'system_qty' => 'decimal:2',
        'counted_qty' => 'decimal:2',
        'adjusted_qty' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function stockAdjustment()
    {
        return $this->belongsTo(StockAdjustment::class);
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
