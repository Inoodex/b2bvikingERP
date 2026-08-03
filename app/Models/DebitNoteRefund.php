<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebitNoteRefund extends Model
{
    use HasFactory;

    protected $fillable = [
        'refund_no',
        'vendor_return_id',
        'vendor_id',
        'amount',
        'refund_date',
        'payment_method',
        'bank_name',
        'cheque_no',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'refund_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function vendorReturn(): BelongsTo
    {
        return $this->belongsTo(VendorReturn::class, 'vendor_return_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
