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
        // Enterprise Idempotency Guard: Never duplicate if already posted
        if (JournalEntry::where('reference_type', $model->getMorphClass())->where('reference_id', $model->id)->exists()) {
            return;
        }

        if ($model instanceof GoodsReceipt) {
            $this->handleGoodsReceipt($model);
        } elseif ($model instanceof OrderPayment) {
            $this->handleOrderPayment($model);
        }
    }

    protected function handleGoodsReceipt(GoodsReceipt $receipt): void
    {
        try {
            $receipt->loadMissing(['items.product']);
            $totalAmount = (float) $receipt->items->sum(fn($item) => $item->accepted_qty * ($item->product?->purchase_price ?? 0));
            if ($totalAmount <= 0) return;

            $lines = [
                ['account_code' => '1050', 'debit' => $totalAmount, 'credit' => 0], // DR Inventory Asset
                ['account_code' => '2020', 'debit' => 0,           'credit' => $totalAmount], // CR GRN Accrued Clearing
            ];

            $date = $receipt->receipt_date ? $receipt->receipt_date->toDateString() : null;
            $this->journalService->postJournal('Goods Receipt (GRN)', $receipt, $lines, $date, "Inventory GRN #{$receipt->receipt_no}");
        } catch (\Exception $e) {
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
