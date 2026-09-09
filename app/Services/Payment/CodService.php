<?php

namespace App\Services\Payment;

use App\Models\ChartOfAccount;
use App\Models\CodCollection;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\SalesInvoice;
use App\Services\CustomerPaymentService;
use App\Services\OrderNumberService;
use Exception;
use Illuminate\Support\Facades\DB;

class CodService
{
    protected CustomerPaymentService $customerPaymentService;

    public function __construct(CustomerPaymentService $customerPaymentService)
    {
        $this->customerPaymentService = $customerPaymentService;
    }

    /**
     * Create or initialize a COD Collection record when order is placed.
     */
    public function createCodCollection(Order $order, ?SalesInvoice $invoice = null, ?int $driverId = null, ?string $courierName = null): CodCollection
    {
        $gateway = \App\Models\PaymentSetting::getGateway('cod');
        $setting = GeneralSetting::first();

        $isEnabled = $gateway ? $gateway->isEnabled() : (bool)($setting?->cod_enabled ?? true);
        if (!$isEnabled) {
            throw new Exception("Cash On Delivery (COD) is currently disabled.");
        }

        $dueAmount = $invoice ? (float)$invoice->due_amount : (float)$order->due_amount;

        return DB::transaction(function () use ($order, $invoice, $driverId, $courierName, $dueAmount, $setting, $gateway) {
            $seqNo = 'COD-' . date('Ym') . '-' . str_pad((string)(CodCollection::count() + 1), 4, '0', STR_PAD_LEFT);

            // Create preliminary payment transaction
            $txn = PaymentTransaction::create([
                'transaction_no'     => 'TXN-' . date('Ym') . '-' . str_pad((string)(PaymentTransaction::count() + 1), 5, '0', STR_PAD_LEFT),
                'gateway'            => 'cod',
                'order_id'           => $order->id,
                'sales_invoice_id'   => $invoice?->id,
                'customer_id'        => $order->user_id,
                'amount'             => $dueAmount,
                'currency'           => 'DKK',
                'external_reference' => $seqNo,
                'status'             => 'pending',
            ]);

            // Resolve default cash account (1010 Petty Cash / Cash in Hand)
            $defaultAccount = $gateway?->deposit_account_id
                ?: ($setting?->cod_deposit_account_id ?: ChartOfAccount::where('account_code', '1010')->value('id'));

            return CodCollection::create([
                'collection_no'      => $seqNo,
                'transaction_id'     => $txn->id,
                'order_id'           => $order->id,
                'sales_invoice_id'   => $invoice?->id,
                'driver_id'          => $driverId,
                'courier_name'       => $courierName,
                'expected_amount'    => $dueAmount,
                'collected_amount'   => 0.00,
                'difference_amount'  => 0.00,
                'status'             => 'pending_dispatch',
                'deposit_account_id' => $defaultAccount,
            ]);
        });
    }

    /**
     * Mark COD order as out for delivery.
     */
    public function markOutForDelivery(CodCollection $cod, ?int $driverId = null, ?string $courierName = null): CodCollection
    {
        $cod->update([
            'driver_id'    => $driverId ?: $cod->driver_id,
            'courier_name' => $courierName ?: $cod->courier_name,
            'status'       => 'out_for_delivery',
        ]);

        return $cod;
    }

    /**
     * Delivery agent acknowledges physical cash collection in field.
     */
    public function markCollected(CodCollection $cod, float $collectedAmount, ?string $notes = null): CodCollection
    {
        $difference = round($cod->expected_amount - $collectedAmount, 2);

        $cod->update([
            'collected_amount'  => $collectedAmount,
            'difference_amount' => $difference,
            'status'            => 'collected',
            'collected_at'      => now(),
            'notes'             => $notes ?: $cod->notes,
        ]);

        return $cod;
    }

    /**
     * Cashier reconciles physical cash handover, settles invoice, and posts GL journal.
     */
    public function settleHandover(CodCollection $cod, float $cashReceived, int $cashierUserId, ?int $depositAccountId = null): CodCollection
    {
        if ($cod->status === 'handed_over') {
            throw new Exception("This COD collection #{$cod->collection_no} has already been settled.");
        }

        return DB::transaction(function () use ($cod, $cashReceived, $cashierUserId, $depositAccountId) {
            $depositAccountId = $depositAccountId ?: ($cod->deposit_account_id ?: ChartOfAccount::where('account_code', '1010')->value('id'));
            $difference = round($cod->expected_amount - $cashReceived, 2);

            // Update COD record
            $cod->update([
                'collected_amount'       => $cashReceived,
                'difference_amount'      => $difference,
                'status'                 => 'handed_over',
                'handed_over_to_user_id' => $cashierUserId,
                'deposit_account_id'     => $depositAccountId,
                'handed_over_at'         => now(),
            ]);

            // Update PaymentTransaction
            if ($cod->transaction) {
                $cod->transaction->update([
                    'status' => 'captured',
                    'amount' => $cashReceived,
                ]);
            }

            // Settle via Phase 5 CustomerPaymentService (Posts DR 1010 / CR 1030)
            $customerId = $cod->salesInvoice?->order?->user_id ?? ($cod->order?->user_id ?? $cashierUserId);

            $this->customerPaymentService->recordPayment([
                'user_id'          => $customerId,
                'order_id'         => $cod->order_id,
                'sales_invoice_id' => $cod->sales_invoice_id,
                'amount'           => $cashReceived,
                'payment_method'   => 'cash',
                'account_id'       => $depositAccountId,
                'reference_no'     => $cod->collection_no,
                'notes'            => "Cash On Delivery (COD) Physical Settlement #{$cod->collection_no}",
            ], $cashierUserId);

            return $cod;
        });
    }
}
