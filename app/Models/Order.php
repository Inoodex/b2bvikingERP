<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_no',
        'user_id',
        'status',
        'shipping_method',
        'ship_different',
        'billing_name',
        'billing_email',
        'billing_phone',
        'billing_address',
        'billing_outlet_name',
        'pi_email',
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_zip_code',
        'shipping_country',
        'shipping_outlet_name',
        'subtotal_amount',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'tax_label',
        'vat_rate',
        'placed_at',
        'fulfillment_status',
    ];

    protected $casts = [
        'ship_different' => 'boolean',
        'subtotal_amount' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'due_amount' => 'float',
        'vat_rate' => 'float',
        'placed_at' => 'datetime',
        'pi_info' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    public function isFullyApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function salesInvoice()
    {
        return $this->hasOne(SalesInvoice::class, 'order_id');
    }

    public function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class, 'order_id');
    }

    public function reconcileTotals(): bool
    {
        $this->loadMissing('items');

        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += ((float) $item->unit_price * (float) $item->quantity);
        }

        $discount = (float) ($this->discount_amount ?? 0);
        $tax = (float) ($this->tax_amount ?? 0);
        $total = max(0, round(($subtotal - $discount) + $tax, 2));
        $paid = (float) ($this->paid_amount ?? 0);
        $due = max(0, round($total - $paid, 2));

        $changed = false;
        if (round((float) $this->subtotal_amount, 2) !== round((float) $subtotal, 2)) {
            $this->subtotal_amount = $subtotal;
            $changed = true;
        }
        if (round((float) $this->total_amount, 2) !== round((float) $total, 2)) {
            $this->total_amount = $total;
            $changed = true;
        }
        if (round((float) $this->due_amount, 2) !== round((float) $due, 2)) {
            $this->due_amount = $due;
            $changed = true;
        }

        if ($due <= 0 && $this->payment_status !== 'paid' && $paid > 0) {
            $this->payment_status = 'paid';
            $changed = true;
        } elseif ($due > 0 && $paid > 0 && $this->payment_status !== 'partial') {
            $this->payment_status = 'partial';
            $changed = true;
        }

        if ($changed) {
            $this->save();
            return true;
        }

        return false;
    }
}
