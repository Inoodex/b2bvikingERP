<?php

namespace App\Services;

use App\Models\PurchasePayment;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\VendorReturn;
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

        $totalBilled = $bills->sum('grand_total');
        $totalPaid = $payments->sum('base_amount');
        $totalDebitNotes = $returns->sum('total_claim_amount');
        $currentOutstanding = max(0, round($totalBilled - $totalPaid - $totalDebitNotes, 2));

        // Combine into chronological transactions list
        $transactions = collect([]);

        foreach ($bills as $bill) {
            $transactions->push([
                'date' => $bill->bill_date->format('Y-m-d'),
                'type' => 'Bill',
                'reference' => $bill->bill_no,
                'po_no' => $bill->purchase?->po_no ?? 'N/A',
                'debit' => $bill->grand_total, // Vendor billed us (our liability increases)
                'credit' => 0.00,
                'status' => ucfirst($bill->payment_status),
            ]);
        }

        foreach ($payments as $payment) {
            $transactions->push([
                'date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : $payment->created_at->format('Y-m-d'),
                'type' => 'Payment',
                'reference' => $payment->payment_no ?? ('PAY-' . $payment->id),
                'po_no' => $payment->purchase?->po_no ?? 'N/A',
                'debit' => 0.00,
                'credit' => $payment->base_amount > 0 ? $payment->base_amount : $payment->amount, // We paid vendor (liability decreases)
                'status' => 'Approved',
            ]);
        }

        foreach ($returns as $ret) {
            $transactions->push([
                'date' => $ret->created_at->format('Y-m-d'),
                'type' => 'Debit Note',
                'reference' => $ret->debit_note_no,
                'po_no' => $ret->purchase?->po_no ?? 'N/A',
                'debit' => 0.00,
                'credit' => $ret->total_claim_amount, // Debit note reduces liability
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
     */
    public function getAgingReport(?int $vendorId = null): Collection
    {
        $query = VendorBill::whereIn('payment_status', ['unpaid', 'partial']);

        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        $unpaidBills = $query->with('vendor')->get();

        $today = now();
        $vendorAging = [];

        foreach ($unpaidBills as $bill) {
            $vId = $bill->vendor_id;
            if (!isset($vendorAging[$vId])) {
                $vendorAging[$vId] = [
                    'vendor_name' => $bill->vendor->name ?? 'Unknown Vendor',
                    'vendor_code' => $bill->vendor->code ?? 'N/A',
                    'phone' => $bill->vendor->phone ?? 'N/A',
                    'current' => 0.00,   // 0-30 days
                    'days_31_60' => 0.00, // 31-60 days
                    'days_61_90' => 0.00, // 61-90 days
                    'days_90_plus' => 0.00, // 90+ days
                    'total_due' => 0.00,
                ];
            }

            $dueAmount = $bill->due_amount;
            $ageInDays = $today->diffInDays($bill->bill_date);

            if ($ageInDays <= 30) {
                $vendorAging[$vId]['current'] += $dueAmount;
            } else if ($ageInDays <= 60) {
                $vendorAging[$vId]['days_31_60'] += $dueAmount;
            } else if ($ageInDays <= 90) {
                $vendorAging[$vId]['days_61_90'] += $dueAmount;
            } else {
                $vendorAging[$vId]['days_90_plus'] += $dueAmount;
            }

            $vendorAging[$vId]['total_due'] += $dueAmount;
        }

        return collect(array_values($vendorAging));
    }
}
