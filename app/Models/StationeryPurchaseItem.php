<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StationeryPurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stationery_purchase_id',
        'stationery_item_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(StationeryPurchase::class, 'stationery_purchase_id');
    }

    public function stationeryItem()
    {
        return $this->belongsTo(StationeryItem::class, 'stationery_item_id');
    }
}

