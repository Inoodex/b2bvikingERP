@extends('backend.layouts.master')

@section('title', 'Stock Returns')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <div class="d-flex align-items-center flex-wrap w-100">
                <h1 class="mb-2 mb-sm-0 d-flex align-items-center">
                    <i class="fas fa-undo-alt mr-2 text-primary"></i>
                    Stock Returns
                </h1>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        </div>
                        <div class="breadcrumb-item active">Stock Returns</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    {{-- Filter Card --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 p-2 bg-primary rounded-circle text-white d-none d-sm-flex">
                                    <i class="fas fa-filter"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold text-dark">Filter Returns</h5>
                                    <small class="text-muted">Search and filter return requests</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 col-sm-6 col-12 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-tag text-warning mr-1"></i>
                                            Status
                                        </label>
                                        <select id="filter-status" class="form-control form-control-sm">
                                            <option value="">All Statuses</option>
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-calendar-alt text-primary mr-1"></i>
                                            Date From
                                        </label>
                                        <input type="date" id="filter-date-from" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-calendar-alt text-primary mr-1"></i>
                                            Date To
                                        </label>
                                        <input type="date" id="filter-date-to" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12">
                                    <div class="d-flex flex-wrap align-items-end gap-2" style="height: 100%;">
                                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="reset-filters">
                                            <i class="fas fa-undo mr-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Table Card --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center flex-wrap w-100">
                                <div class="d-flex align-items-center mb-2 mb-sm-0">
                                    <div class="mr-3 p-2 bg-primary rounded-circle text-white d-none d-sm-flex">
                                        <i class="fas fa-list"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 font-weight-bold text-dark">All Stock Returns</h5>
                                        <small class="text-muted">Manage all return requests</small>
                                    </div>
                                </div>
                                <div class="ml-auto">
                                    <a href="{{ route('admin.issue-returns.create') }}" class="btn btn-primary btn-sm shadow-sm">
                                        <i class="fas fa-plus mr-1"></i>
                                        <span class="d-none d-sm-inline">Create Return Request</span>
                                        <span class="d-sm-none">Create</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="table-returns" style="width: 100%;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="font-weight-bold text-dark">Date</th>
                                            <th class="font-weight-bold text-dark">Return No</th>
                                            <th class="font-weight-bold text-dark">Issue No</th>
                                            <th class="font-weight-bold text-dark">Outlet / User</th>
                                            <th class="font-weight-bold text-dark text-right">Refund Amount</th>
                                            <th class="font-weight-bold text-dark">Status</th>
                                            <th class="font-weight-bold text-dark text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    /* =============================================
       STOCK RETURNS - CUSTOM STYLES
       ============================================= */

    .form-group label {
        font-size: 0.8rem;
        margin-bottom: 0.4rem;
        letter-spacing: 0.3px;
    }

    .form-control {
        border-radius: 8px !important;
        border: 1.5px solid #e2e8f0;
        transition: all 0.3s ease;
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
        background: #fafbfc;
    }

    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        background: #ffffff;
    }

    .form-control-sm {
        height: 38px !important;
        font-size: 0.85rem !important;
    }

    .btn {
        border-radius: 8px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
        min-height: 38px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 0.85rem !important;
        letter-spacing: 0.3px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    .btn:active {
        transform: scale(0.97);
    }

    .btn-primary {
        background: linear-gradient(135deg, #4e73df, #224abe) !important;
        border: none !important;
        color: #ffffff !important;
    }

    .btn-primary:hover {
        box-shadow: 0 6px 20px rgba(78, 115, 223, 0.3) !important;
    }

    .btn-outline-secondary {
        color: #858796 !important;
        border-color: #858796 !important;
        background: transparent !important;
    }

    .btn-outline-secondary:hover {
        background: #858796 !important;
        color: #fff !important;
        border-color: #858796 !important;
    }

    .card {
        border-radius: 12px !important;
        overflow: hidden !important;
    }

    .card-header:first-child {
        border-radius: 12px 12px 0 0 !important;
    }

    .card-footer:last-child {
        border-radius: 0 0 12px 12px !important;
    }

    /* Table Styles */
    .table th {
        border-top: none !important;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.75rem 1rem !important;
    }

    .table td {
        vertical-align: middle !important;
        font-size: 0.85rem;
        padding: 0.75rem 1rem !important;
    }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.03);
    }

    /* Badge Styles */
    .badge {
        font-weight: 600 !important;
        padding: 0.35rem 0.75rem !important;
        border-radius: 50rem !important;
        font-size: 0.7rem !important;
    }

    .badge-pending {
        background: #f6c23e !important;
        color: #1a1a2e !important;
    }

    .badge-approved {
        background: #1cc88a !important;
        color: #ffffff !important;
    }

    .badge-cancelled {
        background: #e74a3b !important;
        color: #ffffff !important;
    }

    /* Action Buttons */
    .btn-action {
        width: 32px !important;
        height: 32px !important;
        min-height: 32px !important;
        padding: 0 !important;
        border-radius: 8px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 0.75rem !important;
        transition: all 0.3s ease !important;
        border: none !important;
    }

    .btn-action:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }

    .btn-action i {
        font-size: 0.8rem !important;
    }

    .btn-action-view {
        background: #4e73df !important;
        color: #fff !important;
    }

    .btn-action-view:hover {
        background: #224abe !important;
    }

    .btn-action-edit {
        background: #1cc88a !important;
        color: #fff !important;
    }

    .btn-action-edit:hover {
        background: #13855c !important;
    }

    .btn-action-delete {
        background: #e74a3b !important;
        color: #fff !important;
    }

    .btn-action-delete:hover {
        background: #c0392b !important;
    }

    /* Mobile Responsive */
    @media (max-width: 575.98px) {
        .section-header {
            padding: 12px 15px !important;
        }
        .section-header h1 {
            font-size: 1rem !important;
        }
        .card-body {
            padding: 15px !important;
        }
        .card-header {
            padding: 10px 15px !important;
        }
        .form-group {
            margin-bottom: 1rem !important;
        }
        .form-control-sm {
            height: 36px !important;
            font-size: 0.8rem !important;
        }
        .btn {
            font-size: 0.8rem !important;
            min-height: 36px !important;
            padding: 0 0.8rem !important;
        }
        .table td {
            font-size: 0.75rem !important;
            padding: 0.5rem 0.6rem !important;
        }
        .table th {
            font-size: 0.65rem !important;
            padding: 0.5rem 0.6rem !important;
        }
        .badge {
            font-size: 0.6rem !important;
            padding: 0.2rem 0.5rem !important;
        }
        .btn-action {
            width: 28px !important;
            height: 28px !important;
            min-height: 28px !important;
            font-size: 0.6rem !important;
        }
        .btn-action i {
            font-size: 0.65rem !important;
        }
        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
        }
        .card-header .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        .card-header .ml-auto {
            margin-left: 0 !important;
            margin-top: 8px !important;
            width: 100% !important;
        }
        .card-header .ml-auto .btn {
            width: 100% !important;
        }
        .table-responsive {
            margin: 0 -15px !important;
            padding: 0 15px !important;
            width: calc(100% + 30px) !important;
        }
        .gap-2 {
            gap: 0.5rem !important;
        }
    }

    @media (min-width: 576px) and (max-width: 767.98px) {
        .card-body {
            padding: 20px !important;
        }
        .card-header {
            padding: 12px 20px !important;
        }
        .table td {
            font-size: 0.8rem !important;
            padding: 0.6rem 0.8rem !important;
        }
        .table th {
            font-size: 0.7rem !important;
            padding: 0.6rem 0.8rem !important;
        }
        .btn-action {
            width: 30px !important;
            height: 30px !important;
            min-height: 30px !important;
        }
    }

    @media (max-width: 767.98px) {
        .section-header .breadcrumb {
            font-size: 0.75rem !important;
        }
        .card-header .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        .card-header .ml-auto {
            margin-left: 0 !important;
            margin-top: 8px !important;
            width: 100% !important;
        }
        .card-header .ml-auto .btn {
            width: 100% !important;
        }
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeInUp 0.4s ease-out;
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #4e73df;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #224abe;
    }

    /* Select2 custom styles if used */
    .select2-container--default .select2-selection--single {
        border-radius: 8px !important;
        border: 1.5px solid #e2e8f0 !important;
        height: 38px !important;
        padding: 0 12px;
        background: #fafbfc !important;
        transition: all 0.3s ease;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 34px !important;
        font-size: 0.85rem;
        color: #495057;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }

    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #4e73df !important;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
    }

    .select2-dropdown {
        border-radius: 8px !important;
        border-color: #e2e8f0 !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    /* DataTable Custom */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.3rem 0.8rem !important;
        border-radius: 6px !important;
        border: none !important;
        margin: 0 2px !important;
        font-size: 0.8rem !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #4e73df, #224abe) !important;
        color: #fff !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #4e73df !important;
        color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.2);
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 8px !important;
        border: 1.5px solid #e2e8f0 !important;
        padding: 0.4rem 0.75rem !important;
        font-size: 0.85rem !important;
        background: #fafbfc !important;
        transition: all 0.3s ease !important;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #4e73df !important;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        background: #ffffff !important;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 8px !important;
        border: 1.5px solid #e2e8f0 !important;
        padding: 0.3rem 0.5rem !important;
        font-size: 0.85rem !important;
        background: #fafbfc !important;
    }

    .dataTables_wrapper .dataTables_info {
        font-size: 0.85rem !important;
        color: #858796 !important;
        padding-top: 0.75rem !important;
    }

    @media (max-width: 575.98px) {
        .dataTables_wrapper .dataTables_filter input {
            width: 150px !important;
            font-size: 0.75rem !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.2rem 0.5rem !important;
            font-size: 0.7rem !important;
        }
        .dataTables_wrapper .dataTables_length select {
            font-size: 0.75rem !important;
            padding: 0.2rem 0.3rem !important;
        }
        .dataTables_wrapper .dataTables_info {
            font-size: 0.75rem !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        const table = $("#table-returns").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.issue-returns.index') }}",
                data: function (d) {
                    d.status = $('#filter-status').val();
                    d.date_from = $('#filter-date-from').val();
                    d.date_to = $('#filter-date-to').val();
                }
            },
            columns: [
                {data: 'date', name: 'created_at'},
                {data: 'return_no', name: 'return_no'},
                {data: 'issue_no', name: 'issue_no', orderable: false, searchable: false},
                {data: 'outlet_name', name: 'outlet_name', orderable: false, searchable: false},
                {data: 'refund_amount', name: 'refund_amount', className: 'text-right font-weight-bold'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
            ],
            order: [[0, "desc"]],
            pageLength: 10,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                zeroRecords: "No returns found"
            }
        });

        // Auto reload on filter change
        $('#filter-status, #filter-date-from, #filter-date-to').on('change', function() {
            table.ajax.reload();
        });

        // Reset filters
        $('#reset-filters').on('click', function() {
            $('#filter-status').val('');
            $('#filter-date-from').val('');
            $('#filter-date-to').val('');
            table.ajax.reload();
        });
    });
</script>
@endpush