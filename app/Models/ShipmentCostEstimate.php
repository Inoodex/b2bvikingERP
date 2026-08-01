<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentCostEstimate extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'cost_element',
        'estimated_amount',
        'actual_amount',
        'currency_id',
    ];

    protected $casts = [
        'estimated_amount' => 'decimal:2',
        'actual_amount'    => 'decimal:2',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
