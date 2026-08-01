<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_return_id',
        'product_id',
        'variant_id',
        'qty',
        'unit_price',
        'total_amount',
        'reason',
    ];

    protected $casts = [
        'qty'          => 'decimal:2',
        'unit_price'   => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function vendorReturn(): BelongsTo
    {
        return $this->belongsTo(VendorReturn::class, 'vendor_return_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
