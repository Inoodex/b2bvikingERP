<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPaymentReceipt extends Model
{
    protected $fillable = [
        'order_payment_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    public function payment()
    {
        return $this->belongsTo(OrderPayment::class, 'order_payment_id');
    }
}
