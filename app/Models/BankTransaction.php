<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_account_id',
        'type',
        'amount',
        'currency_id',
        'exchange_rate',
        'reference_type',
        'reference_id',
        'transaction_date',
        'reconciled',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'exchange_rate'    => 'decimal:6',
        'transaction_date' => 'date',
        'reconciled'       => 'boolean',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
