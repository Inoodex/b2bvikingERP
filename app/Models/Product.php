<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'thumb_image',
        'category_id',
        'sub_category_id',
        'child_category_id',
        'brand_id',
        'unit_id',
        'vendor_id',
        'product_number',
        'sku',
        'qty',
        'long_description',
        'purchase_price',
        'price',
        'outlet_price',
        'barcode',
        'status',
        'product_type',
        'product_type_id',
        'custom_label',
        'self_number',
        'raw_material_cost',
        'transport_cost',
        'tax',
        'minimum_order_qty',
        'discount',
        'discount_type',
        'vat_type',
        'vat_value',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function childCategory()
    {
        return $this->belongsTo(ChildCategory::class);
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function activePurchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class)
            ->whereHas('purchase', function ($q) {
                $q->whereNotIn('milestone_status', ['goods_received', 'cancelled']);
            });
    }

    /**
     * Get active procurement & shipment pipeline status dynamically from database relations
     */
    public function getProcurementPipelineAttribute(): array
    {
        $activeDetails = $this->activePurchaseDetails;
        if (!$activeDetails || $activeDetails->isEmpty()) {
            return [
                'has_active_order'  => false,
                'total_ordered_qty' => 0,
                'status_key'        => null,
                'status_label'      => null,
                'badge_class'       => null,
                'icon'              => null,
                'po_no'             => null,
                'vendor_name'       => null,
                'latest_purchase'   => null,
                'shipment'          => null,
                'popover_title'     => null,
                'popover_content'   => null,
            ];
        }

        $totalOrderedQty = (float) $activeDetails->sum('qty');

        // Look for shipped order or shipment in transit first (most advanced)
        $shippedDetail = null;
        $activeShipment = null;
        foreach ($activeDetails as $ad) {
            $p = $ad->purchase;
            if (!$p) continue;
            if ($p->milestone_status === 'shipped') {
                $shippedDetail = $ad;
                $activeShipment = $p->shipments?->where('status', 'in_transit')->first()
                    ?? $p->shipments?->sortByDesc('id')->first();
                break;
            }
            $inTransit = $p->shipments?->where('status', 'in_transit')->first();
            if ($inTransit) {
                $shippedDetail = $ad;
                $activeShipment = $inTransit;
                break;
            }
        }

        $latestDetail = $shippedDetail ?? $activeDetails->sortByDesc('created_at')->first();
        $purchase = $latestDetail?->purchase;
        $milestone = $purchase?->milestone_status ?? 'draft';
        $vName = $purchase?->vendor?->shop_name ?? ($purchase?->vendor?->name ?? 'Supplier');
        $poNo = $purchase?->po_no ?: ($purchase?->invoice_no ?: ('PO-' . $purchase?->id));

        if ($shippedDetail || $milestone === 'shipped') {
            $ship = $activeShipment ?? $purchase?->shipments?->sortByDesc('id')->first();
            $etaFull = $ship?->eta ? \Carbon\Carbon::parse($ship->eta)->format('d M Y') : null;
            $etaShort = $ship?->eta ? \Carbon\Carbon::parse($ship->eta)->format('d M') : null;
            $label = 'In Shipment' . ($etaShort ? ' (ETA: ' . $etaShort . ')' : '') . ' • ' . number_format($totalOrderedQty) . ' pcs';
            $badgeClass = 'badge-primary';
            $icon = 'fa-shipping-fast';
            $title = 'Procurement in Shipment';
            $content = "<strong>Procurement Ref:</strong> {$poNo}<br><strong>Supplier:</strong> " . e($vName) . "<br><strong>Procurement Qty:</strong> " . number_format($totalOrderedQty) . " pcs<br><strong>Stage:</strong> Shipped / In Transit" . ($etaFull ? "<br><strong>Expected ETA:</strong> {$etaFull}" : '') . ($ship?->container_no ? "<br><strong>Container:</strong> " . e($ship->container_no) : '');

            return [
                'has_active_order'  => true,
                'total_ordered_qty' => $totalOrderedQty,
                'status_key'        => 'in_shipment',
                'status_label'      => $label,
                'badge_class'       => $badgeClass,
                'icon'              => $icon,
                'po_no'             => $poNo,
                'vendor_name'       => $vName,
                'latest_purchase'   => $purchase,
                'shipment'          => $ship,
                'popover_title'     => $title,
                'popover_content'   => $content,
            ];
        }

        $humanMilestone = ucwords(str_replace('_', ' ', $milestone));
        $badgeClass = match ($milestone) {
            'approved'                 => 'badge-warning text-dark',
            'po_sent'                  => 'badge-warning text-dark',
            'pi_attached', 'lc_opened' => 'badge-info',
            'goods_partial'            => 'badge-info',
            'pending_approval'         => 'badge-secondary',
            default                    => 'badge-warning text-dark',
        };
        $icon = match ($milestone) {
            'goods_partial' => 'fa-boxes',
            'lc_opened'     => 'fa-university',
            'pi_attached'   => 'fa-file-invoice-dollar',
            'po_sent'       => 'fa-paper-plane',
            default         => 'fa-clock',
        };

        $label = 'In Procurement • ' . number_format($totalOrderedQty) . ' pcs (' . $humanMilestone . ')';
        $title = 'Procurement Pipeline (' . $humanMilestone . ')';
        $content = "<strong>Procurement Ref:</strong> {$poNo}<br><strong>Supplier:</strong> " . e($vName) . "<br><strong>Stage:</strong> {$humanMilestone}<br><strong>Procurement Qty:</strong> " . number_format($totalOrderedQty) . " pcs";

        return [
            'has_active_order'  => true,
            'total_ordered_qty' => $totalOrderedQty,
            'status_key'        => $milestone,
            'status_label'      => $label,
            'badge_class'       => $badgeClass,
            'icon'              => $icon,
            'po_no'             => $poNo,
            'vendor_name'       => $vName,
            'latest_purchase'   => $purchase,
            'shipment'          => null,
            'popover_title'     => $title,
            'popover_content'   => $content,
        ];
    }

    public function getTotalAttribute()
    {
        // Return latest purchase total safely using collection
        return optional($this->purchaseDetails->last())->total ?? 0;
    }

    public function inventoryStocks()
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function stockBatches()
    {
        return $this->hasMany(StockBatch::class);
    }

    public function stockLedgers()
    {
        return $this->hasMany(StockLedger::class, 'variant_id');
    }

    public function getInventoryStockAttribute()
    {
        return $this->inventoryStocks->sum('quantity');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function productRequestItems()
    {
        return $this->hasMany(ProductRequestItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get average rating for product (published only)
     */
    public function getAverageRatingAttribute()
    {
        try {
            return round((float) ($this->reviews()->where('status', 1)->avg('rating') ?? 0), 1);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get total review count (published only)
     */
    public function getTotalReviewsAttribute()
    {
        try {
            return (int) ($this->reviews()->where('status', 1)->count());
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get detailed breakdown per star rating (5, 4, 3, 2, 1)
     */
    public function getRatingBreakdownAttribute(): array
    {
        try {
            $total = $this->total_reviews;
            $counts = $this->reviews()->where('status', 1)
                ->selectRaw('rating, count(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray();

            $breakdown = [];
            for ($star = 5; $star >= 1; $star--) {
                $count = (int) ($counts[$star] ?? 0);
                $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                $breakdown[$star] = [
                    'star' => $star,
                    'count' => $count,
                    'percentage' => $percentage,
                ];
            }
            return $breakdown;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Customer and outlet specific stock visibility overrides
     */
    public function customerVisibilities()
    {
        return $this->hasMany(\App\Models\CustomerProductVisibility::class);
    }
}
