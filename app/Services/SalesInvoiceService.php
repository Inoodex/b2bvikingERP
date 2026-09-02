<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Support\Facades\DB;

class SalesInvoiceService
{
    protected JournalEntryService $journalService;

    public function __construct(JournalEntryService $journalService)
    {
        $this->journalService = $journalService;
    }

    /**
     * Generate a new Sales Invoice from an approved / placed Order.
     */
    public function createInvoiceFromOrder(Order $order, array $customData = []): SalesInvoice
    {
        return DB::transaction(function () use ($order, $customData) {
            $invoiceNo = OrderNumberService::generateSalesInvoiceNumber();
            $issueDate = $customData['date'] ?? ($customData['issue_date'] ?? now()->toDateString());
            $dueDate = $customData['due_date'] ?? now()->addDays(30)->toDateString();

            $subtotal = (float)$order->subtotal_amount;
            $taxAmount = (float)$order->tax_amount;
            $discountAmount = (float)$order->discount_amount;
            $totalAmount = (float)$order->total_amount;
            $paidAmount = (float)$order->paid_amount;
            $dueAmount = max(0, $totalAmount - $paidAmount);

            $status = 'posted';
            if ($dueAmount <= 0) {
                $status = 'paid';
            } elseif ($paidAmount > 0) {
                $status = 'partial';
            }

            $invoice = SalesInvoice::create([
                'invoice_no'       => $invoiceNo,
                'order_id'         => $order->id,
                'date'             => $issueDate,
                'due_date'         => $dueDate,
                'subtotal_amount'  => $subtotal,
                'tax_amount'       => $taxAmount,
                'discount_amount'  => $discountAmount,
                'total_amount'     => $totalAmount,
                'paid_amount'      => $paidAmount,
                'due_amount'       => $dueAmount,
                'status'           => $status,
                'notes'            => $customData['notes'] ?? "Invoice generated for Order #{$order->order_no}",
                'created_by'       => auth()->id() ?? 1,
            ]);

            // Copy items from order to invoice items
            $order->loadMissing('items.product');
            foreach ($order->items as $item) {
                $qty = (float)$item->quantity;
                $price = (float)$item->unit_price;
                $lineTotal = round($qty * $price, 2);

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_id'       => $item->product_id,
                    'variant_id'       => $item->variant_id ?? null,
                    'description'      => $item->product ? $item->product->name : 'Item',
                    'qty'              => $qty,
                    'price'            => $price,
                    'subtotal'         => $lineTotal,
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
        return DB::transaction(function () use ($invoice, $userId) {
            $order = $invoice->order;
            $customerName = $order && $order->user ? ($order->user->outlet_name ?: $order->user->name) : 'Customer';

            $netSales = max(0, (float)$invoice->subtotal_amount - (float)$invoice->discount_amount);
            $taxAmount = (float)$invoice->tax_amount;
            $totalAmount = (float)$invoice->total_amount;

            $lines = [
                ['account_code' => '1030', 'debit' => $totalAmount, 'credit' => 0],         // DR Accounts Receivable
                ['account_code' => '4010', 'debit' => 0,            'credit' => $netSales],    // CR Sales Revenue
            ];

            if ($taxAmount > 0) {
                $lines[] = ['account_code' => '2030', 'debit' => 0, 'credit' => $taxAmount]; // CR Sales Tax Payable
            }

            $date = $invoice->date ? date('Y-m-d', strtotime($invoice->date)) : now()->toDateString();
            $this->journalService->postJournal(
                'Sales Invoice Posted',
                $invoice,
                $lines,
                $date,
                "Sales Invoice #{$invoice->invoice_no} ({$customerName})"
            );

            return $invoice;
        });
    }
}
