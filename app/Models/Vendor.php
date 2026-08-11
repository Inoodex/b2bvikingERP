<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_name',
        'phone',
        'email',
        'address',
        'country',
        'currency_id',
        'description',
        'status'
    ];

    /**
     * Accessor for name attribute (alias for shop_name for backward compatibility across modules)
     */
    public function getNameAttribute(): string
    {
        return $this->attributes['shop_name'] ?? $this->attributes['name'] ?? 'N/A';
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function getEffectiveExchangeRateAttribute()
    {
        return (float) ($this->currency ? $this->currency->exchange_rate : 1.0000);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'vendor_id');
    }
}
