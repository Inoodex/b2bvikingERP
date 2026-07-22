<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueReturnItem extends Model
{
    protected $fillable = [
        'issue_return_id',
        'product_id',
        'variant_id',
        'quantity',
        'unit_price',
        'condition'
    ];

    protected $casts = [
        'unit_price' => 'float',
    ];

    public function issueReturn()
    {
        return $this->belongsTo(IssueReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
