<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GoodsReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_no',
        'purchase_id',
        'outlet_id',
        'bin_id',
        'received_by',
        'qc_status',
        'remarks',
    ];

    /**
     * Relationship: GRN belongs to Purchase PO
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    /**
     * Relationship: GRN belongs to Outlet
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    /**
     * Relationship: GRN belongs to Warehouse Bin
     */
    public function bin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'bin_id');
    }

    /**
     * Relationship: GRN belongs to Receiving User
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Relationship: GRN has many line items
     */
    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class, 'goods_receipt_id');
    }

    /**
     * Relationship: GRN has one Vendor Return (if QC failed/partial)
     */
    public function vendorReturn(): HasOne
    {
        return $this->hasOne(VendorReturn::class, 'goods_receipt_id');
    }

    /**
     * Helper Badge HTML for QC Status
     */
    public function getQcStatusBadgeAttribute(): string
    {
        return match ($this->qc_status) {
            'pending' => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending QC</span>',
            'passed'  => '<span class="badge badge-success"><i class="fas fa-check-double"></i> QC Passed</span>',
            'partial' => '<span class="badge badge-info"><i class="fas fa-exclamation-circle"></i> QC Partial</span>',
            'failed'  => '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> QC Failed</span>',
            default   => '<span class="badge badge-secondary">' . ucfirst($this->qc_status) . '</span>',
        };
    }
}
