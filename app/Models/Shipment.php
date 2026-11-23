<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    public const MODE_SEA  = 'sea';
    public const MODE_AIR  = 'air';
    public const MODE_ROAD = 'road';
    public const MODE_RAIL = 'rail';

    protected $fillable = [
        'purchase_id',
        'transport_mode',
        'carrier_name',
        'vessel_or_flight',
        'container_no',
        'bl_awb_no',
        'port_of_loading',
        'port_of_discharge',
        'etd',
        'eta',
        'status',
        'mode_change_reason',
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
     * Helper Badge HTML for Transport Mode
     */
    public function getTransportBadgeAttribute(): string
    {
        return match ($this->transport_mode) {
            self::MODE_AIR  => '<span class="badge badge-info" style="font-size: 85%;"><i class="fas fa-plane-departure mr-1"></i> Air Cargo</span>',
            self::MODE_ROAD => '<span class="badge badge-warning" style="font-size: 85%;"><i class="fas fa-truck mr-1"></i> Road Freight</span>',
            self::MODE_RAIL => '<span class="badge badge-secondary" style="font-size: 85%;"><i class="fas fa-train mr-1"></i> Rail Freight</span>',
            default         => '<span class="badge badge-primary" style="font-size: 85%;"><i class="fas fa-ship mr-1"></i> Sea Freight</span>',
        };
    }

    /**
     * Transport Mode Name
     */
    public function getTransportNameAttribute(): string
    {
        return match ($this->transport_mode) {
            self::MODE_AIR  => 'Air Cargo',
            self::MODE_ROAD => 'Road Freight',
            self::MODE_RAIL => 'Rail Freight',
            default         => 'Sea Freight',
        };
    }

    /**
     * Transport Icon Class
     */
    public function getTransportIconAttribute(): string
    {
        return match ($this->transport_mode) {
            self::MODE_AIR  => 'fas fa-plane-departure',
            self::MODE_ROAD => 'fas fa-truck',
            self::MODE_RAIL => 'fas fa-train',
            default         => 'fas fa-ship',
        };
    }

    /**
     * Dynamic Carrier Label
     */
    public function getCarrierLabelAttribute(): string
    {
        return match ($this->transport_mode) {
            self::MODE_AIR  => 'Airline / Air Cargo Carrier',
            self::MODE_ROAD => 'Logistics / Trucking Company',
            self::MODE_RAIL => 'Rail Freight Operator',
            default         => 'Shipping Line / Ocean Carrier',
        };
    }

    /**
     * Dynamic Identifier Label (Flight vs Vessel vs Truck vs Train)
     */
    public function getIdentifierLabelAttribute(): string
    {
        return match ($this->transport_mode) {
            self::MODE_AIR  => 'Flight Number',
            self::MODE_ROAD => 'Truck / Trailer Reg Plate',
            self::MODE_RAIL => 'Train / Wagon Number',
            default         => 'Vessel Name & Voyage',
        };
    }

    /**
     * Dynamic Document Reference Label (BL vs AWB vs CMR vs CIM)
     */
    public function getDocumentLabelAttribute(): string
    {
        return match ($this->transport_mode) {
            self::MODE_AIR  => 'Air Waybill (AWB) Number',
            self::MODE_ROAD => 'Consignment Note (CMR) Number',
            self::MODE_RAIL => 'Railway Waybill (CIM) Number',
            default         => 'Bill of Lading (BL) Number',
        };
    }

    /**
     * Dynamic Container / Unit Label
     */
    public function getContainerLabelAttribute(): string
    {
        return match ($this->transport_mode) {
            self::MODE_AIR  => 'ULD Container / Pallet Unit No',
            self::MODE_ROAD => 'Trailer Box / Container No',
            self::MODE_RAIL => 'Wagon / Container Unit No',
            default         => 'Container Number (FCL / LCL)',
        };
    }

    /**
     * Dynamic Origin Location Label
     */
    public function getOriginLabelAttribute(): string
    {
        return match ($this->transport_mode) {
            self::MODE_AIR  => 'Airport of Departure (Origin)',
            self::MODE_ROAD => 'Origin Hub / Dispatch Terminal',
            self::MODE_RAIL => 'Origin Rail Terminal / Yard',
            default         => 'Port of Loading (POL)',
        };
    }

    /**
     * Dynamic Destination Location Label
     */
    public function getDestinationLabelAttribute(): string
    {
        return match ($this->transport_mode) {
            self::MODE_AIR  => 'Airport of Destination',
            self::MODE_ROAD => 'Destination Hub / Delivery Point',
            self::MODE_RAIL => 'Destination Rail Terminal',
            default         => 'Port of Discharge (POD)',
        };
    }

    /**
     * Helper Badge HTML for Status (Context-aware based on transport mode)
     */
    public function getStatusBadgeAttribute(): string
    {
        if ($this->status === 'in_transit') {
            return match ($this->transport_mode) {
                self::MODE_AIR  => '<span class="badge badge-info"><i class="fas fa-plane mr-1"></i> In Flight / Transit</span>',
                self::MODE_ROAD => '<span class="badge badge-info"><i class="fas fa-truck-moving mr-1"></i> On Route</span>',
                self::MODE_RAIL => '<span class="badge badge-info"><i class="fas fa-train mr-1"></i> On Rail / Transit</span>',
                default         => '<span class="badge badge-info"><i class="fas fa-ship mr-1"></i> In Transit (Voyage)</span>',
            };
        }

        if ($this->status === 'arrived') {
            return match ($this->transport_mode) {
                self::MODE_AIR  => '<span class="badge badge-warning"><i class="fas fa-plane-arrival mr-1"></i> Landed at Airport</span>',
                self::MODE_ROAD => '<span class="badge badge-warning"><i class="fas fa-warehouse mr-1"></i> Arrived at Hub</span>',
                self::MODE_RAIL => '<span class="badge badge-warning"><i class="fas fa-warehouse mr-1"></i> Arrived at Rail Terminal</span>',
                default         => '<span class="badge badge-warning"><i class="fas fa-anchor mr-1"></i> Berthed at Port</span>',
            };
        }

        return match ($this->status) {
            'cleared'   => '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Customs Cleared</span>',
            'cancelled' => '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i> Cancelled</span>',
            default     => '<span class="badge badge-secondary">' . ucfirst($this->status) . '</span>',
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
