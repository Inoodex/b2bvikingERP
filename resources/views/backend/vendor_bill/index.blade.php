@extends('backend.layouts.master')
@section('title', 'Vendor Bills (AP) — 3-Way Matching Invoices')

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-file-invoice-dollar text-primary mr-2"></i> Vendor Bills (Accounts Payable)</h1>
            <p class="text-muted mb-0 small">SAP / Odoo 3-Way Matching Invoices & Supplier Payment Obligations</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.vendor-bills.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Vendor Bills</div>
        </div>
    </div>

    <!-- 4 Live Executive AP KPI Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #dc3545 !important;">
                <div class="card-icon bg-danger text-white">
                    <i class="fas fa-money-check-alt fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total AP Payable</h4></div>
                    <div class="card-body font-weight-bold text-dark h5 mb-0">
                        kr. {{ number_format($totalApPayable, 2) }}
                    </div>
                    <small class="text-danger font-weight-bold"><i class="fas fa-clock mr-1"></i> Supplier Debt</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #ffc107 !important;">
                <div class="card-icon bg-warning text-white">
                    <i class="fas fa-calendar-alt fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Due in Next 7 Days</h4></div>
                    <div class="card-body font-weight-bold text-dark h5 mb-0">
                        kr. {{ number_format($dueInNext7Days, 2) }}
                    </div>
                    <small class="text-warning font-weight-bold"><i class="fas fa-exclamation-circle mr-1"></i> Upcoming Outflow</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #6c757d !important;">
                <div class="card-icon bg-secondary text-white">
                    <i class="fas fa-hourglass-end fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Overdue Payables</h4></div>
                    <div class="card-body font-weight-bold text-dark h5 mb-0">
                        kr. {{ number_format($criticalOverdueAp, 2) }}
                    </div>
                    <small class="text-muted font-weight-bold"><i class="fas fa-times-circle mr-1"></i> Grace Expired</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #28a745 !important;">
                <div class="card-icon bg-success text-white">
                    <i class="fas fa-check-double fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Registered Bills</h4></div>
                    <div class="card-body font-weight-bold text-dark h5 mb-0">
                        {{ $totalBillsCount }} Invoices
                    </div>
                    <small class="text-success font-weight-bold"><i class="fas fa-shield-alt mr-1"></i> 3-Way Verified</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Container -->
    <div class="section-body">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-list-alt text-primary mr-2"></i> Supplier Invoices Registry</h5>
                <div class="card-header-action">
                    <a href="{{ route('admin.vendor-bills.create') }}" class="btn btn-primary font-weight-bold shadow-sm px-3 py-2" style="border-radius: 8px;">
                        <i class="fas fa-plus-circle mr-1"></i> Create Vendor Bill
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
