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

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function getEffectiveExchangeRateAttribute()
    {
        return (float) ($this->currency ? $this->currency->exchange_rate : 1.0000);
    }
}
