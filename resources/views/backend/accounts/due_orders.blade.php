@extends('backend.layouts.master')

@section('title', 'Due Orders')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Accounts - Due Orders</h1>
        </div>

        <div class="section-body">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Outstanding Balances</h4>
                    <div class="card-header-action">
                        <form id="filter-form" class="form-inline">
                            <div class="form-group mr-2">
                                <label class="mr-1 d-none d-lg-block">From:</label>
                                <input type="date" id="start_date" class="form-control form-control-sm">
                            </div>
                            <div class="form-group mr-2">
                                <label class="mr-1 d-none d-lg-block">To:</label>
                                <input type="date" id="end_date" class="form-control form-control-sm">
                            </div>
                            <div class="form-group mr-2">
                                <input type="text" id="customer" class="form-control form-control-sm" placeholder="Search Customer/Order...">
                            </div>
                            <button type="button" id="btn-reset" class="btn btn-danger btn-sm" title="Reset Filters"><i class="fas fa-undo"></i></button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    {{ $dataTable->table() }}
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
            $('#start_date, #end_date').on('change', function() { table.draw(); });
            $('#customer').on('keyup', function() { table.draw(); });

            // Reset filters
            $('#btn-reset').on('click', function(e) {
                e.preventDefault();
                $('#filter-form')[0].reset();
                table.draw();
            });
            
            // Bind to DataTable query
            table.on('preXhr.dt', function(e, settings, data) {
                data.start_date = $('#start_date').val();
                data.end_date = $('#end_date').val();
                data.customer = $('#customer').val();
            });
        });
    </script>
@endpush
