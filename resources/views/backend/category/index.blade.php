@extends('backend.layouts.master')
@section('title', $settings->site_name . ' | Category')

@push('css')
<style>
    :root {
        --pp-blue: #2563eb;
        --pp-blue-bright: #60a5fa;
        --pp-blue-deep: #1d4ed8;
        --pp-blue-soft: rgba(37, 99, 235, 0.08);
        --pp-teal: #328A9A;
        --pp-teal-soft: rgba(50, 138, 154, 0.12);
        --pp-teal-glow: rgba(50, 138, 154, 0.25);
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

    /* ===== Section Header ===== */
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
        color: #ffffff;
        background: linear-gradient(145deg, var(--pp-blue-bright), var(--pp-blue));
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35), inset 0 1px 0 rgba(255,255,255,0.3);
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
    .pp-btn-blue {
        background: linear-gradient(145deg, var(--pp-blue-bright), var(--pp-blue-deep)) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px -4px rgba(37, 99, 235, 0.45), inset 0 1px 0 rgba(255,255,255,0.25);
    }
    .pp-btn-blue:hover { filter: brightness(1.06); box-shadow: 0 8px 24px -6px rgba(37, 99, 235, 0.5); }

    /* ===== Card ===== */
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
    .pp-card-header h4 i { color: var(--pp-blue); }

    /* ===== DataTables Controls ===== */
    #category-table_wrapper {
        padding: 4px 0 0;
    }
    #category-table_wrapper .dataTables_length label,
    #category-table_wrapper .dataTables_filter label {
        font-size: 12px;
        font-weight: 600;
        color: var(--pp-muted);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    #category-table_wrapper .dataTables_length select,
    #category-table_wrapper .dataTables_filter input {
        border-radius: 8px !important;
        border: 1.5px solid #dbeafe !important;
        padding: 5px 10px !important;
        font-size: 12px !important;
        background: #fff !important;
        transition: all 0.2s ease;
    }
    #category-table_wrapper .dataTables_length select:focus,
    #category-table_wrapper .dataTables_filter input:focus {
        border-color: var(--pp-teal) !important;
        box-shadow: 0 0 0 3px var(--pp-teal-soft) !important;
        outline: none;
    }

    /* ===== Table Styling ===== */
    #category-table {
        font-size: 12px !important;
        border-collapse: separate;
        border-spacing: 0;
        width: 100% !important;
        margin-bottom: 0 !important;
    }
    #category-table thead th {
        background: linear-gradient(135deg, #fafbfc, #f4f5f8);
        color: var(--pp-ink-soft) !important;
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
    #category-table tbody td {
        padding: 11px 14px !important;
        vertical-align: middle;
        color: var(--pp-ink);
        border-bottom: 1px solid var(--pp-border);
        background: #fff;
        transition: background 0.15s;
    }
    #category-table tbody tr:hover td {
        background: var(--pp-blue-soft) !important;
    }
    #category-table tbody tr:nth-child(even) td {
        background: var(--pp-surface);
    }
    #category-table tbody tr:nth-child(even):hover td {
        background: var(--pp-blue-soft) !important;
    }

    /* Action Buttons Inside Category Table */
    #category-table .btn {
        padding: 5px 9px !important;
        font-size: 11px !important;
        border-radius: 7px !important;
        line-height: 1.3 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    #category-table .btn + .btn {
        margin-left: 5px !important;
    }

    /* GS1 GPC Code Badge */
    #category-table .badge {
        font-weight: 700;
        border-radius: 6px;
        letter-spacing: 0.3px;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }

    /* Switch Indicator */
    #category-table .custom-switch {
        padding-left: 0 !important;
        margin: 0 !important;
        display: inline-flex;
        align-items: center;
    }
    #category-table .custom-switch-indicator {
        border-radius: 16px !important;
        width: 32px !important;
        height: 18px !important;
        border: 1.5px solid #cbd5e1 !important;
        background-color: #e2e8f0;
        transition: all 0.2s ease !important;
        cursor: pointer;
    }
    #category-table .custom-switch-indicator::after {
        width: 12px !important;
        height: 12px !important;
        top: 1.5px !important;
        left: 2px !important;
        transition: all 0.2s ease !important;
    }
    #category-table .custom-switch-input:checked ~ .custom-switch-indicator {
        background: linear-gradient(135deg, var(--pp-teal), #2b7785) !important;
        border-color: var(--pp-teal) !important;
        box-shadow: 0 2px 6px -1px rgba(50, 138, 154, 0.4);
    }
    #category-table .custom-switch-input:checked ~ .custom-switch-indicator::after {
        left: calc(100% - 14px) !important;
    }

    /* Pagination & Info */
    #category-table_wrapper .dataTables_info {
        font-size: 11.5px;
        color: var(--pp-muted);
        font-weight: 500;
        padding-top: 12px;
    }
    #category-table_wrapper .dataTables_paginate {
        padding-top: 12px;
    }
    #category-table_wrapper .paginate_button {
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
    #category-table_wrapper .paginate_button:hover {
        border-color: var(--pp-teal) !important;
        background: var(--pp-teal-soft) !important;
        color: var(--pp-teal) !important;
    }
    #category-table_wrapper .paginate_button.current {
        background: linear-gradient(145deg, var(--pp-teal), #256b77) !important;
        border-color: var(--pp-teal) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px -3px rgba(50, 138, 154, 0.45);
    }
    #category-table_wrapper .paginate_button.disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* ========================================================
       TABLET RESPONSIVE (iPad / Android Tablet: 768px - 991.98px)
       ======================================================== */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .pp-header {
            margin-top: 4px;
            margin-bottom: 16px;
        }
        .pp-header h1 {
            font-size: 18px;
            gap: 10px;
        }
        .pp-header h1 .pp-icon {
            width: 30px;
            height: 30px;
            min-width: 30px;
            font-size: 13px;
        }

        /* Card and Table container on Tablet */
        .pp-card {
            border-radius: 12px !important;
        }
        .pp-card-header {
            padding: 12px 16px !important;
        }
        .pp-table-container {
            padding: 12px 14px !important;
            overflow: visible !important;
            width: 100% !important;
        }

        /* Ensure table fits inside tablet card without clipping or horizontal overflow */
        #category-table {
            width: 100% !important;
            min-width: 100% !important;
            table-layout: auto !important;
        }
        #category-table thead th {
            font-size: 10.5px !important;
            padding: 10px 8px !important;
            letter-spacing: 0.2px;
        }
        #category-table tbody td {
            font-size: 11px !important;
            padding: 9px 8px !important;
        }

        /* Action Buttons on Tablet: compact and aligned */
        #category-table .btn {
            padding: 4px 8px !important;
            font-size: 10.5px !important;
        }
        #category-table .btn + .btn {
            margin-left: 3px !important;
        }

        /* GPC Code badge on Tablet */
        #category-table .badge {
            font-size: 11px !important;
            padding: 3px 6px !important;
        }

        /* DataTable controls on Tablet */
        #category-table_wrapper .dataTables_length,
        #category-table_wrapper .dataTables_filter {
            padding: 0 4px;
            margin-bottom: 10px;
        }
        #category-table_wrapper .dataTables_filter input {
            max-width: 170px;
        }
        #category-table_wrapper .dataTables_info,
        #category-table_wrapper .dataTables_paginate {
            padding: 8px 4px 0;
            font-size: 11px !important;
        }
        #category-table_wrapper .paginate_button {
            font-size: 10.5px !important;
            padding: 4px 8px !important;
        }
    }

    /* ========================================================
       MOBILE RESPONSIVE (< 768px: Mobile Phones)
       ======================================================== */
    @media (max-width: 767.98px) {
        .pp-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
            margin-top: 4px;
            margin-bottom: 14px;
        }
        .pp-header h1 {
            font-size: 16px;
            gap: 8px;
        }
        .pp-header h1 .pp-icon {
            width: 28px;
            height: 28px;
            min-width: 28px;
            font-size: 12px;
        }
        .pp-card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            padding: 12px 14px !important;
        }
        .pp-table-container {
            padding: 10px 10px !important;
            overflow: visible !important;
            width: 100% !important;
        }

        /* Table scroll on mobile phones */
        #category-table {
            width: 100% !important;
            min-width: 540px !important;
            table-layout: auto !important;
        }
        #category-table thead th {
            font-size: 9.5px !important;
            padding: 8px 8px !important;
            white-space: nowrap;
        }
        #category-table tbody td {
            font-size: 10.5px !important;
            padding: 8px 8px !important;
        }
        #category-table .btn {
            padding: 4px 7px !important;
            font-size: 10px !important;
        }
        #category-table .badge {
            font-size: 10.5px !important;
            padding: 3px 5px !important;
        }

        /* Stacking controls on mobile */
        #category-table_wrapper .dataTables_length,
        #category-table_wrapper .dataTables_filter {
            float: none !important;
            text-align: left !important;
            padding: 0 4px;
        }
        #category-table_wrapper .dataTables_length {
            margin-bottom: 8px;
        }
        #category-table_wrapper .dataTables_filter input {
            max-width: 100%;
            width: 100%;
            margin-left: 0 !important;
        }
        #category-table_wrapper .dataTables_info {
            font-size: 10.5px;
            padding: 8px 4px 0;
            text-align: left;
        }
        #category-table_wrapper .dataTables_paginate {
            padding: 8px 4px 0;
            text-align: left;
        }
        #category-table_wrapper .paginate_button {
            font-size: 10px !important;
            padding: 4px 7px !important;
            margin: 0 1px;
        }
    }
</style>
@endpush

@section('content')
    <section class="section">
        {{-- Modern Section Header with Action Button --}}
        <div class="pp-header">
            <h1>
                <span class="pp-icon"><i class="fas fa-sitemap"></i></span>
                Category
            </h1>
            <div>
                <a href="{{ route('admin.category.create') }}" class="btn pp-btn pp-btn-blue shadow-sm">
                    <i class="fas fa-plus mr-1"></i> Create New
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card pp-card">
                        <div class="card-header pp-card-header">
                            <h4><i class="fas fa-list-ul"></i> All Categories</h4>
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
                    url: "{{ route('admin.category.change-status') }}",
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

            $('body').on('click', '.change-frontend-show', function(){
                let isChecked = $(this).is(':checked');
                let id = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.category.change-frontend-show') }}",
                    method: 'PUT',
                    data: {
                        frontend_show: isChecked,
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
