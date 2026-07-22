<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Issue;
use App\Models\Product;
use App\Models\ProductVariant;

class IssueItem extends Model
{
    protected $fillable = [
        'issue_id',
        'product_id',
        'variant_id',
        'quantity',
        'unit_price'
    ];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
