<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_note_no',
        'sales_return_id',
        'customer_id',
        'currency_id',
        'amount',
        'settled_amount',
        'remaining_amount',
        'settlement_status',
        'settlement_mode',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'settled_amount' => 'float',
        'remaining_amount' => 'float',
    ];

    public function salesReturn(): BelongsTo
    {
        return $this->belongsTo(SalesReturn::class, 'sales_return_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
