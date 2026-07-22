<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingRule extends Model
{
    protected $fillable = [
        'name',
        'sale_multiplier',
        'outlet_multiplier',
        'is_default',
        'status',
    ];

    protected $casts = [
        'sale_multiplier' => 'decimal:4',
        'outlet_multiplier' => 'decimal:4',
        'is_default' => 'boolean',
        'status' => 'boolean',
    ];
}

