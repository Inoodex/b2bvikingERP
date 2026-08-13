<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiftCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'initial_value',
        'balance',
        'currency_id',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'initial_value' => 'float',
        'balance' => 'float',
        'expires_at' => 'datetime',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(GiftCardTransaction::class, 'gift_card_id');
    }
}
