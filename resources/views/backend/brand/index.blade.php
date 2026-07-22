@extends('backend.layouts.master')
@section('title', $settings->site_name . ' | Brand')

@push('css')
<style>
    :root {
        --pp-amber: #d4a24e;
        --pp-amber-bright: #ecc78b;
        --pp-amber-deep: #b8852a;
        --pp-amber-soft: rgba(212, 162, 78, 0.08);
        --pp-border: rgba(11, 17, 32, 0.07);
        --pp-border-hover: rgba(212, 162, 78, 0.15);
        --pp-ink: #161e2e;
        --pp-ink-soft: #2d3748;
        --pp-muted: #6b788e;
        --pp-surface: #f8f9fc;
        --pp-radius-md: 14px;
        --pp-radius-lg: 20px;
        --pp-shadow-card: 0 1px 3px rgba(11,17,32,0.04), 0 8px 20px -12px rgba(11,17,32,0.12);
        --pp-shadow-card-hover: 0 12px 32px -12px rgba(11,17,32,0.16), 0 0 0 1px rgba(212,162,78,0.06);
    }

    .pp-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
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

    #category-table_wrapper .dataTables_length label,
    #category-table_wrapper .dataTables_filter label {
        font-size: 12px;
        font-weight: 600;
        color: var(--pp-muted);
    }
    #category-table_wrapper .dataTables_length select,
    #category-table_wrapper .dataTables_filter input {
        border-radius: 10px !important;
        border: 1.5px solid var(--pp-border) !important;
        padding: 4px 10px !important;
        font-size: 12px !important;
        background: #fff !important;
        transition: all 0.2s ease;
    }
    #category-table_wrapper .dataTables_length select:focus,
    #category-table_wrapper .dataTables_filter input:focus {
        border-color: var(--pp-amber) !important;
        box-shadow: 0 0 0 3px var(--pp-amber-soft) !important;
        outline: none;
    }
    #category-table {
        font-size: 12px !important;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
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
        background: var(--pp-amber-soft);
    }
    #category-table tbody tr:nth-child(even) td {
        background: var(--pp-surface);
    }
    #category-table tbody tr:nth-child(even):hover td {
        background: var(--pp-amber-soft);
    }
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
        border-radius: 10px !important;
        border: 1px solid var(--pp-border) !important;
        margin: 0 2px;
        font-weight: 600;
        font-size: 11px;
        color: var(--pp-muted) !important;
        padding: 6px 11px !important;
        transition: all 0.2s cubic-bezier(.2,.8,.2,1);
        background: #fff !important;
    }
    #category-table_wrapper .paginate_button:hover {
        border-color: var(--pp-amber) !important;
        background: var(--pp-amber-soft) !important;
        color: var(--pp-amber-deep) !important;
    }
    #category-table_wrapper .paginate_button.current {
        background: linear-gradient(145deg, var(--pp-amber-bright), var(--pp-amber)) !important;
        border-color: var(--pp-amber) !important;
        color: #1a1306 !important;
        box-shadow: 0 4px 14px -3px rgba(212, 162, 78, 0.35);
    }
    #category-table_wrapper .paginate_button.disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .pp-status-switch .custom-switch-indicator {
        border-radius: 16px !important;
        width: 31px !important;
        height: 17px !important;
        transition: all 0.25s ease !important;
        border: 1px solid var(--pp-border);
    }
    .pp-status-switch .custom-switch-indicator::after {
        width: 13px !important;
        height: 13px !important;
        top: 2px !important;
        left: 2px !important;
        transition: all 0.25s ease !important;
    }
    .pp-status-switch .custom-switch-input:checked ~ .custom-switch-indicator {
        background: linear-gradient(135deg, var(--pp-amber-bright), var(--pp-amber)) !important;
        border-color: var(--pp-amber) !important;
        box-shadow: 0 2px 8px -2px rgba(212, 162, 78, 0.3);
    }

    @media (max-width: 767.98px) {
        .pp-header { flex-direction: column; align-items: flex-start; gap: 8px; }
        .pp-header h1 { font-size: 16px; gap: 8px; }
        .pp-header h1 .pp-icon { width: 28px; height: 28px; min-width: 28px; font-size: 12px; }
        .pp-card-header { flex-direction: column; align-items: flex-start; gap: 8px; }
        #category-table thead th { font-size: 9px; padding: 8px 8px !important; white-space: nowrap; }
        #category-table tbody td { font-size: 10px; padding: 7px 8px !important; }
        #category-table_wrapper .dataTables_length,
        #category-table_wrapper .dataTables_filter { float: none !important; text-align: left !important; padding: 0 10px; }
        #category-table_wrapper .dataTables_length { margin-bottom: 6px; }
        #category-table_wrapper .dataTables_filter input { max-width: 160px; }
        #category-table_wrapper .dataTables_info { font-size: 10px; padding: 8px 10px 0; text-align: left; }
        #category-table_wrapper .dataTables_paginate { padding: 8px 10px 0; text-align: left; }
        #category-table_wrapper .paginate_button { font-size: 10px !important; padding: 4px 8px !important; margin: 0 1px; }
    }
</style>
@endpush

@section('content')
    <section class="section">
        <div class="pp-header">
            <h1>
                <span class="pp-icon"><i class="fas fa-tag"></i></span>
                Brands
            </h1>
            <div>
                <a href="{{ route('admin.brand.create') }}" class="btn pp-btn pp-btn-amber shadow-sm">
                    <i class="fas fa-plus mr-1"></i> Create New
                </a>
            </div>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card pp-card">
                        <div class="pp-card-header">
                            <h4><i class="fas fa-list"></i> All Brands</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                {{ $dataTable->table(['class' => 'table', 'id' => 'category-table']) }}
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
            $('body').on('click', '.change-status', function() {
                let isChecked = $(this).is(':checked');
                let id = $(this).data('id');
                $.ajax({
                    url: "{{ route('admin.brand.change-status') }}",
                    method: 'put',
                    data: {
                        id: id,
                        status: isChecked
                    },
                    success: function(data) {
                        toastr.success(data.message)
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                })
            })
        })
    </script>
@endpush