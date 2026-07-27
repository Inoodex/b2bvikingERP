<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorQuotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'vendor_id',
        'quotation_no',
        'currency_id',
        'delivery_terms',
        'validity_date',
        'status',
    ];

    protected $casts = [
        'validity_date' => 'date',
    ];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorQuotationItem::class);
    }
}
