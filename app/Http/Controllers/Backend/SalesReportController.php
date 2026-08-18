<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\SalesInvoice;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SalesReportController extends Controller
{
    /**
     * Display Customer Accounts Receivable (AR) Aging Report.
     * Categorizes unpaid dues into 0-30, 31-60, 61-90, and 90+ day risk buckets.
     */
    public function arAging(Request $request)
    {
        $customerId = $request->get('customer_id');

        $query = SalesInvoice::with(['order.user'])
            ->where('due_amount', '>', 0)
            ->where('status', 'posted');

        if ($customerId) {
            $query->whereHas('order', function ($q) use ($customerId) {
                $q->where('user_id', $customerId);
            });
        }

        $invoices = $query->get();

        $agingData = [];
        $totals = [
            'total_due' => 0.00,
            'current_0_30' => 0.00,
            'days_31_60' => 0.00,
            'days_61_90' => 0.00,
            'over_90' => 0.00,
        ];

        $now = Carbon::now();

        foreach ($invoices as $inv) {
            $user = $inv->order ? $inv->order->user : null;
            $userId = $user ? $user->id : 0;
            $customerName = $user ? ($user->outlet_name ? $user->outlet_name . ' (' . $user->name . ')' : $user->name) : 'Guest / Unassigned';
            $phone = $user ? ($user->phone ?: 'N/A') : 'N/A';

            if (!isset($agingData[$userId])) {
                $agingData[$userId] = [
                    'customer_id' => $userId,
                    'customer_name' => $customerName,
                    'phone' => $phone,
                    'total_due' => 0.00,
                    'current_0_30' => 0.00,
                    'days_31_60' => 0.00,
                    'days_61_90' => 0.00,
                    'over_90' => 0.00,
                    'invoice_count' => 0,
                ];
            }

            $invDate = Carbon::parse($inv->created_at);
            $ageInDays = $invDate->diffInDays($now);
            $due = (float)$inv->due_amount;

            $agingData[$userId]['total_due'] += $due;
            $agingData[$userId]['invoice_count'] += 1;
            $totals['total_due'] += $due;

            if ($ageInDays <= 30) {
                $agingData[$userId]['current_0_30'] += $due;
                $totals['current_0_30'] += $due;
            } elseif ($ageInDays <= 60) {
                $agingData[$userId]['days_31_60'] += $due;
                $totals['days_31_60'] += $due;
            } elseif ($ageInDays <= 90) {
                $agingData[$userId]['days_61_90'] += $due;
                $totals['days_61_90'] += $due;
            } else {
                $agingData[$userId]['over_90'] += $due;
                $totals['over_90'] += $due;
            }
        }

        $customers = User::where('status', 1)->orderBy('name')->get();

        return view('backend.reports.ar_aging', compact('agingData', 'totals', 'customers', 'customerId'));
    }

    /**
     * Download printable AR Aging PDF Report.
     */
    public function exportArAgingPdf(Request $request)
    {
        $customerId = $request->get('customer_id');

        $query = SalesInvoice::with(['order.user'])
            ->where('due_amount', '>', 0)
            ->where('status', 'posted');

        if ($customerId) {
            $query->whereHas('order', function ($q) use ($customerId) {
                $q->where('user_id', $customerId);
            });
        }

        $invoices = $query->get();

        $agingData = [];
        $totals = [
            'total_due' => 0.00,
            'current_0_30' => 0.00,
            'days_31_60' => 0.00,
            'days_61_90' => 0.00,
            'over_90' => 0.00,
        ];

        $now = Carbon::now();

        foreach ($invoices as $inv) {
            $user = $inv->order ? $inv->order->user : null;
            $userId = $user ? $user->id : 0;
            $customerName = $user ? ($user->outlet_name ? $user->outlet_name . ' (' . $user->name . ')' : $user->name) : 'Guest / Unassigned';
            $phone = $user ? ($user->phone ?: 'N/A') : 'N/A';

            if (!isset($agingData[$userId])) {
                $agingData[$userId] = [
                    'customer_id' => $userId,
                    'customer_name' => $customerName,
                    'phone' => $phone,
                    'total_due' => 0.00,
                    'current_0_30' => 0.00,
                    'days_31_60' => 0.00,
                    'days_61_90' => 0.00,
                    'over_90' => 0.00,
                    'invoice_count' => 0,
                ];
            }

            $invDate = Carbon::parse($inv->created_at);
            $ageInDays = $invDate->diffInDays($now);
            $due = (float)$inv->due_amount;

            $agingData[$userId]['total_due'] += $due;
            $agingData[$userId]['invoice_count'] += 1;
            $totals['total_due'] += $due;

            if ($ageInDays <= 30) {
                $agingData[$userId]['current_0_30'] += $due;
                $totals['current_0_30'] += $due;
            } elseif ($ageInDays <= 60) {
                $agingData[$userId]['days_31_60'] += $due;
                $totals['days_31_60'] += $due;
            } elseif ($ageInDays <= 90) {
                $agingData[$userId]['days_61_90'] += $due;
                $totals['days_61_90'] += $due;
            } else {
                $agingData[$userId]['over_90'] += $due;
                $totals['over_90'] += $due;
            }
        }

        $generalSetting = GeneralSetting::first();
        $pdf = Pdf::loadView('backend.pdf.ar_aging', compact('agingData', 'totals', 'generalSetting'));
        return $pdf->stream('Customer_AR_Aging_Report_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Display Salesperson & Account Manager Performance Analytics Report.
     */
    public function salespersonPerformance(Request $request)
    {
        $orders = Order::with(['user'])
            ->whereIn('status', ['approved', 'processing', 'completed'])
            ->get();

        $performance = [];

        foreach ($orders as $order) {
            $creator = $order->created_by ? User::find($order->created_by) : $order->user;
            $repId = $creator ? $creator->id : 0;
            $repName = $creator ? $creator->name : 'System Admin / Guest';

            if (!isset($performance[$repId])) {
                $performance[$repId] = [
                    'rep_name' => $repName,
                    'order_count' => 0,
                    'total_sales' => 0.00,
                    'total_paid' => 0.00,
                    'total_due' => 0.00,
                    'avg_deal_size' => 0.00,
                ];
            }

            $performance[$repId]['order_count'] += 1;
            $performance[$repId]['total_sales'] += (float)$order->total_amount;
            $performance[$repId]['total_paid'] += (float)$order->paid_amount;
            $performance[$repId]['total_due'] += (float)$order->due_amount;
        }

        foreach ($performance as $repId => $data) {
            $performance[$repId]['avg_deal_size'] = $data['order_count'] > 0 ? $data['total_sales'] / $data['order_count'] : 0.00;
        }

        return view('backend.reports.salesperson_performance', compact('performance'));
    }
}
