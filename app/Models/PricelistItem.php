<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricelistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pricelist_id',
        'product_id',
        'variant_id',
        'price',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    public function pricelist(): BelongsTo
    {
        return $this->belongsTo(Pricelist::class, 'pricelist_id');
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
