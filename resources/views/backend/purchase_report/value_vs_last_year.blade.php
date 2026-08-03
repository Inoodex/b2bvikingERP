@extends('backend.layouts.master')

@section('title', 'Purchase Value vs Last Year')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-balance-scale text-primary mr-2"></i> Purchase Value vs Last Year Comparison (Client Req 2.27)</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Reports</div>
            <div class="breadcrumb-item">Vs Last Year</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header">
                <h4>Select Comparison Year</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.purchase-reports.vs-last-year') }}" method="GET" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label>Target Year:</label>
                            <select name="year" class="form-control">
                                @for($y = date('Y'); $y >= 2024; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-sync"></i> Compare Growth</button>
                        </div>
                    </div>
                </form>

                <div class="row">
                    <div class="col-md-4">
                        <div class="p-4 bg-light rounded text-center border">
                            <h6 class="text-muted font-weight-bold">YEAR {{ $comparison['current_year'] }} PURCHASE VALUE</h6>
                            <h2 class="text-primary mt-2">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($comparison['current_year_value'], 2) }}</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4 bg-light rounded text-center border">
                            <h6 class="text-muted font-weight-bold">YEAR {{ $comparison['last_year'] }} PURCHASE VALUE</h6>
                            <h2 class="text-secondary mt-2">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($comparison['last_year_value'], 2) }}</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4 bg-light rounded text-center border border-{{ $comparison['growth_percentage'] >= 0 ? 'success' : 'danger' }}">
                            <h6 class="text-muted font-weight-bold">YEAR-OVER-YEAR (YoY) GROWTH</h6>
                            <h2 class="text-{{ $comparison['growth_percentage'] >= 0 ? 'success' : 'danger' }} mt-2">
                                {{ $comparison['growth_percentage'] >= 0 ? '+' : '' }}{{ number_format($comparison['growth_percentage'], 2) }}%
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
