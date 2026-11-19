@extends('backend.layouts.master')
@section('title', $settings->site_name . ' | Sub Category')

@push('css')
<style>
    :root {
        --pp-amber: #d4a24e;
        --pp-amber-bright: #ecc78b;
        --pp-amber-deep: #b8852a;
        --pp-amber-soft: rgba(212, 162, 78, 0.08);
        --pp-teal: #328A9A;
        --pp-teal-soft: rgba(50, 138, 154, 0.12);
        --pp-border: rgba(11, 17, 32, 0.07);
        --pp-border-hover: rgba(50, 138, 154, 0.25);
        --pp-ink: #161e2e;
        --pp-ink-soft: #2d3748;
        --pp-muted: #6b788e;
        --pp-surface: #f8f9fc;
        --pp-radius-md: 14px;
        --pp-radius-lg: 20px;
        --pp-shadow-card: 0 1px 3px rgba(11,17,32,0.04), 0 8px 20px -12px rgba(11,17,32,0.12);
        --pp-shadow-card-hover: 0 12px 32px -12px rgba(11,17,32,0.16), 0 0 0 1px rgba(50, 138, 154, 0.15);
    }

    .pp-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 4px;
        margin-bottom: 20px;
        padding: 0;
        position: relative;
    }
    .pp-header h1 {
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 800;
        font-size: 20px;
        color: var(--pp-ink);
        letter-spacing: -0.3px;
        margin: 0;
    }
    .pp-header h1 .pp-icon {
        width: 34px;
        height: 34px;
        min-width: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 14px;
        color: #1a1306;
        background: linear-gradient(145deg, var(--pp-amber-bright), var(--pp-amber));
        box-shadow: 0 4px 14px rgba(212, 162, 78, 0.35), inset 0 1px 0 rgba(255,255,255,0.3);
    }
    .pp-btn {
        border: none !important;
        font-weight: 700 !important;
        font-size: 11.5px !important;
        letter-spacing: 0.2px;
        border-radius: var(--pp-radius-lg) !important;
        padding: 7px 16px !important;
        transition: all 0.25s cubic-bezier(.2,.8,.2,1);
        position: relative;
        overflow: hidden;
    }
    .pp-btn:hover { transform: translateY(-2px); }
    .pp-btn:active { transform: translateY(0) scale(0.97); }
    .pp-btn-amber {
        background: linear-gradient(145deg, var(--pp-amber-bright), var(--pp-amber-deep)) !important;
        color: #1a1306 !important;
        box-shadow: 0 4px 14px -4px rgba(212, 162, 78, 0.45), inset 0 1px 0 rgba(255,255,255,0.25);
    }
    .pp-btn-amber:hover { filter: brightness(1.06); box-shadow: 0 8px 24px -6px rgba(212, 162, 78, 0.5); }

    .pp-card {
        border-radius: var(--pp-radius-md) !important;
        border: 1px solid var(--pp-border) !important;
        background: #fff !important;
        box-shadow: var(--pp-shadow-card) !important;
        transition: all 0.3s cubic-bezier(.2,.8,.2,1) !important;
        overflow: hidden;
    }
    .pp-card:hover {
        box-shadow: var(--pp-shadow-card-hover) !important;
        border-color: var(--pp-border-hover) !important;
    }
    .pp-card-header {
        padding: 14px 20px !important;
        background: linear-gradient(135deg, #fafbfc, #f4f5f8);
        border-bottom: 1px solid var(--pp-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pp-card-header h4 {
        font-weight: 800;
        font-size: 14px;
        color: var(--pp-ink);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pp-card-header h4 i { color: var(--pp-amber); }

    #subcategory-table_wrapper {
        padding: 4px 0 0;
    }
    .pp-table-container {
        overflow: visible !important;
    }
    #subcategory-table_wrapper .dataTables_scrollBody {
        overflow-x: auto !important;
        overflow-y: hidden !important;
        scrollbar-width: auto;
        scrollbar-color: #2563eb #e2e8f0;
        -webkit-overflow-scrolling: touch;
    }
    #subcategory-table_wrapper .dataTables_scrollBody::-webkit-scrollbar {
        height: 10px;
        display: block;
    }
    #subcategory-table_wrapper .dataTables_scrollBody::-webkit-scrollbar-track {
        background: #e2e8f0;
        border-radius: 5px;
    }
    #subcategory-table_wrapper .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #2563eb;
        border: 2px solid #e2e8f0;
        border-radius: 5px;
    }
    #subcategory-table_wrapper .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
        background: #1d4ed8;
    }
    #subcategory-table_wrapper .dataTables_length label,
    #subcategory-table_wrapper .dataTables_filter label {
        font-size: 12px;
        font-weight: 600;
        color: var(--pp-muted);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    #subcategory-table_wrapper .dataTables_length select,
    #subcategory-table_wrapper .dataTables_filter input {
        border-radius: 8px !important;
        border: 1.5px solid #dbeafe !important;
        padding: 5px 10px !important;
        font-size: 12px !important;
        background: #fff !important;
        transition: all 0.2s ease;
    }
    #subcategory-table_wrapper .dataTables_length select:focus,
    #subcategory-table_wrapper .dataTables_filter input:focus {
        border-color: var(--pp-teal) !important;
        box-shadow: 0 0 0 3px var(--pp-teal-soft) !important;
        outline: none;
    }

    #subcategory-table {
        font-size: 12px !important;
        border-collapse: separate;
        border-spacing: 0;
        width: 100% !important;
        margin-bottom: 0 !important;
    }
    #subcategory-table thead th {
        background: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 14px !important;
        border: none !important;
        border-bottom: 2px solid var(--pp-border) !important;
        vertical-align: middle;
        white-space: nowrap;
    }
    #subcategory-table thead th *,
    #subcategory-table thead .sorting:before,
    #subcategory-table thead .sorting_asc:before,
    #subcategory-table thead .sorting_desc:before,
    #subcategory-table thead .sorting:after,
    #subcategory-table thead .sorting_asc:after,
    #subcategory-table thead .sorting_desc:after {
        color: #ffffff !important;
    }
    #subcategory-table tbody td {
        padding: 11px 14px !important;
        vertical-align: middle;
        color: var(--pp-ink);
        border-bottom: 1px solid var(--pp-border);
        background: #fff;
        transition: background 0.15s;
    }
    #subcategory-table tbody tr:hover td {
        background: var(--pp-amber-soft) !important;
    }
    #subcategory-table tbody tr:nth-child(even) td {
        background: var(--pp-surface);
    }
    #subcategory-table tbody tr:nth-child(even):hover td {
        background: var(--pp-amber-soft) !important;
    }

    #subcategory-table .btn {
        padding: 5px 9px !important;
        font-size: 11px !important;
        border-radius: 7px !important;
        line-height: 1.3 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    #subcategory-table .btn + .btn {
        margin-left: 5px !important;
    }

    #subcategory-table .custom-switch {
        padding-left: 0 !important;
        margin: 0 !important;
        display: inline-flex;
        align-items: center;
    }
    #subcategory-table .custom-switch-indicator {
        border-radius: 16px !important;
        width: 32px !important;
        height: 18px !important;
        border: 1.5px solid #cbd5e1 !important;
        background-color: #e2e8f0;
        transition: all 0.2s ease !important;
        cursor: pointer;
    }
    #subcategory-table .custom-switch-indicator::after {
        width: 12px !important;
        height: 12px !important;
        top: 1.5px !important;
        left: 2px !important;
        transition: all 0.2s ease !important;
    }
    #subcategory-table .custom-switch-input:checked ~ .custom-switch-indicator {
        background: linear-gradient(135deg, var(--pp-teal), #2b7785) !important;
        border-color: var(--pp-teal) !important;
        box-shadow: 0 2px 6px -1px rgba(50, 138, 154, 0.4);
    }
    #subcategory-table .custom-switch-input:checked ~ .custom-switch-indicator::after {
        left: calc(100% - 14px) !important;
    }

    #subcategory-table_wrapper .dataTables_info {
        font-size: 11.5px;
        color: var(--pp-muted);
        font-weight: 500;
        padding-top: 12px;
    }
    #subcategory-table_wrapper .dataTables_paginate {
        padding-top: 12px;
    }
    #subcategory-table_wrapper .paginate_button {
        border-radius: 8px !important;
        border: 1px solid var(--pp-border) !important;
        margin: 0 2px;
        font-weight: 600;
        font-size: 11px;
        color: var(--pp-muted) !important;
        padding: 5px 10px !important;
        transition: all 0.2s cubic-bezier(.2,.8,.2,1);
        background: #fff !important;
    }
    #subcategory-table_wrapper .paginate_button:hover {
        border-color: var(--pp-teal) !important;
        background: var(--pp-teal-soft) !important;
        color: var(--pp-teal) !important;
    }
    #subcategory-table_wrapper .paginate_button.current {
        background: linear-gradient(145deg, var(--pp-teal), #256b77) !important;
        border-color: var(--pp-teal) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px -3px rgba(50, 138, 154, 0.45);
    }
    #subcategory-table_wrapper .paginate_button.disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* Tablet Responsive (768px - 991.98px) */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .pp-header { margin-top: 4px; margin-bottom: 16px; }
        .pp-header h1 { font-size: 18px; gap: 10px; }
        .pp-header h1 .pp-icon { width: 30px; height: 30px; min-width: 30px; font-size: 13px; }
        .pp-card { border-radius: 12px !important; }
        .pp-card-header { padding: 12px 16px !important; }
        .pp-table-container { padding: 12px 14px !important; overflow: visible !important; width: 100% !important; }
        #subcategory-table { width: 100% !important; min-width: 100% !important; }
        #subcategory-table thead th { font-size: 10.5px !important; padding: 10px 8px !important; }
        #subcategory-table tbody td { font-size: 11px !important; padding: 9px 8px !important; }
        #subcategory-table .btn { padding: 4px 8px !important; font-size: 10.5px !important; }
        #subcategory-table .btn + .btn { margin-left: 3px !important; }
        #subcategory-table_wrapper .dataTables_length, #subcategory-table_wrapper .dataTables_filter { padding: 0 4px; margin-bottom: 10px; }
        #subcategory-table_wrapper .dataTables_filter input { max-width: 170px; }
        #subcategory-table_wrapper .dataTables_info, #subcategory-table_wrapper .dataTables_paginate { padding: 8px 4px 0; font-size: 11px !important; }
        #subcategory-table_wrapper .paginate_button { font-size: 10.5px !important; padding: 4px 8px !important; }
    }

    /* Mobile Responsive (< 768px) */
    @media (max-width: 767.98px) {
        .pp-header { flex-direction: column; align-items: flex-start; gap: 10px; margin-top: 4px; margin-bottom: 14px; }
        .pp-header h1 { font-size: 16px; gap: 8px; }
        .pp-header h1 .pp-icon { width: 28px; height: 28px; min-width: 28px; font-size: 12px; }
        .pp-card-header { flex-direction: column; align-items: flex-start; gap: 8px; padding: 12px 14px !important; }
        .pp-table-container { padding: 10px 10px !important; overflow: visible !important; }
        #subcategory-table { width: 100% !important; min-width: 580px !important; table-layout: auto !important; }
        #subcategory-table thead th { font-size: 9.5px !important; padding: 8px 5px !important; white-space: nowrap !important; }
        #subcategory-table tbody td { font-size: 10.5px !important; padding: 8px 5px !important; white-space: nowrap !important; }
        #subcategory-table .btn { padding: 4px 7px !important; font-size: 10px !important; }
        #subcategory-table_wrapper .dataTables_length, #subcategory-table_wrapper .dataTables_filter { float: none !important; text-align: left !important; padding: 0 4px; }
        #subcategory-table_wrapper .dataTables_length { margin-bottom: 8px; }
        #subcategory-table_wrapper .dataTables_filter input { max-width: 100%; width: 100%; margin-left: 0 !important; }
        #subcategory-table_wrapper .dataTables_info, #subcategory-table_wrapper .dataTables_paginate { padding: 8px 4px 0; text-align: left; }
        #subcategory-table_wrapper .paginate_button { font-size: 10px !important; padding: 4px 7px !important; margin: 0 1px; }
        #subcategory-table_wrapper .dataTables_paginate .pagination { flex-wrap: wrap !important; gap: 2px; margin-bottom: 0; }
    }
</style>
@endpush

@section('content')
    <section class="section">
        <div class="pp-header">
            <h1>
                <span class="pp-icon"><i class="fas fa-project-diagram"></i></span>
                Sub Category
            </h1>
            <div>
                <a href="{{ route('admin.sub-category.create') }}" class="btn pp-btn pp-btn-amber shadow-sm">
                    <i class="fas fa-plus mr-1"></i> Create New
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card pp-card">
                        <div class="card-header pp-card-header">
                            <h4><i class="fas fa-list-ul"></i> All Sub Categories</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="pp-table-container">
                                {{ $dataTable->table() }}
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
        $(document).ready(function(){
            $('body').on('click', '.change-status', function(){
                let isChecked = $(this).is(':checked');
                let id = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.subcategory.change-status') }}",
                    method: 'PUT',
                    data: {
                        status: isChecked,
                        id: id
                    },
                    success: function(data){
                        toastr.success(data.message)
                    },
                    error: function(xhr, status, error){
                        console.log(error);
                    }
                })
            })
        })
    </script>
@endpush
