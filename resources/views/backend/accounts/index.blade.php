@extends('backend.layouts.master')
@section('title', 'Transaction Ledger — Customer Payment History')

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-file-invoice-dollar text-primary mr-2"></i> Customer Payment Ledger</h1>
            <p class="text-muted mb-0 small">Live Transaction History — All Customer Receipts & Payment Records</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.chart-of-accounts.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Transaction Ledger</div>
        </div>
    </div>

    <div class="section-body">
        <!-- Main Card -->
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-list text-primary mr-2"></i> All Customer Transactions</h5>

                <!-- Filter Bar -->
                <form id="filter-form" class="form-inline mt-2 mt-md-0">
                    <div class="form-group mr-2">
                        <label class="mr-1 d-none d-lg-block small font-weight-bold text-muted">From:</label>
                        <input type="date" id="start_date" class="form-control form-control-sm" style="border-radius:6px;">
                    </div>
                    <div class="form-group mr-2">
                        <label class="mr-1 d-none d-lg-block small font-weight-bold text-muted">To:</label>
                        <input type="date" id="end_date" class="form-control form-control-sm" style="border-radius:6px;">
                    </div>
                    <div class="form-group mr-2">
                        <select id="method" class="form-control form-control-sm" style="border-radius:6px;">
                            <option value="">All Methods</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="mobile_banking">Mobile Pay</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <button type="button" id="btn-export-pdf" class="btn btn-primary btn-sm mr-1" style="border-radius:6px;" title="Download PDF">
                        <i class="fas fa-file-pdf mr-1"></i> PDF
                    </button>
                    <button type="button" id="btn-reset" class="btn btn-light border btn-sm" style="border-radius:6px;" title="Reset Filters">
                        <i class="fas fa-undo"></i>
                    </button>
                </form>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table table-bordered table-striped table-hover align-middle']) }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        $(document).ready(function() {
            const table = window.LaravelDataTables["order-payment-table"];

            $('#start_date, #end_date, #method').on('change', function() { table.draw(); });

            $('#btn-reset').on('click', function(e) {
                e.preventDefault();
                $('#filter-form')[0].reset();
                table.draw();
            });

            $('#btn-export-pdf').on('click', function() {
                const params = new URLSearchParams();
                const startDate = $('#start_date').val();
                const endDate   = $('#end_date').val();
                const method    = $('#method').val();
                const search    = table.search();

                if (startDate) params.set('start_date', startDate);
                if (endDate)   params.set('end_date', endDate);
                if (method)    params.set('method', method);
                if (search)    params.set('search', search);

                window.location.href = "{{ route('admin.accounts.payments.pdf') }}" + (params.toString() ? `?${params}` : '');
            });

            table.on('preXhr.dt', function(e, settings, data) {
                data.start_date = $('#start_date').val();
                data.end_date   = $('#end_date').val();
                data.method     = $('#method').val();
            });
        });
    </script>
@endpush
