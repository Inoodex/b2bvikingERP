<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'name',
        'type',
        'value',
        'is_seasonal',
        'product_type_id',
        'occasion',
        'start_date',
        'end_date',
        'category_ids',
        'user_ids',
        'is_default',
        'status',
    ];

    protected $casts = [
        'value' => 'float',
        'is_seasonal' => 'boolean',
        'product_type_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'category_ids' => 'array',
        'user_ids' => 'array',
        'is_default' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * ProductType (Occasion Type) relation
     */
    public function productType()
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    /**
     * Determine if seasonal date range is currently active
     */
    public function isDateActive(?Carbon $date = null): bool
    {
        if (!$this->is_seasonal) {
            return true;
        }

        $targetDate = ($date ?? Carbon::today())->startOfDay();

        if ($this->start_date && $targetDate->lt(Carbon::parse($this->start_date)->startOfDay())) {
            return false;
        }

        if ($this->end_date && $targetDate->gt(Carbon::parse($this->end_date)->endOfDay())) {
            return false;
        }

        return true;
    }

    /**
     * Check if discount applies to a specific product type / occasion
     */
    public function appliesToProductType(?int $productTypeId): bool
    {
        if (!$this->is_seasonal) {
            return true;
        }

        if (empty($this->product_type_id)) {
            return true; // Applies to all product types if none specified
        }

        if ($productTypeId === null) {
            return false;
        }

        return (int)$this->product_type_id === (int)$productTypeId;
    }

    /**
     * Check if discount applies to a specific category
     */
    public function appliesToCategory(?int $categoryId): bool
    {
        if (!$this->is_seasonal) {
            return true;
        }

        if (empty($this->category_ids)) {
            return true; // Applies to all categories if none specified
        }

        if ($categoryId === null) {
            return false;
        }

        return in_array($categoryId, array_map('intval', $this->category_ids), true);
    }

    /**
     * Check if discount applies to a specific user/outlet
     */
    public function appliesToUser(?int $userId): bool
    {
        if (!$this->is_seasonal) {
            return true;
        }

        if (empty($this->user_ids)) {
            return true; // Applies to all users if none specified
        }

        if ($userId === null) {
            return false;
        }

        return in_array($userId, array_map('intval', $this->user_ids), true);
    }
}
