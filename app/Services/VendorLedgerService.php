<?php

namespace App\Services;

use App\Models\PurchasePayment;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\VendorReturn;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class VendorLedgerService
{
    /**
     * Get running statement of account for a vendor.
     */
    public function getVendorStatement(Vendor $vendor, ?string $fromDate = null, ?string $toDate = null): array
    {
        $billsQuery = VendorBill::where('vendor_id', $vendor->id);
        $paymentsQuery = PurchasePayment::where('vendor_id', $vendor->id);
        $returnsQuery = VendorReturn::where('status', 'approved')
            ->whereHas('purchase', function ($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id);
            });

        if ($fromDate) {
            $billsQuery->whereDate('bill_date', '>=', $fromDate);
            $paymentsQuery->whereDate('payment_date', '>=', $fromDate);
            $returnsQuery->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $billsQuery->whereDate('bill_date', '<=', $toDate);
            $paymentsQuery->whereDate('payment_date', '<=', $toDate);
            $returnsQuery->whereDate('created_at', '<=', $toDate);
        }

        $bills = $billsQuery->orderBy('bill_date', 'asc')->get();
        $payments = $paymentsQuery->orderBy('payment_date', 'asc')->get();
        $returns = $returnsQuery->orderBy('created_at', 'asc')->get();

        $totalBilled = (float)$bills->sum('grand_total');
        $totalPaid = (float)$payments->sum(fn($p) => $p->base_amount > 0 ? $p->base_amount : $p->amount);
        $totalDebitNotes = (float)$returns->sum('total_claim_amount');
        $currentOutstanding = max(0, round($totalBilled - $totalPaid - $totalDebitNotes, 2));

        // Combine into chronological transactions list
        $transactions = collect([]);

        foreach ($bills as $bill) {
            $date = $bill->bill_date ? Carbon::parse($bill->bill_date)->format('Y-m-d') : $bill->created_at->format('Y-m-d');
            $transactions->push([
                'date' => $date,
                'type' => 'Bill',
                'reference' => $bill->bill_no,
                'po_no' => $bill->purchase?->po_no ?? 'N/A',
                'debit' => (float)$bill->grand_total, // Vendor billed us (our liability increases)
                'credit' => 0.00,
                'status' => ucfirst($bill->payment_status),
            ]);
        }

        foreach ($payments as $payment) {
            $date = $payment->payment_date ? Carbon::parse($payment->payment_date)->format('Y-m-d') : $payment->created_at->format('Y-m-d');
            $transactions->push([
                'date' => $date,
                'type' => 'Payment',
                'reference' => $payment->payment_no ?? ('PAY-' . $payment->id),
                'po_no' => $payment->purchase?->po_no ?? 'N/A',
                'debit' => 0.00,
                'credit' => (float)($payment->base_amount > 0 ? $payment->base_amount : $payment->amount), // We paid vendor (liability decreases)
                'status' => 'Approved',
            ]);
        }

        foreach ($returns as $ret) {
            $date = Carbon::parse($ret->created_at)->format('Y-m-d');
            $transactions->push([
                'date' => $date,
                'type' => 'Debit Note',
                'reference' => $ret->debit_note_no,
                'po_no' => $ret->purchase?->po_no ?? 'N/A',
                'debit' => 0.00,
                'credit' => (float)$ret->total_claim_amount, // Debit note reduces liability
                'status' => 'Claim Approved',
            ]);
        }

        $sortedTransactions = $transactions->sortBy('date')->values();

        // Compute running balance
        $runningBalance = 0;
        $formattedTransactions = $sortedTransactions->map(function ($item) use (&$runningBalance) {
            $runningBalance += ($item['debit'] - $item['credit']);
            $item['running_balance'] = round($runningBalance, 2);
            return $item;
        });

        return [
            'vendor' => $vendor,
            'total_billed' => round($totalBilled, 2),
            'total_paid' => round($totalPaid, 2),
            'total_debit_notes' => round($totalDebitNotes, 2),
            'outstanding_balance' => round($currentOutstanding, 2),
            'transactions' => $formattedTransactions,
        ];
    }

    /**
     * Get AP Aging analysis for all vendors or a specific vendor.
     * Uses due_date for true standard accounts payable aging.
     */
    public function getAgingReport(?int $vendorId = null): Collection
    {
        $query = VendorBill::whereIn('payment_status', ['unpaid', 'partial'])->where('due_amount', '>', 0);

        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        $unpaidBills = $query->with('vendor')->get();
        $today = now()->startOfDay();
        $vendorAging = [];

        foreach ($unpaidBills as $bill) {
            $vId = $bill->vendor_id;
            if (!isset($vendorAging[$vId])) {
                $vendorAging[$vId] = [
                    'vendor_name' => $bill->vendor->shop_name ?? ($bill->vendor->name ?? 'Unknown Supplier'),
                    'vendor_code' => $bill->vendor->code ?? 'N/A',
                    'phone'       => $bill->vendor->phone ?? 'N/A',
                    'current'     => 0.00,   // Not overdue or <= 30 days
                    'days_31_60'  => 0.00,   // 31-60 days overdue
                    'days_61_90'  => 0.00,   // 61-90 days overdue
                    'days_90_plus'=> 0.00,   // 90+ days overdue
                    'total_due'   => 0.00,
                ];
            }

            $dueAmount = (float)$bill->due_amount;
            $dueDate = $bill->due_date ? Carbon::parse($bill->due_date)->startOfDay() : ($bill->bill_date ? Carbon::parse($bill->bill_date)->addDays(30)->startOfDay() : $today);

            if ($today->lte($dueDate)) {
                // Not yet past due
                $vendorAging[$vId]['current'] += $dueAmount;
            } else {
                $daysOverdue = $dueDate->diffInDays($today);

                if ($daysOverdue <= 30) {
                    $vendorAging[$vId]['current'] += $dueAmount;
                } elseif ($daysOverdue <= 60) {
                    $vendorAging[$vId]['days_31_60'] += $dueAmount;
                } elseif ($daysOverdue <= 90) {
                    $vendorAging[$vId]['days_61_90'] += $dueAmount;
                } else {
                    $vendorAging[$vId]['days_90_plus'] += $dueAmount;
                }
            }

            $vendorAging[$vId]['total_due'] += $dueAmount;
        }

        return collect(array_values($vendorAging));
    }
}
