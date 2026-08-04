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
        'settlement_type',
        'replacement_product_id',
        'replacement_variant_id',
        'replacement_qty',
        'settled_at',
        'approved_by',
    ];

    protected $casts = [
        'settled_at' => 'datetime',
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

    public function replacementProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'replacement_product_id');
    }

    public function replacementVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'replacement_variant_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(DebitNoteRefund::class, 'vendor_return_id');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(DebitNoteSettlement::class, 'vendor_return_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorReturnItem::class, 'vendor_return_id');
    }

    public function getTotalClaimAmountAttribute(): float
    {
        if ($this->relationLoaded('items')) {
            return (float) $this->items->sum('total_amount');
        }
        return (float) $this->items()->sum('total_amount');
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

    public function getSettlementBadgeAttribute(): string
    {
        return match ($this->settlement_type) {
            'pending'             => '<span class="badge badge-warning"><i class="fas fa-hourglass-start"></i> Pending Settlement</span>',
            'bill_credit'          => '<span class="badge badge-info"><i class="fas fa-file-invoice-dollar"></i> Bill Credit Deducted</span>',
            'product_replacement' => '<span class="badge badge-primary"><i class="fas fa-boxes"></i> Product Replaced</span>',
            'cash_refund'         => '<span class="badge badge-success"><i class="fas fa-hand-holding-usd"></i> Money Refunded</span>',
            default               => '<span class="badge badge-secondary">' . ucfirst(str_replace('_', ' ', $this->settlement_type)) . '</span>',
        };
    }
}
