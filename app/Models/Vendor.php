<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

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

    /**
     * Accessor for supplier / vendor code (e.g. V-0001).
     */
    public function getCodeAttribute(): string
    {
        if (!empty($this->attributes['code'])) {
            return (string) $this->attributes['code'];
        }

        return 'V-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
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
