<?php

namespace App\Services;

use App\Models\PaymentAllocation;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\PurchasePaymentReceipt;
use App\Models\VendorBill;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VendorPaymentService
{
    /**
     * Process a purchase payment voucher and allocate against Vendor Bill / Purchase Order.
     */
    public function processPayment(array $data, mixed $receiptFile = null): PurchasePayment
    {
        return DB::transaction(function () use ($data, $receiptFile) {
            $purchase = Purchase::lockForUpdate()->findOrFail($data['purchase_id']);
            $vendorBill = isset($data['vendor_bill_id']) && $data['vendor_bill_id']
                ? VendorBill::lockForUpdate()->find($data['vendor_bill_id'])
                : null;

            // Generate Payment Voucher No: PAY-YYYYMMDD-XXXX
            $today = now()->format('Ymd');
            $countToday = PurchasePayment::whereDate('created_at', now()->toDateString())->count() + 1;
            $paymentNo = 'PAY-' . $today . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            $amount = (float) $data['amount'];
            $exchangeRate = isset($data['exchange_rate']) && (float) $data['exchange_rate'] > 0
                ? (float) $data['exchange_rate']
                : 1.0;
            $baseAmount = round($amount * $exchangeRate, 2);

            // Save Payment Voucher record
            $payment = PurchasePayment::create([
                'payment_no' => $paymentNo,
                'payment_date' => $data['payment_date'],
                'purchase_id' => $purchase->id,
                'vendor_id' => $data['vendor_id'],
                'currency_id' => $data['currency_id'] ?? $purchase->currency_id,
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_method' => $data['payment_method'],
                'bank_name' => $data['bank_name'] ?? null,
                'cheque_no' => $data['cheque_no'] ?? null,
                'amount' => $amount,
                'exchange_rate' => $exchangeRate,
                'base_amount' => $baseAmount,
                'status' => 'approved',
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Save receipt file if uploaded
            if ($receiptFile && $receiptFile->isValid()) {
                $path = $receiptFile->store('purchase_payments/receipts', 'public');
                PurchasePaymentReceipt::create([
                    'purchase_payment_id' => $payment->id,
                    'file_path' => $path,
                    'original_name' => $receiptFile->getClientOriginalName(),
                    'mime_type' => $receiptFile->getClientMimeType(),
                    'file_size' => $receiptFile->getSize(),
                ]);
            }

            // Allocate against Vendor Bill if bill provided
            if ($vendorBill) {
                $newPaid = round($vendorBill->paid_amount + $amount, 2);
                $newDue = max(0, round($vendorBill->grand_total - $newPaid, 2));

                $status = 'unpaid';
                if ($newDue <= 0) {
                    $status = $newPaid > $vendorBill->grand_total ? 'overpaid' : 'paid';
                } else if ($newPaid > 0) {
                    $status = 'partial';
                }

                $vendorBill->update([
                    'paid_amount' => $newPaid,
                    'due_amount' => $newDue,
                    'payment_status' => $status,
                ]);

                // Create payment allocation entry
                PaymentAllocation::create([
                    'payment_type' => 'purchase_payment',
                    'payment_id' => $payment->id,
                    'invoice_type' => 'purchase',
                    'invoice_id' => $vendorBill->id,
                    'matched_amount' => $amount,
                    'allocated_at' => now(),
                ]);
            }

            // Update Purchase total paid & due amounts
            $poPaid = round($purchase->paid_amount + $baseAmount, 2);
            $poTotal = $purchase->base_amount > 0 ? $purchase->base_amount : $purchase->total_amount;
            $poDue = max(0, round($poTotal - $poPaid, 2));

            $poPaymentStatus = 'unpaid';
            if ($poDue <= 0) {
                $poPaymentStatus = 'paid';
            } else if ($poPaid > 0) {
                $poPaymentStatus = 'partial';
            }

            $purchase->update([
                'paid_amount' => $poPaid,
                'due_amount' => $poDue,
                'payment_status' => $poPaymentStatus,
            ]);

            return $payment;
        });
    }
}
