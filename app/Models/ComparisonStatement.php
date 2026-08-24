<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComparisonStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'cs_no',
        'rfq_id',
        'recommended_vendor_id',
        'approval_status',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function recommendedVendor()
    {
        return $this->belongsTo(Vendor::class, 'recommended_vendor_id');
    }

    public function items()
    {
        return $this->hasMany(ComparisonStatementItem::class);
    }

    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'comparison_statement_id');
    }

    public function getTotalAmountAttribute()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $vqi = $item->selectedQuotationItem;
            if (!$vqi && $this->recommended_vendor_id) {
                $vqi = VendorQuotationItem::whereHas('vendorQuotation', function ($q) {
                    $q->where('rfq_id', $this->rfq_id)
                      ->where('vendor_id', $this->recommended_vendor_id);
                })
                ->where('product_id', $item->product_id)
                ->when($item->variant_id, fn($query) => $query->where('variant_id', $item->variant_id))
                ->first();
            }

            if ($vqi) {
                $quotation = $vqi->vendorQuotation;
                $exchangeRate = ($quotation && $quotation->currency) ? (float)$quotation->currency->exchange_rate : 1.0;
                $total += ($vqi->qty * $vqi->unit_price * $exchangeRate);
            }
        }
        return $total;
    }
}
