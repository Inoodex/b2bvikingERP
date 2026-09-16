@extends('backend.layouts.master')

@section('title', 'Purchase Value vs Last Year Comparison')

@section('content')
<section class="section">
    @php
        $currentVal = (float) ($comparison['current_year_value'] ?? 0);
        $lastVal = (float) ($comparison['last_year_value'] ?? 0);
        $variance = $currentVal - $lastVal;
        $growth = (float) ($comparison['growth_percentage'] ?? 0);
        $isExpansion = ($variance >= 0);

        // Chart Data for 12 Months
        $chartMonths = [];
        $currentYearMonthly = [];
        $lastYearMonthly = [];
        $currentCumulative = [];
        $lastCumulative = [];
        $runningCurrent = 0;
        $runningLast = 0;

        $peakMonthName = 'None';
        $peakMonthSpend = 0;
        $lowestMonthName = 'None';
        $lowestMonthSpend = PHP_FLOAT_MAX;
        $h1Spend = 0;
        $h2Spend = 0;

        $monthlyList = $comparison['monthly_matrix'] ?? [];

        foreach ($monthlyList as $mRow) {
            $c = (float) $mRow['current_year_value'];
            $l = (float) $mRow['last_year_value'];
            $chartMonths[] = $mRow['month'];
            $currentYearMonthly[] = $c;
            $lastYearMonthly[] = $l;

            $runningCurrent += $c;
            $runningLast += $l;
            $currentCumulative[] = round($runningCurrent, 2);
            $lastCumulative[] = round($runningLast, 2);

            if ($c > $peakMonthSpend) {
                $peakMonthSpend = $c;
                $peakMonthName = $mRow['month'];
            }
            if ($c > 0 && $c < $lowestMonthSpend) {
                $lowestMonthSpend = $c;
                $lowestMonthName = $mRow['month'];
            }

            if ($mRow['month_num'] <= 6) {
                $h1Spend += $c;
            } else {
                $h2Spend += $c;
            }
        }

        if ($lowestMonthSpend === PHP_FLOAT_MAX) {
            $lowestMonthSpend = 0;
            $lowestMonthName = 'None';
        }

        $avgMonthlySpend = $currentVal > 0 ? round($currentVal / 12, 2) : 0;
        $h1Pct = $currentVal > 0 ? round(($h1Spend / $currentVal) * 100, 1) : 0;
        $h2Pct = $currentVal > 0 ? round(($h2Spend / $currentVal) * 100, 1) : 0;

        // Sort Top 3 Months by spend
        $sortedMonths = $monthlyList;
        usort($sortedMonths, function($a, $b) {
            return $b['current_year_value'] <=> $a['current_year_value'];
        });
        $top3Months = array_slice($sortedMonths, 0, 3);
    @endphp

    {{-- =========================================================================
         INLINE EXECUTIVE DESIGN SYSTEM STYLES (MATCHES DASHBOARD.BLADE.PHP)
         ========================================================================= --}}
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    .executive-wrapper {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        color: #0f172a;
    }
    .executive-header-bar {
        border-bottom: 1px solid #e2e8f0;
        padding-top: 24px;
        padding-bottom: 20px;
        margin-bottom: 24px;
    }
    .executive-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }
    @media (max-width: 1200px) {
        .executive-kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 576px) {
        .executive-kpi-grid {
            grid-template-columns: 1fr;
        }
    }
    .executive-kpi-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 16px 18px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .executive-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.06);
        border-color: #cbd5e1;
    }
    .kpi-accent-stripe {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
    }
    .period-tab-btn {
        padding: 5px 14px;
        font-weight: 600;
        font-size: 12px;
        border-radius: 6px;
        text-decoration: none !important;
        transition: all 0.15s ease;
    }
    .period-tab-btn.active {
        background: #ffffff;
        color: #0f172a !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .period-tab-btn:not(.active) {
        color: #64748b !important;
    }
    .period-tab-btn:not(.active):hover {
        color: #0f172a !important;
    }
    .chart-mode-btn {
        font-size: 11px;
        border-radius: 6px;
        padding: 3px 10px;
        border: 1px solid transparent;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.15s ease;
    }
    .chart-mode-btn.active {
        background: #ffffff;
        color: #0f172a;
        border: 1px solid #cbd5e1;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
    .chart-mode-btn:not(.active) {
        color: #64748b;
        background: transparent;
        border: 1px solid transparent;
    }
    .chart-mode-btn:not(.active):hover {
        color: #0f172a;
    }
    .table-yoy-matrix thead th {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        padding: 12px 18px;
        border: none;
        background: #f8fafc;
    }
    .table-yoy-matrix tbody tr {
        border-top: 1px solid #f1f5f9;
        transition: background-color 0.15s ease;
    }
    .table-yoy-matrix tbody tr:hover {
        background-color: #f8fafc;
    }
    .table-yoy-matrix tbody td {
        padding: 13px 18px;
        vertical-align: middle;
        font-size: 13px;
    }
    </style>

    <div class="executive-wrapper">
        {{-- =========================================================================
             1. INTEGRATED EXECUTIVE COMMAND HEADER (MATCHES EXECUTIVE DASHBOARD)
             ========================================================================= --}}
        <div class="executive-header-bar d-flex justify-content-between align-items-center flex-wrap" style="gap: 16px;">
            <div>
                <h1 class="font-weight-bold text-dark mb-1" style="font-size: 24px; letter-spacing: -0.025em;">
                    Purchase Value vs Last Year
                </h1>
                <p class="text-muted mb-0" style="font-size: 13px;">
                    12-month annual comparative spend analysis, budget variance & procurement growth velocity.
                </p>
            </div>

            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                {{-- Fiscal Year Selector Tabs (Matching Period Selector in Dashboard) --}}
                <div class="d-inline-flex align-items-center p-1" style="background: #f1f5f9; border-radius: 8px; border: 1px solid #e2e8f0; gap: 4px;">
                    @foreach($availableYears as $yr)
                        <a href="{{ route('admin.purchase-reports.vs-last-year', ['year' => $yr]) }}" 
                           class="period-tab-btn {{ $year == $yr ? 'active' : '' }}">
                           Year {{ $yr }}
                        </a>
                    @endforeach
                </div>

                {{-- Download PDF Button (revealed dynamically when ready) --}}
                @if(!empty($latestPdf))
                    <a href="{{ $latestPdf['url'] }}" id="btn-download-pdf" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" style="background: #ecfdf5; color: #047857; border-radius: 8px; font-size: 12px; padding: 7px 15px; gap: 6px; border: 1px solid #a7f3d0; text-decoration: none;" title="{{ $latestPdf['filename'] }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Download PDF
                    </a>
                @else
                    <a href="javascript:void(0);" id="btn-download-pdf" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" style="background: #ecfdf5; color: #047857; border-radius: 8px; font-size: 12px; padding: 7px 15px; gap: 6px; border: 1px solid #a7f3d0; text-decoration: none; display: none;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Download PDF
                    </a>
                @endif

                {{-- Async PDF Export Trigger (Dark Slate Executive Button) --}}
                <button type="button" id="btn-generate-pdf" class="btn btn-generate-pdf font-weight-bold d-inline-flex align-items-center shadow-sm"
                    data-url="{{ route('admin.purchase-reports.vs-last-year.pdf.async', ['year' => $year]) }}"
                    data-type="purchase_vs_last_year_report"
                    data-check-url="{{ route('admin.purchase-reports.vs-last-year.check-status') }}"
                    style="background: #0f172a; color: #ffffff; border-radius: 8px; font-size: 12px; padding: 7px 15px; gap: 6px; border: 1px solid #0f172a; transition: all 0.2s ease;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                    Export PDF
                </button>

                {{-- Export CSV Trigger (Clean White Border Button) --}}
                <button type="button" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" id="btnExportCsv"
                    style="background: #ffffff; color: #0f172a; border-radius: 8px; font-size: 12px; padding: 7px 15px; gap: 6px; border: 1px solid #cbd5e1; transition: all 0.2s ease;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                    Export CSV
                </button>
            </div>
        </div>

        {{-- =========================================================================
             2. 4-PILLAR EXECUTIVE KPI GRID (MATCHES DASHBOARD KPI CARDS)
             ========================================================================= --}}
        <div class="executive-kpi-grid">
            {{-- KPI 1: Active Year Spend --}}
            <div class="executive-kpi-card">
                <div class="kpi-accent-stripe" style="background: #2563eb;"></div>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.06em; color: #64748b;">
                            Year {{ $comparison['current_year'] }} Spend
                        </span>
                        <div class="d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 7px; background: #eff6ff; color: #2563eb;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><path d="M3 6h18"></path><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                        </div>
                    </div>
                    <h3 class="mb-0 font-weight-bold text-dark" style="font-size: 21px; letter-spacing: -0.02em;">
                        {!! formatConverted($currentVal) !!}
                    </h3>
                </div>
                <div class="pt-2 mt-2" style="border-top: 1px solid #f1f5f9;">
                    <div class="d-flex justify-content-between align-items-center" style="font-size: 11px;">
                        <span class="text-muted" style="font-size: 10.5px;">Active Fiscal Evaluation</span>
                        <span class="badge font-weight-bold px-1.5 py-0.5" style="font-size: 9.5px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 4px;">
                            Year {{ $comparison['current_year'] }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- KPI 2: Prior Year Benchmark --}}
            <div class="executive-kpi-card">
                <div class="kpi-accent-stripe" style="background: #64748b;"></div>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.06em; color: #64748b;">
                            Year {{ $comparison['last_year'] }} Benchmark
                        </span>
                        <div class="d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 7px; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                    </div>
                    <h3 class="mb-0 font-weight-bold text-dark" style="font-size: 21px; letter-spacing: -0.02em;">
                        {!! formatConverted($lastVal) !!}
                    </h3>
                </div>
                <div class="pt-2 mt-2" style="border-top: 1px solid #f1f5f9;">
                    <div class="d-flex justify-content-between align-items-center" style="font-size: 11px;">
                        <span class="text-muted" style="font-size: 10.5px;">Baseline Prior Year</span>
                        <span class="badge font-weight-bold px-1.5 py-0.5 text-muted border" style="font-size: 9.5px; background: #f8fafc; border-radius: 4px;">
                            Year {{ $comparison['last_year'] }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- KPI 3: Net Annual Variance --}}
            <div class="executive-kpi-card">
                <div class="kpi-accent-stripe" style="background: {{ $isExpansion ? '#dc2626' : '#10b981' }};"></div>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.06em; color: #64748b;">
                            Net Annual Variance
                        </span>
                        <div class="d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 7px; background: {{ $isExpansion ? '#fef2f2; color: #dc2626;' : '#ecfdf5; color: #059669;' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="{{ $isExpansion ? '23 6 13.5 15.5 8.5 10.5 1 18' : '23 18 13.5 8.5 8.5 13.5 1 6' }}"></polyline><polyline points="{{ $isExpansion ? '17 6 23 6 23 12' : '17 18 23 18 23 12' }}"></polyline></svg>
                        </div>
                    </div>
                    <h3 class="mb-0 font-weight-bold {{ $isExpansion ? 'text-danger' : 'text-success' }}" style="font-size: 21px; letter-spacing: -0.02em;">
                        {{ $isExpansion ? '+' : '-' }}{!! formatConverted(abs($variance)) !!}
                    </h3>
                </div>
                <div class="pt-2 mt-2" style="border-top: 1px solid #f1f5f9;">
                    <div class="d-flex justify-content-between align-items-center" style="font-size: 11px;">
                        <span class="text-muted" style="font-size: 10.5px;">{{ $isExpansion ? 'Spend Expansion' : 'Spend Reduction' }}</span>
                        <span class="badge font-weight-bold px-1.5 py-0.5" style="font-size: 9.5px; {{ $isExpansion ? 'background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;' : 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;' }} border-radius: 4px;">
                            {{ $isExpansion ? '+Increase' : '-Savings' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- KPI 4: Annual YoY Growth Rate --}}
            <div class="executive-kpi-card">
                <div class="kpi-accent-stripe" style="background: {{ $growth >= 0 ? '#d97706' : '#10b981' }};"></div>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.06em; color: #64748b;">
                            Annual YoY Growth Rate
                        </span>
                        <div class="d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 7px; background: {{ $growth >= 0 ? '#fffbeb; color: #d97706;' : '#ecfdf5; color: #059669;' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="m16 12-4-4-4 4"></path><path d="M12 16V8"></path></svg>
                        </div>
                    </div>
                    <h3 class="mb-0 font-weight-bold text-dark" style="font-size: 21px; letter-spacing: -0.02em;">
                        {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 2) }}%
                    </h3>
                </div>
                <div class="pt-2 mt-2" style="border-top: 1px solid #f1f5f9;">
                    <div class="d-flex justify-content-between align-items-center" style="font-size: 11px;">
                        <span class="text-muted" style="font-size: 10.5px;">Annual Velocity</span>
                        <span class="badge font-weight-bold px-1.5 py-0.5" style="font-size: 9.5px; {{ $growth >= 0 ? 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;' : 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;' }} border-radius: 4px;">
                            {{ $growth >= 0 ? 'Surge Rate' : 'Contracted' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- =========================================================================
             3. OPERATIONAL QUICK STATUS RIBBON (MATCHES DASHBOARD RIBBON ROW)
             ========================================================================= --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0 !important; overflow: hidden; background: #ffffff;">
            <div class="row no-gutters">
                {{-- Velocity 1: Peak Spend Month --}}
                <div class="col-xl-3 col-md-6 col-12 p-3 border-right border-bottom border-xl-bottom-0 d-flex align-items-center justify-content-between" style="border-color: #f1f5f9 !important;">
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; border-radius: 8px; background: #eff6ff; color: #2563eb;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m13 2-2 2.5V8l3 3-5 5v3l4-2 3 3 2-2-3-3 2-4h-3l-3-3V4.5L13 2Z"></path></svg>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 13.5px;">{!! formatConverted($peakMonthSpend) !!}</div>
                            <small class="text-muted" style="font-size: 11px;">Peak: <strong class="text-dark">{{ $peakMonthName }} {{ $year }}</strong></small>
                        </div>
                    </div>
                    <span class="badge font-weight-bold px-2 py-1" style="font-size: 10px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 6px;">Peak Month</span>
                </div>

                {{-- Velocity 2: Monthly Average Run-Rate --}}
                <div class="col-xl-3 col-md-6 col-12 p-3 border-right border-bottom border-xl-bottom-0 d-flex align-items-center justify-content-between" style="border-color: #f1f5f9 !important;">
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; border-radius: 8px; background: #f5f3ff; color: #7c3aed;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="16" y1="14" x2="16" y2="18"></line><path d="M16 10h.01"></path><path d="M12 10h.01"></path><path d="M8 10h.01"></path><path d="M12 14h.01"></path><path d="M8 14h.01"></path><path d="M12 18h.01"></path><path d="M8 18h.01"></path></svg>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 13.5px;">{!! formatConverted($avgMonthlySpend) !!}/mo</div>
                            <small class="text-muted" style="font-size: 11px;">12-Month Average Run-Rate</small>
                        </div>
                    </div>
                    <span class="badge font-weight-bold px-2 py-1 text-muted border" style="font-size: 10px; background: #f8fafc; border-radius: 6px;">Run-Rate</span>
                </div>

                {{-- Velocity 3: Lowest Spend Month --}}
                <div class="col-xl-3 col-md-6 col-12 p-3 border-right border-bottom border-md-bottom-0 d-flex align-items-center justify-content-between" style="border-color: #f1f5f9 !important;">
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; border-radius: 8px; background: #ecfdf5; color: #059669;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 13.5px;">{!! formatConverted($lowestMonthSpend) !!}</div>
                            <small class="text-muted" style="font-size: 11px;">Lowest: <strong class="text-dark">{{ $lowestMonthName }} {{ $year }}</strong></small>
                        </div>
                    </div>
                    <span class="badge font-weight-bold px-2 py-1" style="font-size: 10px; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 6px;">Trough</span>
                </div>

                {{-- Velocity 4: Fiscal Spend Trajectory --}}
                <div class="col-xl-3 col-md-6 col-12 p-3 d-flex align-items-center justify-content-between" style="border-color: #f1f5f9 !important;">
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; border-radius: 8px; background: {{ $isExpansion ? '#fffbeb; color: #d97706;' : '#ecfdf5; color: #059669;' }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="{{ $isExpansion ? '23 6 13.5 15.5 8.5 10.5 1 18' : '23 18 13.5 8.5 8.5 13.5 1 6' }}"></polyline><polyline points="{{ $isExpansion ? '17 6 23 6 23 12' : '17 18 23 18 23 12' }}"></polyline></svg>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 13.5px;">{{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}% YoY</div>
                            <small class="text-muted" style="font-size: 11px;">{{ $isExpansion ? 'Procurement Expansion' : 'Procurement Contraction' }}</small>
                        </div>
                    </div>
                    <span class="badge font-weight-bold px-2 py-1" style="font-size: 10px; {{ $isExpansion ? 'background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;' : 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;' }} border-radius: 6px;">
                        {{ $isExpansion ? 'Surging' : 'Controlled' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- =========================================================================
             4. CHARTS SECTION: COMPARATIVE SPEND VELOCITY & SEASONAL SHARE (8 + 4 COLS)
             ========================================================================= --}}
        <div class="row mb-4">
            {{-- Left (8 cols): 12-Month Comparative Spend Velocity --}}
            <div class="col-lg-8 col-12 mb-4 mb-lg-0">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0 !important; background: #ffffff;">
                    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-3 px-4" style="border-bottom: 1px solid #f1f5f9; gap: 12px;">
                        <div>
                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <span class="d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 6px; background: #eff6ff; color: #2563eb;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                                </span>
                                <div>
                                    <h4 class="font-weight-bold text-dark mb-0" style="font-size: 15px;">Comparative Spend Velocity</h4>
                                    <small class="text-muted">Benchmark: <strong class="text-dark">Year {{ $comparison['last_year'] }}</strong> vs Active: <strong class="text-primary">Year {{ $comparison['current_year'] }}</strong></small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                            {{-- Interactive Metric Switcher (Matching Executive Dashboard) --}}
                            <div class="btn-group btn-group-sm p-0.5 bg-light border" style="border-radius: 8px; border-color: #e2e8f0 !important;">
                                <button type="button" class="chart-mode-btn active" id="btnModeBar" onclick="switchYoyChart('bar')">
                                    Monthly Bars
                                </button>
                                <button type="button" class="chart-mode-btn" id="btnModeLine" onclick="switchYoyChart('line')">
                                    Cumulative Trend
                                </button>
                            </div>

                            <span class="badge border font-weight-bold px-2 py-1 text-muted" style="font-size: 10px; background: #f8fafc; border-radius: 6px;">
                                YoY Matrix
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        {{-- Custom Legend Bar --}}
                        <div class="d-flex align-items-center justify-content-end mb-3" style="gap: 16px; font-size: 11px;">
                            <span class="d-inline-flex align-items-center" style="gap: 6px; color: #475569; font-weight: 600;">
                                <span style="width: 10px; height: 10px; border-radius: 3px; background: #2563eb; display: inline-block;"></span>
                                Year {{ $comparison['current_year'] }} Spend
                            </span>
                            <span class="d-inline-flex align-items-center" style="gap: 6px; color: #64748b; font-weight: 600;">
                                <span style="width: 10px; height: 10px; border-radius: 3px; background: #cbd5e1; display: inline-block;"></span>
                                Year {{ $comparison['last_year'] }} Benchmark
                            </span>
                        </div>
                        <div style="position: relative; height: 260px;">
                            <canvas id="yoyComparisonChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right (4 cols): Seasonal Spend Distribution & Peak Months --}}
            <div class="col-lg-4 col-12">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0 !important; background: #ffffff;">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4" style="border-bottom: 1px solid #f1f5f9;">
                        <div>
                            <h4 class="font-weight-bold text-dark mb-0 d-flex align-items-center" style="font-size: 15px; gap: 8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                                Capital Allocation
                            </h4>
                            <small class="text-muted">Seasonality & peak purchase concentration</small>
                        </div>
                    </div>

                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        {{-- H1 vs H2 Split Progress --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 11.5px;">
                                <span class="font-weight-bold text-dark d-flex align-items-center" style="gap: 6px;">
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #2563eb;"></span>
                                    H1 (Jan – Jun)
                                </span>
                                <span class="text-dark font-weight-bold">
                                    {!! formatConverted($h1Spend) !!}
                                    <span class="text-muted font-weight-normal ml-1" style="font-size: 10px;">({{ $h1Pct }}%)</span>
                                </span>
                            </div>
                            <div class="progress mb-3" style="height: 5px; border-radius: 9999px; background: #f1f5f9;">
                                <div class="progress-bar" style="width: {{ $h1Pct }}%; background: #2563eb; border-radius: 9999px;"></div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 11.5px;">
                                <span class="font-weight-bold text-dark d-flex align-items-center" style="gap: 6px;">
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #7c3aed;"></span>
                                    H2 (Jul – Dec)
                                </span>
                                <span class="text-dark font-weight-bold">
                                    {!! formatConverted($h2Spend) !!}
                                    <span class="text-muted font-weight-normal ml-1" style="font-size: 10px;">({{ $h2Pct }}%)</span>
                                </span>
                            </div>
                            <div class="progress" style="height: 5px; border-radius: 9999px; background: #f1f5f9;">
                                <div class="progress-bar" style="width: {{ $h2Pct }}%; background: #7c3aed; border-radius: 9999px;"></div>
                            </div>
                        </div>

                        {{-- Top 3 Peak Purchase Months --}}
                        <div class="w-100 pt-3" style="border-top: 1px solid #f1f5f9;">
                            <div class="text-uppercase font-weight-bold mb-2.5" style="font-size: 10.5px; letter-spacing: 0.06em; color: #64748b;">
                                Top Spending Months ({{ $year }})
                            </div>
                            @forelse($top3Months as $idx => $tRow)
                                @php
                                    $rank = $idx + 1;
                                    $rankBadge = match($rank) {
                                        1 => 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;',
                                        2 => 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;',
                                        default => 'background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa;',
                                    };
                                    $tSpend = (float) $tRow['current_year_value'];
                                    $tShare = $currentVal > 0 ? round(($tSpend / $currentVal) * 100, 1) : 0;
                                @endphp
                                <div class="d-flex justify-content-between align-items-center py-1.5" style="border-bottom: 1px dashed #f1f5f9; font-size: 12px;">
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <span class="badge font-weight-bold" style="width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; {{ $rankBadge }}">
                                            {{ $rank }}
                                        </span>
                                        <span class="font-weight-600 text-dark">{{ $tRow['month'] }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-weight-bold text-dark">{!! formatConverted($tSpend) !!}</span>
                                        <span class="text-muted ml-1" style="font-size: 10px;">({{ $tShare }}%)</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted text-center py-2" style="font-size: 11px;">No purchase activity in period</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- =========================================================================
             5. 12-MONTH ITEMIZE MATRIX TABLE (EXACT INVARIANT: '12-Month Comparative Spend Breakdown')
             ========================================================================= --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0 !important; overflow: hidden; background: #ffffff;">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-3 px-4" style="border-bottom: 1px solid #f1f5f9; gap: 12px;">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <span class="d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 8px; background: #f8fafc; color: #0f172a; border: 1px solid #e2e8f0;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="3" y1="15" x2="21" y2="15"></line><line x1="9" y1="3" x2="9" y2="21"></line><line x1="15" y1="3" x2="15" y2="21"></line></svg>
                    </span>
                    <div>
                        <h4 class="font-weight-bold text-dark mb-0" style="font-size: 15px;">
                            12-Month Comparative Spend Breakdown
                        </h4>
                        <small class="text-muted">Itemized month-by-month spend comparison and variance calculation</small>
                    </div>
                </div>

                <div class="d-flex align-items-center" style="gap: 12px;">
                    <span class="text-muted" style="font-size: 12.5px;">
                        Benchmark: <strong class="text-dark">{!! formatConverted($lastVal) !!}</strong>
                        &nbsp;&bull;&nbsp;
                        Active: <strong class="text-primary">{!! formatConverted($currentVal) !!}</strong>
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 table-yoy-matrix" id="table-yoy-matrix">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-right">Year {{ $comparison['last_year'] }} Benchmark</th>
                                <th class="text-right">Year {{ $comparison['current_year'] }} Spend</th>
                                <th class="text-right">Variance Amount</th>
                                <th class="text-center">YoY Growth</th>
                                <th class="text-center">Annual Share</th>
                                <th class="text-center">Trend</th>
                                <th class="text-center">Drill-Down</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comparison['monthly_matrix'] ?? [] as $row)
                                @php
                                    $mNum = str_pad($row['month_num'], 2, '0', STR_PAD_LEFT);
                                    $monthStart = "{$year}-{$mNum}-01";
                                    $monthEnd = \Carbon\Carbon::parse($monthStart)->endOfMonth()->format('Y-m-d');
                                    $rowVar = (float) $row['variance_amount'];
                                    $rowGrowth = (float) $row['growth_percentage'];
                                    $cSpend = (float) $row['current_year_value'];
                                    $monthShare = ($currentVal > 0) ? round(($cSpend / $currentVal) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center" style="gap: 10px;">
                                            <div class="d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 6px; background: #f1f5f9; color: #475569; font-size: 11px;">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                            </div>
                                            <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $row['month'] }}</span>
                                        </div>
                                    </td>
                                    <td class="text-right" style="color: #64748b; font-size: 13px;">
                                        {!! formatConverted($row['last_year_value']) !!}
                                    </td>
                                    <td class="text-right" style="font-size: 13px; font-weight: 700; color: #0f172a;">
                                        {!! formatConverted($cSpend) !!}
                                    </td>
                                    <td class="text-right" style="font-size: 13px; font-weight: 700; color: {{ $rowVar > 0 ? '#dc2626' : ($rowVar < 0 ? '#10b981' : '#64748b') }};">
                                        {{ $rowVar > 0 ? '+' : ($rowVar < 0 ? '-' : '') }}{!! formatConverted(abs($rowVar)) !!}
                                    </td>
                                    <td class="text-center">
                                        @if($rowGrowth > 0)
                                            <span class="badge font-weight-bold px-2 py-0.5" style="font-size: 10px; background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; border-radius: 4px;">
                                                +{{ number_format($rowGrowth, 1) }}%
                                            </span>
                                        @elseif($rowGrowth < 0)
                                            <span class="badge font-weight-bold px-2 py-0.5" style="font-size: 10px; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 4px;">
                                                {{ number_format($rowGrowth, 1) }}%
                                            </span>
                                        @else
                                            <span class="badge font-weight-bold px-2 py-0.5 text-muted border" style="font-size: 10px; background: #f8fafc; border-radius: 4px;">
                                                0.0%
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="width: 140px;">
                                        <div class="d-flex align-items-center justify-content-center" style="gap: 6px;">
                                            <div class="progress flex-grow-1" style="height: 4px; border-radius: 9999px; background: #f1f5f9;">
                                                <div class="progress-bar" style="width: {{ $monthShare }}%; background: #2563eb; border-radius: 9999px;"></div>
                                            </div>
                                            <span class="text-muted font-weight-600" style="font-size: 10.5px; width: 34px; text-align: right;">{{ $monthShare }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($rowVar > 0)
                                            <span style="color: #dc2626;" title="Spend Surge (+{{ number_format($rowGrowth, 1) }}%)">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                                            </span>
                                        @elseif($rowVar < 0)
                                            <span style="color: #10b981;" title="Spend Savings ({{ number_format($rowGrowth, 1) }}%)">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                                            </span>
                                        @else
                                            <span style="color: #94a3b8;" title="Flat Spend">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($cSpend > 0)
                                            <a href="{{ route('admin.reports.purchase', ['start_date' => $monthStart, 'end_date' => $monthEnd]) }}"
                                               class="btn btn-sm font-weight-bold d-inline-flex align-items-center"
                                               target="_blank"
                                               title="Investigate Purchase Orders for {{ $row['month'] }} {{ $year }}"
                                               style="background: #eff6ff; color: #2563eb; border-radius: 6px; font-size: 11px; padding: 3px 10px; border: 1px solid #bfdbfe; text-decoration: none; gap: 4px;">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                                Orders
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="background: #f8fafc; border-top: 2px solid #e2e8f0;">
                            <tr>
                                <td style="padding: 14px 18px; font-weight: 800; font-size: 13px; color: #0f172a;">TOTAL (Full Year)</td>
                                <td class="text-right" style="padding: 14px 18px; font-weight: 600; color: #64748b; font-size: 13px;">{!! formatConverted($lastVal) !!}</td>
                                <td class="text-right" style="padding: 14px 18px; font-weight: 800; color: #0f172a; font-size: 14.5px;">
                                    {!! formatConverted($currentVal) !!}
                                </td>
                                <td class="text-right" style="padding: 14px 18px; font-weight: 800; font-size: 14px; color: {{ $variance > 0 ? '#dc2626' : ($variance < 0 ? '#10b981' : '#0f172a') }};">
                                    {{ $variance > 0 ? '+' : ($variance < 0 ? '-' : '') }}{!! formatConverted(abs($variance)) !!}
                                </td>
                                <td class="text-center" style="padding: 14px 18px;">
                                    <span class="badge font-weight-bold px-2 py-1" style="font-size: 11px; {{ $isExpansion ? 'background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;' : 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;' }} border-radius: 4px;">
                                        {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 2) }}%
                                    </span>
                                </td>
                                <td class="text-center" style="padding: 14px 18px;">
                                    <span class="badge font-weight-bold px-2 py-0.5 text-dark border" style="font-size: 10px; background: #ffffff; border-radius: 4px;">100.0%</span>
                                </td>
                                <td class="text-center" style="padding: 14px 18px;">
                                    <span style="color: {{ $isExpansion ? '#dc2626' : '#10b981' }};">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="{{ $isExpansion ? '23 6 13.5 15.5 8.5 10.5 1 18' : '23 18 13.5 8.5 8.5 13.5 1 6' }}"></polyline><polyline points="{{ $isExpansion ? '17 6 23 6 23 12' : '17 18 23 18 23 12' }}"></polyline></svg>
                                    </span>
                                </td>
                                <td class="text-center" style="padding: 14px 18px;">
                                    <a href="{{ route('admin.reports.purchase', ['start_date' => "{$year}-01-01", 'end_date' => "{$year}-12-31"]) }}"
                                       class="btn btn-sm font-weight-bold d-inline-flex align-items-center shadow-sm"
                                       target="_blank"
                                       title="View All {{ $year }} Procurement Orders"
                                       style="background: #0f172a; color: #ffffff; border-radius: 6px; font-size: 11px; padding: 4px 10px; border: 1px solid #0f172a; text-decoration: none; gap: 4px;">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        All Orders
                                    </a>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
{{-- Include reusable async PDF generation script (Step 1, 2, 3 Pattern) --}}
@include('backend.reports.partials.async_analytics_report_pdf_js')

<script>
var yoyChartInstance = null;
var monthsLabels = @json($chartMonths);
var currentSpendData = @json($currentYearMonthly);
var lastSpendData = @json($lastYearMonthly);
var currentCumulativeData = @json($currentCumulative);
var lastCumulativeData = @json($lastCumulative);
var currencyIcon = @json($settings->currency_icon ?? 'Kr.');
var currentYear = @json($comparison['current_year']);
var lastYear = @json($comparison['last_year']);

function renderYoyChart(mode) {
    var chartCanvas = document.getElementById('yoyComparisonChart');
    if (!chartCanvas) return;

    var ctx = chartCanvas.getContext('2d');
    if (yoyChartInstance) {
        yoyChartInstance.destroy();
    }

    if (mode === 'line') {
        // Create Gradient Fill for Area Trend
        var gradientCurrent = ctx.createLinearGradient(0, 0, 0, 260);
        gradientCurrent.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
        gradientCurrent.addColorStop(1, 'rgba(37, 99, 235, 0.01)');

        var gradientLast = ctx.createLinearGradient(0, 0, 0, 260);
        gradientLast.addColorStop(0, 'rgba(148, 163, 184, 0.15)');
        gradientLast.addColorStop(1, 'rgba(148, 163, 184, 0.01)');

        yoyChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthsLabels,
                datasets: [
                    {
                        label: 'Year ' + currentYear + ' Cumulative Spend',
                        data: currentCumulativeData,
                        borderColor: '#2563eb',
                        backgroundColor: gradientCurrent,
                        borderWidth: 2.5,
                        pointRadius: 3.5,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#ffffff',
                        pointHoverRadius: 5.5,
                        lineTension: 0.35,
                        fill: true
                    },
                    {
                        label: 'Year ' + lastYear + ' Cumulative Benchmark',
                        data: lastCumulativeData,
                        borderColor: '#94a3b8',
                        backgroundColor: gradientLast,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#94a3b8',
                        pointBorderColor: '#ffffff',
                        borderDash: [5, 5],
                        lineTension: 0.35,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                layout: { padding: { top: 10, bottom: 5, left: 10, right: 10 } },
                scales: {
                    xAxes: [{
                        gridLines: { display: false },
                        ticks: { fontColor: '#64748b', fontFamily: "'Plus Jakarta Sans', sans-serif", fontSize: 11 }
                    }],
                    yAxes: [{
                        gridLines: { color: 'rgba(226, 232, 240, 0.6)', zeroLineColor: '#e2e8f0' },
                        ticks: {
                            fontColor: '#64748b',
                            fontFamily: "'Plus Jakarta Sans', sans-serif",
                            fontSize: 11,
                            callback: function(val) { return currencyIcon + ' ' + Number(val).toLocaleString(); }
                        }
                    }]
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#0f172a',
                    titleFontFamily: "'Plus Jakarta Sans', sans-serif",
                    bodyFontFamily: "'Plus Jakarta Sans', sans-serif",
                    titleFontSize: 12,
                    bodyFontSize: 11,
                    cornerRadius: 6,
                    xPadding: 12,
                    yPadding: 10,
                    callbacks: {
                        label: function(item, data) {
                            var label = data.datasets[item.datasetIndex].label || '';
                            return label + ': ' + currencyIcon + ' ' + Number(item.yLabel).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    }
                }
            }
        });
    } else {
        // Grouped Dual-Bar Mode
        yoyChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: monthsLabels,
                datasets: [
                    {
                        label: 'Year ' + currentYear + ' Spend',
                        data: currentSpendData,
                        backgroundColor: '#2563eb',
                        hoverBackgroundColor: '#1d4ed8',
                        borderColor: '#2563eb',
                        borderWidth: 0,
                        borderRadius: 4,
                        barPercentage: 0.65,
                        categoryPercentage: 0.65
                    },
                    {
                        label: 'Year ' + lastYear + ' Benchmark',
                        data: lastSpendData,
                        backgroundColor: '#cbd5e1',
                        hoverBackgroundColor: '#94a3b8',
                        borderColor: '#cbd5e1',
                        borderWidth: 0,
                        borderRadius: 4,
                        barPercentage: 0.65,
                        categoryPercentage: 0.65
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                layout: { padding: { top: 10, bottom: 5, left: 10, right: 10 } },
                scales: {
                    xAxes: [{
                        gridLines: { display: false },
                        ticks: { fontColor: '#64748b', fontFamily: "'Plus Jakarta Sans', sans-serif", fontSize: 11 }
                    }],
                    yAxes: [{
                        gridLines: { color: 'rgba(226, 232, 240, 0.6)', zeroLineColor: '#e2e8f0' },
                        ticks: {
                            fontColor: '#64748b',
                            fontFamily: "'Plus Jakarta Sans', sans-serif",
                            fontSize: 11,
                            callback: function(val) { return currencyIcon + ' ' + Number(val).toLocaleString(); }
                        }
                    }]
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#0f172a',
                    titleFontFamily: "'Plus Jakarta Sans', sans-serif",
                    bodyFontFamily: "'Plus Jakarta Sans', sans-serif",
                    titleFontSize: 12,
                    bodyFontSize: 11,
                    cornerRadius: 6,
                    xPadding: 12,
                    yPadding: 10,
                    callbacks: {
                        label: function(item, data) {
                            var label = data.datasets[item.datasetIndex].label || '';
                            return label + ': ' + currencyIcon + ' ' + Number(item.yLabel).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    }
                }
            }
        });
    }
}

function switchYoyChart(mode) {
    if (mode === 'line') {
        $('#btnModeLine').addClass('active');
        $('#btnModeBar').removeClass('active');
        renderYoyChart('line');
    } else {
        $('#btnModeBar').addClass('active');
        $('#btnModeLine').removeClass('active');
        renderYoyChart('bar');
    }
}

$(document).ready(function() {
    renderYoyChart('bar');

    // Export Table to CSV
    $('#btnExportCsv').on('click', function() {
        var table = document.getElementById('table-yoy-matrix');
        if (!table) return;

        var csv = [];
        var rows = table.querySelectorAll('tr');

        for (var i = 0; i < rows.length; i++) {
            var row = [], cols = rows[i].querySelectorAll('th, td');
            var colCount = cols.length > 1 ? cols.length - 1 : cols.length; // skip action column
            for (var j = 0; j < colCount; j++) {
                var text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/\s+/g, ' ').trim();
                text = text.replace(/"/g, '""');
                row.push('"' + text + '"');
            }
            if (row.length > 0) {
                csv.push(row.join(','));
            }
        }

        var csvContent = "data:text/csv;charset=utf-8,\uFEFF" + csv.join("\n");
        var encodedUri = encodeURI(csvContent);
        var link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "purchase_vs_last_year_{{ $year }}.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
</script>
@endpush
