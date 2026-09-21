<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackagingType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'level_order',
        'tare_weight',
        'max_weight',
        'length_cm',
        'width_cm',
        'height_cm',
        'is_active',
    ];

    protected $casts = [
        'level_order' => 'integer',
        'tare_weight' => 'decimal:3',
        'max_weight' => 'decimal:3',
        'length_cm' => 'decimal:2',
        'width_cm' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope for active packaging types ordered by hierarchy level.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('level_order');
    }

    public function productPackagings(): HasMany
    {
        return $this->hasMany(ProductPackaging::class);
    }

    public function handlingUnits(): HasMany
    {
        return $this->hasMany(HandlingUnit::class);
    }
}
