<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\SalesInvoice;
use App\Services\CustomerPaymentService;
use App\Services\Payment\PayPalService;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayPalPaymentController extends Controller
{
    protected PayPalService $paypalService;
    protected CustomerPaymentService $customerPaymentService;

    public function __construct(PayPalService $paypalService, CustomerPaymentService $customerPaymentService)
    {
        $this->paypalService = $paypalService;
        $this->customerPaymentService = $customerPaymentService;
    }

    /**
     * Initiate PayPal Express Checkout for an invoice or order.
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:sales_invoices,id',
        ]);

        try {
            $invoice = SalesInvoice::with('order')->findOrFail($request->input('invoice_id'));

            if ((float)$invoice->due_amount <= 0) {
                Toastr::warning("Invoice #{$invoice->invoice_no} is already fully paid.");
                return redirect()->back();
            }

            $orderResult = $this->paypalService->createOrder(
                $invoice,
                route('admin.payments.paypal.success'),
                route('admin.payments.paypal.cancel')
            );

            // Record pending PaymentTransaction
            PaymentTransaction::create([
                'transaction_no'     => 'TXN-PAYPAL-' . date('YmdHis') . '-' . rand(100, 999),
                'gateway'            => 'paypal',
                'order_id'           => $invoice->order_id,
                'sales_invoice_id'   => $invoice->id,
                'customer_id'        => $invoice->user_id,
                'amount'             => (float)$invoice->due_amount,
                'currency'           => $orderResult['currency'],
                'external_reference' => $orderResult['id'], // PayPal Order ID
                'status'             => 'pending',
            ]);

            // Redirect user to PayPal approval URL
            return redirect()->away($orderResult['approve_url']);
        } catch (Exception $e) {
            Log::error("PayPal Order Error: " . $e->getMessage());
            Toastr::error("PayPal Error: " . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Synchronous Return URL: Capture PayPal funds and settle invoice into General Ledger.
     */
    public function success(Request $request)
    {
        $paypalOrderId = $request->query('token');

        if (empty($paypalOrderId)) {
            Toastr::error("Invalid or missing PayPal token.");
            return redirect()->route('admin.sales-invoices.index');
        }

        try {
            return DB::transaction(function () use ($paypalOrderId) {
                // 1. Double-Capture Idempotency Check
                $existingTxn = PaymentTransaction::where('external_reference', $paypalOrderId)
                    ->where('status', 'captured')
                    ->lockForUpdate()
                    ->first();

                if ($existingTxn) {
                    Toastr::info("This payment has already been captured and processed.");
                    return redirect()->route('admin.sales-invoices.show', $existingTxn->sales_invoice_id);
                }

                // 2. Synchronously Capture Order via PayPal REST API v2
                $captureData = $this->paypalService->captureOrder($paypalOrderId);

                // 3. Extract settled figures
                $purchaseUnit = $captureData['purchase_units'][0] ?? [];
                $capture = $purchaseUnit['payments']['captures'][0] ?? [];
                $captureId = $capture['id'] ?? $paypalOrderId;
                $settledAmount = (float)($capture['amount']['value'] ?? 0);
                $currency = $capture['amount']['currency_code'] ?? 'DKK';

                // Resolve target invoice
                $customId = $purchaseUnit['custom_id'] ?? null;
                $invoice = null;
                if ($customId) {
                    $invoice = SalesInvoice::find($customId);
                }
                if (!$invoice) {
                    $pendingTxn = PaymentTransaction::where('external_reference', $paypalOrderId)->first();
                    $invoice = $pendingTxn ? SalesInvoice::find($pendingTxn->sales_invoice_id) : null;
                }

                if (!$invoice) {
                    throw new Exception("Unable to link PayPal capture #{$captureId} to a valid sales invoice.");
                }

                // 4. Update PaymentTransaction record
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

                // 5. Knockdown Invoice Due Balance & Auto-Post GL Journal (DR 1020 Bank / CR 1030 AR)
                $this->customerPaymentService->recordPayment([
                    'order_id'         => $invoice->order_id,
                    'sales_invoice_id' => $invoice->id,
                    'amount'           => $settledAmount,
                    'payment_method'   => 'paypal',
                    'reference_no'     => $captureId,
                    'notes'            => "PayPal Direct Capture #{$captureId}",
                ], auth()->id() ?? 1);

                Toastr::success("Payment of kr. " . number_format($settledAmount, 2) . " captured via PayPal successfully!");
                return redirect()->route('admin.sales-invoices.show', $invoice->id);
            });
        } catch (Exception $e) {
            Log::error("PayPal Capture Error: " . $e->getMessage());
            Toastr::error("PayPal Capture Failed: " . $e->getMessage());
            return redirect()->route('admin.sales-invoices.index');
        }
    }

    /**
     * Customer cancelled payment on PayPal.
     */
    public function cancel(Request $request)
    {
        $paypalOrderId = $request->query('token');

        if ($paypalOrderId) {
            PaymentTransaction::where('external_reference', $paypalOrderId)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);
        }

        Toastr::info("PayPal payment was cancelled.");
        return redirect()->route('admin.sales-invoices.index');
    }
}
