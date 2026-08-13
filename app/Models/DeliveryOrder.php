<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'delivery_no',
        'delivery_type',
        'tracking_number',
        'carrier_name',
        'shipping_address',
        'status',
        'date',
        'actual_delivery_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'actual_delivery_date' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryOrderItem::class, 'delivery_order_id');
    }
}
