<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetterOfCredit extends Model
{
    use HasFactory;

    protected $table = 'letters_of_credit';

    protected $fillable = [
        'lc_no',
        'proforma_invoice_id',
        'vendor_id',
        'issuing_bank',
        'margin_percent',
        'amount',
        'currency_id',
        'issue_date',
        'expiry_date',
        'status',
    ];

    protected $casts = [
        'margin_percent' => 'float',
        'amount' => 'float',
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function proformaInvoice()
    {
        return $this->belongsTo(ProformaInvoice::class, 'proforma_invoice_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function expenses()
    {
        return $this->hasMany(LcExpense::class, 'lc_id');
    }

    public function amendments()
    {
        return $this->hasMany(LcAmendment::class, 'lc_id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'lc_id');
    }

    public function getTotalExpensesAttribute()
    {
        return $this->expenses->sum('amount');
    }

    public function getCurrencySymbolAttribute(): string
    {
        return $this->currency?->symbol ?? $this->vendor?->currency?->symbol ?? 'kr.';
    }
}
