<?php

namespace App\Services;

use App\Models\CustomerPayment;
use App\Models\Order;
use App\Models\SalesInvoice;
use App\Services\Accounting\JournalEntryService;
use Exception;
use Illuminate\Support\Facades\DB;

class CustomerPaymentService
{
    protected JournalEntryService $journalService;

    public function __construct(JournalEntryService $journalService)
    {
        $this->journalService = $journalService;
    }

    /**
     * Record customer payment, knockdown invoice/order balance, and post GL journal.
     * Supports single-invoice, single-order, and smart multi-invoice allocations.
     */
    public function recordPayment(array $data, int $userId): CustomerPayment
    {
        return DB::transaction(function () use ($data, $userId) {
            $amount = (float) $data['amount'];
            if ($amount <= 0) {
                throw new Exception("Payment amount must be greater than zero.");
            }

            $allocations = $data['allocations'] ?? [];
            if (empty($allocations) && !empty($data['allocations_json'])) {
                $allocations = is_array($data['allocations_json']) ? $data['allocations_json'] : json_decode($data['allocations_json'], true);
            }

            // 1. Overpayment Guard for Single Sales Invoice
            if (empty($allocations) && !empty($data['sales_invoice_id']) && !($data['allow_advance'] ?? false)) {
                $invoice = SalesInvoice::lockForUpdate()->find($data['sales_invoice_id']);
                if ($invoice) {
                    $due = (float)$invoice->due_amount;
                    if ($amount > ($due + 0.01)) {
                        throw new Exception("Payment amount (kr. {$amount}) exceeds outstanding invoice due balance (kr. {$due}).");
                    }
                }
            }

            $paymentNo = OrderNumberService::generateCustomerPaymentNumber();
            $paymentDate = !empty($data['payment_date']) ? date('Y-m-d', strtotime($data['payment_date'])) : now()->toDateString();

            $payment = CustomerPayment::create([
                'payment_no'       => $paymentNo,
                'user_id'          => $data['user_id'] ?? ($data['customer_id'] ?? null),
                'sales_invoice_id' => $data['sales_invoice_id'] ?? null,
                'order_id'         => $data['order_id'] ?? null,
                'account_id'       => $data['account_id'] ?? ($data['bank_account_id'] ?? null),
                'amount'           => $amount,
                'payment_method'   => $data['payment_method'],
                'reference_no'     => $data['reference_no'] ?? ($data['transaction_id'] ?? null),
                'payment_date'     => $paymentDate,
                'notes'            => $data['notes'] ?? null,
                'created_by'       => $userId,
                'status'           => 'posted',
            ]);

            $totalSettled = 0.00;

            // 2. Multi-Invoice Allocation Engine
            if (!empty($allocations) && is_array($allocations)) {
                foreach ($allocations as $alloc) {
                    $allocAmount = (float) ($alloc['amount'] ?? 0);
                    $invId = $alloc['sales_invoice_id'] ?? ($alloc['invoice_id'] ?? null);
                    if ($allocAmount <= 0 || !$invId) continue;

                    $invoice = SalesInvoice::with('order')->lockForUpdate()->find($invId);
                    if ($invoice) {
                        $newPaid = round((float)$invoice->paid_amount + $allocAmount, 2);
                        $newDue = max(0, round((float)$invoice->total_amount - $newPaid, 2));
                        $invoice->update([
                            'paid_amount' => $newPaid,
                            'due_amount'  => $newDue,
                            'status'      => $newDue <= 0 ? 'paid' : 'partial',
                        ]);

                        if ($invoice->order) {
                            $orderPaid = round((float)$invoice->order->paid_amount + $allocAmount, 2);
                            $orderDue = max(0, round((float)$invoice->order->total_amount - $orderPaid, 2));
                            $paymentStatus = $orderDue <= 0 ? 'paid' : ($orderPaid > 0 ? 'partially_paid' : 'unpaid');
                            $invoice->order->update([
                                'paid_amount'    => $orderPaid,
                                'due_amount'     => $orderDue,
                                'payment_status' => $paymentStatus,
                            ]);
                        }
                        $totalSettled += $allocAmount;
                    }
                }
            } elseif ($payment->sales_invoice_id) {
                // Single Invoice Knockdown
                $invoice = SalesInvoice::with('order')->lockForUpdate()->find($payment->sales_invoice_id);
                if ($invoice) {
                    $actualKnockdown = ($data['allow_advance'] ?? false) 
                        ? min($amount, (float)$invoice->due_amount) 
                        : $amount;

                    $newPaid = round((float)$invoice->paid_amount + $actualKnockdown, 2);
                    $newDue = max(0, round((float)$invoice->total_amount - $newPaid, 2));
                    $invoice->update([
                        'paid_amount' => $newPaid,
                        'due_amount'  => $newDue,
                        'status'      => $newDue <= 0 ? 'paid' : 'partial',
                    ]);

                    if ($invoice->order) {
                        $orderPaid = round((float)$invoice->order->paid_amount + $actualKnockdown, 2);
                        $orderDue = max(0, round((float)$invoice->order->total_amount - $orderPaid, 2));
                        $paymentStatus = $orderDue <= 0 ? 'paid' : ($orderPaid > 0 ? 'partially_paid' : 'unpaid');
                        $invoice->order->update([
                            'paid_amount'    => $orderPaid,
                            'due_amount'     => $orderDue,
                            'payment_status' => $paymentStatus,
                        ]);
                    }
                    $totalSettled += $actualKnockdown;
                }
            } elseif ($payment->order_id) {
                // Single Order Knockdown
                $order = Order::lockForUpdate()->find($payment->order_id);
                if ($order) {
                    $orderPaid = round((float)$order->paid_amount + $amount, 2);
                    $orderDue = max(0, round((float)$order->total_amount - $orderPaid, 2));
                    $paymentStatus = $orderDue <= 0 ? 'paid' : ($orderPaid > 0 ? 'partially_paid' : 'unpaid');
                    $order->update([
                        'paid_amount'    => $orderPaid,
                        'due_amount'     => $orderDue,
                        'payment_status' => $paymentStatus,
                    ]);
                    $totalSettled += $amount;
                }
            }

            // Calculate unallocated excess / advance deposit
            $unallocatedAmount = max(0, round($amount - $totalSettled, 2));
            $isAdvance = ($totalSettled <= 0 && $amount > 0);

            $payment->update([
                'unallocated_amount' => $unallocatedAmount,
                'is_advance'         => $isAdvance,
            ]);

            // 3. Double-Entry General Ledger Journal Posting
            $cashBankCode = in_array(strtolower((string)$payment->payment_method), ['cash']) ? '1010' : '1020';
            $lines = [
                ['account_code' => $cashBankCode, 'debit' => $amount, 'credit' => 0], // DR Cash/Bank
            ];

            if ($totalSettled > 0) {
                $lines[] = ['account_code' => '1030', 'debit' => 0, 'credit' => $totalSettled]; // CR Accounts Receivable
            }

            if ($unallocatedAmount > 0) {
                $lines[] = ['account_code' => '2040', 'debit' => 0, 'credit' => $unallocatedAmount]; // CR Customer Advances & Deposits
            }

            $customerName = $payment->user ? ($payment->user->outlet_name ?: $payment->user->name) : 'Customer';
            $narration = $isAdvance 
                ? "Customer Advance Deposit Receipt #{$payment->payment_no} ({$customerName})" 
                : "Customer Payment Receipt #{$payment->payment_no} ({$customerName})";

            $this->journalService->postJournal(
                'Customer Payment Received',
                $payment,
                $lines,
                $paymentDate,
                $narration
            );

            return $payment;
        });
    }
}
