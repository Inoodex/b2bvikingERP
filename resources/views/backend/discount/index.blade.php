@extends('backend.layouts.master')

@section('title', 'Discount Rules')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <div class="d-flex align-items-center flex-wrap w-100">
                <h1 class="mb-2 mb-sm-0 d-flex align-items-center">
                    <i class="fas fa-percent mr-2 text-primary"></i>
                    Discount Rules
                </h1>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        </div>
                        <div class="breadcrumb-item active">Discount Rules</div>
                    </div>
                    <a href="{{ route('admin.discounts.create') }}" class="btn btn-primary btn-sm ml-2 shadow-sm">
                        <i class="fas fa-plus mr-1"></i>
                        <span class="d-none d-sm-inline">Create New</span>
                        <span class="d-sm-none">Create</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        {{-- Card Header --}}
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center flex-wrap w-100">
                                <div class="d-flex align-items-center mb-2 mb-sm-0">
                                    <div class="mr-3 p-2 bg-primary rounded-circle text-white d-none d-sm-flex">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 font-weight-bold text-dark">All Discount Rules</h5>
                                        <small class="text-muted">Manage your discount and promo rules</small>
                                    </div>
                                </div>
                                <div class="ml-auto">
                                    <span class="badge badge-primary p-2">
                                        <i class="fas fa-database mr-1"></i> Live Data
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                {{ $dataTable->table(['class' => 'table table-hover mb-0', 'id' => 'discount-table']) }}
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
       DISCOUNT RULES LIST - PREMIUM DESIGN
       ============================================= */

    /* Table Styles */
    .table th {
        border-top: none !important;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.75rem 1rem !important;
        background: #f8f9fc !important;
        color: #1a1a2e !important;
        font-weight: 700 !important;
        border-bottom: 2px solid #e2e8f0 !important;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table td {
        vertical-align: middle !important;
        font-size: 0.85rem;
        padding: 0.75rem 1rem !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.04);
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.04);
        transform: scale(1.002);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    /* Card */
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

    /* Badge Styles */
    .badge {
        font-weight: 600 !important;
        padding: 0.35rem 0.75rem !important;
        border-radius: 50rem !important;
        font-size: 0.7rem !important;
    }

    .badge-success {
        background: #1cc88a !important;
        color: #ffffff !important;
    }

    .badge-danger {
        background: #e74a3b !important;
        color: #ffffff !important;
    }

    .badge-info {
        background: #36b9cc !important;
        color: #ffffff !important;
    }

    .badge-warning {
        background: #f6c23e !important;
        color: #1a1a2e !important;
    }

    .badge-primary {
        background: #4e73df !important;
        color: #ffffff !important;
    }

    .badge-secondary {
        background: #858796 !important;
        color: #ffffff !important;
    }

    /* Discount Type Badge */
    .discount-type {
        font-weight: 600;
        padding: 0.2rem 0.6rem;
        border-radius: 4px;
        font-size: 0.7rem;
        display: inline-block;
    }

    .discount-type-percent {
        background: #e8f0fe;
        color: #4e73df;
    }

    .discount-type-flat {
        background: #fef3e8;
        color: #e67e22;
    }

    /* Toggle Switch */
    .custom-switch {
        padding-left: 2.8rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
    }

    .custom-switch .custom-control-label {
        cursor: pointer;
        padding-top: 0.1rem;
        font-weight: 500;
        font-size: 0.85rem;
        margin-bottom: 0;
    }

    .custom-switch .custom-control-label::before {
        width: 2.8rem;
        height: 1.5rem;
        border-radius: 1.5rem;
        left: -2.8rem;
        top: 0rem;
        border: 2px solid #d1d3e2;
        background: #e9ecef;
        transition: all 0.3s ease;
    }

    .custom-switch .custom-control-label::after {
        width: 1.1rem;
        height: 1.1rem;
        border-radius: 1.1rem;
        left: -2.6rem;
        top: 0.2rem;
        background: #ffffff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25);
        transition: all 0.3s ease;
    }

    .custom-switch .custom-control-input:checked ~ .custom-control-label::before {
        background: #1cc88a;
        border-color: #1cc88a;
    }

    .custom-switch .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(1.3rem);
        background: #ffffff;
    }

    .custom-switch .custom-control-input:focus ~ .custom-control-label::before {
        box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25);
    }

    /* Action Buttons */
    .btn-action {
        width: 32px !important;
        height: 32px !important;
        min-height: 32px !important;
        padding: 0 !important;
        border-radius: 6px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 0.7rem !important;
        transition: all 0.3s ease !important;
        border: none !important;
        margin: 0 2px;
    }

    .btn-action:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }

    .btn-action:active {
        transform: scale(0.95) !important;
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

    .btn-action-default {
        background: #f6c23e !important;
        color: #1a1a2e !important;
    }

    .btn-action-default:hover {
        background: #dda20a !important;
    }

    /* Buttons */
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
        background: #4e73df !important;
        border: none !important;
        color: #ffffff !important;
    }

    .btn-primary:hover {
        background: #224abe !important;
        box-shadow: 0 6px 20px rgba(78, 115, 223, 0.35) !important;
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
        background: #4e73df !important;
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

    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 10px;
    }

    .dataTables_wrapper .dataTables_length {
        margin-bottom: 10px;
    }

    /* Action Group */
    .action-group {
        display: flex;
        gap: 4px;
        align-items: center;
        justify-content: center;
        flex-wrap: nowrap;
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
            padding: 10px !important;
        }
        .card-header {
            padding: 10px 15px !important;
        }
        .table td {
            font-size: 0.7rem !important;
            padding: 0.4rem 0.5rem !important;
        }
        .table th {
            font-size: 0.6rem !important;
            padding: 0.4rem 0.5rem !important;
        }
        .badge {
            font-size: 0.55rem !important;
            padding: 0.15rem 0.4rem !important;
        }
        .btn-action {
            width: 24px !important;
            height: 24px !important;
            min-height: 24px !important;
            font-size: 0.5rem !important;
            border-radius: 4px !important;
        }
        .btn-action i {
            font-size: 0.55rem !important;
        }
        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
        }
        .section-header .btn {
            width: auto !important;
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
        .card-header .ml-auto .badge {
            display: inline-block !important;
        }
        .table-responsive {
            margin: 0 -10px !important;
            padding: 0 10px !important;
            width: calc(100% + 20px) !important;
        }
        .custom-switch {
            padding-left: 2rem !important;
        }
        .custom-switch .custom-control-label {
            font-size: 0.65rem !important;
        }
        .custom-switch .custom-control-label::before {
            width: 2rem !important;
            height: 1.1rem !important;
            left: -2rem !important;
        }
        .custom-switch .custom-control-label::after {
            width: 0.8rem !important;
            height: 0.8rem !important;
            left: -1.85rem !important;
            top: 0.15rem !important;
        }
        .custom-switch .custom-control-input:checked ~ .custom-control-label::after {
            transform: translateX(0.9rem) !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            width: 120px !important;
            font-size: 0.7rem !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.15rem 0.4rem !important;
            font-size: 0.6rem !important;
        }
        .dataTables_wrapper .dataTables_length select {
            font-size: 0.7rem !important;
            padding: 0.15rem 0.3rem !important;
        }
        .dataTables_wrapper .dataTables_info {
            font-size: 0.7rem !important;
        }
        .action-group {
            gap: 2px !important;
        }
        .discount-type {
            font-size: 0.55rem !important;
            padding: 0.15rem 0.4rem !important;
        }
    }

    @media (min-width: 576px) and (max-width: 767.98px) {
        .card-body {
            padding: 15px !important;
        }
        .card-header {
            padding: 12px 20px !important;
        }
        .table td {
            font-size: 0.75rem !important;
            padding: 0.5rem 0.7rem !important;
        }
        .table th {
            font-size: 0.65rem !important;
            padding: 0.5rem 0.7rem !important;
        }
        .btn-action {
            width: 28px !important;
            height: 28px !important;
            min-height: 28px !important;
        }
        .btn-action i {
            font-size: 0.7rem !important;
        }
    }

    @media (max-width: 767.98px) {
        .section-header .breadcrumb {
            font-size: 0.7rem !important;
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

    /* Tooltip on hover */
    .btn-action[title]:hover::after {
        content: attr(title);
        position: absolute;
        bottom: -28px;
        left: 50%;
        transform: translateX(-50%);
        background: #1a1a2e;
        color: #fff;
        padding: 2px 10px;
        border-radius: 4px;
        font-size: 0.6rem;
        white-space: nowrap;
        z-index: 10;
        font-weight: 500;
    }

    .btn-action {
        position: relative;
    }

    /* Table Striped Alternative */
    .table tbody tr:nth-child(even) {
        background-color: rgba(78, 115, 223, 0.02);
    }

    /* Status Badge Animation */
    .badge-success {
        animation: pulse-green 2s infinite;
    }

    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(28, 200, 138, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(28, 200, 138, 0); }
        100% { box-shadow: 0 0 0 0 rgba(28, 200, 138, 0); }
    }

    .badge-danger {
        animation: pulse-red 2s infinite;
    }

    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(231, 74, 59, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(231, 74, 59, 0); }
        100% { box-shadow: 0 0 0 0 rgba(231, 74, 59, 0); }
    }
</style>
@endpush

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        $(document).ready(function() {
            // Status Change
            $('body').on('change', '.change-status', function() {
                let isChecked = $(this).is(':checked');
                let id = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.discounts.change-status') }}",
                    method: 'put',
                    data: {
                        id: id,
                        status: isChecked
                    },
                    success: function(data) {
                        toastr.success(data.message);
                        $('#discount-table').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Status update failed';
                        toastr.error(message);
                        $('#discount-table').DataTable().ajax.reload(null, false);
                    }
                });
            });

            // Set Default
            $('body').on('click', '.set-default', function() {
                let id = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.discounts.set-default') }}",
                    method: 'put',
                    data: {
                        id: id
                    },
                    success: function(data) {
                        toastr.success(data.message);
                        $('#discount-table').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Default update failed';
                        toastr.error(message);
                    }
                });
            });

            // Tooltip initialization for action buttons
            $('.btn-action').each(function() {
                const title = $(this).attr('title');
                if (title) {
                    $(this).attr('data-tooltip', title);
                }
            });
        });
    </script>
@endpush