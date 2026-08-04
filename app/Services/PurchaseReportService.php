<?php

namespace App\Services;

use App\Models\ProductRequest;
use App\Models\Purchase;
use App\Models\PurchaseDetail;

class PurchaseReportService
{
    /**
     * Client Req 2.23: Supplier-wise Purchase Report
     */
    public function getSupplierWisePurchase(array $filters = []): \Illuminate\Support\Collection
    {
        $query = Purchase::with(['vendor', 'currency'])
            ->where('status', 1);

        if (!empty($filters['start_date'])) {
            $query->whereDate('date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('date', '<=', $filters['end_date']);
        }
        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        $purchases = $query->get();

        return $purchases->groupBy('vendor_id')->map(function ($group) {
            $first = $group->first();
            return [
                'vendor_name' => $first->vendor?->name ?? 'Unknown Vendor',
                'vendor_code' => $first->vendor?->code ?? 'N/A',
                'po_count' => $group->count(),
                'total_base_amount' => round($group->sum('total_amount'), 2),
                'total_paid' => round($group->sum('paid_amount'), 2),
                'total_due' => round($group->sum('due_amount'), 2),
            ];
        })->values();
    }

    /**
     * Client Req 2.24 & 2.26: Item-wise Purchase Report with Pagination
     */
    public function getItemWisePurchase(array $filters = [], int $perPage = 25)
    {
        $query = PurchaseDetail::with(['purchase.vendor', 'product', 'variant'])
            ->whereHas('purchase', function ($q) use ($filters) {
                $q->where('status', 1);
                if (!empty($filters['start_date'])) {
                    $q->whereDate('date', '>=', $filters['start_date']);
                }
                if (!empty($filters['end_date'])) {
                    $q->whereDate('date', '<=', $filters['end_date']);
                }
                if (!empty($filters['vendor_id'])) {
                    $q->where('vendor_id', $filters['vendor_id']);
                }
            });

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        $details = $query->get();

        $grouped = $details->groupBy('product_id')->map(function ($group) {
            $first = $group->first();
            $productCode = $first->product?->product_number ?? $first->product?->sku ?? ($first->product_id ? 'PROD-'.$first->product_id : 'N/A');
            return [
                'product_name' => $first->product?->name ?? 'Unknown Product',
                'product_code' => $productCode,
                'total_qty' => $group->sum('qty'),
                'avg_unit_price' => round($group->avg('unit_cost'), 2),
                'avg_landed_cost' => round($group->avg('landed_cost'), 2),
                'total_value' => round($group->sum('total'), 2),
            ];
        })->values();

        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $currentItems = $grouped->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $grouped->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => request()->query()]
        );
    }

    /**
     * Client Req 2.25: Total Purchase Value (Periodic)
     */
    public function getTotalPurchaseValue(array $filters = []): array
    {
        $query = Purchase::where('status', 1);

        if (!empty($filters['start_date'])) {
            $query->whereDate('date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('date', '<=', $filters['end_date']);
        }

        $purchases = $query->get();

        $byMonth = $purchases->groupBy(function ($p) {
            return \Carbon\Carbon::parse($p->date)->format('Y-m');
        })->map(function ($group, $month) {
            return (object)[
                'period' => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y'),
                'po_count' => $group->count(),
                'subtotal' => round($group->sum('total_amount'), 2),
                'discount' => round($group->sum('discount') ?? 0, 2),
                'tax' => round($group->sum('tax') ?? 0, 2),
                'net_total' => round($group->sum('total_amount'), 2),
                'total_value' => round($group->sum('total_amount'), 2),
            ];
        })->values();

        $summary = [
            'total_pos' => $purchases->count(),
            'total_purchase_value' => round($purchases->sum('total_amount'), 2),
            'total_paid_value' => round($purchases->sum('paid_amount'), 2),
            'total_due_value' => round($purchases->sum('due_amount'), 2),
        ];

        return [
            'summary' => $summary,
            'reportData' => $byMonth,
        ];
    }

    /**
     * Client Req 2.27: Purchase Value vs Last Year Comparison
     */
    public function getPurchaseVsLastYear(int $year): array
    {
        $currentYearPurchases = Purchase::where('status', 1)
            ->whereYear('date', $year)
            ->sum('total_amount');

        $lastYearPurchases = Purchase::where('status', 1)
            ->whereYear('date', $year - 1)
            ->sum('total_amount');

        $growth = $lastYearPurchases > 0
            ? round((($currentYearPurchases - $lastYearPurchases) / $lastYearPurchases) * 100, 2)
            : 100.0;

        return [
            'current_year' => $year,
            'current_year_value' => round($currentYearPurchases, 2),
            'last_year' => $year - 1,
            'last_year_value' => round($lastYearPurchases, 2),
            'growth_percentage' => $growth,
        ];
    }

    /**
     * Client Req 2.28 & 2.29 & 2.30: PR Received / Pending / Items Pending
     */
    public function getPrStatusReport(array $filters = []): array
    {
        $query = ProductRequest::with(['user', 'department', 'items']);

        if (!empty($filters['start_date'])) {
            $query->whereDate('request_date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('request_date', '<=', $filters['end_date']);
        }

        $allPrs = $query->get();

        $receivedPrs = $allPrs->where('status', 'approved');
        $pendingPrs = $allPrs->whereIn('status', ['pending', 'draft']);

        $pendingItemsCount = 0;
        foreach ($pendingPrs as $pr) {
            $pendingItemsCount += $pr->items->count();
        }

        return [
            'total_pr_count' => $allPrs->count(),
            'approved_pr_count' => $receivedPrs->count(),
            'pending_pr_count' => $pendingPrs->count(),
            'pending_items_count' => $pendingItemsCount,
            'pending_prs' => $pendingPrs->values(),
        ];
    }

    /**
     * Client Req 2.31 & 2.32: Items Purchased & PO Issued List
     */
    public function getPoIssuedReport(array $filters = []): \Illuminate\Support\Collection
    {
        $query = Purchase::with(['vendor', 'user', 'currency'])
            ->where('status', 1);

        if (!empty($filters['start_date'])) {
            $query->whereDate('date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('date', '<=', $filters['end_date']);
        }

        return $query->orderBy('date', 'desc')->get();
    }
}
