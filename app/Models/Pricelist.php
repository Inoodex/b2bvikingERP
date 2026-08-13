<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pricelist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'customer_segment',
        'region',
        'valid_from',
        'valid_to',
        'status',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PricelistItem::class, 'pricelist_id');
    }
}
