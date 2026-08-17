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

    public function reconcileTotals(): bool
    {
        // Check if there are any issues linked to this order
        $issues = \App\Models\Issue::where('order_id', $this->id)->with('items')->get();

        if ($issues->isEmpty()) {
            // Re-calculate due amount based on current database total and paid amount
            $due = max(0, round($this->total_amount - $this->paid_amount, 2));
            if ($this->due_amount != $due) {
                $this->due_amount = $due;
                $this->save();
                return true;
            }
            return false;
        }

        $issuedMap = [];
        foreach ($issues as $issue) {
            foreach ($issue->items as $issueItem) {
                $key = $issueItem->product_id . '_' . ($issueItem->variant_id ?? 0);
                $issuedMap[$key] = ($issuedMap[$key] ?? 0) + $issueItem->quantity;
            }
        }

        // Calculate subtotal from issued quantities multiplied by original unit price in order items
        $subtotal = 0;
        foreach ($this->items as $item) {
            $key = $item->product_id . '_' . ($item->variant_id ?? 0);
            if (isset($issuedMap[$key])) {
                $quantity = $issuedMap[$key];
                $subtotal += $item->unit_price * $quantity;
            }
        }

        $total = $subtotal - $this->discount_amount + $this->tax_amount;
        $paid = (float) $this->paid_amount;
        $due = max(0, round($total - $paid, 2));

        $changed = false;
        if ($this->subtotal_amount != $subtotal) {
            $this->subtotal_amount = $subtotal;
            $changed = true;
        }
        if ($this->total_amount != $total) {
            $this->total_amount = $total;
            $changed = true;
        }
        if ($this->due_amount != $due) {
            $this->due_amount = $due;
            $changed = true;
        }

        if ($due <= 0 && $this->payment_status !== 'paid') {
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
