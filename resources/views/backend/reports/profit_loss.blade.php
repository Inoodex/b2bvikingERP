@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Profit & Loss Report</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Profit & Loss</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Financial Performance</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.reports.profit-loss') }}" method="GET" class="mb-4">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Start Date</label>
                                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label>End Date</label>
                                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label>&nbsp;</label>
                                        <div class="d-flex">
                                            <button type="submit" class="btn btn-primary flex-grow-1 mr-2">
                                                <i class="fas fa-filter"></i> Filter
                                            </button>
                                            <a href="{{ route('admin.reports.profit-loss') }}" class="btn btn-secondary">
                                                <i class="fas fa-redo"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="row">
                                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                                    <div class="card card-statistic-1">
                                        <div class="card-icon bg-success">
                                            <i class="fas fa-dollar-sign"></i>
                                        </div>
                                        <div class="card-wrap">
                                            <div class="card-header">
                                                <h4>Base Total Revenue</h4>
                                            </div>
                                            <div class="card-body">
                                                {!! formatConverted($totalRevenue) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                                    <div class="card card-statistic-1">
                                        <div class="card-icon bg-danger">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <div class="card-wrap">
                                            <div class="card-header">
                                                <h4>Total Cost</h4>
                                            </div>
                                            <div class="card-body">
                                                {!! formatConverted($totalCost) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                                    <div class="card card-statistic-1">
                                        <div class="card-icon {{ $grossProfit >= 0 ? 'bg-primary' : 'bg-warning' }}">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <div class="card-wrap">
                                            <div class="card-header">
                                                <h4>Gross Profit</h4>
                                            </div>
                                            <div class="card-body">
                                                {!! formatConverted($grossProfit) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4>Profit & Loss Statement</h4>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <td class="font-weight-bold">Revenue</td>
                                                        <td class="text-right text-black"><strong>{!! formatConverted($totalRevenue) !!}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold">Cost of Goods Sold (Purchases)</td>
                                                        <td class="text-right text-danger"><strong>-{!! formatConverted($totalCost) !!}</strong></td>
                                                    </tr>
                                                    <tr class="table-active">
                                                        <td class="font-weight-bold">Gross Profit</td>
                                                        <td class="text-right font-weight-bold {{ $grossProfit >= 0 ? 'text-black' : 'text-danger' }}">
                                                            <strong> {!! formatConverted($grossProfit) !!}</strong>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold">Profit Margin</td>
                                                        <td class="text-right font-weight-bold text-black "><strong>{{ number_format($profitMargin, 2) }}%</strong></td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            @if($grossProfit < 0)
                                                <div class="alert alert-danger mt-3">
                                                    <i class="fas fa-exclamation-triangle"></i> 
                                                    <strong>Warning:</strong> Your business is currently operating at a loss. Review your pricing and costs.
                                                </div>
                                            @elseif($profitMargin < 10)
                                                <div class="alert alert-warning mt-3">
                                                    <i class="fas fa-info-circle"></i> 
                                                    <strong>Low Margin:</strong> Your profit margin is below 10%. Consider optimizing costs or increasing prices.
                                                </div>
                                            @else
                                                <div class="alert alert-primary mt-3" style="background: linear-gradient(135deg, #4e73df, #224abe); color: #fff; border: none; border-radius: 12px; padding: 18px 24px; box-shadow: 0 4px 20px rgba(78, 115, 223, 0.35);">
                                                    <div style="display: flex; align-items: center; gap: 14px;">
                                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                            <i class="fas fa-check-circle" style="font-size: 20px; color: #fff;"></i>
                                                        </div>
                                                        <div>
                                                            <strong style="font-size: 16px; display: block; color: #fff;">Healthy Profit</strong>
                                                            <span style="font-size: 14px; opacity: 0.9; color: #fff;">Your business is performing well!</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
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

@push('styles')
<style>
    /* Hover effect for the blue alert */
    .alert-primary.mt-3 {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .alert-primary.mt-3:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(78, 115, 223, 0.5) !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 576px) {
        .alert-primary.mt-3 {
            padding: 14px 16px !important;
        }
        .alert-primary.mt-3 div {
            flex-wrap: wrap;
            gap: 10px !important;
        }
        .alert-primary.mt-3 strong {
            font-size: 14px !important;
        }
        .alert-primary.mt-3 span {
            font-size: 12px !important;
        }
    }
</style>
@endpush