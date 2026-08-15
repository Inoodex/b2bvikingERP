<?php

namespace App\Services\Credit;

use App\Models\Order;
use App\Models\User;

class CreditValidationService
{
    /**
     * Evaluate customer credit exposure for a new order.
     *
     * @param int $customerId
     * @param float $newOrderTotal
     * @param int|null $currentOrderId Optional ID of order being edited
     * @return array
     */
    public function evaluateCreditExposure(?int $customerId, float $newOrderTotal, ?int $currentOrderId = null): array
    {
        if (!$customerId) {
            return [
                'credit_limit' => 0.00,
                'current_dues' => 0.00,
                'new_order_total' => $newOrderTotal,
                'total_exposure' => $newOrderTotal,
                'remaining_credit' => 999999999.00,
                'is_exceeded' => false,
                'status' => 'approved',
                'reason' => 'Guest / Cash Sale (No Credit Limit Enforced).',
            ];
        }

        $customer = User::find($customerId);
        if (!$customer) {
            return [
                'credit_limit' => 0.00,
                'current_dues' => 0.00,
                'new_order_total' => $newOrderTotal,
                'total_exposure' => $newOrderTotal,
                'remaining_credit' => 0.00,
                'is_exceeded' => false,
                'status' => 'approved',
                'reason' => 'Customer account not found.',
            ];
        }

        $creditLimit = (float) ($customer->credit_limit ?? 0.00);

        // If credit limit is 0.00 or null, unlimited credit or cash sale applies
        if ($creditLimit <= 0) {
            return [
                'credit_limit' => 0.00,
                'current_dues' => 0.00,
                'new_order_total' => $newOrderTotal,
                'total_exposure' => $newOrderTotal,
                'remaining_credit' => 999999999.00,
                'is_exceeded' => false,
                'status' => 'approved',
                'reason' => 'No credit limit enforced (Cash Sale / Unlimited).',
            ];
        }

        // Calculate sum of unpaid due_amount from active non-cancelled past orders
        $duesQuery = Order::where('user_id', $customerId)
            ->where('payment_status', '!=', 'paid')
            ->whereNotIn('status', ['cancelled', 'rejected']);

        if ($currentOrderId) {
            $duesQuery->where('id', '!=', $currentOrderId);
        }

        $currentDues = (float) $duesQuery->sum('due_amount');
        $totalExposure = round($currentDues + $newOrderTotal, 2);
        $remainingCredit = round($creditLimit - $totalExposure, 2);
        $isExceeded = $totalExposure > $creditLimit;

        return [
            'credit_limit' => $creditLimit,
            'current_dues' => $currentDues,
            'new_order_total' => $newOrderTotal,
            'total_exposure' => $totalExposure,
            'remaining_credit' => $remainingCredit,
            'is_exceeded' => $isExceeded,
            'status' => $isExceeded ? 'credit_hold' : 'approved',
            'reason' => $isExceeded 
                ? 'Credit Limit Exceeded: Total exposure (' . number_format($totalExposure, 2) . ') exceeds approved limit (' . number_format($creditLimit, 2) . ').'
                : 'Credit Limit Approved: Remaining credit line is kr. ' . number_format($remainingCredit, 2),
        ];
    }
}
