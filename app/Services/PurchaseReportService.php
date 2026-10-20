<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\GeneralSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

        return $query->get()->groupBy('vendor_id')->map(function ($purchases) {
            $first = $purchases->first();
            $vendor = $first->vendor;

            return (object)[
                'vendor_id' => $vendor ? $vendor->id : null,
                'supplier_name' => $vendor ? ($vendor->shop_name ?? $vendor->name) : 'N/A',
                'po_count' => $purchases->count(),
                'currency' => $vendor && $vendor->currency ? $vendor->currency->currency_code : 'DKK',
                'currency_icon' => $vendor && $vendor->currency ? $vendor->currency->currency_icon : 'Kr.',
                'total_foreign_amount' => round($purchases->sum('foreign_amount'), 2),
                'total_base_amount' => round($purchases->sum('total_amount'), 2),
                'total_paid' => round($purchases->sum('paid_amount'), 2),
                'total_due' => round($purchases->sum('due_amount'), 2),
            ];
        })->values();
    }

    /**
     * Client Req 2.24: Item-wise Purchase Report
     */
    public function getItemWisePurchase(array $filters = [], int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = PurchaseDetail::with(['purchase.vendor', 'product'])
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

        $allDetails = $query->get();

        $grouped = $allDetails->groupBy('product_id')->map(function ($details) {
            $first = $details->first();
            $product = $first->product;

            $totalQty = $details->sum('qty');
            $totalAmount = $details->sum('total');
            $avgCost = $totalQty > 0 ? round($totalAmount / $totalQty, 2) : 0;
            $avgLandedCost = $totalQty > 0 ? round($details->sum(fn($d) => ($d->landed_cost ?? $d->unit_cost) * $d->qty) / $totalQty, 2) : 0;

            return (object)[
                'product_id' => $product ? $product->id : null,
                'item_name' => $product ? $product->name : 'N/A',
                'sku' => $product ? ($product->product_number ?? $product->sku) : 'N/A',
                'total_quantity_purchased' => $totalQty,
                'average_unit_cost' => $avgCost,
                'average_landed_cost' => $avgLandedCost,
                'total_purchase_value' => round($totalAmount, 2),
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
        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        $purchases = $query->orderBy('date', 'asc')->get();

        $byMonth = $purchases->groupBy(function ($p) {
            return \Carbon\Carbon::parse($p->date)->format('Y-m');
        })->sortKeys()->map(function ($group, $month) {
            return (object)[
                'period' => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y'),
                'month_key' => $month,
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
    public function getPurchaseVsLastYear(int $year, ?int $benchmarkYear = null): array
    {
        $benchmarkYear = ($benchmarkYear !== null && $benchmarkYear > 0) ? $benchmarkYear : ($year - 1);

        $currentYearPurchases = Purchase::where('status', 1)
            ->whereYear('date', $year)
            ->sum('total_amount');

        $lastYearPurchases = Purchase::where('status', 1)
            ->whereYear('date', $benchmarkYear)
            ->sum('total_amount');

        $growth = $lastYearPurchases > 0
            ? round((($currentYearPurchases - $lastYearPurchases) / $lastYearPurchases) * 100, 2)
            : 100.0;

        // 12-month Comparative Matrix
        $monthlyCurrent = Purchase::where('status', 1)
            ->whereYear('date', $year)
            ->select(DB::raw('MONTH(date) as month'), DB::raw('SUM(total_amount) as total'))
            ->groupBy(DB::raw('MONTH(date)'))
            ->pluck('total', 'month');

        $monthlyLast = Purchase::where('status', 1)
            ->whereYear('date', $benchmarkYear)
            ->select(DB::raw('MONTH(date) as month'), DB::raw('SUM(total_amount) as total'))
            ->groupBy(DB::raw('MONTH(date)'))
            ->pluck('total', 'month');

        $monthlyMatrix = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthName = \Carbon\Carbon::create($year, $m, 1)->format('F');
            $cVal = (float) round($monthlyCurrent->get($m, 0), 2);
            $lVal = (float) round($monthlyLast->get($m, 0), 2);
            $varKr = round($cVal - $lVal, 2);
            $growthMo = $lVal > 0 ? round(($varKr / $lVal) * 100, 2) : ($cVal > 0 ? 100.0 : 0.0);

            $monthlyMatrix[] = [
                'month' => $monthName,
                'month_num' => $m,
                'current_year_value' => $cVal,
                'last_year_value' => $lVal,
                'variance_amount' => $varKr,
                'growth_percentage' => $growthMo,
            ];
        }

        return [
            'current_year' => $year,
            'current_year_value' => round($currentYearPurchases, 2),
            'last_year' => $benchmarkYear,
            'last_year_value' => round($lastYearPurchases, 2),
            'benchmark_year' => $benchmarkYear,
            'benchmark_year_value' => round($lastYearPurchases, 2),
            'growth_percentage' => $growth,
            'monthly_matrix' => $monthlyMatrix,
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

    /**
     * Item-wise Purchase PC Breakdown (Step 5)
     */
    public function getItemPcBreakdown(int $productId, array $filters = []): array
    {
        $product = Product::with('unit')->findOrFail($productId);
        $unitName = $product->unit ? $product->unit->name : 'Units';

        $query = PurchaseDetail::with(['purchase.vendor'])
            ->where('product_id', $productId)
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

        $details = $query->get()->sortByDesc(function ($detail) {
            return $detail->purchase?->date?->timestamp ?? 0;
        })->values();

        $currency = GeneralSetting::first()->currency_icon ?? 'Kr.';

        $orders = $details->map(function ($detail, $index) use ($currency, $product, $unitName) {
            $purchase = $detail->purchase;
            $vendor = $purchase?->vendor;

            // Fallback to product catalog purchase price if unit_cost is not recorded
            $unitCost = (float) $detail->unit_cost;
            if ($unitCost <= 0 && $product->purchase_price > 0) {
                $unitCost = (float) $product->purchase_price;
            }

            $landedCost = ($detail->landed_cost !== null && (float)$detail->landed_cost > 0) 
                ? (float) $detail->landed_cost 
                : $unitCost;

            $qty = (float) $detail->qty;
            $total = (float) ($detail->total > 0 ? $detail->total : ($qty * $unitCost));

            return [
                'index' => $index + 1,
                'purchase_id' => $purchase?->id,
                'po_no' => $purchase?->po_no ?? ('PO-' . $purchase?->id),
                'invoice_no' => $purchase?->invoice_no ?? '-',
                'date' => $purchase && $purchase->date ? Carbon::parse($purchase->date)->format('Y-m-d') : '-',
                'supplier_name' => $vendor ? ($vendor->shop_name ?? $vendor->name) : 'N/A',
                'qty' => $qty,
                'formatted_qty' => number_format($qty) . ' ' . $unitName,
                'unit_cost' => $unitCost,
                'formatted_unit_cost' => $currency . ' ' . number_format($unitCost, 2),
                'landed_cost' => $landedCost,
                'formatted_landed_cost' => $currency . ' ' . number_format($landedCost, 2),
                'total' => $total,
                'formatted_total' => $currency . ' ' . number_format($total, 2),
                'variant_info' => $detail->variant_info,
            ];
        });

        $totalQty = (float) $orders->sum('qty');
        $totalAmount = (float) $orders->sum('total');
        $totalLandedAmount = (float) $orders->sum(function ($o) {
            return (float)$o['landed_cost'] * (float)$o['qty'];
        });

        $weightedAvgUnitCost = $totalQty > 0 ? round($totalAmount / $totalQty, 2) : 0;
        $weightedAvgLandedCost = $totalQty > 0 ? round($totalLandedAmount / $totalQty, 2) : 0;

        return [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->product_number ?? $product->sku ?? ('PROD-' . $product->id),
                'unit_name' => $unitName,
            ],
            'summary' => [
                'total_qty' => $totalQty,
                'formatted_total_qty' => number_format($totalQty) . ' ' . $unitName,
                'total_value' => $totalAmount,
                'formatted_total_value' => $currency . ' ' . number_format($totalAmount, 2),
                'weighted_avg_unit_cost' => $weightedAvgUnitCost,
                'formatted_weighted_avg_unit_cost' => $currency . ' ' . number_format($weightedAvgUnitCost, 2),
                'weighted_avg_landed_cost' => $weightedAvgLandedCost,
                'formatted_weighted_avg_landed_cost' => $currency . ' ' . number_format($weightedAvgLandedCost, 2),
                'po_count' => $details->pluck('purchase_id')->unique()->count(),
                'currency' => $currency,
                'unit_name' => $unitName,
            ],
            'orders' => $orders,
        ];
    }
}
