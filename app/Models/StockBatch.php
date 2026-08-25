<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    use HasFactory;
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_id',
        'outlet_id',
        'goods_receipt_id',
        'purchase_detail_id',
        'batch_no',
        'qty_received',
        'qty_remaining',
        'unit_cost',
        'received_date',
    ];

    protected $casts = [
        'qty_received' => 'decimal:4',
        'qty_remaining' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'received_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }
}
