@extends('backend.layouts.master')
@section('title', 'Frontend Orders')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Frontend Orders</h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Frontend Orders</h4>
                            <div id="custom-status-filter" style="display: none; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <label for="filter_user" class="mb-0" style="white-space: nowrap;"><strong>User/Outlet:</strong></label>
                                <select id="filter_user" class="form-control form-control-sm" style="width: auto; min-width: 140px;">
                                    <option value="">All Users</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->outlet_name ?: $user->name }}</option>
                                    @endforeach
                                </select>
                                <label for="filter_status" class="mb-0" style="white-space: nowrap;"><strong>Status:</strong></label>
                                <select id="filter_status" class="form-control form-control-sm" style="width: auto;">
                                    <option value="">All Orders</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <button type="button" id="reset_filter" class="btn btn-danger btn-sm" style="display: none;">Reset</button>
                            </div>
                        </div>
                        <div class="table-responsive card-body">
                            {{ $dataTable->table(['class' => 'table table-striped table-bordered', 'id' => 'order-table']) }}
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
            $('#order-table').on('init.dt', function() {
                var filterWrapper = $('#custom-status-filter');
                filterWrapper.css('display', 'flex');

                var dtFilter = $('.dataTables_filter');
                dtFilter.css({
                    'display': 'flex',
                    'align-items': 'center',
                    'justify-content': 'flex-end',
                    'gap': '10px'
                });
                dtFilter.prepend(filterWrapper);
                $('#filter_user').select2({ width: '200px' });
            });

            function toggleResetBtn() {
                if ($('#filter_status').val() || $('#filter_user').val()) {
                    $('#reset_filter').show();
                } else {
                    $('#reset_filter').hide();
                }
            }

            $('#filter_status, #filter_user').on('change', function() {
                toggleResetBtn();
                if(window.LaravelDataTables && window.LaravelDataTables['order-table']) {
                    window.LaravelDataTables['order-table'].ajax.reload();
                }
            });

            $('#reset_filter').on('click', function() {
                $('#filter_status').val('');
                $('#filter_user').val('').trigger('change.select2');
                toggleResetBtn();
                if(window.LaravelDataTables && window.LaravelDataTables['order-table']) {
                    window.LaravelDataTables['order-table'].ajax.reload();
                }
            });
        });
    </script>
@endpush
