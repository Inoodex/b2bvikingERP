@extends('backend.layouts.master')

@section('title', 'Total Purchase Value (Periodic)')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-calculator text-primary mr-2"></i> Total Purchase Value — Periodic</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Reports</div>
            <div class="breadcrumb-item">Total Purchase Value</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header">
                <h4>Periodic Range Selection</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.purchase-reports.total-value') }}" method="GET" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label>Start Date:</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label>End Date:</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary mr-1"><i class="fas fa-chart-line"></i> Calculate Total Value</button>
                            <a href="{{ route('admin.purchase-reports.total-value') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr class="bg-light">
                                <th>Period / Month</th>
                                <th class="text-center">Total PO Count</th>
                                <th class="text-right">Total Subtotal</th>
                                <th class="text-right">Total Discount</th>
                                <th class="text-right">Total Tax / VAT</th>
                                <th class="text-right font-weight-bold">Net Total Spend</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportData as $row)
                                <tr>
                                    <td><strong>{{ $row->period }}</strong></td>
                                    <td class="text-center">{{ $row->po_count }}</td>
                                    <td class="text-right">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($row->subtotal, 2) }}</td>
                                    <td class="text-right text-success">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($row->discount, 2) }}</td>
                                    <td class="text-right">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($row->tax, 2) }}</td>
                                    <td class="text-right font-weight-bold text-primary">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($row->net_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No data found for the selected period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Issued POs</h4>
                                </div>
                                <div class="card-body">
                                    {{ $summary['total_pos'] }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-money-bill-alt"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Purchase Value</h4>
                                </div>
                                <div class="card-body">
                                    {{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($summary['total_purchase_value'], 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Amount Paid</h4>
                                </div>
                                <div class="card-body">
                                    ${{ number_format($summary['total_paid_value'], 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Outstanding Due</h4>
                                </div>
                                <div class="card-body">
                                    ${{ number_format($summary['total_due_value'], 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
