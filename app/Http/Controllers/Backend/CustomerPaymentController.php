<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CustomerPaymentDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreCustomerPaymentRequest;
use App\Models\ChartOfAccount;
use App\Models\CustomerPayment;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\CustomerPaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerPaymentController extends Controller
{
    protected CustomerPaymentService $customerPaymentService;

    public function __construct(CustomerPaymentService $customerPaymentService)
    {
        $this->customerPaymentService = $customerPaymentService;
    }

    public function index(CustomerPaymentDataTable $dataTable)
    {
        // 4 Core Financial AR Metrics (Live from Invoices & Payments)
        $totalArOutstanding = (float) SalesInvoice::where('due_amount', '>', 0)->sum('due_amount');
        if ($totalArOutstanding <= 0) {
            $totalArOutstanding = (float) Order::where('due_amount', '>', 0)->sum('due_amount');
        }

        $collectedThisMonth = (float) CustomerPayment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        $overdueAr30Days = (float) SalesInvoice::where('due_amount', '>', 0)
            ->where('due_date', '<', now()->subDays(30))
            ->sum('due_amount');

        $totalPaymentsCount = CustomerPayment::count();

        return $dataTable->render('backend.customer_payments.index', compact(
            'totalArOutstanding',
            'collectedThisMonth',
            'overdueAr30Days',
            'totalPaymentsCount'
        ));
    }

    public function create(Request $request): View
    {
        $customers = User::where('status', 1)->orderBy('name')->get();
        $accounts = ChartOfAccount::where('is_active', 1)->orderBy('account_code')->get();

        $selectedInvoiceId = $request->get('sales_invoice_id');
        $selectedOrderId = $request->get('order_id');
        $orderNo = $request->get('order_no');

        if (!$selectedOrderId && $orderNo) {
            $matchedOrder = Order::where('order_no', $orderNo)->first();
            if ($matchedOrder) {
                $selectedOrderId = $matchedOrder->id;
            }
        }

        $preloadedInvoice = null;
        $preloadedOrder = null;

        if ($selectedInvoiceId) {
            $preloadedInvoice = SalesInvoice::with(['order.user'])->find($selectedInvoiceId);
        }

        if ($selectedOrderId) {
            $preloadedOrder = Order::with(['user'])->find($selectedOrderId);
        }

        $unpaidInvoices = SalesInvoice::where('due_amount', '>', 0)
            ->where('status', 'posted')
            ->orderBy('id', 'desc')
            ->get();

        return view('backend.customer_payments.create', compact(
            'customers',
            'accounts',
            'selectedInvoiceId',
            'selectedOrderId',
            'preloadedInvoice',
            'preloadedOrder',
            'unpaidInvoices'
        ));
    }

    public function getCustomerInvoices(Request $request): JsonResponse
    {
        $userId = $request->get('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'invoices' => []]);
        }

        $user = User::find($userId);
        $invoices = SalesInvoice::where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('order', fn($oq) => $oq->where('user_id', $userId));
            })
            ->where('due_amount', '>', 0)
            ->orderBy('date', 'asc') // FIFO sorting
            ->get(['id', 'invoice_no', 'date', 'due_date', 'total_amount', 'paid_amount', 'due_amount']);

        $totalCustomerDue = $invoices->sum('due_amount');

        return response()->json([
            'success'            => true,
            'customer_name'      => $user ? ($user->outlet_name ?: $user->name) : 'Customer',
            'customer_phone'     => $user ? $user->phone : '',
            'customer_email'     => $user ? $user->email : '',
            'credit_limit'       => (float) ($user->credit_limit ?? 0),
            'total_customer_due' => (float) $totalCustomerDue,
            'invoices'           => $invoices,
        ]);
    }

    public function getInvoiceDetails(Request $request): JsonResponse
    {
        $invoiceId = $request->get('sales_invoice_id');
        $customerId = $request->get('user_id');

        if ($invoiceId) {
            $inv = SalesInvoice::with(['order.user'])->find($invoiceId);
            if ($inv) {
                $user = $inv->order ? $inv->order->user : null;
                return response()->json([
                    'success'         => true,
                    'user_id'         => $inv->user_id,
                    'customer_name'   => $user ? ($user->outlet_name ?: $user->name) : 'Guest / Cash',
                    'subtotal_amount' => (float)$inv->subtotal_amount,
                    'tax_amount'      => (float)$inv->tax_amount,
                    'discount_amount' => (float)$inv->discount_amount,
                    'total_amount'    => (float)$inv->total_amount,
                    'paid_amount'     => (float)$inv->paid_amount,
                    'due_amount'      => (float)$inv->due_amount,
                ]);
            }
        }

        if ($customerId) {
            return $this->getCustomerInvoices($request);
        }

        return response()->json(['success' => false, 'message' => 'Invalid parameters']);
    }

    public function store(StoreCustomerPaymentRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $data['customer_id'] ?? ($data['user_id'] ?? null);

        // If JSON string allocations passed, decode
        if ($request->has('allocations_json') && !empty($request->allocations_json)) {
            $decoded = json_decode($request->allocations_json, true);
            if (is_array($decoded)) {
                $data['allocations'] = $decoded;
            }
        }

        $payment = $this->customerPaymentService->recordPayment($data, Auth::id() ?? 1);

        Toastr::success("Payment Receipt #{$payment->payment_no} recorded successfully!", "Payment Posted");

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Payment recorded successfully',
                'redirect' => route('admin.customer-payments.show', $payment->id),
            ]);
        }

        return redirect()->route('admin.customer-payments.show', $payment->id);
    }

    public function show($id): View
    {
        $payment = CustomerPayment::with(['user', 'order', 'invoice', 'account', 'creator', 'journalEntry.lines.account'])->findOrFail($id);
        $customerPayment = $payment;
        return view('backend.customer_payments.show', compact('payment', 'customerPayment'));
    }

    public function pdf($id)
    {
        return $this->printPdf($id);
    }

    public function downloadPdf($id)
    {
        $payment = CustomerPayment::with(['user', 'order', 'invoice', 'account', 'creator', 'journalEntry.lines.account'])->findOrFail($id);
        $settings = GeneralSetting::first();

        $pdf = Pdf::loadView('backend.customer_payments.receipt_pdf', compact('payment', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("Receipt_{$payment->payment_no}.pdf");
    }

    public function printPdf($id)
    {
        $payment = CustomerPayment::with(['user', 'order', 'invoice', 'account', 'creator', 'journalEntry.lines.account'])->findOrFail($id);
        $settings = GeneralSetting::first();

        $pdf = Pdf::loadView('backend.customer_payments.receipt_pdf', compact('payment', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Receipt_{$payment->payment_no}.pdf");
    }
}
