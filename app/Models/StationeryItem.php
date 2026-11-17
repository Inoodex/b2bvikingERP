<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StationeryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_code',
        'name',
        'image',
        'category',
        'stationery_category_id',
        'unit_id',
        'unit_name',
        'current_stock',
        'unit_cost',
        'min_alert_qty',
        'allow_sale',
        'status',
        'notes',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'min_alert_qty' => 'decimal:2',
        'allow_sale' => 'boolean',
        'status' => 'boolean',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function stationeryCategory()
    {
        return $this->belongsTo(StationeryCategory::class, 'stationery_category_id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(StationeryPurchaseItem::class, 'stationery_item_id');
    }

    public function consumptionItems()
    {
        return $this->hasMany(StationeryConsumptionItem::class, 'stationery_item_id');
    }

    public function ledgers()
    {
        return $this->hasMany(StationeryStockLedger::class, 'stationery_item_id')->orderByDesc('id');
    }

    public function getCategoryNameAttribute(): string
    {
        if ($this->stationeryCategory && !empty($this->stationeryCategory->name)) {
            return $this->stationeryCategory->name;
        }

        return ucfirst(str_replace('_', ' ', $this->category ?: 'General Supplies'));
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image && trim($this->image) !== '') {
            if (str_starts_with($this->image, 'http')) {
                return $this->image;
            }
            return asset('storage/' . ltrim($this->image, '/'));
        }

        return asset('uploads/no-image.svg');
    }

    /**
     * Get real-time health status badge info
     */
    public function getStockStatusAttribute(): array
    {
        $stock = (float) $this->current_stock;
        $min = (float) $this->min_alert_qty;

        if ($stock <= 0) {
            return [
                'key' => 'out_of_stock',
                'label' => 'Out of Stock',
                'badge_class' => 'badge-danger',
                'icon' => 'fa-times-circle',
                'color' => '#dc3545',
            ];
        }

        if ($stock <= $min) {
            return [
                'key' => 'low_stock',
                'label' => 'Low Stock Alert',
                'badge_class' => 'badge-warning',
                'icon' => 'fa-exclamation-triangle',
                'color' => '#f59e0b',
            ];
        }

        return [
            'key' => 'healthy',
            'label' => 'Adequate Stock',
            'badge_class' => 'badge-success',
            'icon' => 'fa-check-circle',
            'color' => '#10b981',
        ];
    }

    /**
     * Live inventory valuation of this item (Stock * Unit Cost)
     */
    public function getTotalValuationAttribute(): float
    {
        return round((float) $this->current_stock * (float) $this->unit_cost, 2);
    }
}
