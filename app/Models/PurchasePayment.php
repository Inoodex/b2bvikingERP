<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    protected $fillable = [
        'payment_no',
        'payment_date',
        'purchase_id',
        'vendor_id',
        'currency_id',
        'transaction_id',
        'payment_method',
        'bank_name',
        'cheque_no',
        'amount',
        'exchange_rate',
        'base_amount',
        'status',
        'note',
        'created_by',
    ];

    protected $casts = [
        'payment_date'  => 'date',
        'amount'        => 'float',
        'exchange_rate' => 'float',
        'base_amount'   => 'float',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receipts()
    {
        return $this->hasMany(PurchasePaymentReceipt::class, 'purchase_payment_id');
    }

    public function journalEntries()
    {
        return $this->morphMany(JournalEntry::class, 'reference', 'reference_type', 'reference_id');
    }

    public function journalEntry()
    {
        return $this->morphOne(JournalEntry::class, 'reference', 'reference_type', 'reference_id');
    }
}
