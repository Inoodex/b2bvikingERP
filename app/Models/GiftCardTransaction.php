<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftCardTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'gift_card_id',
        'order_id',
        'type',
        'amount',
        'balance_after',
    ];

    protected $casts = [
        'amount' => 'float',
        'balance_after' => 'float',
    ];

    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class, 'gift_card_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
