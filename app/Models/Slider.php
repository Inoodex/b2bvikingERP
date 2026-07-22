<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'title',
        'description',
        'starting_price',
        'button_url',
        'serial',
        'status',
        'banner',
    ];

    protected $casts = [
        'starting_price' => 'float',
        'serial' => 'integer',
        'status' => 'boolean',
    ];
}
