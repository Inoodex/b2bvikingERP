@extends('backend.layouts.master')

@section('title', 'Total Purchase Value Analysis')

@section('content')
<section class="section">
    {{-- Standard Stisla Section Header (h1 and breadcrumb as direct children to prevent overlap) --}}
    <div class="section-header">
        <h1><i class="fas fa-chart-line text-primary mr-2"></i> Total Purchase Value Analysis</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
            <div class="breadcrumb-item">Total Purchase Value</div>
        </div>
    </div>

    <div class="section-body">
        <!-- ═══════════════════════════════════════════════════
             1. EXECUTIVE KPI COCKPIT TILES (UNBREAKABLE NUMBERS)
             ═══════════════════════════════════════════════════ -->
        <div class="row">
            {{-- Tile 1: POs Issued --}}
            <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                <div class="card h-100 shadow-sm border-0 kpi-stat-card border-indicator-primary">
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-uppercase text-muted font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                                POs Issued
                            </span>
                            <div class="stat-icon-circle bg-primary-light text-primary">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                        </div>
                        <div class="kpi-value text-dark">
                            {{ number_format($summary['total_pos']) }}
                        </div>
                        <div class="d-flex align-items-center mt-2 text-muted" style="font-size: 11px;">
                            <i class="fas fa-receipt text-primary mr-1"></i> Procurement orders in period
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tile 2: Total Purchase Value --}}
            <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                <div class="card h-100 shadow-sm border-0 kpi-stat-card border-indicator-info">
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-uppercase text-muted font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                                Total Purchase Value
                            </span>
                            <div class="stat-icon-circle bg-info-light text-info">
                                <i class="fas fa-coins"></i>
                            </div>
                        </div>
                        <div class="kpi-value text-primary font-weight-bold" title="{{ strip_tags(formatConverted($summary['total_purchase_value'])) }}">
                            {!! formatConverted($summary['total_purchase_value']) !!}
                        </div>
                        <div class="d-flex align-items-center mt-2 text-muted" style="font-size: 11px;">
                            <i class="fas fa-wallet text-info mr-1"></i> Gross committed spend
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tile 3: Amount Disbursed (Paid) --}}
            <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                <div class="card h-100 shadow-sm border-0 kpi-stat-card border-indicator-success">
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-uppercase text-muted font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                                Amount Disbursed
                            </span>
                            <div class="stat-icon-circle bg-success-light text-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="kpi-value text-success font-weight-bold" title="{{ strip_tags(formatConverted($summary['total_paid_value'])) }}">
                            {!! formatConverted($summary['total_paid_value']) !!}
                        </div>
                        @php
                            $paidPct = ($summary['total_purchase_value'] > 0) ? round(($summary['total_paid_value'] / $summary['total_purchase_value']) * 100, 1) : 0;
                        @endphp
                        <div class="d-flex align-items-center mt-2 text-muted" style="font-size: 11px;">
                            <span class="badge badge-success px-1 py-0 mr-1" style="font-size: 10px;">{{ $paidPct }}%</span> settled disbursements
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tile 4: Outstanding Balance (Due) --}}
            <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                <div class="card h-100 shadow-sm border-0 kpi-stat-card border-indicator-danger">
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-uppercase text-muted font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                                Outstanding Balance
                            </span>
                            <div class="stat-icon-circle bg-danger-light text-danger">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="kpi-value text-danger font-weight-bold" title="{{ strip_tags(formatConverted($summary['total_due_value'])) }}">
                            {!! formatConverted($summary['total_due_value']) !!}
                        </div>
                        @php
                            $duePct = ($summary['total_purchase_value'] > 0) ? round(($summary['total_due_value'] / $summary['total_purchase_value']) * 100, 1) : 0;
                        @endphp
                        <div class="d-flex align-items-center mt-2 text-muted" style="font-size: 11px;">
                            <span class="badge badge-danger px-1 py-0 mr-1" style="font-size: 10px;">{{ $duePct }}%</span> pending vendor payables
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════
             2. FILTER BAR, DATE PRESETS & ASYNC PDF ENGINE
             ═══════════════════════════════════════════════════ -->
        <div class="card card-primary shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center py-2 flex-wrap">
                <div class="d-flex align-items-center">
                    <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 0;">
                        <i class="fas fa-filter text-primary mr-2"></i> Periodic Filter & Date Presets
                    </h4>
                </div>
                <div class="card-header-action d-flex align-items-center flex-wrap mt-2 mt-md-0">
                    {{-- Async PDF Download Button (revealed when ready) --}}
                    @if(!empty($latestPdf))
                        <a href="{{ $latestPdf['url'] }}" id="btn-download-pdf" class="btn btn-success btn-download-pdf mr-2 shadow-sm" title="{{ $latestPdf['filename'] }}">
                            <i class="fas fa-file-download mr-1"></i> Download PDF (Ready: {{ $latestPdf['time'] }})
                        </a>
                    @else
                        <a href="javascript:void(0);" id="btn-download-pdf" class="btn btn-success btn-download-pdf mr-2 shadow-sm" style="display: none;">
                            <i class="fas fa-file-download mr-1"></i> Download PDF
                        </a>
                    @endif

                    {{-- Async PDF Generate Trigger Button --}}
                    <button type="button" id="btn-generate-pdf" class="btn btn-outline-primary btn-generate-pdf mr-2 shadow-sm"
                        data-url="{{ route('admin.purchase-reports.total-value.pdf.async') }}"
                        data-type="total_purchase_value_report"
                        data-check-url="{{ route('admin.purchase-reports.total-value.check-status') }}"
                        title="Export high-resolution PDF report">
                        <i class="fas fa-file-pdf mr-1"></i> Export PDF
                    </button>

                    {{-- CSV Export Button --}}
                    <button type="button" class="btn btn-outline-success shadow-sm" id="btnExportCsv" title="Export Table Data to CSV / Excel">
                        <i class="fas fa-file-excel mr-1"></i> Export CSV
                    </button>
                </div>
            </div>

            <div class="card-body pt-3 pb-2">
                {{-- Quick Presets Pill Buttons --}}
                <div class="d-flex align-items-center mb-3 flex-wrap">
                    <span class="text-muted font-weight-bold mr-2" style="font-size: 12px;">Quick Presets:</span>
                    <div class="btn-group btn-group-sm" role="group" id="quickDatePresets">
                        <button type="button" class="btn btn-outline-primary btn-preset" data-preset="this-month">This Month</button>
                        <button type="button" class="btn btn-outline-primary btn-preset" data-preset="last-month">Last Month</button>
                        <button type="button" class="btn btn-outline-primary btn-preset" data-preset="this-quarter">This Quarter</button>
                        <button type="button" class="btn btn-outline-primary btn-preset" data-preset="this-year">This Year</button>
                        <button type="button" class="btn btn-outline-secondary btn-preset" data-preset="all-time">All Time</button>
                    </div>
                </div>

                <form action="{{ route('admin.purchase-reports.total-value') }}" method="GET" id="filterForm">
                    <div class="row align-items-end">
                        <div class="col-md-3 col-sm-6 mb-2">
                            <label class="font-weight-bold text-dark" style="font-size: 12px;">Start Date</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <label class="font-weight-bold text-dark" style="font-size: 12px;">End Date</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-2">
                            <label class="font-weight-bold text-dark" style="font-size: 12px;">Supplier / Vendor</label>
                            <select name="vendor_id" class="form-control form-control-sm select2">
                                <option value="">All Suppliers</option>
                                @foreach($vendors ?? [] as $vendor)
                                    <option value="{{ $vendor->id }}" {{ ($filters['vendor_id'] ?? '') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->shop_name ?? $vendor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-primary flex-grow-1 mr-2 shadow-sm">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.purchase-reports.total-value') }}" class="btn btn-sm btn-outline-secondary shadow-sm" title="Reset Filters">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @php
            $grandTotal = $summary['total_purchase_value'] ?? 0;
            $chartLabels = $reportData->pluck('period')->toArray();
            $chartValues = $reportData->pluck('net_total')->toArray();
            $chartCounts = $reportData->pluck('po_count')->toArray();
            $activePeriodsCount = count($chartLabels);
            $avgMonthlySpend = $activePeriodsCount > 0 ? round($grandTotal / $activePeriodsCount, 2) : 0;
        @endphp

        <!-- ═══════════════════════════════════════════════════
             3. VISUAL PERIODIC SPEND DISTRIBUTION CHART
             ═══════════════════════════════════════════════════ -->
        @if($activePeriodsCount > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center py-2 flex-wrap">
                    <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 0;">
                        <i class="fas fa-chart-bar text-primary mr-2"></i> Monthly Spend & Order Volume Trend
                    </h4>
                    <div class="d-flex align-items-center flex-wrap mt-2 mt-md-0">
                        {{-- Clean Custom Header Legend --}}
                        <span class="mr-3" style="font-size: 11px; color: #495057;">
                            <span class="d-inline-block mr-1" style="width: 10px; height: 10px; background: #28549e; border-radius: 2px;"></span> Net Spend
                        </span>
                        <span class="mr-3" style="font-size: 11px; color: #495057;">
                            <span class="d-inline-block mr-1" style="width: 10px; height: 10px; background: #10b981; border-radius: 50%;"></span> POs Issued
                        </span>
                        <span class="badge badge-light border text-muted mr-2" style="font-size: 11px;">
                            <i class="fas fa-calendar-check mr-1 text-primary"></i> {{ $activePeriodsCount }} {{ Str::plural('Month', $activePeriodsCount) }}
                        </span>
                        <span class="badge badge-light border text-muted" style="font-size: 11px;">
                            <i class="fas fa-calculator mr-1 text-info"></i> Avg: <strong class="text-dark">{!! formatConverted($avgMonthlySpend) !!}</strong> / mo
                        </span>
                    </div>
                </div>
                <div class="card-body py-3">
                    <div style="height: 250px; position: relative;">
                        <canvas id="periodicSpendChart"></canvas>
                    </div>
                </div>
            </div>
        @endif

        <!-- ═══════════════════════════════════════════════════
             4. PERIODIC BREAKDOWN DATA TABLE
             ═══════════════════════════════════════════════════ -->
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-3 flex-wrap">
                <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 0;">
                    <i class="fas fa-table text-primary mr-2"></i> Monthly Procurement Spend Breakdown
                </h4>
                <div class="d-flex align-items-center mt-2 mt-md-0">
                    <span class="text-muted" style="font-size: 13px;">
                        Committed Spend: <strong class="text-primary font-weight-bold">{!! formatConverted($grandTotal) !!}</strong>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="table-periodic">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th style="width: 20%; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #495057;">Period / Month</th>
                                <th class="text-center" style="width: 10%; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #495057;">Total POs</th>
                                <th class="text-right" style="width: 14%; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #495057;">Gross Subtotal</th>
                                <th class="text-right" style="width: 12%; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #495057;">Discount</th>
                                <th class="text-right" style="width: 12%; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #495057;">Tax / VAT</th>
                                <th class="text-right" style="width: 15%; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #495057;">Net Total Spend</th>
                                <th class="text-center" style="width: 11%; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #495057;">Share %</th>
                                <th class="text-center" style="width: 6%; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #495057;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportData as $row)
                                @php
                                    $sharePct = ($grandTotal > 0) ? round(($row->net_total / $grandTotal) * 100, 1) : 0;
                                    $monthStart = isset($row->month_key) ? $row->month_key . '-01' : '';
                                    $monthEnd = isset($row->month_key) ? \Carbon\Carbon::parse($monthStart)->endOfMonth()->format('Y-m-d') : '';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-dark d-flex align-items-center">
                                            <span class="badge badge-light border text-primary mr-2 px-2 py-1" style="font-size: 11px;">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                            <span>{{ $row->period }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-light border font-weight-bold px-2 py-1" style="font-size: 12px;">
                                            {{ number_format($row->po_count) }}
                                        </span>
                                    </td>
                                    <td class="text-right align-middle font-weight-500">
                                        {!! formatConverted($row->subtotal) !!}
                                    </td>
                                    <td class="text-right align-middle font-weight-600 text-success">
                                        @if($row->discount > 0)
                                            <span class="badge badge-success-light px-2 py-1" style="background-color: #e8f5e9; color: #2e7d32; font-weight: 600; border-radius: 4px;">
                                                <i class="fas fa-tag mr-1"></i>{!! formatConverted($row->discount) !!}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-right align-middle text-muted">
                                        @if($row->tax > 0)
                                            {!! formatConverted($row->tax) !!}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-right align-middle font-weight-bold text-primary" style="font-size: 14px;">
                                        {!! formatConverted($row->net_total) !!}
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span class="font-weight-bold text-dark mr-2" style="font-size: 11px; min-width: 38px;">{{ $sharePct }}%</span>
                                            <div class="progress flex-grow-1" style="height: 6px; max-width: 70px; border-radius: 10px; background-color: #e9ecef;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $sharePct }}%; border-radius: 10px;" aria-valuenow="{{ $sharePct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($monthStart && $monthEnd)
                                            <a href="{{ route('admin.reports.purchase', ['start_date' => $monthStart, 'end_date' => $monthEnd, 'vendor_id' => $filters['vendor_id'] ?? '']) }}"
                                               class="btn btn-sm btn-outline-primary shadow-sm"
                                               target="_blank"
                                               title="View Purchase Orders for {{ $row->period }}">
                                                <i class="fas fa-list-ul"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <div class="py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block text-muted" style="opacity: 0.4;"></i>
                                            <h5 class="text-dark">No procurement records found for the selected period</h5>
                                            <p class="small text-muted mb-3">Try clearing your filters or selecting "All Suppliers".</p>
                                            <a href="{{ route('admin.purchase-reports.total-value') }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-redo mr-1"></i> Clear Filters
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($reportData) > 0)
                            <tfoot style="background-color: #f8f9fa; font-weight: bold; border-top: 2px solid #28549e; font-size: 13px;">
                                <tr>
                                    <td class="font-weight-bold text-dark">GRAND TOTAL</td>
                                    <td class="text-center">
                                        <span class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size: 12px;">
                                            {{ number_format($reportData->sum('po_count')) }}
                                        </span>
                                    </td>
                                    <td class="text-right">{!! formatConverted($reportData->sum('subtotal')) !!}</td>
                                    <td class="text-right">
                                        @if($reportData->sum('discount') > 0)
                                            <span class="text-success">{!! formatConverted($reportData->sum('discount')) !!}</span>
                                        @else
                                            <span class="text-muted font-weight-normal">—</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($reportData->sum('tax') > 0)
                                            {!! formatConverted($reportData->sum('tax')) !!}
                                        @else
                                            <span class="text-muted font-weight-normal">—</span>
                                        @endif
                                    </td>
                                    <td class="text-right text-primary font-weight-bold" style="font-size: 15px;">
                                        {!! formatConverted($reportData->sum('net_total')) !!}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-success px-2 py-1">100.0%</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.reports.purchase', ['start_date' => $filters['start_date'] ?? '', 'end_date' => $filters['end_date'] ?? '', 'vendor_id' => $filters['vendor_id'] ?? '']) }}"
                                           class="btn btn-sm btn-primary shadow-sm"
                                           target="_blank"
                                           title="View All Procurement Orders in Period">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

@push('styles')
<style>
    /* KPI Card styling to prevent number wrapping */
    .kpi-stat-card {
        border-radius: 8px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        background: #ffffff;
    }
    .kpi-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08) !important;
    }
    .border-indicator-primary { border-left: 4px solid #28549e !important; }
    .border-indicator-info    { border-left: 4px solid #17a2b8 !important; }
    .border-indicator-success { border-left: 4px solid #28a745 !important; }
    .border-indicator-danger  { border-left: 4px solid #dc3545 !important; }

    .stat-icon-circle {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }
    .bg-primary-light { background: #e8f0fe; color: #28549e; }
    .bg-info-light    { background: #e0f7fa; color: #00838f; }
    .bg-success-light { background: #e8f5e9; color: #2e7d32; }
    .bg-danger-light  { background: #ffebee; color: #c62828; }

    .kpi-value {
        font-size: 20px;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.25;
        margin-top: 4px;
    }

    .btn-preset.active {
        background-color: #28549e !important;
        color: #fff !important;
        border-color: #28549e !important;
    }
</style>
@endpush

@push('scripts')
{{-- Include reusable async PDF generation and status checking script (matching Step 1, 2, 3) --}}
@include('backend.reports.partials.async_analytics_report_pdf_js')

<script>
$(document).ready(function() {
    // 1. Detect & Highlight Active Date Preset
    var currentStart = $('#start_date').val();
    var currentEnd = $('#end_date').val();

    function formatDate(d) {
        var month = '' + (d.getMonth() + 1);
        var day = '' + d.getDate();
        var year = d.getFullYear();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        return [year, month, day].join('-');
    }

    var today = new Date();
    var thisMonthStart = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
    var thisMonthEnd = formatDate(new Date(today.getFullYear(), today.getMonth() + 1, 0));

    var lastMonthStart = formatDate(new Date(today.getFullYear(), today.getMonth() - 1, 1));
    var lastMonthEnd = formatDate(new Date(today.getFullYear(), today.getMonth(), 0));

    var quarter = Math.floor(today.getMonth() / 3);
    var quarterStart = formatDate(new Date(today.getFullYear(), quarter * 3, 1));
    var quarterEnd = formatDate(new Date(today.getFullYear(), (quarter + 1) * 3, 0));

    var thisYearStart = formatDate(new Date(today.getFullYear(), 0, 1));
    var thisYearEnd = formatDate(new Date(today.getFullYear(), 11, 31));

    if (currentStart === thisMonthStart && currentEnd === thisMonthEnd) {
        $('.btn-preset[data-preset="this-month"]').addClass('active');
    } else if (currentStart === lastMonthStart && currentEnd === lastMonthEnd) {
        $('.btn-preset[data-preset="last-month"]').addClass('active');
    } else if (currentStart === quarterStart && currentEnd === quarterEnd) {
        $('.btn-preset[data-preset="this-quarter"]').addClass('active');
    } else if (currentStart === thisYearStart && currentEnd === thisYearEnd) {
        $('.btn-preset[data-preset="this-year"]').addClass('active');
    } else if (!currentStart && !currentEnd) {
        $('.btn-preset[data-preset="all-time"]').addClass('active');
    }

    // 2. Preset Button Click Handler
    $('.btn-preset').on('click', function(e) {
        e.preventDefault();
        var preset = $(this).data('preset');
        var startDate = '';
        var endDate = '';

        if (preset === 'this-month') {
            startDate = thisMonthStart;
            endDate = thisMonthEnd;
        } else if (preset === 'last-month') {
            startDate = lastMonthStart;
            endDate = lastMonthEnd;
        } else if (preset === 'this-quarter') {
            startDate = quarterStart;
            endDate = quarterEnd;
        } else if (preset === 'this-year') {
            startDate = thisYearStart;
            endDate = thisYearEnd;
        } else if (preset === 'all-time') {
            startDate = '';
            endDate = '';
        }

        $('#start_date').val(startDate);
        $('#end_date').val(endDate);

        // Auto submit form
        $('#filterForm').submit();
    });

    // 3. Chart.js Dual-Axis Monthly Spend Visualization
    var chartCanvas = document.getElementById('periodicSpendChart');
    if (chartCanvas) {
        var labels = @json($chartLabels);
        var spendData = @json($chartValues);
        var poCounts = @json($chartCounts);
        var currencyIcon = @json($settings->currency_icon ?? 'Kr.');

        var ctx = chartCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Net Spend (' + currencyIcon + ')',
                        data: spendData,
                        backgroundColor: 'rgba(40, 84, 158, 0.75)',
                        borderColor: '#28549e',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'POs Issued',
                        data: poCounts,
                        type: 'line',
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 2.5,
                        fill: false,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false // Using clean HTML legend in header
                },
                layout: {
                    padding: {
                        top: 15,
                        bottom: 5,
                        left: 10,
                        right: 10
                    }
                },
                scales: {
                    xAxes: [{
                        gridLines: { display: false },
                        ticks: { fontColor: '#6c757d', maxRotation: 45, minRotation: 0 }
                    }],
                    yAxes: [
                        {
                            id: 'y',
                            type: 'linear',
                            position: 'left',
                            gridLines: { color: 'rgba(0, 0, 0, 0.05)' },
                            ticks: {
                                fontColor: '#6c757d',
                                callback: function(value) {
                                    return Number(value).toLocaleString();
                                }
                            }
                        },
                        {
                            id: 'y1',
                            type: 'linear',
                            position: 'right',
                            gridLines: { drawOnChartArea: false },
                            ticks: {
                                beginAtZero: true,
                                precision: 0,
                                fontColor: '#10b981'
                            }
                        }
                    ]
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(33, 37, 41, 0.9)',
                    titleFontColor: '#ffffff',
                    bodyFontColor: '#ffffff',
                    cornerRadius: 6,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                            if (tooltipItem.datasetIndex === 0) {
                                return datasetLabel + ': ' + Number(tooltipItem.yLabel).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                            return datasetLabel + ': ' + tooltipItem.yLabel;
                        }
                    }
                }
            }
        });
    }

    // 4. Export Table to CSV
    $('#btnExportCsv').on('click', function() {
        var table = document.getElementById('table-periodic');
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
        var timestamp = new Date().toISOString().slice(0, 10);
        link.setAttribute("download", "total_purchase_value_" + timestamp + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
</script>
@endpush
