<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLedger;
use App\Models\OrderItem;
use Carbon\Carbon;

class InventoryVelocityService
{
    /**
     * Compute sell-through velocity percentage and run-rate metrics for a product over any dynamic timeframe.
     *
     * @param int $productId
     * @param string|null $preset ('30_days', '90_days', '180_days', '365_days', 'custom')
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int|null $month
     * @param int|null $year
     * @return array
     */
    public function calculateVelocity(
        int $productId,
        ?string $preset = '90_days',
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $month = null,
        ?int $year = null
    ): array {
        // Resolve date range
        [$start, $end, $periodLabel] = $this->resolveDateRange($preset, $startDate, $endDate, $month, $year);

        // 1. Total Quantity Sold in period (from completed / delivered / processing orders)
        $soldQty = (float) OrderItem::where('product_id', $productId)
            ->whereHas('order', function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            })
            ->sum('quantity');

        // 2. Inflow in the selected window (from StockLedger where in_qty > 0)
        $inflowInPeriod = (float) StockLedger::where('product_id', $productId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('in_qty');

        // 3. Fallback: If no new stock arrived during this exact period, evaluate against lifetime inflow or opening stock
        $lifetimeInflow = (float) StockLedger::where('product_id', $productId)->sum('in_qty');
        $baselineInflow = $inflowInPeriod > 0 ? $inflowInPeriod : ($lifetimeInflow > 0 ? $lifetimeInflow : max(1.0, $soldQty));

        // 4. Calculate Sell-Through Rate %: (Sold Qty / Baseline Inflow Qty) * 100
        $sellThroughRate = round(($soldQty / $baselineInflow) * 100, 1);

        // Cap at 100% for baseline presentation if no stock anomalies
        $displayRate = min(100.0, $sellThroughRate);

        // 5. Monthly Run-Rate (Average units sold per 30-day block)
        $daysInWindow = max(1, $start->diffInDays($end) + 1);
        $monthlyRunRate = round(($soldQty / $daysInWindow) * 30, 1);

        // 6. Velocity Classification
        $classification = $this->classifyVelocity($displayRate, $daysInWindow);

        return [
            'product_id' => $productId,
            'preset' => $preset,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'period_label' => $periodLabel,
            'days_in_window' => $daysInWindow,
            'sold_qty' => $soldQty,
            'inflow_qty' => $baselineInflow,
            'inflow_in_period' => $inflowInPeriod,
            'baseline_inflow' => $baselineInflow,
            'lifetime_inflow' => $lifetimeInflow,
            'sell_through_rate' => $displayRate,
            'raw_sell_through_rate' => $sellThroughRate,
            'monthly_run_rate' => $monthlyRunRate,
            'classification' => $classification
        ];
    }

    /**
     * Resolve start and end Carbon dates based on preset, month/year, or custom range.
     */
    protected function resolveDateRange(?string $preset, ?string $startDate, ?string $endDate, ?int $month, ?int $year): array
    {
        $now = Carbon::now();

        // 1. Specific Month & Year
        if ($month && $year) {
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            return [$start, $end, $start->format('M Y')];
        }

        // 2. Specific Full Year
        if ($year && !$month) {
            $start = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $end = $start->copy()->endOfYear();
            return [$start, $end, "Year " . $year];
        }

        // 3. Custom Date Range
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            return [$start, $end, $start->format('d M Y') . ' - ' . $end->format('d M Y')];
        }

        // 4. Quick Presets
        return match ($preset) {
            '30_days' => [$now->copy()->subDays(30)->startOfDay(), $now->copy()->endOfDay(), 'Last 30 Days (1 Mo)'],
            '180_days' => [$now->copy()->subDays(180)->startOfDay(), $now->copy()->endOfDay(), 'Last 180 Days (6 Mo)'],
            '365_days' => [$now->copy()->subDays(365)->startOfDay(), $now->copy()->endOfDay(), 'Last 365 Days (1 Yr)'],
            default => [$now->copy()->subDays(90)->startOfDay(), $now->copy()->endOfDay(), 'Last 90 Days (3 Mo)'],
        };
    }

    /**
     * Classify velocity into actionable procurement badges.
     */
    protected function classifyVelocity(float $rate, int $days): array
    {
        if ($rate >= 50.0) {
            return [
                'badge' => 'badge-success',
                'bg_color' => '#10b981',
                'icon' => 'fas fa-fire text-danger',
                'label' => 'High Velocity (Fast Mover)',
                'advice' => 'High Demand: Increase Reorder Volume',
                'ratio_multiplier' => '1.5x - 2.0x'
            ];
        } elseif ($rate >= 20.0) {
            return [
                'badge' => 'badge-primary',
                'bg_color' => '#3b82f6',
                'icon' => 'fas fa-balance-scale text-primary',
                'label' => 'Steady Mover',
                'advice' => 'Consistent Sales: Maintain Regular PO',
                'ratio_multiplier' => '1.0x'
            ];
        } elseif ($rate >= 5.0) {
            return [
                'badge' => 'badge-warning',
                'bg_color' => '#f59e0b',
                'icon' => 'fas fa-hourglass-half text-warning',
                'label' => 'Slow Mover',
                'advice' => 'Low Sales: Reduce Reorder Volume',
                'ratio_multiplier' => '0.5x'
            ];
        } else {
            return [
                'badge' => 'badge-danger',
                'bg_color' => '#ef4444',
                'icon' => 'fas fa-skull-crossbones text-danger',
                'label' => 'Dead Stock Risk',
                'advice' => 'Critical: Freeze Orders & Plan Clearance',
                'ratio_multiplier' => '0.0x'
            ];
        }
    }
}
