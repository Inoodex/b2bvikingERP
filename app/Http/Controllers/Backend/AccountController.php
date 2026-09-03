<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderPaymentReceipt;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\PurchasePaymentReceipt;
use App\Models\GeneralSetting;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use App\Support\AuditLogSupport;
use App\Support\StoredFileSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Support\PdfImageHelper;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    /**
     * Display a listing of all transactions (Ledger).
     */
    public function index(\App\DataTables\OrderPaymentDataTable $dataTable)
    {
        return $dataTable->render('backend.accounts.index');
    }

    /**
     * Display orders that have a due balance.
     */
    public function dueOrders(\App\DataTables\DueOrderDataTable $dataTable)
    {
        return $dataTable->render('backend.accounts.due_orders');
    }

    /**
     * Display vendor payment history.
     */
    public function vendorPaymentIndex(Request $request)
    {
        $vendors = Vendor::where('status', 1)->orderBy('shop_name')->get();
        $query = $this->vendorPaymentQuery($request);

        $payments = $query->orderByDesc('id')->paginate(30)->withQueryString();
        $summaryQuery = clone $query;

        $summary = [
            'count' => (clone $summaryQuery)->count(),
            'total_amount' => (clone $summaryQuery)->sum('amount'),
        ];

        return view('backend.accounts.vendor_payments_index', [
            'payments' => $payments,
            'vendors' => $vendors,
            'summary' => $summary,
        ]);
    }

    /**
     * Download vendor payment history PDF (filtered).
     */
    public function vendorPaymentHistoryPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $query = $this->vendorPaymentQuery($request);
        $count = (clone $query)->count();
        $maxRows = 1000;
        if ($count > $maxRows) {
            Toastr::error('Too many records for PDF. Please filter by vendor, date, or method to reduce results.');
            return redirect()->back();
        }

        $payments = $query->orderByDesc('id')->get();
        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();

        $summary = [
            'count' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
        ];

        $filters = $this->vendorPaymentFilters($request);

        $pdf = Pdf::loadView('backend.accounts.vendor_payment_history_pdf', [
            'payments' => $payments,
            'settings' => $settings,
            'logoData' => $logoData,
            'filters' => $filters,
            'summary' => $summary,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'portrait');

        $fileName = 'vendor_payment_history_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * View vendor payment history with print/close controls.
     */
    public function vendorPaymentHistoryView(Request $request)
    {
        $query = $this->vendorPaymentQuery($request);
        $count = (clone $query)->count();
        $maxRows = 1000;
        if ($count > $maxRows) {
            Toastr::error('Too many records for view. Please filter by vendor, date, or method to reduce results.');
            return redirect()->back();
        }

        $payments = $query->orderByDesc('id')->get();
        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();

        $summary = [
            'count' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
        ];

        $filters = $this->vendorPaymentFilters($request);

        $downloadUrl = route('admin.accounts.vendor-payments.pdf', array_filter([
            'vendor_id' => $filters['vendor_id'] ?: null,
            'start_date' => $filters['start_date'] ?: null,
            'end_date' => $filters['end_date'] ?: null,
            'method' => $filters['method'] ?: null,
            'search' => $filters['search'] ?: null,
        ]));

        return view('backend.accounts.vendor_payment_history_view', [
            'payments' => $payments,
            'settings' => $settings,
            'logoData' => $logoData,
            'filters' => $filters,
            'summary' => $summary,
            'generatedAt' => $generatedAt,
            'downloadUrl' => $downloadUrl,
        ]);
    }

    /**
     * Download a single vendor payment PDF.
     */
    public function vendorPaymentSinglePdf(PurchasePayment $payment)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $payment->load(['purchase.vendor', 'receipts']);
        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();

        $pdf = Pdf::loadView('backend.accounts.vendor_payment_single_pdf', [
            'payment' => $payment,
            'settings' => $settings,
            'logoData' => $logoData,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'portrait');

        $fileName = 'vendor_payment_' . ($payment->purchase?->invoice_no ?? $payment->id) . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * View a single vendor payment with print/close controls.
     */
    public function vendorPaymentSingleView(PurchasePayment $payment)
    {
        $payment->load(['purchase.vendor', 'receipts']);
        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();
        $downloadUrl = route('admin.accounts.vendor-payments.single.pdf', $payment->id);

        return view('backend.accounts.vendor_payment_single_view', [
            'payment' => $payment,
            'settings' => $settings,
            'logoData' => $logoData,
            'generatedAt' => $generatedAt,
            'downloadUrl' => $downloadUrl,
        ]);
    }

    /**
     * Display purchases that still have vendor dues.
     */
    public function vendorDuePurchases(Request $request)
    {
        $vendors = Vendor::where('status', 1)->orderBy('shop_name')->get();

        $query = Purchase::query()->with('vendor')->where('due_amount', '>', 0);

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', (int) $request->vendor_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', '%' . $search . '%')
                    ->orWhereHas('vendor', function ($vendorQuery) use ($search) {
                        $vendorQuery->where('shop_name', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%');
                    });
            });
        }

        $purchases = $query->orderByDesc('date')->orderByDesc('id')->paginate(30)->withQueryString();
        $summaryQuery = clone $query;

        $summary = [
            'count'        => (clone $summaryQuery)->count(),
            'total_due'    => (clone $summaryQuery)->sum('due_amount'),
            'total_amount' => (clone $summaryQuery)->sum('total_amount'),
        ];

        // Aging buckets — all unpaid purchases (no pagination)
        $allDuePurchases = Purchase::query()->where('due_amount', '>', 0)->get(['date', 'due_amount']);
        $today = now()->startOfDay();
        $aging = ['current' => 0, 'days_30' => 0, 'days_60' => 0, 'days_90' => 0];
        $agingAmt = ['current' => 0, 'days_30' => 0, 'days_60' => 0, 'days_90' => 0];
        foreach ($allDuePurchases as $p) {
            $daysOverdue = (int) $today->diffInDays(\Carbon\Carbon::parse($p->date), false) * -1;
            $due = (float) $p->due_amount;
            if ($daysOverdue <= 30)      { $aging['current']++; $agingAmt['current'] += $due; }
            elseif ($daysOverdue <= 60)  { $aging['days_30']++; $agingAmt['days_30'] += $due; }
            elseif ($daysOverdue <= 90)  { $aging['days_60']++; $agingAmt['days_60'] += $due; }
            else                         { $aging['days_90']++; $agingAmt['days_90'] += $due; }
        }

        return view('backend.accounts.vendor_due_purchases', [
            'purchases'  => $purchases,
            'vendors'    => $vendors,
            'summary'    => $summary,
            'aging'      => $aging,
            'agingAmt'   => $agingAmt,
        ]);
    }

    /**
     * Download all vendor payments for a specific purchase invoice.
     */
    public function vendorPurchasePaymentsPdf(Purchase $purchase)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $purchase->load(['payments', 'vendor', 'user']);
        $payments = $purchase->payments->sortByDesc('id')->values();

        $maxRows = 1000;
        if ($payments->count() > $maxRows) {
            Toastr::error('Too many payments for PDF. Please narrow down.');
            return redirect()->back();
        }

        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();

        $summary = [
            'count' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'paid_amount' => $purchase->paid_amount,
            'due_amount' => $purchase->due_amount,
        ];

        $pdf = Pdf::loadView('backend.accounts.vendor_purchase_payment_pdf', [
            'purchase' => $purchase,
            'payments' => $payments,
            'settings' => $settings,
            'logoData' => $logoData,
            'generatedAt' => $generatedAt,
            'summary' => $summary,
        ])->setPaper('a4', 'portrait');

        $fileName = 'purchase_payments_' . $purchase->invoice_no . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * View all vendor payments for a specific purchase invoice.
     */
    public function vendorPurchasePaymentsView(Purchase $purchase)
    {
        $purchase->load(['payments', 'vendor', 'user']);
        $payments = $purchase->payments->sortByDesc('id')->values();

        $maxRows = 1000;
        if ($payments->count() > $maxRows) {
            Toastr::error('Too many payments for view. Please narrow down.');
            return redirect()->back();
        }

        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();

        $summary = [
            'count' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'paid_amount' => $purchase->paid_amount,
            'due_amount' => $purchase->due_amount,
        ];

        $downloadUrl = route('admin.accounts.vendor-purchases.payments.pdf', $purchase->id);

        return view('backend.accounts.vendor_purchase_payment_view', [
            'purchase' => $purchase,
            'payments' => $payments,
            'settings' => $settings,
            'logoData' => $logoData,
            'generatedAt' => $generatedAt,
            'summary' => $summary,
            'downloadUrl' => $downloadUrl,
        ]);
    }

    /**
     * Download payment history PDF (filtered).
     */
    public function paymentHistoryPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $method = $request->input('method');
        $search = trim((string) $request->input('search', ''));

        $query = OrderPayment::query()->with(['order', 'receipts']);

        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if (!empty($method)) {
            $query->where('payment_method', $method);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', '%' . $search . '%')
                    ->orWhere('payment_method', 'like', '%' . $search . '%')
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_no', 'like', '%' . $search . '%')
                            ->orWhere('billing_name', 'like', '%' . $search . '%')
                            ->orWhere('billing_phone', 'like', '%' . $search . '%');
                    });
            });
        }

        $count = (clone $query)->count();
        $maxRows = 1000;
        if ($count > $maxRows) {
            Toastr::error('Too many records for PDF. Please filter by date or method to reduce results.');
            return redirect()->back();
        }

        $payments = $query->orderByDesc('id')->get();
        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();

        $summary = [
            'count' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
        ];

        $pdf = Pdf::loadView('backend.accounts.payment_history_pdf', [
            'payments' => $payments,
            'settings' => $settings,
            'logoData' => $logoData,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'method' => $method,
                'search' => $search,
            ],
            'summary' => $summary,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'portrait');

        $fileName = 'payment_history_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * View payment history with print/close controls.
     */
    public function paymentHistoryPdfView(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $method = $request->input('method');
        $search = trim((string) $request->input('search', ''));

        $query = OrderPayment::query()->with(['order', 'receipts']);

        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if (!empty($method)) {
            $query->where('payment_method', $method);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', '%' . $search . '%')
                    ->orWhere('payment_method', 'like', '%' . $search . '%')
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_no', 'like', '%' . $search . '%')
                            ->orWhere('billing_name', 'like', '%' . $search . '%')
                            ->orWhere('billing_phone', 'like', '%' . $search . '%');
                    });
            });
        }

        $count = (clone $query)->count();
        $maxRows = 1000;
        if ($count > $maxRows) {
            Toastr::error('Too many records for view. Please filter by date or method to reduce results.');
            return redirect()->back();
        }

        $payments = $query->orderByDesc('id')->get();
        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();

        $summary = [
            'count' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
        ];

        $downloadUrl = route('admin.accounts.payments.pdf', array_filter([
            'start_date' => $startDate ?: null,
            'end_date' => $endDate ?: null,
            'method' => $method ?: null,
            'search' => $search ?: null,
        ]));

        return view('backend.accounts.payment_history_view', [
            'payments' => $payments,
            'settings' => $settings,
            'logoData' => $logoData,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'method' => $method,
                'search' => $search,
            ],
            'summary' => $summary,
            'generatedAt' => $generatedAt,
            'downloadUrl' => $downloadUrl,
        ]);
    }

    /**
     * Download a single payment PDF.
     */
    public function paymentSinglePdf(OrderPayment $payment)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $payment->load(['order', 'receipts']);
        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();

        $pdf = Pdf::loadView('backend.accounts.payment_single_pdf', [
            'payment' => $payment,
            'settings' => $settings,
            'logoData' => $logoData,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'portrait');

        $fileName = 'payment_' . ($payment->order?->order_no ?? $payment->id) . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * View a single payment with print/close controls.
     */
    public function paymentSingleView(OrderPayment $payment)
    {
        $payment->load(['order', 'receipts']);
        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();
        $downloadUrl = route('admin.accounts.payments.single.pdf', $payment->id);

        return view('backend.accounts.payment_single_view', [
            'payment' => $payment,
            'settings' => $settings,
            'logoData' => $logoData,
            'generatedAt' => $generatedAt,
            'downloadUrl' => $downloadUrl,
        ]);
    }

    /**
     * Download all payments for a specific order.
     */
    public function paymentOrderPdf(Order $order)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $order->load(['payments', 'user']);
        $payments = $order->payments->sortByDesc('id')->values();

        $maxRows = 1000;
        if ($payments->count() > $maxRows) {
            Toastr::error('Too many payments for PDF. Please narrow down.');
            return redirect()->back();
        }

        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();

        $summary = [
            'count' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'paid_amount' => $order->paid_amount,
            'due_amount' => $order->due_amount,
        ];

        $pdf = Pdf::loadView('backend.accounts.payment_order_pdf', [
            'order' => $order,
            'payments' => $payments,
            'settings' => $settings,
            'logoData' => $logoData,
            'generatedAt' => $generatedAt,
            'summary' => $summary,
        ])->setPaper('a4', 'portrait');

        $fileName = 'order_payments_' . $order->order_no . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * View all payments for a specific order with print/close controls.
     */
    public function paymentOrderView(Order $order)
    {
        $order->load(['payments', 'user']);
        $payments = $order->payments->sortByDesc('id')->values();

        $maxRows = 1000;
        if ($payments->count() > $maxRows) {
            Toastr::error('Too many payments for view. Please narrow down.');
            return redirect()->back();
        }

        $settings = GeneralSetting::first();
        $logoData = $this->resolveLogoData($settings);
        $generatedAt = Carbon::now();

        $summary = [
            'count' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'paid_amount' => $order->paid_amount,
            'due_amount' => $order->due_amount,
        ];

        $downloadUrl = route('admin.accounts.orders.payments.pdf', $order->id);

        return view('backend.accounts.payment_order_view', [
            'order' => $order,
            'payments' => $payments,
            'settings' => $settings,
            'logoData' => $logoData,
            'generatedAt' => $generatedAt,
            'summary' => $summary,
            'downloadUrl' => $downloadUrl,
        ]);
    }

    private function resolveLogoData(?GeneralSetting $settings): ?string
    {
        $logoPath = $settings?->site_logo ?: 'uploads/logo.png';
        return PdfImageHelper::optimize($logoPath, 160, 40);
    }

    private function vendorPaymentQuery(Request $request)
    {
        $query = PurchasePayment::query()->with(['purchase.vendor', 'receipts']);

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', (int) $request->vendor_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%')
                    ->orWhereHas('purchase', function ($purchaseQuery) use ($search) {
                        $purchaseQuery->where('invoice_no', 'like', '%' . $search . '%')
                            ->orWhereHas('vendor', function ($vendorQuery) use ($search) {
                                $vendorQuery->where('shop_name', 'like', '%' . $search . '%')
                                    ->orWhere('phone', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        return $query;
    }

    private function vendorPaymentFilters(Request $request): array
    {
        $vendorId = $request->input('vendor_id');
        $vendorName = null;

        if (!empty($vendorId)) {
            $vendorName = Vendor::whereKey((int) $vendorId)->value('shop_name');
        }

        return [
            'vendor_id' => $vendorId,
            'vendor_name' => $vendorName,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'method' => $request->input('method'),
            'search' => trim((string) $request->input('search', '')),
        ];
    }

    /**
     * Show the manual payment entry page (Redirect to Enterprise Customer Payments).
     */
    public function create()
    {
        return redirect()->route('admin.customer-payments.create', request()->query());
    }

    /**
     * Show the vendor payment entry page (Redirect to Enterprise Purchase Payments).
     */
    public function createVendorPayment()
    {
        return redirect()->route('admin.purchase-payments.create', request()->query());
    }

    /**
     * Search for an order by number for manual payment.
     */
    public function searchOrder(Request $request)
    {
        $request->validate([
            'order_no' => 'required|string',
        ]);

        $order = Order::with('user')->where('order_no', $request->order_no)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found!',
            ], 404);
        }

        if ($order->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Payment can only be recorded for completed orders.',
            ], 422);
        }

        // Reconcile totals in database dynamically
        $order->reconcileTotals();
        $order->refresh();

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'customer_name' => $order->billing_name,
                'total_amount' => number_format($order->total_amount, 2),
                'paid_amount' => number_format($order->paid_amount, 2),
                'due_amount' => number_format($order->due_amount, 2),
                'due_raw' => $order->due_amount,
                'status' => ucfirst($order->status),
            ]
        ]);
    }

    /**
     * Search for a purchase invoice for vendor payment.
     */
    public function searchPurchase(Request $request)
    {
        $request->validate([
            'invoice_no' => 'required|string',
        ]);

        $purchase = Purchase::with('vendor')->where('invoice_no', $request->invoice_no)->first();

        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase invoice not found!',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'purchase' => [
                'id' => $purchase->id,
                'invoice_no' => $purchase->invoice_no,
                'vendor_name' => $purchase->vendor->shop_name ?? 'N/A',
                'purchase_date' => (string) $purchase->date,
                'total_amount' => number_format((float) $purchase->total_amount, 2),
                'paid_amount' => number_format((float) $purchase->paid_amount, 2),
                'due_amount' => number_format((float) $purchase->due_amount, 2),
                'due_raw' => (float) $purchase->due_amount,
                'payment_status' => ucfirst((string) ($purchase->payment_status ?: 'pending')),
            ],
        ]);
    }

    /**
     * Store a new payment for an order.
     */
    public function storePayment(Request $request, Order $order)
    {
        if ($order->status !== 'completed') {
            Toastr::error('Payment can only be recorded for completed orders.');
            return redirect()->back();
        }

        // Reconcile totals in database dynamically
        $order->reconcileTotals();
        $order->refresh();

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'note' => 'nullable|string',
            'receipts' => 'nullable|array',
            'receipts.*' => 'file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        ]);

        $amount = (float) $request->amount;

        if ($amount > $order->due_amount) {
            Toastr::error('Payment amount cannot be greater than the due amount!');
            return redirect()->back();
        }

        $before = [
            'paid_amount'    => (float) $order->paid_amount,
            'due_amount'     => (float) $order->due_amount,
            'payment_status' => (string) $order->payment_status,
        ];

        DB::transaction(function () use ($order, $amount, $request, $before) {
            $payment = OrderPayment::create([
                'order_id' => $order->id,
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'note' => $request->note,
            ]);

            if ($request->hasFile('receipts')) {
                foreach ($request->file('receipts') as $file) {
                    if (!$file || !$file->isValid()) {
                        continue;
                    }

                    $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $storedPath = StoredFileSupport::storePrivateFile($file, "order-payments/{$payment->id}", $filename);
                    OrderPaymentReceipt::create([
                        'order_payment_id' => $payment->id,
                        'file_path' => $storedPath,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            // Update the order totals
            $order->paid_amount += $amount;
            $order->due_amount -= $amount;

            if ($order->due_amount <= 0) {
                $order->payment_status = 'paid';
            } else {
                $order->payment_status = 'partial';
            }

            $order->save();

            AuditLogSupport::log([
                'module' => 'accounts',
                'action' => 'customer_payment_created',
                'entity_type' => 'order_payment',
                'entity_id' => $payment->id,
                'reference_no' => $order->order_no,
                'description' => 'Customer payment recorded.',
                'old_values' => $before,
                'new_values' => [
                    'payment_id' => $payment->id,
                    'amount' => $amount,
                    'payment_method' => $payment->payment_method,
                    'transaction_id' => $payment->transaction_id,
                    'receipt_count' => $payment->receipts()->count(),
                    'paid_amount' => (float) $order->paid_amount,
                    'due_amount' => (float) $order->due_amount,
                    'payment_status' => (string) $order->payment_status,
                ],
            ]);
        });

        Toastr::success('Payment recorded successfully!');

        if ($request->filled('source') && $request->source === 'central_entry') {
            return redirect()->route('admin.accounts.index');
        }

        return redirect()->back();
    }

    /**
     * Store a vendor payment for a purchase invoice.
     */
    public function storePurchasePayment(Request $request, Purchase $purchase)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'note' => 'nullable|string',
            'receipts' => 'nullable|array',
            'receipts.*' => 'file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        ]);

        $amount = round((float) $request->amount, 2);

        if ($amount > (float) $purchase->due_amount) {
            Toastr::error('Payment amount cannot be greater than the due amount!');
            return redirect()->back();
        }

        $before = [
            'paid_amount' => (float) $purchase->paid_amount,
            'due_amount' => (float) $purchase->due_amount,
            'payment_status' => (string) $purchase->payment_status,
        ];

        DB::beginTransaction();

        try {
            $payment = PurchasePayment::create([
                'purchase_id' => $purchase->id,
                'vendor_id' => $purchase->vendor_id,
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'note' => $request->note,
            ]);

            if ($request->hasFile('receipts')) {
                foreach ($request->file('receipts') as $file) {
                    if (!$file || !$file->isValid()) {
                        continue;
                    }

                    $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $storedPath = StoredFileSupport::storePrivateFile($file, "purchase-payments/{$payment->id}", $filename);

                    PurchasePaymentReceipt::create([
                        'purchase_payment_id' => $payment->id,
                        'file_path' => $storedPath,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            $purchase->paid_amount = round((float) $purchase->paid_amount + $amount, 2);
            $purchase->due_amount = max(0, round((float) $purchase->due_amount - $amount, 2));
            $purchase->payment_status = $purchase->due_amount <= 0 ? 'paid' : 'partial';
            $purchase->save();

            AuditLogSupport::log([
                'user_id' => auth()->id(),
                'vendor_id' => $purchase->vendor_id,
                'module' => 'accounts',
                'action' => 'vendor_payment_created',
                'entity_type' => 'purchase_payment',
                'entity_id' => $payment->id,
                'reference_no' => $purchase->invoice_no,
                'description' => 'Vendor payment recorded.',
                'old_values' => $before,
                'new_values' => [
                    'payment_id' => $payment->id,
                    'purchase_id' => $purchase->id,
                    'amount' => $amount,
                    'payment_method' => $payment->payment_method,
                    'transaction_id' => $payment->transaction_id,
                    'receipt_count' => $payment->receipts()->count(),
                    'paid_amount' => (float) $purchase->paid_amount,
                    'due_amount' => (float) $purchase->due_amount,
                    'payment_status' => (string) $purchase->payment_status,
                ],
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Payment failed: ' . $e->getMessage());
            return redirect()->back();
        }

        Toastr::success('Vendor payment recorded successfully!');

        if ($request->filled('source') && $request->source === 'central_entry') {
            return redirect()->route('admin.accounts.vendor-payments.index');
        }

        return redirect()->back();
    }

    public function downloadReceipt(OrderPaymentReceipt $receipt)
    {
        $downloadName = $receipt->original_name ?: basename($receipt->file_path);
        $response = StoredFileSupport::download($receipt->file_path, $downloadName);

        if (!$response) {
            Toastr::error('Receipt file not found.');
            return redirect()->back();
        }

        return $response;
    }

    public function destroyReceipt(OrderPaymentReceipt $receipt)
    {
        $payment = $receipt->payment()->with('order')->first();

        AuditLogSupport::log([
            'module' => 'accounts',
            'action' => 'customer_payment_receipt_deleted',
            'entity_type' => 'order_payment_receipt',
            'entity_id' => $receipt->id,
            'reference_no' => $payment?->order?->order_no,
            'description' => 'Customer payment receipt deleted.',
            'old_values' => [
                'receipt_id' => $receipt->id,
                'payment_id' => $payment?->id,
                'original_name' => $receipt->original_name,
                'file_path' => $receipt->file_path,
            ],
        ]);

        StoredFileSupport::delete($receipt->file_path);

        $receipt->delete();
        $message = 'Receipt deleted successfully.';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);
        }

        Toastr::success($message);
        return redirect()->back();
    }

    public function downloadPurchaseReceipt(PurchasePaymentReceipt $receipt)
    {
        $downloadName = $receipt->original_name ?: basename($receipt->file_path);
        $response = StoredFileSupport::download($receipt->file_path, $downloadName);

        if (!$response) {
            Toastr::error('Receipt file not found.');
            return redirect()->back();
        }

        return $response;
    }

    public function destroyPurchaseReceipt(PurchasePaymentReceipt $receipt)
    {
        $payment = $receipt->payment()->with('purchase')->first();

        AuditLogSupport::log([
            'vendor_id' => $payment?->vendor_id,
            'module' => 'accounts',
            'action' => 'vendor_payment_receipt_deleted',
            'entity_type' => 'purchase_payment_receipt',
            'entity_id' => $receipt->id,
            'reference_no' => $payment?->purchase?->invoice_no,
            'description' => 'Vendor payment receipt deleted.',
            'old_values' => [
                'receipt_id' => $receipt->id,
                'payment_id' => $payment?->id,
                'original_name' => $receipt->original_name,
                'file_path' => $receipt->file_path,
            ],
        ]);

        StoredFileSupport::delete($receipt->file_path);
        $receipt->delete();

        $message = 'Receipt deleted successfully.';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);
        }

        Toastr::success($message);
        return redirect()->back();
    }
}
