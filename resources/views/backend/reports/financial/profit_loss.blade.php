@extends('backend.layouts.master')
@section('title', 'Profit & Loss Statement (Income Statement)')

@push('css')
<style>
    .pl-page {
        --pl-red: #E11835;
        --pl-ink: #1f2937;
        --pl-muted: #6b7280;
        --pl-border: #e8ebf0;
    }

    .pl-page .pl-header {
        padding: 18px 22px;
        margin-bottom: 20px;
        border: 1px solid var(--pl-border);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 4px 16px rgba(31, 41, 55, 0.05);
    }

    .pl-page .pl-header h1 {
        color: var(--pl-ink);
        font-size: 21px;
        line-height: 1.3;
    }

    .pl-page .pl-accent {
        color: var(--pl-red) !important;
    }

    .pl-page .pl-header-actions {
        gap: 8px;
        flex-wrap: wrap;
    }

    .pl-page .pl-action,
    .pl-page .pl-filter-button {
        border: 1px solid var(--pl-red) !important;
        border-radius: 8px;
        background: var(--pl-red) !important;
        color: #fff !important;
        font-weight: 700;
        box-shadow: 0 3px 10px rgba(225, 24, 53, 0.16);
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }

    .pl-page .pl-filter-button {
        min-height: 48px;
        padding: 10px 16px;
    }

    .pl-page .pl-action:hover,
    .pl-page .pl-filter-button:hover {
        border-color: var(--pl-red) !important;
        background: var(--pl-red) !important;
        color: #fff !important;
        box-shadow: 0 5px 14px rgba(225, 24, 53, 0.24);
    }

    .pl-page .pl-dashboard-link {
        border-radius: 8px;
        font-weight: 700;
    }

    .pl-page .pl-filter-card,
    .pl-page .pl-report-card,
    .pl-page .pl-summary-card {
        overflow: hidden;
        border: 1px solid var(--pl-border) !important;
        border-radius: 14px !important;
        background: #fff;
        box-shadow: 0 4px 16px rgba(31, 41, 55, 0.05) !important;
    }

    .pl-page .pl-filter-card {
        border-top: 3px solid var(--pl-red) !important;
    }

    .pl-page .pl-filter-card .card-body {
        padding: 20px;
    }

    .pl-page .pl-date-input {
        min-height: 44px;
        border: 1px solid #d8dde5 !important;
        border-radius: 8px !important;
        box-shadow: none !important;
    }

    .pl-page .pl-date-input:hover,
    .pl-page .pl-date-input:focus {
        border-color: var(--pl-red) !important;
        box-shadow: 0 0 0 3px rgba(225, 24, 53, 0.1) !important;
    }

    .pl-page .pl-reset-button {
        min-height: 48px;
        border: 1px solid var(--pl-red) !important;
        border-radius: 8px;
        background: #fff !important;
        color: var(--pl-red) !important;
        font-weight: 700;
    }

    .pl-page .pl-reset-button:hover {
        background: var(--pl-red) !important;
        color: #fff !important;
    }

    .pl-page .pl-quick-ranges {
        gap: 8px;
        flex-wrap: wrap;
    }

    .pl-page .pl-range-link {
        border-color: rgba(225, 24, 53, 0.35) !important;
        border-radius: 999px;
        color: var(--pl-red) !important;
    }

    .pl-page .pl-range-link:hover {
        border-color: var(--pl-red) !important;
        background: var(--pl-red) !important;
        color: #fff !important;
    }

    .pl-page .pl-metric-card {
        position: relative;
        height: 100%;
        border: 1px solid var(--pl-border) !important;
        border-radius: 14px !important;
        background: #fff;
        color: var(--pl-ink);
        box-shadow: 0 4px 16px rgba(31, 41, 55, 0.06) !important;
    }

    .pl-page .pl-metric-card .card-body {
        padding: 22px;
    }

    .pl-page .pl-metric-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(225, 24, 53, 0.09);
        color: var(--pl-red);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pl-page .pl-metric-label {
        color: var(--pl-muted);
        font-size: 11px;
        letter-spacing: 0.7px;
    }

    .pl-page .pl-metric-value {
        color: var(--pl-ink);
        font-size: clamp(20px, 2vw, 27px);
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .pl-page .pl-metric-note {
        color: var(--pl-muted);
        font-size: 12px;
    }

    .pl-page .pl-report-card .card-header {
        min-height: 62px;
        padding: 14px 18px;
        gap: 12px;
        flex-wrap: wrap;
    }

    .pl-page .pl-report-card h5 {
        font-size: 15px;
    }

    .pl-page .pl-report-card .table td,
    .pl-page .pl-report-card .table th {
        padding: 12px 14px;
        vertical-align: middle;
    }

    .pl-page .pl-summary-card .card-body {
        padding: 24px;
    }

    .pl-page .pl-summary-title {
        color: var(--pl-muted);
        font-size: 12px;
        letter-spacing: 0.8px;
    }

    .pl-page .pl-summary-equation {
        font-size: clamp(21px, 2.4vw, 32px);
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    @media (max-width: 767.98px) {
        .pl-page .pl-header {
            align-items: flex-start !important;
            flex-direction: column;
            gap: 14px;
            padding: 16px;
        }

        .pl-page .pl-header-actions {
            width: 100%;
        }

        .pl-page .pl-header-actions .btn {
            margin: 0 !important;
        }

        .pl-page .pl-filter-card .card-body {
            padding: 16px;
        }

        .pl-page .pl-quick-ranges {
            align-items: flex-start;
        }

        .pl-page .pl-summary-card .card-body {
            padding: 20px 16px;
        }
    }
</style>
@endpush

@section('content')
<section class="section pl-page">
    <div class="section-header pl-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="font-weight-bold mb-1"><i class="fas fa-chart-line pl-accent mr-2"></i> Profit & Loss Statement</h1>
            <p class="text-muted mb-0 small">IFRS & GAAP Compliant Income Statement (Revenue vs Operating Expenses & COGS)</p>
        </div>
        <div class="section-header-breadcrumb pl-header-actions d-flex align-items-center">
            @if(!empty($latestPdf))
                <a href="{{ $latestPdf['url'] }}" id="btn-download-pdf" class="btn pl-action font-weight-bold mr-2 shadow-sm" title="{{ $latestPdf['filename'] }}">
                    <i class="fas fa-file-download mr-1"></i> Download PDF (Ready: {{ $latestPdf['time'] }})
                </a>
            @else
                <a href="#" id="btn-download-pdf" class="btn pl-action font-weight-bold mr-2 shadow-sm" style="display: none;">
                    <i class="fas fa-file-download mr-1"></i> Download PDF (Ready)
                </a>
            @endif

            <button type="button" id="btn-generate-pdf" class="btn pl-action font-weight-bold mr-2 shadow-sm" data-url="{{ route('admin.reports.profit-loss.pdf') }}" data-type="profit_loss">
                <i class="fas fa-file-pdf mr-1"></i> Generate Fresh PDF
            </button>

            <a href="{{ route('admin.dashboard') }}" class="btn btn-light border pl-dashboard-link"><i class="fas fa-arrow-left mr-1"></i> Dashboard</a>
        </div>
    </div>

    <div class="section-body">
        <!-- Date Filter Card -->
        <div class="card pl-filter-card mb-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.reports.profit-loss') }}" method="GET" class="row align-items-end">
                    <div class="col-md-4 form-group mb-md-0">
                        <label class="font-weight-bold text-dark mb-1"><i class="fas fa-calendar-alt pl-accent mr-1"></i> Start Date</label>
                        <input type="date" name="date_from" class="form-control pl-date-input" value="{{ request()->filled('date_from') ? request('date_from') : '' }}">
                    </div>
                    <div class="col-md-4 form-group mb-md-0">
                        <label class="font-weight-bold text-dark mb-1"><i class="fas fa-calendar-check pl-accent mr-1"></i> End Date</label>
                        <input type="date" name="date_to" class="form-control pl-date-input" value="{{ request()->filled('date_to') ? request('date_to') : '' }}">
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex">
                            <button type="submit" class="btn pl-filter-button font-weight-bold flex-grow-1 mr-2">
                                <i class="fas fa-filter mr-2"></i> Filter
                            </button>
                            <a href="{{ route('admin.reports.profit-loss') }}" class="btn pl-reset-button font-weight-bold">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Quick Date Presets -->
                <div class="mt-3 pt-3 border-top d-flex align-items-center pl-quick-ranges">
                    <span class="text-muted font-weight-bold small mr-3">Quick Range:</span>
                    <a href="{{ route('admin.reports.profit-loss', ['date_from' => date('Y-m-01'), 'date_to' => date('Y-m-d')]) }}" class="btn btn-sm btn-outline-danger pl-range-link font-weight-bold">This Month</a>
                    <a href="{{ route('admin.reports.profit-loss', ['date_from' => date('Y-01-01'), 'date_to' => date('Y-12-31')]) }}" class="btn btn-sm btn-outline-danger pl-range-link font-weight-bold">YTD {{ date('Y') }}</a>
                    <a href="{{ route('admin.reports.profit-loss') }}" class="btn btn-sm btn-outline-danger pl-range-link font-weight-bold">Full History (All Time)</a>
                </div>
            </div>
        </div>

        <!-- Metric Overview Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card pl-metric-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-uppercase pl-metric-label">Total Revenue</span>
                            <div class="pl-metric-icon">
                                <i class="fas fa-hand-holding-usd fa-lg"></i>
                            </div>
                        </div>
                        <h2 class="font-weight-bold pl-metric-value mb-1">kr. {{ number_format($totalRevenue, 2) }}</h2>
                        <small class="pl-metric-note">Operating Revenue & Sales Income</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card pl-metric-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-uppercase pl-metric-label">Operating Expenses & COGS</span>
                            <div class="pl-metric-icon">
                                <i class="fas fa-file-invoice-dollar fa-lg"></i>
                            </div>
                        </div>
                        <h2 class="font-weight-bold pl-metric-value mb-1">kr. {{ number_format($totalExpense, 2) }}</h2>
                        <small class="pl-metric-note">Cost of Goods Sold & Expenses</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                @php
                    $isProfitable = $netProfit >= 0;
                    $marginPercent = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;
                @endphp
                <div class="card pl-metric-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-uppercase pl-metric-label">
                                {{ $isProfitable ? 'Net Profit' : 'Net Loss' }}
                            </span>
                            <span class="badge badge-light text-dark font-weight-bold px-3 py-1 rounded-pill">
                                Margin: {{ $marginPercent }}%
                            </span>
                        </div>
                        <h2 class="font-weight-bold pl-metric-value mb-1">kr. {{ number_format(abs($netProfit), 2) }}</h2>
                        <small class="pl-metric-note">Calculated Net Operating Income</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown Tables -->
        <div class="row">
            <!-- Revenue Breakdown -->
            <div class="col-lg-6 mb-4">
                <div class="card pl-report-card h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-arrow-down pl-accent mr-2"></i> Operating Revenue Breakdown</h5>
                        <span class="badge badge-danger font-weight-bold px-3 py-1">kr. {{ number_format($totalRevenue, 2) }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-dark">
                                    <tr>
                                        <th>Account Code & Name</th>
                                        <th class="text-right">Total Amount</th>
                                        <th width="20%" class="text-right">% Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($revenueData as $row)
                                        @php
                                            $pct = $totalRevenue > 0 ? round(($row['amount'] / $totalRevenue) * 100, 1) : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge badge-light border font-weight-bold text-dark mr-2">{{ $row['code'] }}</span>
                                                <strong class="text-dark">{{ $row['name'] }}</strong>
                                            </td>
                                            <td class="text-right font-weight-bold text-danger">kr. {{ number_format($row['amount'], 2) }}</td>
                                            <td class="text-right text-muted font-weight-bold">{{ $pct }}%</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">No revenue transactions recorded for this date range.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td>TOTAL OPERATING REVENUE:</td>
                                        <td class="text-right text-danger" style="font-size: 16px;">kr. {{ number_format($totalRevenue, 2) }}</td>
                                        <td class="text-right text-danger">100%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expense Breakdown -->
            <div class="col-lg-6 mb-4">
                <div class="card pl-report-card h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-arrow-up pl-accent mr-2"></i> Operating Expenses & COGS</h5>
                        <span class="badge badge-danger font-weight-bold px-3 py-1">kr. {{ number_format($totalExpense, 2) }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-dark">
                                    <tr>
                                        <th>Account Code & Name</th>
                                        <th class="text-right">Total Amount</th>
                                        <th width="20%" class="text-right">% Expenses</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expenseData as $row)
                                        @php
                                            $pct = $totalExpense > 0 ? round(($row['amount'] / $totalExpense) * 100, 1) : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge badge-light border font-weight-bold text-dark mr-2">{{ $row['code'] }}</span>
                                                <strong class="text-dark">{{ $row['name'] }}</strong>
                                            </td>
                                            <td class="text-right font-weight-bold text-danger">kr. {{ number_format($row['amount'], 2) }}</td>
                                            <td class="text-right text-muted font-weight-bold">{{ $pct }}%</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">No expense transactions recorded for this date range.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td>TOTAL OPERATING EXPENSES & COGS:</td>
                                        <td class="text-right text-danger" style="font-size: 16px;">kr. {{ number_format($totalExpense, 2) }}</td>
                                        <td class="text-right text-danger">100%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Net Statement Summary Card -->
            <div class="col-12">
                <div class="card pl-summary-card">
                    <div class="card-body text-center">
                        <h5 class="text-uppercase font-weight-bold pl-summary-title mb-2">Summary Financial Equation</h5>
                        <h2 class="font-weight-bold {{ $isProfitable ? 'text-success' : 'text-danger' }} pl-summary-equation mb-2">
                            {{ $isProfitable ? '🟢 NET OPERATING PROFIT: kr. ' . number_format($netProfit, 2) : '🔴 NET OPERATING LOSS: kr. ' . number_format(abs($netProfit), 2) }}
                        </h2>
                        <p class="text-muted font-weight-bold mb-0">
                            Net Income = Total Revenue (kr. {{ number_format($totalRevenue, 2) }}) − Total Expenses & COGS (kr. {{ number_format($totalExpense, 2) }})
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    @include('backend.reports.financial.partials.async_pdf_js')
@endpush
