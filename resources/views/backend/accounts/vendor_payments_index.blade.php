@extends('backend.layouts.master')
@section('title', 'Vendor Payment Ledger — Accounts Payable')

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-money-check-alt text-primary mr-2"></i> Vendor Payment Ledger</h1>
            <p class="text-muted mb-0 small">Accounts Payable — All Vendor Payment Vouchers & Receipt Records</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.chart-of-accounts.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Vendor Payments</div>
        </div>
    </div>

    <!-- 3 KPI Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #6777ef !important;">
                <div class="card-icon bg-primary text-white">
                    <i class="fas fa-receipt fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total Payments</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">{{ number_format($summary['count']) }}</div>
                    <small class="text-primary font-weight-bold"><i class="fas fa-file-invoice mr-1"></i> Vouchers Issued</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #28a745 !important;">
                <div class="card-icon bg-success text-white">
                    <i class="fas fa-hand-holding-usd fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total Amount Paid</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">{!! formatConverted($summary['total_amount']) !!}</div>
                    <small class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Settled to Vendors</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #17a2b8 !important;">
                <div class="card-icon bg-info text-white">
                    <i class="fas fa-calculator fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Avg per Payment</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">
                        {!! formatConverted($summary['count'] > 0 ? round($summary['total_amount'] / $summary['count'], 2) : 0) !!}
                    </div>
                    <small class="text-info font-weight-bold"><i class="fas fa-chart-bar mr-1"></i> Average Voucher Value</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="section-body">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-list text-primary mr-2"></i> Vendor Payment History</h5>
                <div class="mt-2 mt-md-0">
                    <a href="{{ route('admin.accounts.vendor-payments.pdf', request()->except('page')) }}"
                       class="btn btn-primary btn-sm font-weight-bold mr-2" style="border-radius:6px;">
                        <i class="fas fa-file-pdf mr-1"></i> Download PDF
                    </a>
                    <a href="{{ route('admin.accounts.vendor-payments.pdf.view', request()->except('page')) }}"
                       class="btn btn-outline-secondary btn-sm font-weight-bold mr-2" target="_blank" style="border-radius:6px;">
                        <i class="fas fa-eye mr-1"></i> View
                    </a>
                    <a href="{{ route('admin.accounts.vendor-payments.record-payment') }}"
                       class="btn btn-dark btn-sm font-weight-bold" style="border-radius:6px;">
                        <i class="fas fa-plus-circle mr-1"></i> Pay Vendor Invoice
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Filter Bar -->
                <form method="GET" class="mb-4 p-3 bg-light rounded border" id="vendor-payment-filter-form">
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
                        <div class="col-md-2 form-group mb-2">
                            <label class="small font-weight-bold text-muted text-uppercase">Method</label>
                            <select name="method" class="form-control form-control-sm">
                                <option value="">All Methods</option>
                                <option value="cash"           {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank"           {{ request('method') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="mobile_banking" {{ request('method') === 'mobile_banking' ? 'selected' : '' }}>Mobile Pay</option>
                                <option value="cheque"         {{ request('method') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <label class="small font-weight-bold text-muted text-uppercase">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Invoice, vendor, transaction...">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end align-items-center mt-1">
                        <small class="text-muted mr-3"><i class="fas fa-info-circle mr-1"></i> Filters apply automatically.</small>
                        <a href="{{ route('admin.accounts.vendor-payments.index') }}" class="btn btn-light border btn-sm font-weight-bold" style="border-radius:6px;">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </form>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0">
                        <thead class="bg-light text-dark">
                            <tr>
                                <th>Date</th>
                                <th>Invoice No</th>
                                <th>Vendor</th>
                                <th>Method</th>
                                <th>Transaction ID</th>
                                <th>Receipts</th>
                                <th class="text-right">Amount</th>
                                <th>Note</th>
                                <th class="text-center">PDF</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td><small>{{ $payment->created_at->format('d M, Y') }}<br><span class="text-muted">{{ $payment->created_at->format('h:i A') }}</span></small></td>
                                    <td><span class="font-weight-bold font-monospace small">{{ $payment->purchase->invoice_no ?? 'N/A' }}</span></td>
                                    <td>
                                        <strong>{{ $payment->purchase->vendor->shop_name ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info font-weight-bold">{{ strtoupper($payment->payment_method) }}</span>
                                    </td>
                                    <td><small class="text-muted font-monospace">{{ $payment->transaction_id ?: '—' }}</small></td>
                                    <td>
                                        @if($payment->receipts->count() > 0)
                                            @foreach($payment->receipts as $receipt)
                                                <div class="mb-1">
                                                    <a href="{{ route('admin.accounts.vendor-payments.receipts.download', $receipt->id) }}" class="btn btn-sm btn-outline-primary py-0 px-1" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <a href="{{ route('admin.accounts.vendor-payments.receipts.destroy', $receipt->id) }}" class="btn btn-sm btn-outline-danger py-0 px-1 delete-item" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-right font-weight-bold text-success">{!! formatConverted($payment->amount) !!}</td>
                                    <td><small class="text-muted">{{ $payment->note ?: '—' }}</small></td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ route('admin.accounts.vendor-payments.single.pdf', $payment->id) }}" class="btn btn-sm btn-warning" title="Download PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <a href="{{ route('admin.accounts.vendor-payments.single.view', $payment->id) }}" class="btn btn-sm btn-outline-info ml-1" title="View" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        @if($payment->purchase)
                                            <a href="{{ route('admin.purchases.show', $payment->purchase->id) }}" class="btn btn-sm btn-primary" title="Purchase Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        <i class="fas fa-money-check-alt fa-2x mb-2 d-block text-muted"></i>
                                        No vendor payments found matching the current filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $payments->links() }}</div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const $form = $('#vendor-payment-filter-form');
        const $search = $form.find('input[name="search"]');
        let searchTimer = null;

        $form.find('select, input[type="date"]').on('change', function() {
            $form.trigger('submit');
        });

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
