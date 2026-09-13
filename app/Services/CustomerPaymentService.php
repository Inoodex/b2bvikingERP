<?php

namespace App\Services;

use App\Models\AdvancePayment;
use App\Models\ChartOfAccount;
use App\Models\CustomerPayment;
use App\Models\Order;
use App\Models\PaymentAllocation;
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
     * Get total unallocated advance balance currently available for a customer.
     */
    public function getCustomerAdvanceBalance(int $userId): float
    {
        return (float) CustomerPayment::where('user_id', $userId)
            ->where('unallocated_amount', '>', 0)
            ->sum('unallocated_amount');
    }

    /**
     * Record customer payment, knockdown invoice/order balance, and post GL journal.
     * Supports single-invoice, single-order, smart multi-invoice allocations,
     * and Advance Settlement (GL 2040 to GL 1030).
     */
    public function recordPayment(array $data, int $userId): CustomerPayment
    {
        return DB::transaction(function () use ($data, $userId) {
            $amount = (float) $data['amount'];
            if ($amount <= 0) {
                throw new Exception("Payment amount must be greater than zero.");
            }

            $isAdvanceSettlement = (strtolower((string)($data['payment_method'] ?? '')) === 'advance');

            $allocations = $data['allocations'] ?? [];
            if (empty($allocations) && !empty($data['allocations_json'])) {
                $allocations = is_array($data['allocations_json']) ? $data['allocations_json'] : json_decode($data['allocations_json'], true);
            }

            $invoice = null;
            if (!empty($data['sales_invoice_id'])) {
                $invoice = SalesInvoice::lockForUpdate()->find($data['sales_invoice_id']);
                if ($invoice && empty($allocations) && !($data['allow_advance'] ?? false) && !$isAdvanceSettlement) {
                    $due = (float)$invoice->due_amount;
                    if ($amount > ($due + 0.01)) {
                        throw new Exception("Payment amount (kr. {$amount}) exceeds outstanding invoice due balance (kr. {$due}).");
                    }
                }
            }

            $order = null;
            if (!empty($data['order_id'])) {
                $order = Order::find($data['order_id']);
            } elseif ($invoice && !empty($invoice->order_id)) {
                $order = $invoice->order;
            }

            $customerId = (int) ($data['user_id'] ?? ($data['customer_id'] ?? ($order?->user_id ?? $invoice?->order?->user_id ?? $userId)));

            // If advance settlement, validate that customer has sufficient advance deposit
            if ($isAdvanceSettlement) {
                $availableAdvance = $this->getCustomerAdvanceBalance($customerId);
                if ($amount > ($availableAdvance + 0.01)) {
                    throw new Exception("Requested settlement amount (kr. {$amount}) exceeds customer available advance deposit (kr. {$availableAdvance}).");
                }
            }

            $paymentNo = OrderNumberService::generateCustomerPaymentNumber();
            $paymentDate = !empty($data['payment_date']) ? date('Y-m-d', strtotime($data['payment_date'])) : now()->toDateString();

            $account2040 = ChartOfAccount::where('account_code', '2040')->first();
            $accountId = $data['account_id'] ?? ($isAdvanceSettlement ? $account2040?->id : ($data['bank_account_id'] ?? null));

            $payment = CustomerPayment::create([
                'payment_no'       => $paymentNo,
                'user_id'          => $customerId,
                'sales_invoice_id' => $data['sales_invoice_id'] ?? null,
                'order_id'         => $data['order_id'] ?? ($invoice?->order_id ?? null),
                'account_id'       => $accountId,
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

                        if ($isAdvanceSettlement) {
                            PaymentAllocation::create([
                                'payment_type'   => 'advance_payment',
                                'payment_id'     => $payment->id,
                                'invoice_type'   => 'order',
                                'invoice_id'     => $invoice->id,
                                'matched_amount' => $allocAmount,
                                'allocated_at'   => now(),
                            ]);
                        }
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

                    if ($isAdvanceSettlement) {
                        PaymentAllocation::create([
                            'payment_type'   => 'advance_payment',
                            'payment_id'     => $payment->id,
                            'invoice_type'   => 'order',
                            'invoice_id'     => $invoice->id,
                            'matched_amount' => $actualKnockdown,
                            'allocated_at'   => now(),
                        ]);
                    }
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

                    if ($isAdvanceSettlement) {
                        PaymentAllocation::create([
                            'payment_type'   => 'advance_payment',
                            'payment_id'     => $payment->id,
                            'invoice_type'   => 'order',
                            'invoice_id'     => $order->id,
                            'matched_amount' => $amount,
                            'allocated_at'   => now(),
                        ]);
                    }
                }
            }

            // ADVANCE SETTLEMENT PATHWAY
            if ($isAdvanceSettlement) {
                // 1. Deduct settled amount from earlier customer advances in FIFO order
                $remainingToDeduct = $totalSettled > 0 ? $totalSettled : $amount;
                $advanceSourcePayments = CustomerPayment::where('user_id', $customerId)
                    ->where('id', '!=', $payment->id)
                    ->where('unallocated_amount', '>', 0)
                    ->orderBy('id', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($advanceSourcePayments as $srcPayment) {
                    if ($remainingToDeduct <= 0) break;
                    $deduct = min((float)$srcPayment->unallocated_amount, $remainingToDeduct);
                    $newUnallocated = round((float)$srcPayment->unallocated_amount - $deduct, 2);
                    $srcPayment->update(['unallocated_amount' => $newUnallocated]);
                    $remainingToDeduct = round($remainingToDeduct - $deduct, 2);
                }

                // 2. Sync with advance_payments ledger table
                $advRecs = AdvancePayment::where('party_type', 'customer')
                    ->where('party_id', $customerId)
                    ->where('balance', '>', 0)
                    ->orderBy('id', 'asc')
                    ->lockForUpdate()
                    ->get();

                $remAdv = $totalSettled > 0 ? $totalSettled : $amount;
                foreach ($advRecs as $advRec) {
                    if ($remAdv <= 0) break;
                    $deduct = min((float)$advRec->balance, $remAdv);
                    $newBal = round((float)$advRec->balance - $deduct, 2);
                    $newApplied = round((float)$advRec->applied_amount + $deduct, 2);
                    $advRec->update([
                        'balance'        => $newBal,
                        'applied_amount' => $newApplied,
                    ]);
                    $remAdv = round($remAdv - $deduct, 2);
                }

                $settledAmount = $totalSettled > 0 ? $totalSettled : $amount;
                $payment->update([
                    'amount'             => $settledAmount,
                    'unallocated_amount' => 0.00,
                    'is_advance'         => false,
                ]);

                // 3. Post Double-Entry General Ledger Journal: DR 2040 / CR 1030
                $lines = [
                    ['account_code' => '2040', 'debit' => $settledAmount, 'credit' => 0],              // DR Customer Advances & Deposits
                    ['account_code' => '1030', 'debit' => 0,              'credit' => $settledAmount], // CR Accounts Receivable
                ];

                $customerName = $payment->user ? ($payment->user->outlet_name ?: $payment->user->name) : 'Customer';
                $narration = "Customer Advance Applied to Invoiced Dues #{$payment->payment_no} ({$customerName})";

                $this->journalService->postJournal(
                    'Customer Advance Applied',
                    $payment,
                    $lines,
                    $paymentDate,
                    $narration
                );

                return $payment;
            }

            // STANDARD CASH / BANK PAYMENT PATHWAY
            // Calculate unallocated excess / advance deposit
            $unallocatedAmount = max(0, round($amount - $totalSettled, 2));
            $isAdvance = ($totalSettled <= 0 && $amount > 0);

            $payment->update([
                'unallocated_amount' => $unallocatedAmount,
                'is_advance'         => $isAdvance,
            ]);

            // Sync with advance_payments ledger table when advance deposit is received
            if ($unallocatedAmount > 0) {
                AdvancePayment::create([
                    'party_type'     => 'customer',
                    'party_id'       => $customerId,
                    'amount'         => $unallocatedAmount,
                    'applied_amount' => 0,
                    'balance'        => $unallocatedAmount,
                    'payment_date'   => $paymentDate,
                    'note'           => "Advance deposit from Payment Receipt #{$payment->payment_no}",
                ]);
            }

            // Double-Entry General Ledger Journal Posting: DR 1010/1020 / CR 1030 / CR 2040
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
