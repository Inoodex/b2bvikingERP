@extends('backend.layouts.master')

@section('title', 'AP Aging Analysis Report')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.vendor-ledger.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Accounts Payable (AP) Aging Analysis Report</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.vendor-ledger.index') }}">Supplier Ledger</a></div>
            <div class="breadcrumb-item">AP Aging Report</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4>Supplier Payables Overdue Aging Buckets</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr class="bg-light">
                                        <th>Supplier Code</th>
                                        <th>Supplier Name</th>
                                        <th>Phone</th>
                                        <th class="text-right text-success">Current (0-30 Days)</th>
                                        <th class="text-right text-info">31 - 60 Days</th>
                                        <th class="text-right text-warning">61 - 90 Days</th>
                                        <th class="text-right text-danger">90+ Days Overdue</th>
                                        <th class="text-right font-weight-bold">Total Overdue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($agingData as $row)
                                        <tr>
                                            <td><code>{{ $row['vendor_code'] }}</code></td>
                                            <td><strong>{{ $row['vendor_name'] }}</strong></td>
                                            <td>{{ $row['phone'] }}</td>
                                            <td class="text-right">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($row['current'], 2) }}</td>
                                            <td class="text-right">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($row['days_31_60'], 2) }}</td>
                                            <td class="text-right">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($row['days_61_90'], 2) }}</td>
                                            <td class="text-right text-danger font-weight-bold">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($row['days_90_plus'], 2) }}</td>
                                            <td class="text-right font-weight-bold h6 mb-0">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($row['total_due'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">All supplier bills are fully settled! No outstanding payables found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($agingData->count() > 0)
                                <tfoot>
                                    <tr class="table-active font-weight-bold">
                                        <td colspan="3" class="text-right">TOTAL PAYABLES:</td>
                                        <td class="text-right text-success">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($agingData->sum('current'), 2) }}</td>
                                        <td class="text-right text-info">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($agingData->sum('days_31_60'), 2) }}</td>
                                        <td class="text-right text-warning">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($agingData->sum('days_61_90'), 2) }}</td>
                                        <td class="text-right text-danger">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($agingData->sum('days_90_plus'), 2) }}</td>
                                        <td class="text-right text-primary h5">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($agingData->sum('total_due'), 2) }}</td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
