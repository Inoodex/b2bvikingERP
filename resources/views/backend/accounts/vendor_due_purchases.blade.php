@extends('backend.layouts.master')
@section('title', 'Vendor Due Purchases — Accounts Payable Aging')

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-exclamation-circle text-danger mr-2"></i> Vendor Due — Accounts Payable</h1>
            <p class="text-muted mb-0 small">Outstanding Vendor Balances & Overdue Invoice Aging Analysis</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.chart-of-accounts.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Vendor Due Purchases</div>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #dc3545 !important;">
                <div class="card-icon bg-danger text-white">
                    <i class="fas fa-file-invoice fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total Invoices Due</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">{{ number_format($summary['count']) }}</div>
                    <small class="text-danger font-weight-bold"><i class="fas fa-clock mr-1"></i> Pending Settlement</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #6777ef !important;">
                <div class="card-icon bg-primary text-white">
                    <i class="fas fa-money-bill-wave fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total Outstanding</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">{!! formatConverted($summary['total_due']) !!}</div>
                    <small class="text-primary font-weight-bold"><i class="fas fa-balance-scale mr-1"></i> Net AP Balance</small>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-12 mb-3">
            <!-- Aging Buckets -->
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-hourglass-half text-warning mr-2"></i> AP Aging Analysis</h6>
                    <div class="row text-center">
                        <div class="col-3 border-right">
                            <div class="text-success font-weight-bold h5 mb-0">{{ $aging['current'] }}</div>
                            <small class="text-muted d-block font-weight-bold">0–30 Days</small>
                            <small class="text-success font-weight-bold">kr. {{ number_format($agingAmt['current'], 0) }}</small>
                        </div>
                        <div class="col-3 border-right">
                            <div class="text-warning font-weight-bold h5 mb-0">{{ $aging['days_30'] }}</div>
                            <small class="text-muted d-block font-weight-bold">31–60 Days</small>
                            <small class="text-warning font-weight-bold">kr. {{ number_format($agingAmt['days_30'], 0) }}</small>
                        </div>
                        <div class="col-3 border-right">
                            <div class="text-danger font-weight-bold h5 mb-0">{{ $aging['days_60'] }}</div>
                            <small class="text-muted d-block font-weight-bold">61–90 Days</small>
                            <small class="text-danger font-weight-bold">kr. {{ number_format($agingAmt['days_60'], 0) }}</small>
                        </div>
                        <div class="col-3">
                            <div class="font-weight-bold h5 mb-0" style="color: #8b0000;">{{ $aging['days_90'] }}</div>
                            <small class="text-muted d-block font-weight-bold">90+ Days</small>
                            <small class="font-weight-bold" style="color: #8b0000;">kr. {{ number_format($agingAmt['days_90'], 0) }}</small>
                        </div>
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
                <form method="GET" class="mb-4 p-3 bg-light rounded border" id="vendor-due-filter-form">
                    <div class="row">
                        <div class="col-md-3 form-group mb-2">
                            <label class="small font-weight-bold text-muted text-uppercase">Vendor</label>
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
                            <label class="small font-weight-bold text-muted text-uppercase">From</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <label class="small font-weight-bold text-muted text-uppercase">To</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <label class="small font-weight-bold text-muted text-uppercase">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Invoice or vendor name...">
                        </div>
                        <div class="col-md-2 d-flex align-items-end mb-2">
                            <a href="{{ route('admin.accounts.vendor-payments.due-purchases') }}" class="btn btn-light border btn-sm font-weight-bold btn-block" style="border-radius:6px;">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </a>
                        </div>
                    </div>
                    <div class="text-right mt-1">
                        <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Filters apply automatically.</small>
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
                                        'partial' => 'badge-warning',
                                        default   => 'badge-secondary',
                                    };
                                    $rowClass = '';
                                    $agingBadge = '';
                                    if ($daysOverdue > 90)      { $rowClass = 'table-danger'; $agingBadge = '<span class="badge" style="background:#8b0000;color:#fff;font-size:10px;">90+ Days</span>'; }
                                    elseif ($daysOverdue > 60)  { $rowClass = 'table-danger'; $agingBadge = '<span class="badge badge-danger" style="font-size:10px;">61–90 Days</span>'; }
                                    elseif ($daysOverdue > 30)  { $rowClass = 'table-warning'; $agingBadge = '<span class="badge badge-warning" style="font-size:10px;">31–60 Days</span>'; }
                                    else                        { $agingBadge = '<span class="badge badge-success" style="font-size:10px;">0–30 Days</span>'; }
                                @endphp
                                <tr class="{{ $rowClass }}">
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
                                        <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-sm btn-primary" title="View Invoice">
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

                <div class="mt-3">{{ $purchases->links() }}</div>
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
