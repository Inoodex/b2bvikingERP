<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorQuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_quotation_id',
        'product_id',
        'variant_id',
        'qty',
        'unit_price',
    ];

    public function vendorQuotation(): BelongsTo
    {
        return $this->belongsTo(VendorQuotation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
