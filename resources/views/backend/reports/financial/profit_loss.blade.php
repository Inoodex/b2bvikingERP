@extends('backend.layouts.master')
@section('title', 'Profit & Loss Statement (Income Statement)')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-chart-line text-primary mr-2"></i> Profit & Loss Statement</h1>
            <p class="text-muted mb-0 small">IFRS & GAAP Compliant Income Statement (Revenue vs Operating Expenses & COGS)</p>
        </div>
        <div class="section-header-breadcrumb">
            <button onclick="window.print()" class="btn btn-outline-secondary font-weight-bold mr-2"><i class="fas fa-print mr-1"></i> Print</button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-light border font-weight-bold"><i class="fas fa-arrow-left mr-1"></i> Dashboard</a>
        </div>
    </div>

    <div class="section-body">
        <!-- Date Filter Card -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: #ffffff;">
            <div class="card-body p-4">
                <form action="{{ route('admin.reports.profit-loss') }}" method="GET" class="row align-items-end">
                    <div class="col-md-4 form-group mb-md-0">
                        <label class="font-weight-bold text-dark mb-1"><i class="fas fa-calendar-alt text-primary mr-1"></i> Start Date</label>
                        <input type="date" name="date_from" class="form-control form-control-lg border" value="{{ request()->filled('date_from') ? request('date_from') : '' }}" style="border-radius: 8px;">
                    </div>
                    <div class="col-md-4 form-group mb-md-0">
                        <label class="font-weight-bold text-dark mb-1"><i class="fas fa-calendar-check text-primary mr-1"></i> End Date</label>
                        <input type="date" name="date_to" class="form-control form-control-lg border" value="{{ request()->filled('date_to') ? request('date_to') : '' }}" style="border-radius: 8px;">
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary btn-lg font-weight-bold flex-grow-1 mr-2 shadow-sm" style="border-radius: 8px; height: 48px;">
                                <i class="fas fa-filter mr-2"></i> Filter
                            </button>
                            <a href="{{ route('admin.reports.profit-loss') }}" class="btn btn-secondary btn-lg font-weight-bold" style="border-radius: 8px; height: 48px;">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Quick Date Presets -->
                <div class="mt-3 pt-3 border-top d-flex align-items-center">
                    <span class="text-muted font-weight-bold small mr-3">Quick Range:</span>
                    <a href="{{ route('admin.reports.profit-loss', ['date_from' => date('Y-m-01'), 'date_to' => date('Y-m-d')]) }}" class="btn btn-sm btn-outline-primary rounded-pill mr-2 font-weight-bold">This Month</a>
                    <a href="{{ route('admin.reports.profit-loss', ['date_from' => date('Y-01-01'), 'date_to' => date('Y-12-31')]) }}" class="btn btn-sm btn-outline-primary rounded-pill mr-2 font-weight-bold">YTD 2026</a>
                    <a href="{{ route('admin.reports.profit-loss') }}" class="btn btn-sm btn-outline-primary rounded-pill font-weight-bold">Full History (All Time)</a>
                </div>
            </div>
        </div>

        <!-- Metric Overview Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm text-white" style="border-radius: 14px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-uppercase small" style="letter-spacing: 1px; opacity: 0.9;">Total Revenue</span>
                            <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                <i class="fas fa-hand-holding-usd fa-lg"></i>
                            </div>
                        </div>
                        <h2 class="font-weight-bold mb-1" style="font-size: 28px;">kr. {{ number_format($totalRevenue, 2) }}</h2>
                        <small class="opacity-75">Operating Revenue & Sales Income</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm text-white" style="border-radius: 14px; background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-uppercase small" style="letter-spacing: 1px; opacity: 0.9;">Operating Expenses & COGS</span>
                            <div class="bg-white text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                <i class="fas fa-file-invoice-dollar fa-lg"></i>
                            </div>
                        </div>
                        <h2 class="font-weight-bold mb-1" style="font-size: 28px;">kr. {{ number_format($totalExpense, 2) }}</h2>
                        <small class="opacity-75">Cost of Goods Sold & Expenses</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                @php
                    $isProfitable = $netProfit >= 0;
                    $gradient = $isProfitable ? 'linear-gradient(135deg, #0575E6 0%, #00F260 100%)' : 'linear-gradient(135deg, #cb2d3e 0%, #ef473a 100%)';
                    $marginPercent = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;
                @endphp
                <div class="card border-0 shadow-sm text-white" style="border-radius: 14px; background: {{ $gradient }};">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-uppercase small" style="letter-spacing: 1px; opacity: 0.9;">
                                {{ $isProfitable ? 'Net Profit' : 'Net Loss' }}
                            </span>
                            <span class="badge badge-light text-dark font-weight-bold px-3 py-1" style="border-radius: 20px;">
                                Margin: {{ $marginPercent }}%
                            </span>
                        </div>
                        <h2 class="font-weight-bold mb-1" style="font-size: 28px;">kr. {{ number_format(abs($netProfit), 2) }}</h2>
                        <small class="opacity-75">Calculated Net Operating Income</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown Tables -->
        <div class="row">
            <!-- Revenue Breakdown -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-arrow-down text-success mr-2"></i> Operating Revenue Breakdown</h5>
                        <span class="badge badge-success font-weight-bold px-3 py-1">kr. {{ number_format($totalRevenue, 2) }}</span>
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
                                            <td class="text-right font-weight-bold text-success">kr. {{ number_format($row['amount'], 2) }}</td>
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
                                        <td class="text-right text-success" style="font-size: 16px;">kr. {{ number_format($totalRevenue, 2) }}</td>
                                        <td class="text-right text-success">100%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expense Breakdown -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-arrow-up text-danger mr-2"></i> Operating Expenses & COGS</h5>
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
                <div class="card border-0 shadow-sm" style="border-radius: 14px; background: #ffffff;">
                    <div class="card-body p-4 text-center">
                        <h5 class="text-uppercase font-weight-bold text-muted mb-2" style="letter-spacing: 1px;">Summary Financial Equation</h5>
                        <h2 class="font-weight-bold {{ $isProfitable ? 'text-success' : 'text-danger' }} mb-2" style="font-size: 36px;">
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
