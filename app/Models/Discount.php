<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'name',
        'type',
        'value',
        'is_default',
        'status',
    ];

    protected $casts = [
        'value' => 'float',
        'is_default' => 'boolean',
        'status' => 'boolean',
    ];
}
