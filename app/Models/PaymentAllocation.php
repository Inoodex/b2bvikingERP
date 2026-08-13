<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_type',
        'payment_id',
        'invoice_type',
        'invoice_id',
        'matched_amount',
        'allocated_at',
    ];

    protected $casts = [
        'matched_amount' => 'float',
        'allocated_at' => 'datetime',
    ];
}
