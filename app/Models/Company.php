<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'vat_number',
        'address',
        'logo',
        'currency_id',
        'base_currency_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id')->withDefault(function () {
            return $this->belongsTo(Currency::class, 'base_currency_id')->getResults();
        });
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function outlets()
    {
        return $this->hasMany(Outlet::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
