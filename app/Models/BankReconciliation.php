<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_account_id',
        'statement_date',
        'statement_balance',
        'gl_balance',
        'status',
        'created_by',
        'reconciled_by',
    ];

    protected $casts = [
        'statement_date'    => 'date',
        'statement_balance' => 'decimal:2',
        'gl_balance'        => 'decimal:2',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
