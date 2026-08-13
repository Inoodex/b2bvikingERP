<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_id',
        'usage_limit',
        'used_count',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }
}
