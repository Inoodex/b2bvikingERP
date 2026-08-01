<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandedCostAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_detail_id',
        'lc_expense_id',
        'allocated_amount',
        'landed_unit_cost',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'landed_unit_cost' => 'decimal:2',
    ];

    public function purchaseDetail(): BelongsTo
    {
        return $this->belongsTo(PurchaseDetail::class, 'purchase_detail_id');
    }

    public function lcExpense(): BelongsTo
    {
        return $this->belongsTo(LcExpense::class, 'lc_expense_id');
    }
}
