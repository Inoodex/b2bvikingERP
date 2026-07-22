<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    protected $fillable = [
        'purchase_id',
        'vendor_id',
        'transaction_id',
        'payment_method',
        'amount',
        'note',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function receipts()
    {
        return $this->hasMany(PurchasePaymentReceipt::class, 'purchase_payment_id');
    }
}
