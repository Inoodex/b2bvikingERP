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
        'description',
        'status'
    ];
}
