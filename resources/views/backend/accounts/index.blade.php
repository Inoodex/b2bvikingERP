@extends('backend.layouts.master')

@section('title', 'Transaction History')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Accounts - Transaction History</h1>
        </div>

        <div class="section-body">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Transaction Ledger</h4>
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
                                <select id="method" class="form-control form-control-sm">
                                    <option value="">All Methods</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="mobile_banking">Mobile Pay</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                            <button type="button" id="btn-export-pdf" class="btn btn-primary btn-sm mr-2">
                                <i class="fas fa-file-pdf"></i> Download PDF
                            </button>
                            {{-- <button type="button" id="btn-view-pdf" class="btn btn-outline-secondary btn-sm mr-2">
                                <i class="fas fa-eye"></i> View
                            </button> --}}
                            <button type="button" id="btn-reset" class="btn btn-danger btn-sm" title="Reset Filters"><i class="fas fa-undo"></i></button>
                        </form>
                    </div>
                </div>
                <div class="card-body table-responsive">
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
            const table = window.LaravelDataTables["order-payment-table"];

            // Auto-filter on change
            $('#start_date, #end_date, #method').on('change', function() { table.draw(); });

            // Reset filters
            $('#btn-reset').on('click', function(e) {
                e.preventDefault();
                $('#filter-form')[0].reset();
                table.draw();
            });

            $('#btn-export-pdf').on('click', function() {
                const params = new URLSearchParams();
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                const method = $('#method').val();
                const search = table.search();

                if (startDate) params.set('start_date', startDate);
                if (endDate) params.set('end_date', endDate);
                if (method) params.set('method', method);
                if (search) params.set('search', search);

                const url = "{{ route('admin.accounts.payments.pdf') }}" + (params.toString() ? `?${params}` : '');
                window.location.href = url;
            });

            $('#btn-view-pdf').on('click', function() {
                const params = new URLSearchParams();
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                const method = $('#method').val();
                const search = table.search();

                if (startDate) params.set('start_date', startDate);
                if (endDate) params.set('end_date', endDate);
                if (method) params.set('method', method);
                if (search) params.set('search', search);

                const url = "{{ route('admin.accounts.payments.pdf.view') }}" + (params.toString() ? `?${params}` : '');
                window.open(url, '_blank');
            });
            
            // Bind to DataTable query
            table.on('preXhr.dt', function(e, settings, data) {
                data.start_date = $('#start_date').val();
                data.end_date = $('#end_date').val();
                data.method = $('#method').val();
            });
        });
    </script>
@endpush
