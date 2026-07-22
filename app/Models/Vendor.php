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
        'currency_name',
        'currency_icon',
        'currency_rate',
        'currency_id',
        'description',
        'status'
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function getEffectiveExchangeRateAttribute()
    {
        if ($this->currency_id && $this->currency) {
            return (float) $this->currency->exchange_rate;
        }
        return (float) ($this->currency_rate ?? 1.0000);
    }
}
