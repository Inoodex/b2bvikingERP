<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\CustomerPayment;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Order;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\DB;

class CustomerPaymentService
{
    /**
     * Record customer payment, knockdown invoice/order balance, and post GL journal.
     */
    public function recordPayment(array $data, int $userId): CustomerPayment
    {
        return DB::transaction(function () use ($data, $userId) {
            $paymentNo = OrderNumberService::generateCustomerPaymentNumber();

            $payment = CustomerPayment::create([
                'payment_no'       => $paymentNo,
                'user_id'          => $data['user_id'] ?? ($data['customer_id'] ?? null),
                'sales_invoice_id' => $data['sales_invoice_id'] ?? null,
                'order_id'         => $data['order_id'] ?? null,
                'account_id'       => $data['account_id'] ?? ($data['bank_account_id'] ?? null),
                'amount'           => $data['amount'],
                'payment_method'   => $data['payment_method'],
                'reference_no'     => $data['reference_no'] ?? ($data['transaction_id'] ?? null),
                'payment_date'     => $data['payment_date'],
                'notes'            => $data['notes'] ?? null,
                'created_by'       => $userId,
                'status'           => 'posted',
            ]);

            // 1. Auto-Knockdown Sales Invoice & Order Due Amount
            if ($payment->sales_invoice_id) {
                $invoice = SalesInvoice::with('order')->find($payment->sales_invoice_id);
                if ($invoice) {
                    $newPaid = (float)$invoice->paid_amount + (float)$payment->amount;
                    $newDue = max(0, (float)$invoice->total_amount - $newPaid);
                    $invoice->update([
                        'paid_amount' => $newPaid,
                        'due_amount'  => $newDue,
                    ]);

                    if ($invoice->order) {
                        $orderPaid = (float)$invoice->order->paid_amount + (float)$payment->amount;
                        $orderDue = max(0, (float)$invoice->order->total_amount - $orderPaid);
                        $paymentStatus = $orderDue <= 0 ? 'paid' : ($orderPaid > 0 ? 'partially_paid' : 'unpaid');
                        $invoice->order->update([
                            'paid_amount'    => $orderPaid,
                            'due_amount'     => $orderDue,
                            'payment_status' => $paymentStatus,
                        ]);
                    }
                }
            } elseif ($payment->order_id) {
                $order = Order::find($payment->order_id);
                if ($order) {
                    $orderPaid = (float)$order->paid_amount + (float)$payment->amount;
                    $orderDue = max(0, (float)$order->total_amount - $orderPaid);
                    $paymentStatus = $orderDue <= 0 ? 'paid' : ($orderPaid > 0 ? 'partially_paid' : 'unpaid');
                    $order->update([
                        'paid_amount'    => $orderPaid,
                        'due_amount'     => $orderDue,
                        'payment_status' => $paymentStatus,
                    ]);
                }
            }

            // 2. Double-Entry General Ledger Journal Posting
            $this->postJournalEntry($payment, $userId);

            return $payment;
        });
    }

    /**
     * Post Double-Entry Journal for Customer Payment (DR Cash/Bank, CR Accounts Receivable).
     */
    protected function postJournalEntry(CustomerPayment $payment, int $userId): void
    {
        $cashBankCode = in_array($payment->payment_method, ['cash']) ? '1010' : '1020';
        $cashBankAccount = ChartOfAccount::firstOrCreate(
            ['code' => $cashBankCode],
            [
                'name'   => $cashBankCode === '1010' ? 'Cash on Hand' : 'Bank Main Account',
                'type'   => 'asset',
                'status' => 'active'
            ]
        );

        $arAccount = ChartOfAccount::firstOrCreate(
            ['code' => '1030'],
            [
                'name'   => 'Accounts Receivable (Trade Debtors)',
                'type'   => 'asset',
                'status' => 'active'
            ]
        );

        $entryNo = OrderNumberService::generate('JV', JournalEntry::class, 'journal_entries');
        $customerName = $payment->customer ? ($payment->customer->outlet_name ?: $payment->customer->name) : 'Customer';

        $journalEntry = JournalEntry::create([
            'entry_no'       => $entryNo,
            'date'           => $payment->payment_date,
            'reference_type' => CustomerPayment::class,
            'reference_id'   => $payment->id,
            'description'    => "Customer Payment Receipt #{$payment->payment_no} ({$customerName})",
            'status'         => 'posted',
            'created_by'     => $userId,
        ]);

        // DR Cash/Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id'       => $cashBankAccount->id,
            'debit_amount'     => $payment->amount,
            'credit_amount'    => 0,
            'description'      => "Receipt #{$payment->payment_no} - {$payment->payment_method}",
            'party_type'       => 'customer',
            'party_id'         => $payment->user_id,
        ]);

        // CR Accounts Receivable
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id'       => $arAccount->id,
            'debit_amount'     => 0,
            'credit_amount'    => $payment->amount,
            'description'      => "Payment for AR: {$customerName}",
            'party_type'       => 'customer',
            'party_id'         => $payment->user_id,
        ]);
    }
}
