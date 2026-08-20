<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\SalesInvoiceDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreSalesInvoiceRequest;
use App\Models\DeliveryOrder;
use App\Models\Order;
use App\Models\SalesInvoice;
use App\Models\Tax;
use App\Services\SalesInvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SalesInvoiceController extends Controller
{
    protected SalesInvoiceService $salesInvoiceService;

    public function __construct(SalesInvoiceService $salesInvoiceService)
    {
        $this->salesInvoiceService = $salesInvoiceService;
    }

    public function index(SalesInvoiceDataTable $dataTable)
    {
        return $dataTable->render('backend.sales_invoices.index');
    }

    public function create(Request $request): View
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

        $defaultTax = Tax::where('is_default', 1)->first() ?: Tax::where('status', 1)->first();
        $defaultTaxRate = $defaultTax ? (float)$defaultTax->value : 5.00;

        return view('backend.sales_invoices.create', compact('orders', 'deliveryOrders', 'preloadedOrder', 'preloadedDeliveryOrder', 'defaultTaxRate'));
    }

    public function getInvoiceSourceItems(Request $request): JsonResponse
    {
        $deliveryOrderId = $request->get('delivery_order_id');
        $orderId = $request->get('order_id');

        if ($deliveryOrderId) {
            $do = DeliveryOrder::with(['order.user', 'items.product', 'items.variant'])->find($deliveryOrderId);
            if (!$do) {
                return response()->json(['success' => false, 'message' => 'Delivery Order not found']);
            }

            $items = $do->items->map(function ($item) {
                $unitPrice = (float)($item->unit_price > 0 ? $item->unit_price : ($item->product ? $item->product->price : 0));
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

            $defaultTax = Tax::where('is_default', 1)->first() ?: Tax::where('status', 1)->first();
            $defaultTaxRate = $defaultTax ? (float)$defaultTax->value : 5.00;

            return response()->json([
                'success' => true,
                'type' => 'delivery_order',
                'order_id' => $do->order_id,
                'order_no' => $do->order ? $do->order->order_no : '-',
                'customer_name' => $do->order && $do->order->user ? ($do->order->user->outlet_name ?: $do->order->user->name) : 'Guest / Cash',
                'discount_amount' => (float)($do->order ? $do->order->discount_amount : 0),
                'vat_rate' => (float)($do->order && $do->order->vat_rate > 0 ? $do->order->vat_rate : $defaultTaxRate),
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

            $defaultTax = Tax::where('is_default', 1)->first() ?: Tax::where('status', 1)->first();
            $defaultTaxRate = $defaultTax ? (float)$defaultTax->value : 5.00;

            return response()->json([
                'success' => true,
                'type' => 'order',
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'customer_name' => $order->user ? ($order->user->outlet_name ?: $order->user->name) : 'Guest / Cash',
                'discount_amount' => (float)$order->discount_amount,
                'vat_rate' => (float)($order->vat_rate > 0 ? $order->vat_rate : $defaultTaxRate),
                'items' => $items,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid parameters']);
    }

    public function store(StoreSalesInvoiceRequest $request): RedirectResponse
    {
        $invoice = $this->salesInvoiceService->createInvoice($request->validated(), Auth::id() ?? 1);

        Toastr::success('Sales Invoice generated successfully.', 'Success');
        return redirect()->route('admin.sales-invoices.show', $invoice->id);
    }

    public function show($id): View
    {
        $invoice = SalesInvoice::with([
            'order.user',
            'deliveryOrder',
            'items.product',
            'items.variant',
            'creator',
            'journalEntries.lines.account'
        ])->findOrFail($id);

        return view('backend.sales_invoices.show', compact('invoice'));
    }

    public function post($id): RedirectResponse
    {
        $invoice = SalesInvoice::with(['order.user', 'items'])->findOrFail($id);

        if ($invoice->status === 'paid' || $invoice->status === 'sent') {
            Toastr::warning('This invoice is already posted / sent.', 'Notice');
            return redirect()->back();
        }

        $this->salesInvoiceService->postInvoiceToLedger($invoice, Auth::id() ?? 1);

        Toastr::success("Invoice #{$invoice->invoice_no} posted to General Ledger successfully.", 'Posted');
        return redirect()->route('admin.sales-invoices.show', $invoice->id);
    }

    public function printPdf($id)
    {
        $invoice = SalesInvoice::with([
            'order.user',
            'deliveryOrder',
            'items.product',
            'items.variant'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('backend.sales_invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Sales-Invoice-{$invoice->invoice_no}.pdf");
    }
}
