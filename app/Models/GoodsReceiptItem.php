<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receipt_id',
        'product_id',
        'variant_id',
        'accepted_qty',
        'rejected_qty',
        'rejection_reason',
    ];

    protected $casts = [
        'accepted_qty' => 'decimal:2',
        'rejected_qty' => 'decimal:2',
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function getReceivedQtyAttribute(): float
    {
        return (float) $this->accepted_qty + (float) $this->rejected_qty;
    }
}
