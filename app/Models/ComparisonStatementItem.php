<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComparisonStatementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'comparison_statement_id',
        'product_id',
        'variant_id',
        'selected_vendor_quotation_item_id',
    ];

    public function comparisonStatement()
    {
        return $this->belongsTo(ComparisonStatement::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function selectedQuotationItem()
    {
        return $this->belongsTo(VendorQuotationItem::class, 'selected_vendor_quotation_item_id');
    }
}
