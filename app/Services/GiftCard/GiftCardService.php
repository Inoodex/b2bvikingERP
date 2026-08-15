<?php

namespace App\Services\GiftCard;

use App\Models\GiftCard;
use App\Models\GiftCardTransaction;
use Illuminate\Support\Facades\DB;

class GiftCardService
{
    /**
     * Issue a new Gift Card.
     */
    public function issueGiftCard(array $data): GiftCard
    {
        $code = $data['code'] ?? $this->generateUniqueCode();

        $giftCard = GiftCard::create([
            'code' => $code,
            'initial_value' => (float) $data['initial_value'],
            'balance' => (float) $data['initial_value'],
            'currency_id' => $data['currency_id'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'status' => $data['status'] ?? 1,
        ]);

        GiftCardTransaction::create([
            'gift_card_id' => $giftCard->id,
            'order_id' => null,
            'type' => 'issue',
            'amount' => (float) $data['initial_value'],
            'balance_after' => (float) $data['initial_value'],
        ]);

        return $giftCard;
    }

    /**
     * Redeem amount from a Gift Card.
     */
    public function redeem(GiftCard $giftCard, float $amount, ?int $orderId = null): array
    {
        if (!$giftCard->status) {
            return ['status' => 'error', 'message' => 'This Gift Card is inactive.'];
        }

        if ($giftCard->expires_at && $giftCard->expires_at->isPast()) {
            return ['status' => 'error', 'message' => 'This Gift Card has expired.'];
        }

        if ($giftCard->balance <= 0) {
            return ['status' => 'error', 'message' => 'This Gift Card has zero balance.'];
        }

        $redeemAmount = min($amount, (float) $giftCard->balance);
        $newBalance = (float) $giftCard->balance - $redeemAmount;

        DB::transaction(function () use ($giftCard, $redeemAmount, $newBalance, $orderId) {
            $giftCard->update(['balance' => $newBalance]);

            GiftCardTransaction::create([
                'gift_card_id' => $giftCard->id,
                'order_id' => $orderId,
                'type' => 'redeem',
                'amount' => $redeemAmount,
                'balance_after' => $newBalance,
            ]);
        });

        return [
            'status' => 'success',
            'message' => 'Gift Card redeemed successfully!',
            'amount_redeemed' => $redeemAmount,
            'balance_remaining' => $newBalance,
        ];
    }

    /**
     * Credit / Adjust balance on a Gift Card.
     */
    public function adjustBalance(GiftCard $giftCard, float $amount, string $reason = 'Manual Adjustment'): GiftCard
    {
        $newBalance = max(0, (float) $giftCard->balance + $amount);

        DB::transaction(function () use ($giftCard, $amount, $newBalance) {
            $giftCard->update(['balance' => $newBalance]);

            GiftCardTransaction::create([
                'gift_card_id' => $giftCard->id,
                'order_id' => null,
                'type' => 'adjust',
                'amount' => abs($amount),
                'balance_after' => $newBalance,
            ]);
        });

        return $giftCard;
    }

    /**
     * Generate a unique 16-character Gift Card code formatted like GC-XXXX-XXXX-XXXX.
     */
    public function generateUniqueCode(): string
    {
        do {
            $code = 'GC-' . rand(1000, 9999) . '-' . rand(1000, 9999) . '-' . rand(1000, 9999);
        } while (GiftCard::where('code', $code)->exists());

        return $code;
    }
}
