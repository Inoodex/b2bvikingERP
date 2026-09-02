<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'asset_code',
        'name',
        'category',
        'purchase_value',
        'purchase_date',
        'depreciation_method',
        'useful_life_years',
        'outlet_id',
        'status',
    ];

    protected $casts = [
        'purchase_value'    => 'decimal:2',
        'purchase_date'     => 'date',
        'useful_life_years' => 'integer',
    ];

    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class, 'asset_id');
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    public function getTotalDepreciationAttribute(): float
    {
        return (float) $this->depreciations()->sum('amount');
    }

    public function getCurrentBookValueAttribute(): float
    {
        return max(0, (float) $this->purchase_value - $this->total_depreciation);
    }
}
