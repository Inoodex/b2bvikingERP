<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'color',
        'size',
        'color_id',
        'size_id',
        'qty',
        'status',
        'price',
        'outlet_price'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function inventoryStocks()
    {
        return $this->hasMany(InventoryStock::class, 'variant_id');
    }

    public function stockLedgers()
    {
        return $this->hasMany(StockLedger::class, 'variant_id');
    }

    public function getInventoryStockAttribute()
    {
        return $this->inventoryStocks->sum('quantity');
    }
}
