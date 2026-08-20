<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\GeneralSetting;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Order;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use Illuminate\Support\Facades\DB;

class SalesInvoiceService
{
    /**
     * Create a Sales Invoice from validated payload.
     */
    public function createInvoice(array $data, int $userId): SalesInvoice
    {
        return DB::transaction(function () use ($data, $userId) {
            $invoiceNo = OrderNumberService::generateSalesInvoiceNumber();

            $subtotal = 0;
            foreach ($data['items'] as $itemData) {
                $subtotal += ((float)$itemData['qty'] * (float)$itemData['price']);
            }

            $discountAmount = (float)($data['discount_amount'] ?? 0);
            $taxRate = (float)($data['tax_rate'] ?? 0);
            $taxAmount = ($subtotal - $discountAmount) * ($taxRate / 100);
            $totalAmount = ($subtotal - $discountAmount) + $taxAmount;

            $status = $data['status'] ?? 'draft';

            $invoice = SalesInvoice::create([
                'order_id'          => $data['order_id'],
                'delivery_order_id' => $data['delivery_order_id'] ?? null,
                'invoice_no'        => $invoiceNo,
                'date'              => $data['date'],
                'due_date'          => $data['due_date'] ?? null,
                'subtotal'          => $subtotal,
                'discount_amount'   => $discountAmount,
                'tax_rate'          => $taxRate,
                'tax_amount'        => $taxAmount,
                'total_amount'      => $totalAmount,
                'paid_amount'       => 0,
                'due_amount'        => $totalAmount,
                'status'            => $status,
                'notes'             => $data['notes'] ?? null,
                'terms'             => $data['terms'] ?? null,
                'created_by'        => $userId,
            ]);

            foreach ($data['items'] as $itemData) {
                $qty = (float)$itemData['qty'];
                $price = (float)$itemData['price'];
                $lineTotal = $qty * $price;

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_id'       => $itemData['product_id'],
                    'variant_id'       => $itemData['variant_id'] ?? null,
                    'qty'              => $qty,
                    'unit_price'       => $price,
                    'total_price'      => $lineTotal,
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Post a Sales Invoice to General Ledger.
     */
    public function postInvoiceToLedger(SalesInvoice $invoice, int $userId): SalesInvoice
    {
        if ($invoice->status === 'paid' || $invoice->status === 'sent') {
            return $invoice;
        }

        return DB::transaction(function () use ($invoice, $userId) {
            $order = $invoice->order;
            $customerName = $order && $order->user ? ($order->user->outlet_name ?: $order->user->name) : 'Customer';

            $arAccount = ChartOfAccount::firstOrCreate(
                ['code' => '1030'],
                ['name' => 'Accounts Receivable (Trade Debtors)', 'type' => 'asset', 'status' => 'active']
            );

            $salesAccount = ChartOfAccount::firstOrCreate(
                ['code' => '4010'],
                ['name' => 'Sales Revenue', 'type' => 'income', 'status' => 'active']
            );

            $vatAccount = ChartOfAccount::firstOrCreate(
                ['code' => '2030'],
                ['name' => 'VAT / Tax Payable', 'type' => 'liability', 'status' => 'active']
            );

            $entryNo = OrderNumberService::generate('JV', JournalEntry::class, 'journal_entries');

            $journalEntry = JournalEntry::create([
                'entry_no'       => $entryNo,
                'date'           => $invoice->date,
                'reference_type' => SalesInvoice::class,
                'reference_id'   => $invoice->id,
                'description'    => "Automated Journal for Sales Invoice #{$invoice->invoice_no} ({$customerName})",
                'status'         => 'posted',
                'created_by'     => $userId,
            ]);

            // 1. Debit AR (Total Invoice Amount)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id'       => $arAccount->id,
                'debit_amount'     => $invoice->total_amount,
                'credit_amount'    => 0,
                'description'      => "AR: Invoice #{$invoice->invoice_no} - {$customerName}",
                'party_type'       => 'customer',
                'party_id'         => $order?->user_id,
            ]);

            // 2. Credit Sales Revenue (Subtotal - Discount)
            $netSales = max(0, (float)$invoice->subtotal - (float)$invoice->discount_amount);
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id'       => $salesAccount->id,
                'debit_amount'     => 0,
                'credit_amount'    => $netSales,
                'description'      => "Sales Revenue: Invoice #{$invoice->invoice_no}",
                'party_type'       => 'customer',
                'party_id'         => $order?->user_id,
            ]);

            // 3. Credit Tax/VAT Payable
            if ((float)$invoice->tax_amount > 0) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id'       => $vatAccount->id,
                    'debit_amount'     => 0,
                    'credit_amount'    => (float)$invoice->tax_amount,
                    'description'      => "VAT Payable ({$invoice->tax_rate}%): Invoice #{$invoice->invoice_no}",
                    'party_type'       => 'customer',
                    'party_id'         => $order?->user_id,
                ]);
            }

            $invoice->update(['status' => 'sent']);
            return $invoice->fresh();
        });
    }
}
