@extends('backend.layouts.master')
@section('title', 'Supplier Ledger & Accounts Payable Summary')

@section('content')
<section class="section">
    <style>
        /* Modern Executive Styling */
        .page-header-container {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 22px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }
        .header-icon-box {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            border: 1px solid #bfdbfe;
            flex-shrink: 0;
        }
        
        /* Modern 3-Column Responsive KPI Cards */
        .kpi-row {
            margin-bottom: 22px;
        }
        .modern-kpi-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 20px 22px;
            position: relative;
            overflow: hidden;
            transition: all 0.25s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }
        .modern-kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }
        .kpi-top-stripe {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        .kpi-icon-circle {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .kpi-value {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.025em;
            margin: 8px 0 4px 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        /* Modern Table Card & Filter Toolbar */
        .master-table-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
        }
        .table-toolbar-header {
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            padding: 16px 22px;
        }

        /* Responsive Segmented Pill Switcher */
        .segmented-filter-nav {
            background: #f1f5f9;
            border-radius: 10px;
            padding: 4px;
            border: 1px solid #e2e8f0;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            max-width: 100%;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }
        .segmented-btn {
            border: none;
            background: transparent;
            padding: 6px 14px;
            font-weight: 600;
            font-size: 12.5px;
            border-radius: 7px;
            color: #64748b;
            transition: all 0.18s ease;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            text-decoration: none !important;
            white-space: nowrap;
        }
        .segmented-btn:hover {
            color: #0f172a;
        }
        .segmented-btn.active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            font-weight: 700;
        }
        .filter-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        .filter-count-badge {
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 12px;
            font-weight: 700;
        }

        /* Action Buttons */
        .btn-action-pill {
            border-radius: 9px;
            font-size: 12.5px;
            font-weight: 600;
            padding: 7px 15px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            text-decoration: none !important;
        }
        .btn-action-pill:hover {
            transform: translateY(-1px);
        }

        /* Modern DataTables Refinements */
        #vendor-ledger-table_wrapper {
            padding: 8px 4px;
        }
        #vendor-ledger-table_wrapper .dataTables_filter input {
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 6px 12px;
            font-size: 13px;
            outline: none;
            transition: border-color 0.15s ease;
        }
        #vendor-ledger-table_wrapper .dataTables_filter input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        #vendor-ledger-table_wrapper .dataTables_length select {
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 5px 10px;
            font-size: 13px;
        }
        #vendor-ledger-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100% !important;
        }
        #vendor-ledger-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            padding: 13px 16px;
        }
        #vendor-ledger-table tbody td {
            padding: 13px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        #vendor-ledger-table tbody tr:hover {
            background-color: #fbfcfe;
        }
    </style>

    {{-- Modern Page Header with Breadcrumbs & Action Links --}}
    <div class="page-header-container d-flex justify-content-between align-items-center flex-wrap" style="gap: 16px;">
        <div class="d-flex align-items-center" style="gap: 16px;">
            <div class="header-icon-box">
                <i class="fas fa-book-open"></i>
            </div>
            <div>
                <h1 class="font-weight-bold text-dark mb-1" style="font-size: 22px; letter-spacing: -0.02em;">
                    Supplier Ledger & Accounts Payable (AP)
                </h1>
                <p class="text-muted mb-0" style="font-size: 13px;">
                    Consolidated vendor obligations, billed invoices, settlement statuses & account statements
                </p>
            </div>
        </div>

        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
            <a href="{{ route('admin.vendor-ledger.aging') }}" class="btn-action-pill" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">
                <i class="fas fa-clock"></i> AP Aging Analysis
            </a>
            <a href="{{ route('admin.accounts.vendor-payments.index') }}" class="btn-action-pill" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">
                <i class="fas fa-receipt"></i> Payment Vouchers
            </a>
        </div>
    </div>

    {{-- 3 Executive Responsive KPI Cards --}}
    <div class="row g-3 kpi-row">
        {{-- KPI 1: Total Outstanding Payables --}}
        <div class="col-12 col-md-4 mb-3 mb-md-0">
            <div class="modern-kpi-card">
                <div class="kpi-top-stripe" style="background: linear-gradient(90deg, #ef4444, #f87171);"></div>
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.06em; color: #64748b;">
                            Total Outstanding Payables
                        </span>
                        <div class="kpi-value" style="color: #dc2626;">
                            kr. {{ number_format($summary['total_payables'], 2) }}
                        </div>
                    </div>
                    <div class="kpi-icon-circle" style="background: #fef2f2; color: #ef4444; border: 1px solid #fee2e2;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center mt-2" style="font-size: 12px; color: #64748b;">
                    <i class="fas fa-exclamation-circle text-danger mr-1"></i> Across Unpaid & Partial Supplier Bills
                </div>
            </div>
        </div>

        {{-- KPI 2: Suppliers with Dues --}}
        <div class="col-12 col-md-4 mb-3 mb-md-0">
            <div class="modern-kpi-card">
                <div class="kpi-top-stripe" style="background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.06em; color: #64748b;">
                            Suppliers with Dues
                        </span>
                        <div class="kpi-value text-dark">
                            {{ number_format($summary['active_dues']) }} <span style="font-size: 14px; font-weight: 500; color: #64748b;">Vendors</span>
                        </div>
                    </div>
                    <div class="kpi-icon-circle" style="background: #fffbeb; color: #d97706; border: 1px solid #fef3c7;">
                        <i class="fas fa-user-clock"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center mt-2" style="font-size: 12px; color: #d97706; font-weight: 600;">
                    <i class="fas fa-hourglass-half mr-1"></i> Require Payment Clearance
                </div>
            </div>
        </div>

        {{-- KPI 3: Fully Settled Suppliers --}}
        <div class="col-12 col-md-4">
            <div class="modern-kpi-card">
                <div class="kpi-top-stripe" style="background: linear-gradient(90deg, #10b981, #34d399);"></div>
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.06em; color: #64748b;">
                            Fully Settled Suppliers
                        </span>
                        <div class="kpi-value text-dark">
                            {{ number_format($summary['settled_vendors']) }} <span style="font-size: 14px; font-weight: 500; color: #64748b;">/ {{ number_format($summary['total_vendors']) }}</span>
                        </div>
                    </div>
                    <div class="kpi-icon-circle" style="background: #ecfdf5; color: #059669; border: 1px solid #d1fae5;">
                        <i class="fas fa-check-double"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center mt-2" style="font-size: 12px; color: #059669; font-weight: 600;">
                    <i class="fas fa-check-circle mr-1"></i> 100% Zero Outstanding Balance
                </div>
            </div>
        </div>
    </div>

    {{-- Master Table Card --}}
    <div class="master-table-card">
        {{-- Sleek Header Toolbar with Integrated Segmented Filter --}}
        <div class="table-toolbar-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 14px;">
            <div class="d-flex align-items-center" style="gap: 10px;">
                <span class="d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; border-radius: 8px; background: #eff6ff; color: #2563eb; font-size: 15px;">
                    <i class="fas fa-list"></i>
                </span>
                <div>
                    <h5 class="font-weight-bold text-dark mb-0" style="font-size: 15.5px; letter-spacing: -0.01em;">
                        Supplier Payables Directory
                    </h5>
                    <small class="text-muted">Real-time balances, billed grand totals & running statements</small>
                </div>
            </div>

            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                {{-- Sleek Segmented Switcher (Zero-Reload Live Filter) --}}
                <div class="segmented-filter-nav">
                    <button type="button" class="segmented-btn active" data-filter="has_due">
                        <span class="filter-status-dot" style="background: #ef4444;"></span>
                        Has Dues
                        <span class="filter-count-badge bg-danger text-white">{{ $summary['active_dues'] }}</span>
                    </button>
                    <button type="button" class="segmented-btn" data-filter="settled">
                        <span class="filter-status-dot" style="background: #10b981;"></span>
                        Settled
                        <span class="filter-count-badge bg-success text-white">{{ $summary['settled_vendors'] }}</span>
                    </button>
                    <button type="button" class="segmented-btn" data-filter="">
                        All Suppliers
                        <span class="filter-count-badge bg-secondary text-white">{{ $summary['total_vendors'] }}</span>
                    </button>
                </div>

                {{-- Hidden input for DataTables request compatibility --}}
                <input type="hidden" id="balance_filter" value="has_due">
            </div>
        </div>

        {{-- Table Body --}}
        <div class="p-3 p-md-4 table-responsive">
            {{ $dataTable->table(['class' => 'table table-hover align-middle w-100 mb-0']) }}
        </div>
    </div>
</section>
@endsection

@push('scripts')
{{ $dataTable->scripts(attributes: ['type' => 'module']) }}
<script>
    $(document).ready(function() {
        const table = window.LaravelDataTables["vendor-ledger-table"];

        // Handle Segmented Filter Switcher
        $('.segmented-btn').on('click', function(e) {
            e.preventDefault();
            $('.segmented-btn').removeClass('active');
            $(this).addClass('active');

            const filterVal = $(this).data('filter');
            $('#balance_filter').val(filterVal);

            if (table) {
                table.draw();
            }
        });

        if (table) {
            table.on('preXhr.dt', function(e, settings, data) {
                data.balance_filter = $('#balance_filter').val();
            });
        }
    });
</script>
@endpush
