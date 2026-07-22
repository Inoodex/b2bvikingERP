@extends('backend.layouts.master')

@section('title', 'Tax / VAT')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <div class="d-flex align-items-center flex-wrap w-100">
                <h1 class="mb-2 mb-sm-0 d-flex align-items-center" style="font-size: 1.25rem;">
                    <i class="fas fa-percent mr-2" style="color: #2563eb;"></i>
                    Tax / VAT
                </h1>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        </div>
                        <div class="breadcrumb-item active">Tax / VAT</div>
                    </div>
                    <a href="{{ route('admin.taxes.create') }}" class="btn btn-primary btn-sm ml-2 shadow-sm" style="background: #2563eb; border: none; border-radius: 10px; min-height: 38px;">
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
                    <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
                        {{-- Card Header --}}
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center flex-wrap w-100">
                                <div class="d-flex align-items-center mb-2 mb-sm-0">
                                    <div class="mr-3 p-2 rounded-circle text-white d-none d-sm-flex" style="background: #2563eb;">
                                        <i class="fas fa-list"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 font-weight-bold text-dark">All Tax / VAT Rules</h5>
                                        <small class="text-muted">Manage your tax and VAT rules</small>
                                    </div>
                                </div>
                                <div class="ml-auto">
                                    <span class="badge p-2" style="background: #2563eb; color: #ffffff; border-radius: 50rem; font-weight: 600;">
                                        <i class="fas fa-database mr-1"></i> Live Data
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body p-0">
                            <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                {{ $dataTable->table(['class' => 'table table-hover mb-0', 'id' => 'tax-table']) }}
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
       TAX / VAT LIST - FULLY RESPONSIVE
       ============================================= */

    .table {
        width: 100% !important;
        min-width: 400px !important;
        margin-bottom: 0 !important;
    }

    .table th {
        border-top: none !important;
        font-size: 0.7rem !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.6rem 0.8rem !important;
        background: #f8f9fc !important;
        color: #1a1a2e !important;
        font-weight: 700 !important;
        white-space: nowrap !important;
    }

    .table td {
        vertical-align: middle !important;
        font-size: 0.8rem !important;
        padding: 0.6rem 0.8rem !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        white-space: nowrap !important;
    }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr:hover {
        background-color: rgba(37, 99, 235, 0.03);
    }

    /* Card */
    .card {
        border-radius: 16px !important;
        overflow: hidden !important;
    }

    .card-header:first-child {
        border-radius: 16px 16px 0 0 !important;
    }

    .card-footer:last-child {
        border-radius: 0 0 16px 16px !important;
    }

    /* Badge Styles */
    .badge {
        font-weight: 600 !important;
        padding: 0.3rem 0.7rem !important;
        border-radius: 50rem !important;
        font-size: 0.65rem !important;
        display: inline-block !important;
    }

    .badge-success {
        background: linear-gradient(135deg, #1cc88a, #13855c) !important;
        color: #ffffff !important;
    }

    .badge-danger {
        background: linear-gradient(135deg, #e74a3b, #c0392b) !important;
        color: #ffffff !important;
    }

    .badge-info {
        background: linear-gradient(135deg, #36b9cc, #258391) !important;
        color: #ffffff !important;
    }

    .badge-warning {
        background: linear-gradient(135deg, #f6c23e, #dda20a) !important;
        color: #1a1a2e !important;
    }

    .badge-primary {
        background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
        color: #ffffff !important;
    }

    /* Toggle Switch */
    .custom-switch {
        padding-left: 2.5rem !important;
        cursor: pointer !important;
        display: inline-block !important;
    }

    .custom-switch .custom-control-label {
        cursor: pointer !important;
        padding-top: 0.1rem !important;
        font-weight: 500 !important;
        font-size: 0.8rem !important;
    }

    .custom-switch .custom-control-label::before {
        width: 2.5rem !important;
        height: 1.3rem !important;
        border-radius: 1.3rem !important;
        left: -2.5rem !important;
        top: 0.1rem !important;
        border: 2px solid #d1d3e2 !important;
        background: #e9ecef !important;
        transition: all 0.3s ease !important;
    }

    .custom-switch .custom-control-label::after {
        width: 1rem !important;
        height: 1rem !important;
        border-radius: 1rem !important;
        left: -2.35rem !important;
        top: 0.25rem !important;
        background: #ffffff !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2) !important;
        transition: all 0.3s ease !important;
    }

    .custom-switch .custom-control-input:checked ~ .custom-control-label::before {
        background: linear-gradient(135deg, #1cc88a, #13855c) !important;
        border-color: #1cc88a !important;
    }

    .custom-switch .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(1.2rem) !important;
        background: #ffffff !important;
    }

    .custom-switch .custom-control-input:focus ~ .custom-control-label::before {
        box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25) !important;
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
        margin: 0 2px !important;
    }

    .btn-action:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }

    .btn-action i {
        font-size: 0.8rem !important;
    }

    .btn-action-view {
        background: #2563eb !important;
        color: #fff !important;
    }

    .btn-action-view:hover {
        background: #1d4ed8 !important;
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
        border-radius: 10px !important;
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
        background: #2563eb !important;
        border: none !important;
        color: #ffffff !important;
    }

    .btn-primary:hover {
        background: #1d4ed8 !important;
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35) !important;
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
        background: #2563eb;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #1d4ed8;
    }

    /* DataTable Custom */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.25rem 0.7rem !important;
        border-radius: 6px !important;
        border: none !important;
        margin: 0 2px !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #2563eb !important;
        color: #fff !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #2563eb !important;
        color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 8px !important;
        border: 1.5px solid #e2e8f0 !important;
        padding: 0.35rem 0.7rem !important;
        font-size: 0.8rem !important;
        background: #fafbfc !important;
        transition: all 0.3s ease !important;
        height: 38px !important;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
        background: #ffffff !important;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 8px !important;
        border: 1.5px solid #e2e8f0 !important;
        padding: 0.25rem 0.5rem !important;
        font-size: 0.8rem !important;
        background: #fafbfc !important;
        height: 38px !important;
    }

    .dataTables_wrapper .dataTables_info {
        font-size: 0.8rem !important;
        color: #858796 !important;
        padding-top: 0.75rem !important;
    }

    /* Action Group */
    .action-group {
        display: flex;
        gap: 4px;
        align-items: center;
        justify-content: center;
        flex-wrap: nowrap;
    }

    /* =============================================
       MOBILE RESPONSIVE BREAKPOINTS
       ============================================= */

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
        .card-header h5 {
            font-size: 0.95rem !important;
        }
        .card-header .text-muted {
            font-size: 0.7rem !important;
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
            padding: 0.2rem 0.5rem !important;
        }
        
        .btn-action {
            width: 28px !important;
            height: 28px !important;
            min-height: 28px !important;
            font-size: 0.6rem !important;
            border-radius: 6px !important;
        }
        .btn-action i {
            font-size: 0.65rem !important;
        }
        
        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
        }
        .section-header .btn {
            width: 100% !important;
            margin-left: 0 !important;
        }
        .section-header .breadcrumb {
            font-size: 0.65rem !important;
            width: 100% !important;
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
            font-size: 0.6rem !important;
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
            height: 34px !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.15rem 0.4rem !important;
            font-size: 0.65rem !important;
        }
        .dataTables_wrapper .dataTables_length select {
            font-size: 0.7rem !important;
            padding: 0.15rem 0.3rem !important;
            height: 34px !important;
        }
        .dataTables_wrapper .dataTables_info {
            font-size: 0.7rem !important;
        }
        
        .action-group {
            gap: 2px !important;
        }
        .action-group .btn-action {
            width: 24px !important;
            height: 24px !important;
            min-height: 24px !important;
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
            width: 30px !important;
            height: 30px !important;
            min-height: 30px !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            width: 150px !important;
        }
        .section-header .ml-auto {
            flex-wrap: wrap !important;
            gap: 5px !important;
        }
        .card-header .ml-auto {
            margin-top: 5px !important;
        }
    }

    @media (max-width: 767.98px) {
        .section-header .breadcrumb {
            font-size: 0.7rem !important;
        }
        .section-header .ml-auto {
            flex-wrap: wrap !important;
            gap: 8px !important;
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
        .section-header .btn {
            width: 100% !important;
        }
    }

    @media (min-width: 768px) and (max-width: 991.98px) {
        .table td {
            font-size: 0.8rem !important;
            padding: 0.6rem 0.8rem !important;
        }
        .table th {
            font-size: 0.7rem !important;
            padding: 0.6rem 0.8rem !important;
        }
        .card-body {
            padding: 15px !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            width: 180px !important;
        }
    }

    @media (min-width: 992px) {
        .table td {
            font-size: 0.85rem !important;
            padding: 0.75rem 1rem !important;
        }
        .table th {
            font-size: 0.75rem !important;
            padding: 0.75rem 1rem !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            width: 220px !important;
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

    /* Tooltip */
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

    /* Focus glow effect */
    .form-control:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    }

    /* Table striped alternative */
    .table tbody tr:nth-child(even) {
        background-color: rgba(37, 99, 235, 0.02);
    }

    /* Status badge animation */
    .badge-success {
        animation: pulse-green 2s infinite;
    }

    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(28, 200, 138, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(28, 200, 138, 0); }
        100% { box-shadow: 0 0 0 0 rgba(28, 200, 138, 0); }
    }

    /* DataTable wrapper responsive */
    .dataTables_wrapper {
        padding: 10px !important;
    }

    @media (max-width: 575.98px) {
        .dataTables_wrapper {
            padding: 5px !important;
        }
        .dataTables_wrapper .dataTables_filter {
            float: left !important;
            width: 100% !important;
            text-align: left !important;
            margin-bottom: 10px !important;
        }
        .dataTables_wrapper .dataTables_length {
            float: left !important;
            width: 100% !important;
            text-align: left !important;
            margin-bottom: 10px !important;
        }
        .dataTables_wrapper .dataTables_paginate {
            float: left !important;
            width: 100% !important;
            text-align: center !important;
            padding-top: 10px !important;
        }
        .dataTables_wrapper .dataTables_info {
            float: left !important;
            width: 100% !important;
            text-align: center !important;
        }
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
                    url: "{{ route('admin.taxes.change-status') }}",
                    method: 'put',
                    data: {
                        id: id,
                        status: isChecked
                    },
                    success: function(data) {
                        toastr.success(data.message);
                        $('#tax-table').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Status update failed';
                        toastr.error(message);
                        $('#tax-table').DataTable().ajax.reload(null, false);
                    }
                });
            });

            // Set Default
            $('body').on('click', '.set-default', function() {
                let id = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.taxes.set-default') }}",
                    method: 'put',
                    data: {
                        id: id
                    },
                    success: function(data) {
                        toastr.success(data.message);
                        $('#tax-table').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Default update failed';
                        toastr.error(message);
                    }
                });
            });
        });
    </script>
@endpush