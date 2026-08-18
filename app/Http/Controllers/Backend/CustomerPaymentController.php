<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CustomerPaymentDataTable;
use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\CustomerPayment;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Order;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\OrderNumberService;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerPaymentController extends Controller
{
    /**
     * Display listing of customer payment receipts.
     */
    public function index(CustomerPaymentDataTable $dataTable)
    {
        return $dataTable->render('backend.customer_payments.index');
    }

    /**
     * Show form to create new customer payment.
     */
    public function create(Request $request)
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

    /**
     * Get unpaid invoices or order info via AJAX for payment modal.
     */
    public function getInvoiceDetails(Request $request): JsonResponse
    {
        $invoiceId = $request->get('sales_invoice_id');
        $customerId = $request->get('user_id');

        if ($invoiceId) {
            $inv = SalesInvoice::with(['order.user'])->find($invoiceId);
            if ($inv) {
                $user = $inv->order ? $inv->order->user : null;
                return response()->json([
                    'success' => true,
                    'user_id' => $inv->user_id,
                    'customer_name' => $user ? ($user->outlet_name ?: $user->name) : 'Guest / Cash',
                    'subtotal_amount' => (float)$inv->subtotal_amount,
                    'tax_amount' => (float)$inv->tax_amount,
                    'discount_amount' => (float)$inv->discount_amount,
                    'total_amount' => (float)$inv->total_amount,
                    'paid_amount' => (float)$inv->paid_amount,
                    'due_amount' => (float)$inv->due_amount,
                ]);
            }
        }

        if ($customerId) {
            $invoices = SalesInvoice::whereHas('order', function($q) use ($customerId) {
                    $q->where('user_id', $customerId);
                })
                ->where('due_amount', '>', 0)
                ->where('status', 'posted')
                ->get(['id', 'invoice_no', 'total_amount', 'paid_amount', 'due_amount']);

            return response()->json([
                'success' => true,
                'invoices' => $invoices,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No data found']);
    }

    /**
     * Store new payment receipt voucher.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'sales_invoice_id' => 'nullable|exists:sales_invoices,id',
            'order_id' => 'nullable|exists:orders,id',
            'account_id' => 'nullable|exists:chart_of_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,card,mobile_money',
            'reference_no' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $paymentNo = OrderNumberService::generateCustomerPaymentNumber();

            $payment = CustomerPayment::create([
                'payment_no' => $paymentNo,
                'user_id' => $validated['user_id'],
                'sales_invoice_id' => $validated['sales_invoice_id'] ?? null,
                'order_id' => $validated['order_id'] ?? null,
                'account_id' => $validated['account_id'] ?? null,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'] ?? null,
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'status' => 'posted',
            ]);

            // 1. Auto-Knockdown Sales Invoice & Order Due Amount
            if ($payment->sales_invoice_id) {
                $invoice = SalesInvoice::with('order')->find($payment->sales_invoice_id);
                if ($invoice) {
                    $newPaid = (float)$invoice->paid_amount + (float)$payment->amount;
                    $newDue = max(0, (float)$invoice->total_amount - $newPaid);
                    $invoice->update([
                        'paid_amount' => $newPaid,
                        'due_amount' => $newDue,
                    ]);

                    if ($invoice->order) {
                        $orderPaid = (float)$invoice->order->paid_amount + (float)$payment->amount;
                        $orderDue = max(0, (float)$invoice->order->total_amount - $orderPaid);
                        $paymentStatus = $orderDue <= 0 ? 'paid' : ($orderPaid > 0 ? 'partially_paid' : 'unpaid');
                        $invoice->order->update([
                            'paid_amount' => $orderPaid,
                            'due_amount' => $orderDue,
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
                        'paid_amount' => $orderPaid,
                        'due_amount' => $orderDue,
                        'payment_status' => $paymentStatus,
                    ]);
                }
            }

            // 2. Double-Entry General Ledger Journal Posting
            $this->postJournalEntry($payment);

            Toastr::success("Payment Receipt #{$payment->payment_no} recorded successfully!", "Payment Posted");

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment recorded successfully',
                    'redirect' => route('admin.customer-payments.show', $payment->id),
                ]);
            }

            return redirect()->route('admin.customer-payments.show', $payment->id);
        });
    }

    /**
     * Display payment receipt details.
     */
    public function show(CustomerPayment $customerPayment)
    {
        $customerPayment->load(['user', 'invoice', 'order', 'account', 'creator', 'journalEntry.lines.account']);
        return view('backend.customer_payments.show', compact('customerPayment'));
    }

    /**
     * Download printable Payment Receipt PDF.
     */
    public function pdf(CustomerPayment $customerPayment)
    {
        $customerPayment->load(['user', 'invoice', 'order', 'account', 'creator']);
        $pdf = Pdf::loadView('backend.pdf.customer_payment', compact('customerPayment'));
        return $pdf->stream("Customer_Payment_{$customerPayment->payment_no}.pdf");
    }

    /**
     * Internal Double-Entry GL Journal Posting.
     */
    private function postJournalEntry(CustomerPayment $payment): void
    {
        $entryNo = 'JE-' . now()->format('Ym') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $journalEntry = JournalEntry::create([
            'entry_no' => $entryNo,
            'reference_type' => CustomerPayment::class,
            'reference_id' => $payment->id,
            'entry_date' => $payment->payment_date,
            'narration' => "Customer Payment Received #{$payment->payment_no} from " . ($payment->user ? $payment->user->name : 'Customer'),
            'created_by' => auth()->id(),
        ]);

        // Debit: Cash/Bank Account (Assets +)
        $cashOrBankAccount = ChartOfAccount::where('account_type', 'asset')
            ->where(function($q) {
                $q->where('account_name', 'LIKE', '%Cash%')
                  ->orWhere('account_name', 'LIKE', '%Bank%');
            })->first() ?: ChartOfAccount::first();

        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $payment->account_id ?: ($cashOrBankAccount ? $cashOrBankAccount->id : 1),
            'debit' => $payment->amount,
            'credit' => 0.00,
        ]);

        // Credit: Accounts Receivable (Customer Dues -)
        $arAccount = ChartOfAccount::where('account_name', 'LIKE', '%Receivable%')->first()
            ?: ChartOfAccount::where('account_type', 'asset')->skip(1)->first()
            ?: $cashOrBankAccount;

        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $arAccount ? $arAccount->id : 1,
            'debit' => 0.00,
            'credit' => $payment->amount,
        ]);
    }
}
