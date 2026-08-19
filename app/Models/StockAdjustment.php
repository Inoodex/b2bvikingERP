<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'adjustment_no',
        'outlet_id',
        'reason',
        'requested_by',
        'approved_by',
        'adjustment_type',
        'reason_code',
        'status',
        'total_items_count',
        'total_adjusted_cost',
        'note',
        'attachment',
    ];

    protected $casts = [
        'total_items_count' => 'integer',
        'total_adjusted_cost' => 'decimal:2',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function requestedByUser()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }
}
