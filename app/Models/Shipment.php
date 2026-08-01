<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'vessel_or_flight',
        'container_no',
        'bl_awb_no',
        'port_of_loading',
        'port_of_discharge',
        'etd',
        'eta',
        'status',
        'document_path',
    ];

    protected $casts = [
        'etd' => 'date',
        'eta' => 'date',
    ];

    /**
     * Relationship: Shipment belongs to Purchase PO
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    /**
     * Relationship: Shipment has many cost estimates
     */
    public function shipmentCostEstimates(): HasMany
    {
        return $this->hasMany(ShipmentCostEstimate::class, 'shipment_id');
    }

    /**
     * Helper Badge HTML for Status
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'in_transit' => '<span class="badge badge-info"><i class="fas fa-ship"></i> In Transit</span>',
            'arrived'    => '<span class="badge badge-warning"><i class="fas fa-anchor"></i> Arrived at Port</span>',
            'cleared'    => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Customs Cleared</span>',
            'cancelled'  => '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Cancelled</span>',
            default      => '<span class="badge badge-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    /**
     * Count Goods Receipts associated with this shipment's PO
     */
    public function goodsReceiptsCount(): int
    {
        return GoodsReceipt::where('purchase_id', $this->purchase_id)->count();
    }
}
