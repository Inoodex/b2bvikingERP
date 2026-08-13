<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SalesReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_no',
        'order_id',
        'sales_invoice_id',
        'credit_note_no',
        'refund_amount',
        'refund_method',
        'return_to_stock',
        'status',
        'approved_by',
        'created_by',
    ];

    protected $casts = [
        'refund_amount' => 'float',
        'return_to_stock' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class, 'sales_return_id');
    }

    public function creditNote(): HasOne
    {
        return $this->hasOne(CreditNote::class, 'sales_return_id');
    }
}
