<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_no',
        'invoice_no',
        'vendor_id',
        'user_id',
        'date',
        'total_amount',
        'note',
        'status',
        'shipping_method',
        'booking_id',
        'material_cost',
        'transport_cost',
        'tax',
        'invoice_attachment',
        'paid_amount',
        'due_amount',
        'payment_status',
        // Phase 2 Step 2 PO Engine Fields
        'purchase_type',
        'rfq_id',
        'comparison_statement_id',
        'proforma_invoice_id',
        'lc_id',
        'currency_id',
        'foreign_amount',
        'exchange_rate_used',
        'base_amount',
        'approval_status',
        'milestone_status',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'material_cost' => 'float',
        'transport_cost' => 'float',
        'tax' => 'float',
        'paid_amount' => 'float',
        'due_amount' => 'float',
        'foreign_amount' => 'float',
        'exchange_rate_used' => 'float',
        'base_amount' => 'float',
        'date' => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function comparisonStatement()
    {
        return $this->belongsTo(ComparisonStatement::class, 'comparison_statement_id');
    }

    public function proformaInvoice()
    {
        return $this->belongsTo(ProformaInvoice::class, 'proforma_invoice_id');
    }

    public function letterOfCredit()
    {
        return $this->belongsTo(LetterOfCredit::class, 'lc_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    public function emailLogs()
    {
        return $this->hasMany(PoEmailLog::class, 'purchase_id');
    }

    public function attachments()
    {
        return $this->hasMany(PurchaseAttachment::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function vendorBills()
    {
        return $this->hasMany(VendorBill::class);
    }

    public function closePurchaseOrder(): bool
    {
        return $this->update([
            'status' => 1,
            'milestone_status' => 'completed',
        ]);
    }

    public function advanceMilestone(string $newMilestone): bool
    {
        if ($this->milestone_status === 'cancelled' && $newMilestone !== 'cancelled') {
            return false;
        }

        $hierarchy = [
            'draft'          => 1,
            'approved'       => 2,
            'po_sent'        => 3,
            'pi_attached'    => 4,
            'lc_opened'      => 5,
            'shipped'        => 6,
            'goods_partial'  => 6,
            'goods_received' => 7,
            'completed'      => 8,
        ];

        $currentRank = $hierarchy[$this->milestone_status ?? 'draft'] ?? 1;
        $newRank = $hierarchy[$newMilestone] ?? 1;

        if ($newRank >= $currentRank) {
            return $this->update(['milestone_status' => $newMilestone]);
        }

        return true;
    }

    public function getEvaluatedMilestoneAttribute(): string
    {
        if ($this->milestone_status === 'cancelled') {
            return 'cancelled';
        }
        if ($this->relationLoaded('goodsReceipts') ? $this->goodsReceipts->where('qc_status', 'passed')->isNotEmpty() : $this->goodsReceipts()->where('qc_status', 'passed')->exists()) {
            return 'goods_received';
        }
        if ($this->relationLoaded('shipments') ? $this->shipments->isNotEmpty() : $this->shipments()->exists()) {
            return 'shipped';
        }
        if ($this->lc_id || ($this->relationLoaded('letterOfCredit') ? $this->letterOfCredit !== null : $this->letterOfCredit()->exists())) {
            return 'lc_opened';
        }
        if ($this->proforma_invoice_id || ($this->relationLoaded('proformaInvoice') ? $this->proformaInvoice !== null : $this->proformaInvoice()->exists())) {
            return 'pi_attached';
        }
        if ($this->relationLoaded('emailLogs') ? $this->emailLogs->isNotEmpty() : $this->emailLogs()->exists()) {
            return 'po_sent';
        }
        if ($this->approval_status === 'approved') {
            return 'approved';
        }
        return $this->milestone_status ?? 'draft';
    }
}
