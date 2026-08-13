<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_invoice_id',
        'product_id',
        'variant_id',
        'qty',
        'price',
        'discount',
        'tax_amount',
        'subtotal',
        'description',
    ];

    protected $casts = [
        'qty' => 'float',
        'price' => 'float',
        'discount' => 'float',
        'tax_amount' => 'float',
        'subtotal' => 'float',
    ];

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
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
