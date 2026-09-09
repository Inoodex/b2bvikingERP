<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodCollection extends Model
{
    use HasFactory;

    protected $table = 'cod_collections';

    protected $fillable = [
        'collection_no',
        'transaction_id',
        'order_id',
        'sales_invoice_id',
        'driver_id',
        'courier_name',
        'expected_amount',
        'collected_amount',
        'difference_amount',
        'status',
        'collected_at',
        'handed_over_to_user_id',
        'deposit_account_id',
        'handed_over_at',
        'notes',
    ];

    protected $casts = [
        'expected_amount'   => 'decimal:2',
        'collected_amount'  => 'decimal:2',
        'difference_amount' => 'decimal:2',
        'collected_at'      => 'datetime',
        'handed_over_at'    => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(PaymentTransaction::class, 'transaction_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'handed_over_to_user_id');
    }

    public function depositAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'deposit_account_id');
    }
}
