<?php

namespace App\Services;

use App\Models\PaymentAllocation;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\PurchasePaymentReceipt;
use App\Models\VendorBill;
use App\Services\Accounting\JournalEntryService;
use Exception;
use Illuminate\Support\Facades\DB;

class VendorPaymentService
{
    protected JournalEntryService $journalService;

    public function __construct(JournalEntryService $journalService)
    {
        $this->journalService = $journalService;
    }

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

            $amount = (float) $data['amount'];
            if ($amount <= 0) {
                throw new Exception("Payment amount must be greater than zero.");
            }

            // Overpayment Guard for Vendor Bill
            if ($vendorBill) {
                $due = (float) $vendorBill->due_amount;
                if ($amount > ($due + 0.01)) {
                    throw new Exception("Payment amount (kr. {$amount}) exceeds outstanding Vendor Bill due balance (kr. {$due}).");
                }
            }

            // Generate Payment Voucher No: PAY-YYYYMMDD-XXXXX
            $today = now()->format('Ymd');
            $countToday = PurchasePayment::whereDate('created_at', now()->toDateString())->count() + 1;
            $paymentNo = 'PAY-' . $today . '-' . str_pad($countToday, 5, '0', STR_PAD_LEFT);

            $exchangeRate = isset($data['exchange_rate']) && (float) $data['exchange_rate'] > 0
                ? (float) $data['exchange_rate']
                : 1.0;
            $baseAmount = round($amount * $exchangeRate, 2);

            $paymentDate = !empty($data['payment_date']) ? date('Y-m-d', strtotime($data['payment_date'])) : now()->toDateString();

            // Save Payment Voucher record
            $payment = PurchasePayment::create([
                'payment_no'     => $paymentNo,
                'payment_date'   => $paymentDate,
                'purchase_id'    => $purchase->id,
                'vendor_id'      => $data['vendor_id'] ?? $purchase->vendor_id,
                'currency_id'    => $data['currency_id'] ?? $purchase->currency_id,
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_method' => $data['payment_method'],
                'bank_name'      => $data['bank_name'] ?? null,
                'cheque_no'      => $data['cheque_no'] ?? null,
                'amount'         => $amount,
                'exchange_rate'  => $exchangeRate,
                'base_amount'    => $baseAmount,
                'status'         => 'approved',
                'note'           => $data['note'] ?? null,
                'created_by'     => auth()->id() ?? 1,
            ]);

            // Save receipt file if uploaded
            if ($receiptFile && $receiptFile->isValid()) {
                $path = $receiptFile->store('purchase_payments/receipts', 'public');
                PurchasePaymentReceipt::create([
                    'purchase_payment_id' => $payment->id,
                    'file_path'           => $path,
                    'original_name'       => $receiptFile->getClientOriginalName(),
                    'mime_type'           => $receiptFile->getClientMimeType(),
                    'file_size'           => $receiptFile->getSize(),
                ]);
            }

            // Allocate against Vendor Bill if bill provided
            if ($vendorBill) {
                $newPaid = round((float)$vendorBill->paid_amount + $amount, 2);
                $newDue = max(0, round((float)$vendorBill->grand_total - $newPaid, 2));

                $status = 'unpaid';
                if ($newDue <= 0) {
                    $status = 'paid';
                } elseif ($newPaid > 0) {
                    $status = 'partial';
                }

                $vendorBill->update([
                    'paid_amount'    => $newPaid,
                    'due_amount'     => $newDue,
                    'payment_status' => $status,
                ]);

                // Create payment allocation entry
                PaymentAllocation::create([
                    'payment_type'   => 'purchase_payment',
                    'payment_id'     => $payment->id,
                    'invoice_type'   => 'purchase',
                    'invoice_id'     => $vendorBill->id,
                    'matched_amount' => $amount,
                    'allocated_at'   => now(),
                ]);
            }

            // Update Purchase total paid & due amounts
            $poPaid = round((float)$purchase->paid_amount + $baseAmount, 2);
            $poTotal = $purchase->base_amount > 0 ? (float)$purchase->base_amount : (float)$purchase->total_amount;
            $poDue = max(0, round($poTotal - $poPaid, 2));

            $poPaymentStatus = 'unpaid';
            if ($poDue <= 0) {
                $poPaymentStatus = 'paid';
            } elseif ($poPaid > 0) {
                $poPaymentStatus = 'partial';
            }

            $purchase->update([
                'paid_amount'    => $poPaid,
                'due_amount'     => $poDue,
                'payment_status' => $poPaymentStatus,
            ]);

            // Post General Ledger Double-Entry Journal: DR AP (2010) / CR Cash or Bank (1010/1020)
            $cashBankCode = in_array(strtolower((string)$payment->payment_method), ['cash']) ? '1010' : '1020';
            $lines = [
                ['account_code' => '2010',         'debit' => $baseAmount, 'credit' => 0], // DR Accounts Payable
                ['account_code' => $cashBankCode, 'debit' => 0,           'credit' => $baseAmount], // CR Cash/Bank
            ];

            $vendorName = $purchase->vendor ? $purchase->vendor->shop_name : 'Supplier';
            $this->journalService->postJournal(
                'Vendor Payment Paid',
                $payment,
                $lines,
                $paymentDate,
                "Vendor Payment Voucher #{$payment->payment_no} ({$vendorName}) for PO #{$purchase->po_no}"
            );

            return $payment;
        });
    }
}
