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
        'payment_token',
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

    protected static function booted()
    {
        static::creating(function ($invoice) {
            if (empty($invoice->payment_token)) {
                $invoice->payment_token = \Illuminate\Support\Str::random(40);
            }
        });
    }

    public function getPublicPaymentUrlAttribute(): string
    {
        if (empty($this->payment_token)) {
            $this->payment_token = \Illuminate\Support\Str::random(40);
            $this->saveQuietly();
        }

        return route('invoices.pay', ['token' => $this->payment_token]);
    }

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

    public function user()
    {
        return $this->hasOneThrough(User::class, Order::class, 'id', 'id', 'order_id', 'user_id');
    }

    public function getUserIdAttribute()
    {
        return $this->order ? $this->order->user_id : null;
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

    public function journalEntries(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(JournalEntry::class, 'reference');
    }

    public function customerPayments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class, 'sales_invoice_id');
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'sales_invoice_id');
    }

    public function codCollections(): HasMany
    {
        return $this->hasMany(CodCollection::class, 'sales_invoice_id');
    }
}
