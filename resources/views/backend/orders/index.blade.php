@extends('backend.layouts.master')
@section('title', 'Web / Portal Orders')

@section('content')
    <section class="section">
        {{-- Standard Stisla Section Header --}}
        <div class="section-header">
            <h1><i class="fas fa-globe text-primary mr-2"></i>Web & Customer Portal Orders</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Web / Portal Orders</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    {{-- Clean Filter Card --}}
                    <div class="card card-primary mb-4">
                        <div class="card-header">
                            <h4><i class="fas fa-filter mr-2"></i>Filter Web & Portal Orders</h4>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-5 col-sm-6 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">User / Outlet</label>
                                        <select id="filter_user" class="form-control select2" style="width: 100%;">
                                            <option value="">All Users & Outlets</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->outlet_name ? $user->outlet_name . ' (' . $user->name . ')' : $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5 col-sm-6 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Status</label>
                                        <select id="filter_status" class="form-control">
                                            <option value="">All Orders</option>
                                            <option value="pending">Pending</option>
                                            <option value="credit_hold">🔒 CREDIT HOLD</option>
                                            <option value="approved">Approved</option>
                                            <option value="processing">Processing</option>
                                            <option value="completed">Completed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-12">
                                    <button type="button" id="reset_filter" class="btn btn-danger btn-block font-weight-bold" style="display: none;">
                                        <i class="fas fa-redo-alt mr-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Data Table Card --}}
                    <div class="card">
                        <div class="card-header">
                            <h4>All Sales Orders</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                {{ $dataTable->table(['class' => 'table table-striped table-bordered', 'id' => 'order-table']) }}
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
            if ($.fn.select2) {
                $('#filter_user').select2();
            }

            function toggleResetBtn() {
                if ($('#filter_status').val() || $('#filter_user').val()) {
                    $('#reset_filter').show();
                } else {
                    $('#reset_filter').hide();
                }
            }

            $('#filter_status, #filter_user').on('change', function() {
                toggleResetBtn();
                if (window.LaravelDataTables && window.LaravelDataTables['order-table']) {
                    window.LaravelDataTables['order-table'].ajax.reload();
                }
            });

            $('#reset_filter').on('click', function() {
                $('#filter_status').val('');
                $('#filter_user').val('').trigger('change');
                toggleResetBtn();
                if (window.LaravelDataTables && window.LaravelDataTables['order-table']) {
                    window.LaravelDataTables['order-table'].ajax.reload();
                }
            });
        });
    </script>
@endpush
