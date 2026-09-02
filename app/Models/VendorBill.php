<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_no',
        'purchase_id',
        'vendor_id',
        'goods_receipt_id',
        'currency_id',
        'bill_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'debit_note_adjustment',
        'grand_total',
        'paid_amount',
        'due_amount',
        'payment_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'bill_date'             => 'date',
        'due_date'              => 'date',
        'subtotal'              => 'float',
        'tax_amount'            => 'float',
        'discount_amount'       => 'float',
        'debit_note_adjustment' => 'float',
        'grand_total'           => 'float',
        'paid_amount'           => 'float',
        'due_amount'            => 'float',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(VendorBillItem::class);
    }

    public function debitNoteSettlements()
    {
        return $this->hasMany(DebitNoteSettlement::class);
    }

    public function journalEntries()
    {
        return $this->morphMany(JournalEntry::class, 'reference', 'reference_type', 'reference_id');
    }

    public function journalEntry()
    {
        return $this->morphOne(JournalEntry::class, 'reference', 'reference_type', 'reference_id');
    }

    public function getFormattedStatusAttribute(): string
    {
        return match ($this->payment_status) {
            'paid'     => '<span class="badge badge-success">Paid</span>',
            'partial'  => '<span class="badge badge-warning">Partial</span>',
            'overpaid' => '<span class="badge badge-info">Overpaid</span>',
            default    => '<span class="badge badge-danger">Unpaid</span>',
        };
    }
}
