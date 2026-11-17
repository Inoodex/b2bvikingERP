<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StationeryStockLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'stationery_item_id',
        'transaction_type',
        'reference_id',
        'reference_no',
        'date',
        'stock_before',
        'qty_in',
        'qty_out',
        'stock_after',
        'unit_cost',
        'total_value',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'stock_before' => 'decimal:2',
        'qty_in' => 'decimal:2',
        'qty_out' => 'decimal:2',
        'stock_after' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(StationeryItem::class, 'stationery_item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTransactionLabelAttribute(): string
    {
        return match ($this->transaction_type) {
            'purchase_in' => 'Purchase Stock In',
            'consumption_out' => 'Department Issue',
            'adjustment_in' => 'Stock Adjustment (+)',
            'adjustment_out' => 'Stock Adjustment (-)',
            default => ucfirst(str_replace('_', ' ', $this->transaction_type)),
        };
    }
}

