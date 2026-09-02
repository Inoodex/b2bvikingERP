@extends('backend.layouts.master')
@section('title', 'AP Aging Analysis — Accounts Payable Overdue')

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-clock text-danger mr-2"></i> Accounts Payable (AP) Aging Analysis</h1>
            <p class="text-muted mb-0 small">GAAP / IFRS Due-Date Overdue Classification & Liquidity Risk Analysis</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.vendor-ledger.index') }}">Supplier Ledger</a></div>
            <div class="breadcrumb-item active">AP Aging</div>
        </div>
    </div>

    @php
        $totalDue = $agingData->sum('total_due') ?: 1;
        $currSum = $agingData->sum('current');
        $d30Sum = $agingData->sum('days_31_60');
        $d60Sum = $agingData->sum('days_61_90');
        $d90Sum = $agingData->sum('days_90_plus');

        $pCurr = round(($currSum / $totalDue) * 100);
        $p30 = round(($d30Sum / $totalDue) * 100);
        $p60 = round(($d60Sum / $totalDue) * 100);
        $p90 = round(($d90Sum / $totalDue) * 100);
    @endphp

    <!-- Visual Aging Risk Proportions Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-chart-pie text-primary mr-2"></i> Overdue Distribution Profile (Total Debt: kr. {{ number_format($agingData->sum('total_due'), 2) }})</h6>
                    <div class="progress" style="height: 18px; border-radius: 9px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pCurr }}%;" title="Current (0-30d): {{ $pCurr }}%">Current: {{ $pCurr }}%</div>
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $p30 }}%;" title="31-60 Days: {{ $p30 }}%">31-60d: {{ $p30 }}%</div>
                        <div class="progress-bar bg-warning text-dark" role="progressbar" style="width: {{ $p60 }}%;" title="61-90 Days: {{ $p60 }}%">61-90d: {{ $p60 }}%</div>
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $p90 }}%;" title="90+ Days: {{ $p90 }}%">90+d: {{ $p90 }}%</div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 small font-weight-bold">
                        <span class="text-success"><i class="fas fa-circle mr-1"></i> Current (0-30 Days): kr. {{ number_format($currSum, 2) }}</span>
                        <span class="text-info"><i class="fas fa-circle mr-1"></i> 31-60 Days: kr. {{ number_format($d30Sum, 2) }}</span>
                        <span class="text-warning"><i class="fas fa-circle mr-1"></i> 61-90 Days: kr. {{ number_format($d60Sum, 2) }}</span>
                        <span class="text-danger"><i class="fas fa-circle mr-1"></i> 90+ Days Overdue: kr. {{ number_format($d90Sum, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Master AP Aging Table -->
    <div class="section-body">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-list-alt text-primary mr-2"></i> Supplier Payables Aging Matrix</h5>
            </div>
            <div class="card-body p-4 table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th style="width: 12%;">Supplier Code</th>
                            <th style="width: 25%;">Supplier / Vendor Name</th>
                            <th class="text-right text-success" style="width: 15%;">Current (0-30d)</th>
                            <th class="text-right text-info" style="width: 12%;">31 - 60 Days</th>
                            <th class="text-right text-warning" style="width: 12%;">61 - 90 Days</th>
                            <th class="text-right text-danger" style="width: 12%;">90+ Days Overdue</th>
                            <th class="text-right" style="width: 12%;">Total Payable</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agingData as $row)
                            <tr>
                                <td><span class="badge badge-dark font-monospace">{{ $row['vendor_code'] }}</span></td>
                                <td>
                                    @if(!empty($row['vendor_id']))
                                        <a href="{{ route('admin.vendor-ledger.show', $row['vendor_id']) }}" class="font-weight-bold text-primary">
                                            {{ $row['vendor_name'] }} <i class="fas fa-external-link-alt ml-1 small"></i>
                                        </a>
                                    @else
                                        <strong>{{ $row['vendor_name'] }}</strong>
                                    @endif
                                    <br><small class="text-muted">{{ $row['phone'] ?? 'N/A' }}</small>
                                </td>
                                <td class="text-right font-weight-bold text-success">kr. {{ number_format($row['current'], 2) }}</td>
                                <td class="text-right font-weight-bold text-info">kr. {{ number_format($row['days_31_60'], 2) }}</td>
                                <td class="text-right font-weight-bold text-warning">kr. {{ number_format($row['days_61_90'], 2) }}</td>
                                <td class="text-right font-weight-bold text-danger">kr. {{ number_format($row['days_90_plus'], 2) }}</td>
                                <td class="text-right font-weight-bold text-dark h6 mb-0">kr. {{ number_format($row['total_due'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-success py-4">
                                    <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                                    All supplier bills are fully settled! Zero outstanding liabilities.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($agingData->count() > 0)
                    <tfoot>
                        <tr class="table-active font-weight-bold">
                            <td colspan="2" class="text-right">TOTAL AP OBLIGATIONS:</td>
                            <td class="text-right text-success">kr. {{ number_format($agingData->sum('current'), 2) }}</td>
                            <td class="text-right text-info">kr. {{ number_format($agingData->sum('days_31_60'), 2) }}</td>
                            <td class="text-right text-warning">kr. {{ number_format($agingData->sum('days_61_90'), 2) }}</td>
                            <td class="text-right text-danger">kr. {{ number_format($agingData->sum('days_90_plus'), 2) }}</td>
                            <td class="text-right text-primary h5 mb-0">kr. {{ number_format($agingData->sum('total_due'), 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
