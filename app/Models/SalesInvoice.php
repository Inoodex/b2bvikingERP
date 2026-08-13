<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'invoice_no',
        'subtotal_amount',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'currency_id',
        'exchange_rate',
        'incoterm',
        'status',
        'date',
        'due_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'subtotal_amount' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'due_amount' => 'float',
        'exchange_rate' => 'float',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class, 'sales_invoice_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class, 'sales_invoice_id');
    }
}
