<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_no',
        'purchase_id',
        'goods_receipt_id',
        'debit_note_no',
        'reason',
        'status',
        'approved_by',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorReturnItem::class, 'vendor_return_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'  => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending Approval</span>',
            'approved' => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Return Approved</span>',
            'rejected' => '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Rejected</span>',
            default    => '<span class="badge badge-secondary">' . ucfirst($this->status) . '</span>',
        };
    }
}
