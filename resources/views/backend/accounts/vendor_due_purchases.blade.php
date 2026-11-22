@extends('backend.layouts.master')
@section('title', 'Vendor Due Purchases — Accounts Payable Aging')

@push('css')
<style>
    /* Executive Header */
    .ap-header {
        background: #ffffff;
        border-radius: 14px;
        padding: 16px 22px;
        margin-top: 4px;
        margin-bottom: 22px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .ap-header-icon {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border: 1px solid rgba(225, 24, 53, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: 0 4px 12px rgba(225, 24, 53, 0.15);
    }
    .ap-header-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }
    .ap-header-sub {
        font-size: 12px;
        color: #64748b;
        margin-top: 3px;
        line-height: 1.3;
    }

    /* Executive KPI Stat Cards */
    .ap-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .ap-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .ap-icon-pill {
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
    .ap-stat-card:hover .ap-icon-pill {
        transform: scale(1.06);
    }
    .ap-icon-danger {
        background: rgba(225, 24, 53, 0.1);
        color: #E11835;
        border: 1px solid rgba(225, 24, 53, 0.2);
    }
    .ap-icon-primary {
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        border: 1px solid rgba(37, 99, 235, 0.2);
    }

    .ap-stat-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 4px;
        line-height: 1.2;
    }
    .ap-stat-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.55rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }
    .ap-stat-meta {
        margin-top: 8px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }
    .ap-tag {
        font-size: 11px;
        font-weight: 600;
        border-radius: 6px;
        padding: 3px 8px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        line-height: 1.4;
    }
    .ap-tag-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .ap-tag-primary {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }

    /* AP Aging Buckets Box */
    .ap-aging-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 18px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .ap-bucket-tile {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 9px 6px;
        text-align: center;
        transition: all 0.2s ease;
    }
    .ap-bucket-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }
    .ap-bucket-current {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }
    .ap-bucket-30 {
        background: #fffbeb;
        border-color: #fef3c7;
    }
    .ap-bucket-60 {
        background: #fff1f2;
        border-color: #ffe4e6;
    }
    .ap-bucket-90 {
        background: #fef2f2;
        border-color: #fee2e2;
    }
</style>
@endpush

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="ap-header mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 16px;">
            <div class="d-flex align-items-center">
                <div class="ap-header-icon mr-3">
                    <i class="fas fa-file-invoice-dollar text-danger"></i>
                </div>
                <div>
                    <h4 class="ap-header-title mb-0">Vendor Due — Accounts Payable</h4>
                    <div class="ap-header-sub">Outstanding vendor balances, payment tracking & AP overdue aging analysis</div>
                </div>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-light px-3 py-1.5 mb-0" style="border-radius: 8px; font-size: 12px; font-weight: 600; border: 1px solid #e2e8f0;">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-muted"><i class="fas fa-home mr-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.chart-of-accounts.index') }}" class="text-muted">Accounts</a></li>
                    <li class="breadcrumb-item active text-primary" aria-current="page">Vendor Due Purchases</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
            <div class="ap-stat-card">
                <div class="d-flex align-items-center mb-2">
                    <div class="ap-icon-pill ap-icon-danger mr-3">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div style="min-width: 0;">
                        <div class="ap-stat-label">Total Invoices Due</div>
                        <div class="ap-stat-value text-danger">
                            {{ number_format($summary['count']) }}
                        </div>
                    </div>
                </div>
                <div class="ap-stat-meta">
                    <span class="ap-tag ap-tag-danger">
                        <i class="fas fa-clock"></i> Pending Settlement
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
            <div class="ap-stat-card">
                <div class="d-flex align-items-center mb-2">
                    <div class="ap-icon-pill ap-icon-primary mr-3">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div style="min-width: 0;">
                        <div class="ap-stat-label">Total Outstanding</div>
                        <div class="ap-stat-value text-primary">
                            {!! formatConverted($summary['total_due']) !!}
                        </div>
                    </div>
                </div>
                <div class="ap-stat-meta">
                    <span class="ap-tag ap-tag-primary">
                        <i class="fas fa-balance-scale"></i> Net AP Balance
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-12">
            <!-- Aging Buckets -->
            <div class="ap-aging-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center">
                        <div class="mr-2" style="width: 28px; height: 28px; border-radius: 8px; background: rgba(245, 158, 11, 0.12); color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #475569;">AP Aging Analysis</span>
                    </div>
                    <small class="text-muted" style="font-size: 11px;"><i class="fas fa-info-circle mr-1"></i> Outstanding Invoices Breakdown</small>
                </div>
                <div class="row no-gutters text-center" style="gap: 8px; flex-wrap: nowrap;">
                    <div class="col ap-bucket-tile ap-bucket-current">
                        <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.35rem; font-weight: 800; color: #16a34a; line-height: 1.1;">{{ $aging['current'] }}</div>
                        <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #15803d; letter-spacing: 0.3px; margin: 2px 0;">0–30 Days</div>
                        <small style="font-size: 11px; font-weight: 600; color: #166534; white-space: nowrap;">kr. {{ number_format($agingAmt['current'], 0) }}</small>
                    </div>
                    <div class="col ap-bucket-tile ap-bucket-30">
                        <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.35rem; font-weight: 800; color: #d97706; line-height: 1.1;">{{ $aging['days_30'] }}</div>
                        <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #b45309; letter-spacing: 0.3px; margin: 2px 0;">31–60 Days</div>
                        <small style="font-size: 11px; font-weight: 600; color: #92400e; white-space: nowrap;">kr. {{ number_format($agingAmt['days_30'], 0) }}</small>
                    </div>
                    <div class="col ap-bucket-tile ap-bucket-60">
                        <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.35rem; font-weight: 800; color: #e11d48; line-height: 1.1;">{{ $aging['days_60'] }}</div>
                        <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #be123c; letter-spacing: 0.3px; margin: 2px 0;">61–90 Days</div>
                        <small style="font-size: 11px; font-weight: 600; color: #9f1239; white-space: nowrap;">kr. {{ number_format($agingAmt['days_60'], 0) }}</small>
                    </div>
                    <div class="col ap-bucket-tile ap-bucket-90">
                        <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.35rem; font-weight: 800; color: #991b1b; line-height: 1.1;">{{ $aging['days_90'] }}</div>
                        <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #991b1b; letter-spacing: 0.3px; margin: 2px 0;">90+ Days</div>
                        <small style="font-size: 11px; font-weight: 600; color: #7f1d1d; white-space: nowrap;">kr. {{ number_format($agingAmt['days_90'], 0) }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="section-body">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-list text-danger mr-2"></i> Outstanding Vendor Balances</h5>
            </div>
            <div class="card-body p-4">
                <!-- Filter Bar -->
                <form method="GET" class="mb-4 p-3 filter-panel" id="vendor-due-filter-form">
                    <div class="row align-items-end">
                        <div class="col-md-3 form-group mb-2">
                            <label class="small font-weight-bold text-secondary text-uppercase d-flex align-items-center mb-1" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="fas fa-store mr-1 text-primary"></i> Vendor
                            </label>
                            <select name="vendor_id" class="form-control form-control-sm">
                                <option value="">All Vendors</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ (string) request('vendor_id') === (string) $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->shop_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <label class="small font-weight-bold text-secondary text-uppercase d-flex align-items-center mb-1" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="far fa-calendar-alt mr-1 text-primary"></i> From
                            </label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <label class="small font-weight-bold text-secondary text-uppercase d-flex align-items-center mb-1" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="far fa-calendar-alt mr-1 text-primary"></i> To
                            </label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <label class="small font-weight-bold text-secondary text-uppercase d-flex align-items-center mb-1" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="fas fa-search mr-1 text-primary"></i> Search
                            </label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Invoice or vendor name..." style="height: 36px; border-radius: 8px; border: 1px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <a href="{{ route('admin.accounts.vendor-payments.due-purchases') }}" class="btn btn-filter-reset btn-sm btn-block d-flex align-items-center justify-content-center" style="height: 36px; gap: 8px;" title="Reset all filters" aria-label="Reset all filters">
                                <span class="filter-reset-icon"><i class="fas fa-undo"></i></span>
                                <span>Reset</span>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2" style="border-top: 1px dashed #e2e8f0;">
                        <small class="text-muted"><i class="fas fa-clock text-danger mr-1"></i> Overdue & pending invoice balances</small>
                        <small class="text-muted"><i class="fas fa-sync-alt text-primary mr-1"></i> Filters apply automatically</small>
                    </div>
                </form>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0">
                        <thead class="bg-light text-dark">
                            <tr>
                                <th>Invoice Date</th>
                                <th>Invoice No</th>
                                <th>Vendor</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Paid</th>
                                <th class="text-right">Due Balance</th>
                                <th class="text-center">Overdue</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchases as $purchase)
                                @php
                                    $daysOverdue = (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($purchase->date), false) * -1;
                                    $paymentClass = match($purchase->payment_status) {
                                        'paid'    => 'badge-success',
                                        'partial' => 'badge-success',
                                        default   => 'badge-secondary',
                                    };
                                    $agingBadge = '';
                                    if ($daysOverdue > 90)      { $agingBadge = '<span class="badge badge-danger" style="font-size:10px;">90+ Days</span>'; }
                                    elseif ($daysOverdue > 60)  { $agingBadge = '<span class="badge badge-danger" style="font-size:10px;">61–90 Days</span>'; }
                                    elseif ($daysOverdue > 30)  { $agingBadge = '<span class="badge badge-warning" style="font-size:10px;">31–60 Days</span>'; }
                                    else                        { $agingBadge = '<span class="badge badge-success" style="font-size:10px;">0–30 Days</span>'; }
                                @endphp
                                <tr>
                                    <td><small>{{ \Carbon\Carbon::parse($purchase->date)->format('d M, Y') }}</small></td>
                                    <td><span class="font-weight-bold font-monospace small">{{ $purchase->invoice_no }}</span></td>
                                    <td>
                                        <strong>{{ $purchase->vendor->shop_name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $purchase->vendor->phone ?? '' }}</small>
                                    </td>
                                    <td class="text-right">{!! formatConverted($purchase->total_amount) !!}</td>
                                    <td class="text-right text-success font-weight-bold">{!! formatConverted($purchase->paid_amount) !!}</td>
                                    <td class="text-right text-danger font-weight-bold">{!! formatConverted($purchase->due_amount) !!}</td>
                                    <td class="text-center">{!! $agingBadge !!}</td>
                                    <td class="text-center"><span class="badge {{ $paymentClass }}">{{ ucfirst($purchase->payment_status ?? 'pending') }}</span></td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ route('admin.purchase-orders.show', $purchase->id) }}" class="btn btn-sm btn-primary" title="View Purchase Order">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.accounts.vendor-payments.record-payment', ['invoice_no' => $purchase->invoice_no]) }}"
                                           class="btn btn-sm btn-dark ml-1" title="Pay Now">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>
                                        All vendor invoices are fully settled. No outstanding balances.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap mt-3" style="gap: 12px;">
                    <div class="text-muted small">
                        Showing {{ $purchases->firstItem() ?? 0 }} to {{ $purchases->lastItem() ?? 0 }} of {{ number_format($purchases->total()) }} entries
                    </div>
                    <div>
                        {{ $purchases->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const $form = $('#vendor-due-filter-form');
        const $search = $form.find('input[name="search"]');
        let searchTimer = null;

        $form.find('select, input[type="date"]').on('change', function() { $form.trigger('submit'); });

        $search.on('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() { $form.trigger('submit'); }, 450);
        });

        $search.on('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); clearTimeout(searchTimer); $form.trigger('submit'); }
        });
    });
</script>
@endpush
