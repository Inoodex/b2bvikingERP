<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

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

    public function getTotalAttribute()
    {
        // Return latest purchase total safely using collection
        return optional($this->purchaseDetails->last())->total ?? 0;
    }

    public function inventoryStocks()
    {
        return $this->hasMany(InventoryStock::class);
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
     * Get average rating for product
     */
    public function getAverageRatingAttribute()
    {
        try {
            return $this->reviews()->avg('rating') ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get total review count
     */
    public function getTotalReviewsAttribute()
    {
        try {
            return $this->reviews()->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
