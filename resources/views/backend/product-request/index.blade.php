@extends('backend.layouts.master')
@section('title', 'Product Requests')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Product Requests</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Product Requests</h4>
                            <div class="card-header-action d-flex align-items-center" style="gap: 15px;">
                                <!-- Status Filter -->
                                <div id="custom-status-filter" style="display: none; align-items: center; gap: 10px;">
                                    <label for="filter_status" class="mb-0" style="white-space: nowrap;"><strong>Filter Status:</strong></label>
                                    <select id="filter_status" class="form-control form-control-sm" style="width: auto;">
                                        <option value="">All Requests</option>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        {{-- <option value="shipped">Shipped</option> --}}
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <button type="button" id="reset_filter" class="btn btn-danger btn-sm" style="display: none;">Reset</button>
                                </div>

                                @can('Create Product Requests')
                                    <a href="{{ route('admin.product-requests.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create New</a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                {{ $dataTable->table(['class' => 'table table-striped table-bordered', 'id' => 'product-request-table']) }}
                            </div>
                        </div>
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
            // Inject custom filter into DataTables DOM once initialized
            $('#product-request-table').on('init.dt', function() {
                var filterWrapper = $('#custom-status-filter');
                filterWrapper.css('display', 'flex'); // Show it
                
                var dtFilter = $('.dataTables_filter');
                dtFilter.css({
                    'display': 'flex',
                    'align-items': 'center',
                    'justify-content': 'flex-end',
                    'gap': '15px'
                });
                dtFilter.prepend(filterWrapper);
            });

            $('#filter_status').on('change', function() {
                if ($(this).val()) {
                    $('#reset_filter').show();
                } else {
                    $('#reset_filter').hide();
                }

                if(window.LaravelDataTables && window.LaravelDataTables['product-request-table']) {
                    window.LaravelDataTables['product-request-table'].ajax.reload();
                }
            });

            $('#reset_filter').on('click', function() {
                $('#filter_status').val('').trigger('change');
            });
        });
    </script>

    @if(session()->has('clear_request_basket'))
    <script>
        localStorage.removeItem('request_basket');
    </script>
    @endif
@endpush

