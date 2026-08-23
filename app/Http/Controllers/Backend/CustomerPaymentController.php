<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CustomerPaymentDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreCustomerPaymentRequest;
use App\Models\ChartOfAccount;
use App\Models\CustomerPayment;
use App\Models\Order;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\CustomerPaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
        return $dataTable->render('backend.customer_payments.index');
    }

    public function create(Request $request): View
    {
        $customers = User::where('status', 1)->orderBy('name')->get();
        $accounts = ChartOfAccount::where('is_active', 1)->orderBy('account_code')->get();

        $selectedInvoiceId = $request->get('sales_invoice_id');
        $selectedOrderId = $request->get('order_id');
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
                    'subtotal_amount' => (float)$inv->subtotal,
                    'tax_amount'      => (float)$inv->tax_amount,
                    'discount_amount' => (float)$inv->discount_amount,
                    'total_amount'    => (float)$inv->total_amount,
                    'paid_amount'     => (float)$inv->paid_amount,
                    'due_amount'      => (float)$inv->due_amount,
                ]);
            }
        }

        if ($customerId) {
            $invoices = SalesInvoice::whereHas('order', fn($q) => $q->where('user_id', $customerId))
                ->where('due_amount', '>', 0)
                ->where('status', 'posted')
                ->get();

            return response()->json([
                'success'  => true,
                'invoices' => $invoices,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid parameters']);
    }

    public function store(StoreCustomerPaymentRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $data['customer_id'] ?? ($data['user_id'] ?? null);

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
        $payment = CustomerPayment::with(['customer', 'order', 'salesInvoice', 'account', 'creator', 'journalEntries.lines.account'])->findOrFail($id);
        return view('backend.customer_payments.show', compact('payment'));
    }

    public function pdf($id)
    {
        return $this->printPdf($id);
    }

    public function downloadPdf($id)
    {
        return $this->printPdf($id);
    }

    public function printPdf($id)
    {
        $payment = CustomerPayment::with(['customer', 'order', 'salesInvoice', 'account', 'creator'])->findOrFail($id);
        $pdf = Pdf::loadView('backend.pdf.customer_payment', compact('payment'))->setPaper('a4', 'portrait');

        return $pdf->stream("Payment-Receipt-{$payment->payment_no}.pdf");
    }
}
