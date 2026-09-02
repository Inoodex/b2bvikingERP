@extends('backend.layouts.master')
@section('title', 'Customer Payments & Receipts — Accounts Receivable (AR)')

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-receipt text-primary mr-2"></i> Customer Payments & Receipt Vouchers</h1>
            <p class="text-muted mb-0 small">Accounts Receivable (AR) Inbound Cash & General Ledger Receipts</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.customer-payments.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Customer Payments</div>
        </div>
    </div>

    <!-- 4 Live Executive AR KPI Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #ffc107 !important;">
                <div class="card-icon bg-warning text-white">
                    <i class="fas fa-hand-holding-usd fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total AR Outstanding</h4></div>
                    <div class="card-body font-weight-bold text-dark h5 mb-0">
                        kr. {{ number_format($totalArOutstanding, 2) }}
                    </div>
                    <small class="text-warning font-weight-bold"><i class="fas fa-clock mr-1"></i> Open Receivables</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #28a745 !important;">
                <div class="card-icon bg-success text-white">
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Collected This Month</h4></div>
                    <div class="card-body font-weight-bold text-dark h5 mb-0">
                        kr. {{ number_format($collectedThisMonth, 2) }}
                    </div>
                    <small class="text-success font-weight-bold"><i class="fas fa-calendar-check mr-1"></i> {{ now()->format('M Y') }} Inflow</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #dc3545 !important;">
                <div class="card-icon bg-danger text-white">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Overdue AR (>30 Days)</h4></div>
                    <div class="card-body font-weight-bold text-dark h5 mb-0">
                        kr. {{ number_format($overdueAr30Days, 2) }}
                    </div>
                    <small class="text-danger font-weight-bold"><i class="fas fa-bolt mr-1"></i> Critical Follow-up</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #6777ef !important;">
                <div class="card-icon bg-primary text-white">
                    <i class="fas fa-file-invoice fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Payment Receipts</h4></div>
                    <div class="card-body font-weight-bold text-dark h5 mb-0">
                        {{ $totalPaymentsCount }} Vouchers
                    </div>
                    <small class="text-primary font-weight-bold"><i class="fas fa-shield-alt mr-1"></i> 100% GL Reconciled</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Container -->
    <div class="section-body">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-list-alt text-primary mr-2"></i> Payment Receipts Registry</h5>
                <div class="card-header-action">
                    <a href="{{ route('admin.customer-payments.create') }}" class="btn btn-primary font-weight-bold shadow-sm px-3 py-2" style="border-radius: 8px;">
                        <i class="fas fa-plus-circle mr-1"></i> Receive Customer Payment
                    </a>
                </div>
            </div>
            <div class="card-body p-4 table-responsive">
                {{ $dataTable->table(['class' => 'table table-striped table-bordered align-middle w-100']) }}
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush
