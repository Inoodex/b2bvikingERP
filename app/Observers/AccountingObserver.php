<?php

namespace App\Observers;

use App\Models\GoodsReceipt;
use App\Models\JournalEntry;
use App\Models\OrderPayment;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AccountingObserver
{
    protected JournalEntryService $journalService;

    public function __construct(JournalEntryService $journalService)
    {
        $this->journalService = $journalService;
    }

    /**
     * Dispatch created event for observed accounting models.
     * Enforces enterprise idempotency to strictly prevent double journal entries.
     */
    public function created(Model $model): void
    {
        if ($model instanceof GoodsReceipt) {
            $this->handleGoodsReceipt($model);
        } elseif ($model instanceof GoodsReceiptItem) {
            $receipt = $model->goodsReceipt ?? \App\Models\GoodsReceipt::find($model->goods_receipt_id);
            if ($receipt) {
                $this->handleGoodsReceipt($receipt);
            }
        } elseif ($model instanceof OrderPayment) {
            // Enterprise Idempotency Guard: Never duplicate if already posted
            if (JournalEntry::where('reference_type', $model->getMorphClass())->where('reference_id', $model->id)->exists()) {
                return;
            }
            $this->handleOrderPayment($model);
        }
    }

    public function handleGoodsReceipt(GoodsReceipt $receipt): void
    {
        try {
            // Enterprise Idempotency Guard: Never duplicate if already posted
            if (JournalEntry::where('reference_type', $receipt->getMorphClass())->where('reference_id', $receipt->id)->exists()) {
                return;
            }

            $receipt->load(['items.product']);
            $totalAmount = (float) $receipt->items->sum(fn($item) => (float)$item->accepted_qty * (float)($item->product?->purchase_price ?? 0));
            if ($totalAmount <= 0) return;

            $assetAccount = \App\Models\ChartOfAccount::where('account_code', '1050')->first()
                ?? \App\Models\ChartOfAccount::where('account_type', 'asset')->first();
            $clearingAccount = \App\Models\ChartOfAccount::where('account_code', '2020')->first()
                ?? \App\Models\ChartOfAccount::where('account_type', 'liability')->first()
                ?? \App\Models\ChartOfAccount::where('account_type', 'revenue')->first()
                ?? \App\Models\ChartOfAccount::first();

            $assetCode = $assetAccount ? $assetAccount->account_code : '1050';
            $clearingCode = $clearingAccount ? $clearingAccount->account_code : '2020';

            $lines = [
                ['account_code' => $assetCode, 'debit' => $totalAmount, 'credit' => 0], // DR Inventory Asset
                ['account_code' => $clearingCode, 'debit' => 0,          'credit' => $totalAmount], // CR GRN Accrued Clearing
            ];

            $date = $receipt->receipt_date ? (is_string($receipt->receipt_date) ? $receipt->receipt_date : $receipt->receipt_date->toDateString()) : now()->toDateString();
            $receiptNo = $receipt->grn_no ?? ($receipt->receipt_no ?? "GRN-{$receipt->id}");
            $this->journalService->postJournal('Goods Receipt (GRN)', $receipt, $lines, $date, "Inventory GRN #{$receiptNo}");
        } catch (\Throwable $e) {
            Log::error("AccountingObserver error on GoodsReceipt #{$receipt->id}: " . $e->getMessage());
        }
    }

    protected function handleOrderPayment(OrderPayment $payment): void
    {
        try {
            $amount = (float) $payment->amount;
            if ($amount <= 0) return;

            $cashBankCode = in_array(strtolower((string)$payment->payment_method), ['cash']) ? '1010' : '1020';

            $lines = [
                ['account_code' => $cashBankCode, 'debit' => $amount, 'credit' => 0], // DR Cash/Bank
                ['account_code' => '1030',         'debit' => 0,      'credit' => $amount], // CR Accounts Receivable
            ];

            $date = $payment->created_at ? $payment->created_at->toDateString() : null;
            $this->journalService->postJournal('Order Payment Received', $payment, $lines, $date, "Order Payment #{$payment->id} for Order #{$payment->order_id}");
        } catch (\Exception $e) {
            Log::error("AccountingObserver error on OrderPayment #{$payment->id}: " . $e->getMessage());
        }
    }
}
