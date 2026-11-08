<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StationeryPurchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_no',
        'vendor_id',
        'vendor_name_custom',
        'purchase_date',
        'invoice_no',
        'total_amount',
        'payment_status',
        'payment_account_code',
        'paid_amount',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(StationeryPurchaseItem::class, 'stationery_purchase_id');
    }

    public function journalEntry()
    {
        return $this->morphOne(JournalEntry::class, 'reference');
    }

    public function getDisplayVendorAttribute(): string
    {
        if ($this->vendor && !empty($this->vendor->shop_name)) {
            return $this->vendor->shop_name;
        }

        return $this->vendor_name_custom ?: 'Direct / Cash Purchase';
    }
}

