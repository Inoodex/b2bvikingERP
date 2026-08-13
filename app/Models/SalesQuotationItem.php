<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesQuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_quotation_id',
        'product_id',
        'variant_id',
        'qty',
        'unit_price',
    ];

    protected $casts = [
        'qty' => 'float',
        'unit_price' => 'float',
    ];

    public function salesQuotation(): BelongsTo
    {
        return $this->belongsTo(SalesQuotation::class, 'sales_quotation_id');
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
