@extends('backend.layouts.master')
@section('title', $settings->site_name . ' | Roles Management')

@push('css')
<style>
    /* DataTable Top Controls */
    #role-table_wrapper .dataTables_length {
        margin-bottom: 15px;
    }
    #role-table_wrapper .dataTables_length label {
        color: #6c757d;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    #role-table_wrapper .dataTables_length select {
        border: 1px solid #e4e6fc !important;
        border-radius: 4px !important;
        padding: 4px 10px !important;
        height: 35px !important;
        font-size: 13px !important;
        background-color: #fdfdff !important;
        min-width: 65px;
    }
    #role-table_wrapper .dataTables_length select:focus {
        border-color: #6777ef !important;
        box-shadow: 0 0 0 0.2rem rgba(103, 119, 239, 0.2) !important;
    }

    #role-table_wrapper .dataTables_filter {
        margin-bottom: 15px;
        display: flex;
        justify-content: flex-end;
    }
    #role-table_wrapper .dataTables_filter label {
        color: #6c757d;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    #role-table_wrapper .dataTables_filter input {
        border: 1px solid #e4e6fc !important;
        border-radius: 4px !important;
        padding: 6px 12px !important;
        height: 35px !important;
        font-size: 13px !important;
        background-color: #fdfdff !important;
        width: 220px !important;
        transition: all 0.3s ease;
    }
    #role-table_wrapper .dataTables_filter input:focus {
        width: 260px !important;
        border-color: #6777ef !important;
        box-shadow: 0 0 0 0.2rem rgba(103, 119, 239, 0.2) !important;
    }

    /* Table Typography */
    #role-table thead th {
        background-color: #f9fafe !important;
        color: #34395e !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        border-bottom: 2px solid #edf2f9 !important;
        padding: 12px 14px !important;
    }
    #role-table tbody td {
        vertical-align: middle !important;
        padding: 12px 14px !important;
        font-size: 13px !important;
        border-bottom: 1px solid #f2f2f2 !important;
    }
    #role-table tbody td:nth-child(2) {
        font-weight: 700;
        color: #34395e;
    }
    #role-table tbody td:nth-child(3) {
        white-space: normal !important;
    }

    /* Pagination Matching Exactly User Reference Image */
    #role-table_wrapper .dataTables_paginate {
        margin-top: 15px !important;
        display: flex;
        justify-content: flex-end;
    }
    #role-table_wrapper .dataTables_paginate .pagination {
        margin: 0 !important;
        gap: 4px !important;
        display: flex !important;
        align-items: center !important;
    }
    #role-table_wrapper .page-item {
        margin: 0 !important;
    }
    #role-table_wrapper .page-item .page-link {
        color: #6777ef !important;
        background-color: #f9fafe !important;
        border: none !important;
        border-radius: 4px !important;
        margin: 0 2px !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 6px 14px !important;
        line-height: 1.5 !important;
        box-shadow: none !important;
        transition: all 0.2s ease !important;
    }
    #role-table_wrapper .page-item .page-link:hover {
        background-color: #6777ef !important;
        color: #ffffff !important;
        border: none !important;
    }
    #role-table_wrapper .page-item.active .page-link {
        background-color: #6777ef !important;
        border: none !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 2px 6px #acb5f6 !important;
    }
    #role-table_wrapper .page-item.disabled .page-link {
        border: none !important;
        background-color: #f9fafe !important;
        color: #6777ef !important;
        opacity: 0.55 !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
    }
    #role-table_wrapper .dataTables_info {
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #6c757d !important;
        padding-top: 20px !important;
    }

    @media (max-width: 767.98px) {
        #role-table_wrapper .dataTables_length,
        #role-table_wrapper .dataTables_filter {
            float: none !important;
            text-align: left !important;
            justify-content: flex-start !important;
            margin-bottom: 10px;
        }
        #role-table_wrapper .dataTables_filter input {
            width: 100% !important;
        }
        #role-table_wrapper .dataTables_paginate {
            justify-content: flex-start !important;
        }
    }
</style>
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-user-shield text-primary mr-2"></i> Roles Management</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Roles Management</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Configured Roles</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.role.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i> Create New Role
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                {{ $dataTable->table(['class' => 'table table-striped table-hover', 'id' => 'role-table']) }}
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
            $('body').tooltip({selector: '[data-toggle="tooltip"]'});
            
            function polishSearchInput() {
                var searchInput = $('#role-table_filter input');
                if (searchInput.length) {
                    searchInput.attr('placeholder', 'Search roles by name...');
                }
            }
            polishSearchInput();
            setTimeout(polishSearchInput, 300);
            $(document).ajaxComplete(function() {
                polishSearchInput();
            });
        });
    </script>
@endpush