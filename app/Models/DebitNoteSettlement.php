<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitNoteSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_return_id',
        'vendor_bill_id',
        'settled_amount',
        'settlement_date',
        'notes',
        'settled_by',
    ];

    protected $casts = [
        'settled_amount' => 'float',
        'settlement_date' => 'datetime',
    ];

    public function vendorReturn()
    {
        return $this->belongsTo(VendorReturn::class);
    }

    public function vendorBill()
    {
        return $this->belongsTo(VendorBill::class);
    }

    public function settledBy()
    {
        return $this->belongsTo(User::class, 'settled_by');
    }
}
