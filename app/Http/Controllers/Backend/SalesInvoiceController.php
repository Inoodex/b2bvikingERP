<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\SalesInvoiceDataTable;
use App\Http\Controllers\Controller;
use App\Models\DeliveryOrder;
use App\Models\GeneralSetting;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Order;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Services\OrderNumberService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesInvoiceController extends Controller
{
    public function index(SalesInvoiceDataTable $dataTable)
    {
        return $dataTable->render('backend.sales_invoices.index');
    }

    public function create(Request $request)
    {
        $selectedOrderId = $request->get('order_id');
        $selectedDeliveryOrderId = $request->get('delivery_order_id');

        $orders = Order::with(['user', 'items.product', 'items.variant'])
            ->whereIn('status', ['approved', 'processing', 'completed'])
            ->latest()
            ->get();

        $deliveryOrders = DeliveryOrder::with(['order.user', 'items.product', 'items.variant'])
            ->where('status', 'dispatched')
            ->latest()
            ->get();

        $preloadedOrder = null;
        $preloadedDeliveryOrder = null;

        if ($selectedDeliveryOrderId) {
            $preloadedDeliveryOrder = DeliveryOrder::with(['order.user', 'items.product', 'items.variant'])->find($selectedDeliveryOrderId);
            if ($preloadedDeliveryOrder) {
                $selectedOrderId = $preloadedDeliveryOrder->order_id;
            }
        }

        if ($selectedOrderId) {
            $preloadedOrder = Order::with(['user', 'items.product', 'items.variant'])->find($selectedOrderId);
        }

        return view('backend.sales_invoices.create', compact('orders', 'deliveryOrders', 'selectedOrderId', 'selectedDeliveryOrderId', 'preloadedOrder', 'preloadedDeliveryOrder'));
    }

    public function getItems(Request $request)
    {
        $deliveryOrderId = $request->get('delivery_order_id');
        $orderId = $request->get('order_id');

        if ($deliveryOrderId) {
            $do = DeliveryOrder::with(['order.user', 'items.product', 'items.variant', 'items.orderItem'])->find($deliveryOrderId);
            if (!$do) {
                return response()->json(['success' => false, 'message' => 'Delivery Order not found']);
            }

            $items = $do->items->map(function ($item) {
                $unitPrice = (float)($item->unit_price ?: ($item->orderItem ? $item->orderItem->unit_price : $item->product->price));
                $qty = (float)$item->qty_delivered;
                $lineSubtotal = $unitPrice * $qty;

                return [
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->product ? $item->product->name : 'Product',
                    'variant_name' => $item->variant ? $item->variant->name : null,
                    'unit' => ($item->product && $item->product->unit) ? $item->product->unit->name : 'Pcs',
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'line_subtotal' => $lineSubtotal,
                ];
            });

            return response()->json([
                'success' => true,
                'type' => 'delivery_order',
                'order_id' => $do->order_id,
                'order_no' => $do->order ? $do->order->order_no : '-',
                'customer_name' => $do->order && $do->order->user ? ($do->order->user->outlet_name ?: $do->order->user->name) : 'Guest / Cash',
                'items' => $items,
            ]);
        }

        if ($orderId) {
            $order = Order::with(['user', 'items.product', 'items.variant'])->find($orderId);
            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Sales Order not found']);
            }

            $items = $order->items->map(function ($item) {
                $unitPrice = (float)$item->unit_price;
                $qty = (float)$item->quantity;
                $lineSubtotal = $unitPrice * $qty;

                return [
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->product ? $item->product->name : 'Product',
                    'variant_name' => $item->variant ? $item->variant->name : null,
                    'unit' => ($item->product && $item->product->unit) ? $item->product->unit->name : 'Pcs',
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'line_subtotal' => $lineSubtotal,
                ];
            });

            return response()->json([
                'success' => true,
                'type' => 'order',
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'customer_name' => $order->user ? ($order->user->outlet_name ?: $order->user->name) : 'Guest / Cash',
                'items' => $items,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid parameters']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $invoiceNo = OrderNumberService::generateSalesInvoiceNumber();

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $subtotal += ((float)$itemData['qty'] * (float)$itemData['price']);
            }

            $discountAmount = (float)($request->get('discount_amount', 0));
            $taxRate = (float)($request->get('tax_rate', 0));
            $taxAmount = ($subtotal - $discountAmount) * ($taxRate / 100);
            $totalAmount = ($subtotal - $discountAmount) + $taxAmount;

            $status = $request->get('status', 'draft');

            $invoice = SalesInvoice::create([
                'order_id' => $request->order_id,
                'invoice_no' => $invoiceNo,
                'subtotal_amount' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0.00,
                'due_amount' => $totalAmount,
                'status' => $status,
                'date' => $request->date,
                'due_date' => $request->due_date ?: now()->addDays(30),
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $itemData) {
                $lineSubtotal = (float)$itemData['qty'] * (float)$itemData['price'];
                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_id' => $itemData['product_id'],
                    'qty' => $itemData['qty'],
                    'price' => $itemData['price'],
                    'subtotal' => $lineSubtotal,
                ]);
            }

            // If created directly in posted status, trigger GL posting
            if ($status === 'posted') {
                $this->postInvoiceAccounting($invoice);
            }

            DB::commit();

            Toastr::success('Commercial Sales Invoice created successfully!', 'Success');
            return redirect()->route('admin.sales-invoices.show', $invoice->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sales Invoice Store Error: ' . $e->getMessage());
            Toastr::error('Failed to create Sales Invoice: ' . $e->getMessage(), 'Error');
            return redirect()->back()->withInput();
        }
    }

    public function show($id)
    {
        $invoice = SalesInvoice::with([
            'order.user',
            'items.product.unit',
            'items.variant'
        ])->findOrFail($id);

        $generalSetting = GeneralSetting::first();

        return view('backend.sales_invoices.show', compact('invoice', 'generalSetting'));
    }

    public function post($id)
    {
        $invoice = SalesInvoice::findOrFail($id);

        if ($invoice->status === 'posted' || $invoice->status === 'paid') {
            Toastr::warning('This invoice has already been posted.', 'Warning');
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            $invoice->update([
                'status' => 'posted',
            ]);

            $this->postInvoiceAccounting($invoice);

            DB::commit();

            Toastr::success('Commercial Sales Invoice posted successfully! General Ledger updated.', 'Posted');
            return redirect()->route('admin.sales-invoices.show', $invoice->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sales Invoice Post Error: ' . $e->getMessage());
            Toastr::error('Failed to post Sales Invoice: ' . $e->getMessage(), 'Error');
            return redirect()->back();
        }
    }

    private function postInvoiceAccounting(SalesInvoice $invoice)
    {
        // Check if Chart of Accounts / Journal Entries exist
        if (class_exists(JournalEntry::class)) {
            $journalEntry = JournalEntry::create([
                'entry_number' => 'JE-INV-' . $invoice->invoice_no,
                'date' => $invoice->date ?: now(),
                'reference' => 'Commercial Sales Invoice #' . $invoice->invoice_no,
                'notes' => 'Accounting entry for Invoice #' . $invoice->invoice_no,
                'status' => 'posted',
                'created_by' => Auth::id(),
            ]);

            // Accounts Receivable Dr
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_name' => 'Accounts Receivable (Trade Debtors)',
                'debit' => $invoice->total_amount,
                'credit' => 0.00,
                'description' => 'Customer Debt for Invoice #' . $invoice->invoice_no,
            ]);

            // Sales Revenue Cr
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_name' => 'Sales Revenue',
                'debit' => 0.00,
                'credit' => $invoice->subtotal_amount - $invoice->discount_amount,
                'description' => 'Gross Sales Revenue for Invoice #' . $invoice->invoice_no,
            ]);

            // Output VAT Cr (if any)
            if ($invoice->tax_amount > 0) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_name' => 'Output VAT / Sales Tax Payable',
                    'debit' => 0.00,
                    'credit' => $invoice->tax_amount,
                    'description' => 'Output Sales Tax Collected for Invoice #' . $invoice->invoice_no,
                ]);
            }
        }
    }

    public function downloadPdf($id)
    {
        $invoice = SalesInvoice::with([
            'order.user',
            'items.product.unit',
            'items.variant'
        ])->findOrFail($id);

        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('backend.pdf.sales_invoice', compact('invoice', 'generalSetting'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Sales_Invoice_' . $invoice->invoice_no . '.pdf');
    }
}
