<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthEndSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'period',
        'product_id',
        'variant_id',
        'outlet_id',
        'closing_qty',
        'closing_value',
    ];

    protected $casts = [
        'closing_qty' => 'decimal:4',
        'closing_value' => 'decimal:4',
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
}
