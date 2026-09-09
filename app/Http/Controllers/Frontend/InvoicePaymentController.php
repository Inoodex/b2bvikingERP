<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\InvoicePaymentLinkMail;
use App\Models\ChartOfAccount;
use App\Models\GeneralSetting;
use App\Models\PaymentSetting;
use App\Models\PaymentTransaction;
use App\Models\SalesInvoice;
use App\Services\CustomerPaymentService;
use App\Services\Payment\PayPalService;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoicePaymentController extends Controller
{
    protected PayPalService $paypalService;
    protected CustomerPaymentService $customerPaymentService;

    public function __construct(PayPalService $paypalService, CustomerPaymentService $customerPaymentService)
    {
        $this->paypalService = $paypalService;
        $this->customerPaymentService = $customerPaymentService;
    }

    /**
     * Display the public customer invoice payment portal.
     */
    public function show(string $token)
    {
        $invoice = SalesInvoice::with([
            'order.user',
            'order.items.variant',
            'items.product',
            'currency',
        ])->where('payment_token', $token)->firstOrFail();

        $gateways = PaymentSetting::getActiveGateways();
        $settings = GeneralSetting::first() ?? new GeneralSetting();
        $bankAccounts = ChartOfAccount::where('account_type', 'asset')
            ->whereIn('account_code', ['1020', '1010'])
            ->get();

        return view('frontend.pages.invoice_pay', compact('invoice', 'gateways', 'settings', 'bankAccounts'));
    }

    /**
     * Initiate PayPal checkout for this invoice from customer payment page.
     */
    public function payPayPal(string $token)
    {
        $invoice = SalesInvoice::with('order')->where('payment_token', $token)->firstOrFail();

        if ((float)$invoice->due_amount <= 0) {
            return redirect()->route('invoices.pay', $token)->with('info', "Invoice #{$invoice->invoice_no} is already fully paid.");
        }

        try {
            $returnUrl = route('invoices.pay.paypal.success', $token);
            $cancelUrl = route('invoices.pay.paypal.cancel', $token);

            $orderResult = $this->paypalService->createOrder($invoice, $returnUrl, $cancelUrl);

            // Record pending PaymentTransaction
            PaymentTransaction::create([
                'transaction_no'     => 'TXN-INV-PAYPAL-' . date('YmdHis') . '-' . rand(100, 999),
                'gateway'            => 'paypal',
                'order_id'           => $invoice->order_id,
                'sales_invoice_id'   => $invoice->id,
                'customer_id'        => $invoice->user_id,
                'amount'             => (float)$invoice->due_amount,
                'currency'           => $orderResult['currency'],
                'external_reference' => $orderResult['id'],
                'status'             => 'pending',
            ]);

            return redirect()->away($orderResult['approve_url']);
        } catch (Exception $e) {
            Log::error("Invoice PayPal Payment Initiation Failed: " . $e->getMessage());
            return redirect()->route('invoices.pay', $token)->with('error', "Unable to initialize PayPal: " . $e->getMessage());
        }
    }

    /**
     * Handle return from PayPal approval and capture funds.
     */
    public function paypalSuccess(Request $request, string $token)
    {
        $invoice = SalesInvoice::with('order')->where('payment_token', $token)->firstOrFail();
        $paypalOrderId = $request->query('token');

        if (empty($paypalOrderId)) {
            return redirect()->route('invoices.pay', $token)->with('error', "Missing PayPal payment token.");
        }

        try {
            return DB::transaction(function () use ($invoice, $paypalOrderId, $token) {
                // Idempotency check
                $existingTxn = PaymentTransaction::where('external_reference', $paypalOrderId)
                    ->where('status', 'captured')
                    ->lockForUpdate()
                    ->first();

                if ($existingTxn) {
                    return redirect()->route('invoices.pay', $token)->with('info', "This payment has already been captured and recorded.");
                }

                // Capture order via PayPal REST API
                $captureData = $this->paypalService->captureOrder($paypalOrderId);
                $purchaseUnit = $captureData['purchase_units'][0] ?? [];
                $capture = $purchaseUnit['payments']['captures'][0] ?? [];
                $captureId = $capture['id'] ?? $paypalOrderId;
                $settledAmount = (float)($capture['amount']['value'] ?? $invoice->due_amount);
                $currency = $capture['amount']['currency_code'] ?? 'DKK';

                // Update PaymentTransaction
                PaymentTransaction::updateOrCreate(
                    ['external_reference' => $paypalOrderId],
                    [
                        'gateway'            => 'paypal',
                        'order_id'           => $invoice->order_id,
                        'sales_invoice_id'   => $invoice->id,
                        'customer_id'        => $invoice->user_id,
                        'amount'             => $settledAmount,
                        'currency'           => $currency,
                        'external_reference' => $captureId,
                        'status'             => 'captured',
                        'payload'            => $captureData,
                    ]
                );

                // Auto-post customer payment and knock-down invoice due balance
                $bankAccount = ChartOfAccount::where('account_code', '1020')->first()
                    ?? ChartOfAccount::where('account_type', 'asset')->first();

                $this->customerPaymentService->recordPayment([
                    'order_id'         => $invoice->order_id,
                    'customer_id'      => $invoice->user_id,
                    'account_id'       => $bankAccount ? $bankAccount->id : 1,
                    'amount'           => $settledAmount,
                    'payment_method'   => 'paypal',
                    'payment_date'     => now()->toDateString(),
                    'reference_no'     => 'PAYPAL-' . $captureId,
                    'notes'            => "Online PayPal settlement for Invoice #{$invoice->invoice_no}",
                    'allow_advance'    => false,
                    'allocations'      => [
                        $invoice->id => $settledAmount,
                    ],
                ]);

                return redirect()->route('invoices.pay', $token)->with('success', "Payment of kr. " . number_format($settledAmount, 2) . " received successfully! Invoice #{$invoice->invoice_no} has been settled.");
            });
        } catch (Exception $e) {
            Log::error("Invoice PayPal Capture Failed: " . $e->getMessage());
            return redirect()->route('invoices.pay', $token)->with('error', "Payment capture failed: " . $e->getMessage());
        }
    }

    /**
     * Handle cancelled PayPal payment.
     */
    public function paypalCancel(Request $request, string $token)
    {
        return redirect()->route('invoices.pay', $token)->with('warning', "PayPal payment was cancelled. You may retry whenever convenient.");
    }

    /**
     * Send payment link to customer via email (Admin action).
     */
    public function sendEmail(Request $request, int $invoiceId)
    {
        try {
            $invoice = SalesInvoice::with('order.user')->findOrFail($invoiceId);
            $targetEmail = $request->input('email') ?: ($invoice->order?->user?->email ?: $invoice->order?->billing_email);

            if (empty($targetEmail)) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Customer has no valid email address configured.'], 422);
                }
                Toastr::error('Customer has no valid email address configured.');
                return redirect()->back();
            }

            Mail::to($targetEmail)->send(new InvoicePaymentLinkMail($invoice));

            $msg = "Payment link for Invoice #{$invoice->invoice_no} has been successfully sent to {$targetEmail}.";
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            Toastr::success($msg);
            return redirect()->back();
        } catch (Exception $e) {
            Log::error("Failed to send invoice payment link email: " . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage()], 500);
            }
            Toastr::error('Failed to send email: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
