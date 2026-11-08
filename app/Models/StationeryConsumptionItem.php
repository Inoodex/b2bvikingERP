<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StationeryConsumptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stationery_consumption_id',
        'stationery_item_id',
        'quantity',
        'unit_cost',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function consumption()
    {
        return $this->belongsTo(StationeryConsumption::class, 'stationery_consumption_id');
    }

    public function stationeryItem()
    {
        return $this->belongsTo(StationeryItem::class, 'stationery_item_id');
    }
}

