<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvancePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'party_type',
        'party_id',
        'amount',
        'currency_id',
        'applied_amount',
        'balance',
        'payment_date',
        'note',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'float',
        'applied_amount' => 'float',
        'balance' => 'float',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(User::class, 'party_id');
    }
}
