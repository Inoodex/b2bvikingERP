<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    use HasFactory;

    protected $table = 'customer_payments';

    protected $fillable = [
        'payment_no',
        'user_id',
        'sales_invoice_id',
        'order_id',
        'account_id',
        'amount',
        'payment_method',
        'reference_no',
        'payment_date',
        'notes',
        'created_by',
        'status',
        'unallocated_amount',
        'is_advance',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journalEntry()
    {
        return $this->morphOne(JournalEntry::class, 'reference', 'reference_type', 'reference_id');
    }
}
