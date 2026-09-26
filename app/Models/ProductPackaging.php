<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPackaging extends Model
{
    protected $fillable = [
        'product_id',
        'variant_id',
        'packaging_type_id',
        'parent_packaging_type_id',
        'units_per_pack',
        'packs_per_parent',
        'barcode',
    ];

    protected $casts = [
        'units_per_pack' => 'integer',
        'packs_per_parent' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function packagingType(): BelongsTo
    {
        return $this->belongsTo(PackagingType::class, 'packaging_type_id');
    }

    public function parentPackagingType(): BelongsTo
    {
        return $this->belongsTo(PackagingType::class, 'parent_packaging_type_id');
    }
}
