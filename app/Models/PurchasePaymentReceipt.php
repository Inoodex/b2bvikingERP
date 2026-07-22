<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasePaymentReceipt extends Model
{
    protected $fillable = [
        'purchase_payment_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    public function payment()
    {
        return $this->belongsTo(PurchasePayment::class, 'purchase_payment_id');
    }
}
