@extends('backend.layouts.master')
@section('title', 'Vendor Bills (AP) — 3-Way Matching Invoices')

@push('css')
<style>
    /* Executive Header */
    .vb-header {
        background: #ffffff;
        border-radius: 14px;
        padding: 16px 22px;
        margin-top: 4px;
        margin-bottom: 22px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .vb-header-icon {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border: 1px solid rgba(37, 99, 235, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
    }
    .vb-header-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }
    .vb-header-sub {
        font-size: 12px;
        color: #64748b;
        margin-top: 3px;
        line-height: 1.3;
    }

    /* Executive KPI Stat Cards */
    .vb-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .vb-stat-card:hover {
        transform: translateY(-2px);
        border-color: #cbd5e1;
    }

    .vb-icon-pill {
        width: 46px;
        height: 46px;
        min-width: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: transform 0.2s ease;
    }
    .vb-stat-card:hover .vb-icon-pill {
        transform: scale(1.06);
    }
    .vb-icon-danger {
        background: rgba(225, 24, 53, 0.1);
        color: #E11835;
        border: 1px solid rgba(225, 24, 53, 0.2);
    }
    .vb-icon-warning {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.25);
    }
    .vb-icon-critical {
        background: rgba(185, 28, 28, 0.12);
        color: #b91c1c;
        border: 1px solid rgba(185, 28, 28, 0.25);
    }
    .vb-icon-success {
        background: rgba(21, 128, 61, 0.1);
        color: #15803d;
        border: 1px solid rgba(21, 128, 61, 0.22);
    }

    .vb-stat-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 4px;
        line-height: 1.2;
    }
    .vb-stat-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.45rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }
    .vb-stat-meta {
        margin-top: 8px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }
    .vb-tag {
        font-size: 11px;
        font-weight: 600;
        border-radius: 6px;
        padding: 3px 8px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        line-height: 1.4;
    }
    .vb-tag-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .vb-tag-warning {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fef3c7;
    }
    .vb-tag-success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
</style>
@endpush

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="vb-header mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 16px;">
            <div class="d-flex align-items-center">
                <div class="vb-header-icon mr-3">
                    <i class="fas fa-file-invoice-dollar text-primary"></i>
                </div>
                <div>
                    <h4 class="vb-header-title mb-0">Vendor Bills (Accounts Payable)</h4>
                    <div class="vb-header-sub">3-Way Matching Invoices & Supplier Payment Obligations</div>
                </div>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-light px-3 py-1.5 mb-0" style="border-radius: 8px; font-size: 12px; font-weight: 600; border: 1px solid #e2e8f0;">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-muted"><i class="fas fa-home mr-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.vendor-bills.index') }}" class="text-muted">Accounts</a></li>
                    <li class="breadcrumb-item active text-primary" aria-current="page">Vendor Bills</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- 4 Live Executive AP KPI Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3 mb-lg-0">
            <div class="vb-stat-card">
                <div class="d-flex align-items-center mb-2">
                    <div class="vb-icon-pill vb-icon-danger mr-3">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <div style="min-width: 0;">
                        <div class="vb-stat-label">Total AP Payable</div>
                        <div class="vb-stat-value text-danger">
                            kr. {{ number_format($totalApPayable, 2) }}
                        </div>
                    </div>
                </div>
                <div class="vb-stat-meta">
                    <span class="vb-tag vb-tag-danger">
                        <i class="fas fa-clock"></i> Supplier Debt
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3 mb-lg-0">
            <div class="vb-stat-card">
                <div class="d-flex align-items-center mb-2">
                    <div class="vb-icon-pill vb-icon-warning mr-3">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div style="min-width: 0;">
                        <div class="vb-stat-label">Due in Next 7 Days</div>
                        <div class="vb-stat-value" style="color: #d97706;">
                            kr. {{ number_format($dueInNext7Days, 2) }}
                        </div>
                    </div>
                </div>
                <div class="vb-stat-meta">
                    <span class="vb-tag vb-tag-warning">
                        <i class="fas fa-exclamation-circle"></i> Upcoming Outflow
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3 mb-lg-0">
            <div class="vb-stat-card">
                <div class="d-flex align-items-center mb-2">
                    <div class="vb-icon-pill vb-icon-critical mr-3">
                        <i class="fas fa-hourglass-end"></i>
                    </div>
                    <div style="min-width: 0;">
                        <div class="vb-stat-label">Overdue Payables</div>
                        <div class="vb-stat-value text-dark">
                            kr. {{ number_format($criticalOverdueAp, 2) }}
                        </div>
                    </div>
                </div>
                <div class="vb-stat-meta">
                    <span class="vb-tag vb-tag-danger">
                        <i class="fas fa-times-circle"></i> Grace Expired
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3 mb-lg-0">
            <div class="vb-stat-card">
                <div class="d-flex align-items-center mb-2">
                    <div class="vb-icon-pill vb-icon-success mr-3">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div style="min-width: 0;">
                        <div class="vb-stat-label">Registered Bills</div>
                        <div class="vb-stat-value text-success">
                            {{ $totalBillsCount }} Invoices
                        </div>
                    </div>
                </div>
                <div class="vb-stat-meta">
                    <span class="vb-tag vb-tag-success">
                        <i class="fas fa-shield-alt"></i> 3-Way Verified
                    </span>
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
            <div class="card-body p-2 p-md-3">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table table-striped table-bordered align-middle w-100']) }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush
