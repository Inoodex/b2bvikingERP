@extends('backend.layouts.master')

@section('title', 'Customer Due Orders (Accounts Receivable)')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-clock text-warning mr-2"></i> Customer Due Orders (AR)</h1>
            <p class="text-muted mb-0 small">Track all outstanding customer orders and collect receivable payments</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.customer-payments.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Due Orders</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-list-alt text-primary mr-2"></i> Outstanding Order Receivables</h5>
                <div class="card-header-action">
                    <form id="filter-form" class="form-inline">
                        <div class="form-group mr-2">
                            <label class="mr-1 d-none d-lg-block small font-weight-bold text-muted">From:</label>
                            <input type="date" id="start_date" class="form-control form-control-sm" style="border-radius: 6px;">
                        </div>
                        <div class="form-group mr-2">
                            <label class="mr-1 d-none d-lg-block small font-weight-bold text-muted">To:</label>
                            <input type="date" id="end_date" class="form-control form-control-sm" style="border-radius: 6px;">
                        </div>
                        <div class="form-group mr-2">
                            <input type="text" id="customer" class="form-control form-control-sm" placeholder="Search Customer/Order..." style="border-radius: 6px; min-width: 200px;">
                        </div>
                        <button type="button" id="btn-reset" class="btn btn-light btn-sm font-weight-bold text-danger border" title="Reset Filters" style="border-radius: 6px;">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body p-4 table-responsive">
                {{ $dataTable->table(['class' => 'table table-striped table-bordered align-middle']) }}
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        $(document).ready(function() {
            const table = window.LaravelDataTables["due-order-table"];

            // Auto-filter on change/input
            $('#start_date, #end_date').on('change', function() { if (table) table.draw(); });
            $('#customer').on('keyup', function() { if (table) table.draw(); });

            // Reset filters
            $('#btn-reset').on('click', function(e) {
                e.preventDefault();
                $('#filter-form')[0].reset();
                if (table) table.draw();
            });
            
            // Bind to DataTable query
            if (table) {
                table.on('preXhr.dt', function(e, settings, data) {
                    data.start_date = $('#start_date').val();
                    data.end_date = $('#end_date').val();
                    data.customer = $('#customer').val();
                });
            }
        });
    </script>
@endpush
