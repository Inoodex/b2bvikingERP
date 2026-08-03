<?php

namespace App\Services;

use App\Models\DebitNoteRefund;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VendorReturn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorReturnService
{
    /**
     * Settle Debit Note via Product Replacement / Swap (Same SKU or Substitute Item)
     */
    public function settleViaProductReplacement(array $data): VendorReturn
    {
        return DB::transaction(function () use ($data) {
            $return = VendorReturn::with(['items', 'purchase'])->findOrFail($data['vendor_return_id']);

            $product = Product::findOrFail($data['replacement_product_id']);
            $variant = !empty($data['replacement_variant_id'])
                ? ProductVariant::find($data['replacement_variant_id'])
                : null;

            $qty = (float) $data['qty'];

            // 1. Increment warehouse stock for the replacement item
            if ($variant) {
                $variant->increment('qty', $qty);
            } else {
                $product->increment('qty', $qty);
            }

            // 2. Mark Debit Note as settled via product replacement
            $return->update([
                'settlement_type'        => 'product_replacement',
                'replacement_product_id' => $product->id,
                'replacement_variant_id' => $variant?->id,
                'replacement_qty'        => $qty,
                'settled_at'             => now(),
            ]);

            return $return;
        });
    }

    /**
     * Settle Debit Note via Direct Money Refund (Cash/Bank Transfer Deposit)
     */
    public function settleViaCashRefund(array $data): DebitNoteRefund
    {
        return DB::transaction(function () use ($data) {
            $return = VendorReturn::with('purchase')->findOrFail($data['vendor_return_id']);

            // Generate sequential Refund Receipt Number: RCN-YYYYMMDD-XXXX
            $today = now()->format('Ymd');
            $countToday = DebitNoteRefund::whereDate('created_at', now()->toDateString())->count() + 1;
            $refundNo = 'RCN-' . $today . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            $refund = DebitNoteRefund::create([
                'refund_no'        => $refundNo,
                'vendor_return_id' => $return->id,
                'vendor_id'        => $return->purchase?->vendor_id ?? $data['vendor_id'] ?? 1,
                'amount'           => (float) $data['amount'],
                'refund_date'      => $data['refund_date'],
                'payment_method'   => $data['payment_method'],
                'bank_name'        => $data['bank_name'] ?? null,
                'cheque_no'        => $data['cheque_no'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'created_by'       => Auth::id(),
            ]);

            // Update Vendor Return Settlement Status
            $return->update([
                'settlement_type' => 'cash_refund',
                'settled_at'      => now(),
            ]);

            return $refund;
        });
    }
}
