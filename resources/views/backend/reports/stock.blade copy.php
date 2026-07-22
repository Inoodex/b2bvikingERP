@extends('backend.layouts.master')
@section('title', $settings->site_name . ' | Stock Report')

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
    .pp-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        font-weight: 600;
    }
    .pp-breadcrumb-item {
        color: var(--pp-muted);
        padding-right: 14px;
        position: relative;
    }
    .pp-breadcrumb-item + .pp-breadcrumb-item { padding-left: 14px; }
    .pp-breadcrumb-item + .pp-breadcrumb-item::before {
        content: '/';
        position: absolute;
        left: 0;
        color: rgba(11, 17, 32, 0.15);
    }
    .pp-breadcrumb-item a { color: var(--pp-amber); text-decoration: none; transition: color 0.2s; }
    .pp-breadcrumb-item a:hover { color: var(--pp-amber-deep); }
    .pp-breadcrumb-item.active { color: var(--pp-amber-deep); }

    /* Stat Cards */
    .pp-stat-card {
        border-radius: var(--pp-radius-md) !important;
        border: 1px solid var(--pp-border) !important;
        background: #f8f9fc !important;
        box-shadow: var(--pp-shadow-card) !important;
        transition: all 0.3s cubic-bezier(.2,.8,.2,1) !important;
        overflow: hidden;
        padding: 0 !important;
        height: 100%;
    }
    .pp-stat-card:hover {
        box-shadow: var(--pp-shadow-card-hover) !important;
        border-color: var(--pp-border-hover) !important;
        transform: translateY(-3px);
    }
    .pp-stat-card .card-statistic-1 { border: none !important; box-shadow: none !important; margin-bottom: 0 !important; height: 100%; }
    .pp-stat-card .card-statistic-1 .card-wrap { height: 100%; display: flex; flex-direction: column; justify-content: center; }
    .pp-stat-card .card-statistic-1 .card-icon {
        border-radius: 10px !important;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
    }
    .pp-stat-card .card-statistic-1 .card-icon.bg-primary { background: linear-gradient(145deg, #6366f1, #4f46e5) !important; }
    .pp-stat-card .card-statistic-1 .card-icon.bg-success { background: linear-gradient(145deg, #34d399, #16a34a) !important; }
    .pp-stat-card .card-statistic-1 .card-icon.bg-info { background: linear-gradient(145deg, #38bdf8, #0284c7) !important; }
    .pp-stat-card .card-statistic-1 .card-icon.bg-warning { background: linear-gradient(145deg, var(--pp-amber-bright), var(--pp-amber-deep)) !important; }
    .pp-stat-card .card-statistic-1 .card-header h4 {
        font-weight: 600;
        font-size: 10px;
        color: var(--pp-muted);
        letter-spacing: 0.2px;
        padding: 0;
    }
    .pp-stat-card .card-statistic-1 .card-body {
        font-weight: 800;
        font-size: 14px;
        color: var(--pp-ink);
        padding: 4px 0 0;
    }
    .pp-stat-card .card-statistic-1 .card-wrap { padding: 8px 12px 8px 8px; }
    .pp-stat-card .card-statistic-1 { display: flex; align-items: center; }
    .pp-stat-card .card-statistic-1 .card-icon { margin: 8px 0 8px 10px; }

    /* Filter Card */
    .pp-filter-card {
        border-radius: var(--pp-radius-md) !important;
        border: 1px solid var(--pp-border) !important;
        background: #fff !important;
        box-shadow: var(--pp-shadow-card) !important;
        overflow: hidden;
    }
    .pp-filter-card .card-header {
        padding: 12px 20px !important;
        background: linear-gradient(135deg, #fafbfc, #f4f5f8);
        border-bottom: 1px solid var(--pp-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pp-filter-card .card-header h4 {
        font-weight: 800;
        font-size: 13px;
        color: var(--pp-ink);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pp-filter-card .card-header h4 i { color: var(--pp-amber); }
    .pp-filter-card .card-body { padding: 16px 20px !important; }
    .pp-filter-card label {
        font-weight: 700;
        font-size: 11.5px;
        color: var(--pp-ink-soft);
        margin-bottom: 4px;
    }

    /* DataTable styling */
    .pp-table-card {
        border-radius: var(--pp-radius-md) !important;
        border: 1px solid var(--pp-border) !important;
        background: #fff !important;
        box-shadow: var(--pp-shadow-card) !important;
        overflow: hidden;
    }
    .pp-table-card .card-header {
        padding: 12px 20px !important;
        background: linear-gradient(135deg, #fafbfc, #f4f5f8);
        border-bottom: 1px solid var(--pp-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }
    .pp-table-card .card-header h4 {
        font-weight: 800;
        font-size: 13px;
        color: var(--pp-ink);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pp-table-card .card-header h4 i { color: var(--pp-amber); }
    .pp-table-card .card-body { padding: 0 !important; }

    #table-stock {
        font-size: 12px !important;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        margin: 0;
    }
    #table-stock thead th {
        background: linear-gradient(135deg, #fafbfc, #f4f5f8);
        color: var(--pp-ink-soft) !important;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 10px 14px !important;
        border: none !important;
        border-bottom: 2px solid var(--pp-border) !important;
    }
    #table-stock tbody td {
        padding: 10px 14px !important;
        vertical-align: middle;
        color: var(--pp-ink);
        border-bottom: 1px solid var(--pp-border);
        background: #fff;
        transition: background 0.15s;
    }
    #table-stock tbody tr:hover td { background: var(--pp-amber-soft); }
    #table-stock tbody tr:nth-child(even) td { background: var(--pp-surface); }
    #table-stock tbody tr:nth-child(even):hover td { background: var(--pp-amber-soft); }
    #table-stock tfoot td {
        padding: 10px 14px !important;
        background: var(--pp-surface);
        font-weight: 700;
        font-size: 12px;
        border-top: 2px solid var(--pp-border);
    }

    .pp-btn-sm {
        border: none !important;
        font-weight: 700 !important;
        font-size: 10.5px !important;
        border-radius: var(--pp-radius-lg) !important;
        padding: 6px 14px !important;
        transition: all 0.25s cubic-bezier(.2,.8,.2,1);
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .pp-btn-sm:hover { transform: translateY(-1.5px); }
    .pp-btn-indigo { background: linear-gradient(145deg, #6366f1, #4f46e5) !important; color: #fff !important; box-shadow: 0 4px 12px -4px rgba(99,102,241,0.3); }
    .pp-btn-indigo:hover { filter: brightness(1.06); box-shadow: 0 6px 18px -4px rgba(99,102,241,0.4); }
    .pp-btn-emerald { background: linear-gradient(145deg, #34d399, #16a34a) !important; color: #fff !important; box-shadow: 0 4px 12px -4px rgba(22,163,74,0.3); }
    .pp-btn-emerald:hover { filter: brightness(1.06); box-shadow: 0 6px 18px -4px rgba(22,163,74,0.4); }
    .pp-btn-rose { background: linear-gradient(145deg, #fb7185, #e11d48) !important; color: #fff !important; box-shadow: 0 4px 12px -4px rgba(225,29,72,0.3); }
    .pp-btn-rose:hover { filter: brightness(1.06); box-shadow: 0 6px 18px -4px rgba(225,29,72,0.4); }

    .pp-select2 .select2-selection--single {
        height: 36px !important;
        border-radius: var(--pp-radius-lg) !important;
        border: 1.5px solid var(--pp-border) !important;
        background: #fff !important;
        display: flex !important;
        align-items: center;
        transition: all 0.25s ease;
    }
    .pp-select2 .select2-selection--single .select2-selection__rendered {
        color: var(--pp-ink);
        font-size: 12px;
        font-weight: 500;
        padding-left: 14px;
        line-height: 34px;
    }
    .pp-select2 .select2-selection--single .select2-selection__arrow { height: 34px; right: 10px; }
    .pp-select2 .select2-selection--single .select2-selection__arrow b {
        border-color: var(--pp-amber) transparent transparent transparent !important;
        border-width: 4px 4px 0 !important;
    }
    .pp-select2.select2-container--open .select2-selection--single,
    .pp-select2.select2-container--focus .select2-selection--single {
        border-color: var(--pp-amber) !important;
        box-shadow: 0 0 0 3px var(--pp-amber-soft) !important;
    }
    .pp-select2 .select2-dropdown {
        border-radius: var(--pp-radius-sm) !important;
        border: 1px solid var(--pp-border) !important;
        box-shadow: 0 20px 50px -16px rgba(11,17,32,0.2) !important;
        overflow: hidden;
        margin-top: 4px;
    }
    .pp-select2 .select2-results__option {
        font-size: 12px !important;
        padding: 8px 12px !important;
        transition: background 0.15s;
    }
    .pp-select2 .select2-results__option--highlighted[aria-selected] {
        background: linear-gradient(135deg, var(--pp-amber-bright), var(--pp-amber)) !important;
        color: #1a1306 !important;
    }

    .pp-pagination .pagination .page-link {
        border-radius: 10px !important;
        border: 1px solid var(--pp-border) !important;
        margin: 0 2px;
        font-weight: 600;
        font-size: 11px;
        color: var(--pp-muted);
        padding: 6px 11px;
        transition: all 0.2s cubic-bezier(.2,.8,.2,1);
        background: #fff;
    }
    .pp-pagination .pagination .page-link:hover {
        border-color: var(--pp-amber) !important;
        background: var(--pp-amber-soft) !important;
        color: var(--pp-amber-deep) !important;
    }
    .pp-pagination .pagination .page-item.active .page-link {
        background: linear-gradient(145deg, var(--pp-amber-bright), var(--pp-amber)) !important;
        border-color: var(--pp-amber) !important;
        color: #1a1306 !important;
        box-shadow: 0 4px 14px -3px rgba(212, 162, 78, 0.35);
    }

    .pp-info-text {
        font-size: 13px;
        font-weight: 500;
        color: var(--pp-muted);
        text-align: center;
        padding: 12px 0 4px;
    }

    @media (max-width: 767.98px) {
        .pp-header { flex-direction: column; align-items: flex-start; gap: 8px; }
        .pp-header h1 { font-size: 16px; gap: 8px; }
        .pp-header h1 .pp-icon { width: 28px; height: 28px; min-width: 28px; font-size: 12px; }
        .pp-stat-card .card-statistic-1 .card-body { font-size: 13px; }
        .pp-filter-card .card-body { padding: 12px 14px !important; }
        .pp-filter-card .col-md-5 { margin-bottom: 8px; }
        .pp-filter-card .col-md-5:last-of-type { margin-bottom: 0; }
        .pp-filter-card .col-md-2 { margin-top: 0 !important; }
        .pp-filter-card .col-md-2 .btn { width: 100%; }
        .pp-table-card .card-header { flex-direction: column; align-items: flex-start; }
        .pp-table-card .card-header .card-header-action { display: flex; flex-wrap: wrap; gap: 6px; width: 100%; }
        .pp-table-card .card-header .card-header-action .pp-btn-sm { flex: 1; justify-content: center; font-size: 9.5px !important; padding: 5px 10px !important; }
        #table-stock thead th { font-size: 9px; padding: 7px 8px !important; white-space: nowrap; }
        #table-stock tbody td { font-size: 10px; padding: 7px 8px !important; }
        #table-stock tfoot td { font-size: 10px; padding: 7px 8px !important; }
        .pp-pagination .pagination .page-link { font-size: 9px !important; padding: 4px 8px !important; margin: 0 1px; }
        .pp-info-text { font-size: 11px; }
    }
</style>
@endpush

@section('content')
    <section class="section">
        <div class="pp-header">
            <h1>
                <span class="pp-icon"><i class="fas fa-chart-pie"></i></span>
                Stock Report
            </h1>
            <div class="pp-breadcrumb">
                <span class="pp-breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home mr-1"></i>Dashboard</a></span>
                <span class="pp-breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></span>
                <span class="pp-breadcrumb-item active">Stock</span>
            </div>
        </div>

        <div class="section-body">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-2 d-flex">
                    <div class="card pp-stat-card">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary"><i class="fas fa-boxes"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Total Stock Qty</h4></div>
                                <div class="card-body"><span id="span-total-qty">{{ number_format($totalQty) }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-2 d-flex">
                    <div class="card pp-stat-card">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success"><i class="fas fa-dollar-sign"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Total Asset Value</h4></div>
                                <div class="card-body"><span id="span-total-value">{{ $settings->currency_icon }}{{ number_format($totalValue, 2) }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-2 d-flex">
                    <div class="card pp-stat-card">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info"><i class="fas fa-tags"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Potential Revenue</h4></div>
                                <div class="card-body"><span id="span-potential-revenue">{{ $settings->currency_icon }}{{ number_format($potentialRevenue, 2) }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-2 d-flex">
                    <div class="card pp-stat-card">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning"><i class="fas fa-chart-line"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Potential Profit</h4></div>
                                <div class="card-body"><span id="span-potential-profit">{{ $settings->currency_icon }}{{ number_format($potentialProfit, 2) }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card pp-filter-card mb-4">
                <div class="card-header">
                    <h4><i class="fas fa-filter"></i> Filter Options</h4>
                    <div class="card-header-action">
                        <a data-collapse="#mycard-collapse" class="btn btn-sm" href="#" style="background: var(--pp-surface); color: var(--pp-muted); border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-minus"></i></a>
                    </div>
                </div>
                <div class="collapse show" id="mycard-collapse">
                    <div class="card-body">
                        <form id="stock-filter-form" action="{{ route('admin.reports.stock') }}" method="GET">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label>Category</label>
                                    <select name="category_id" class="form-control select2 pp-select2">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ request()->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label>Brand</label>
                                    <select name="brand_id" class="form-control select2 pp-select2">
                                        <option value="">All Brands</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ request()->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.reports.stock') }}" class="btn pp-btn-sm pp-btn-rose w-100" style="margin-top: 4px;">
                                        <i class="fas fa-undo"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Report Table -->
            <div class="card pp-table-card">
                <div class="card-header">
                    <h4><i class="fas fa-table"></i> Detailed Stock List</h4>
                    <div class="card-header-action">
                        <button type="button" class="pp-btn-sm pp-btn-indigo" id="btn-export-excel">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="pp-btn-sm pp-btn-emerald" id="btn-export-pdf">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button type="button" class="pp-btn-sm pp-btn-rose" id="btn-print">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="export-header d-none">
                        <div style="text-align: center; margin-bottom: 10px;">
                            <h3 style="margin: 0; color: #333;">{{ $settings->site_name ?? 'Inventory Management System' }}</h3>
                            @if($settings->contact_email)
                                <p style="margin: 5px 0; color: #666;"><strong>Email:</strong> {{ $settings->contact_email }}</p>
                            @endif
                            @if($settings->address)
                                <p style="margin: 5px 0; color: #666;"><strong>Address:</strong> {{ $settings->address }}</p>
                            @endif
                            <hr style="margin: 10px 0; border-top: 2px solid #007bff;">
                            <h4 style="margin: 10px 0; color: #007bff;">Stock Valuation Report</h4>
                            <p style="margin: 5px 0; color: #666; font-size: 14px;"><strong>Generated on:</strong> {{ date('F d, Y h:i A') }}</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="table-stock">
                            <thead>
                                <tr>
                                    <th>Product Info</th>
                                    <th>Category / Brand</th>
                                    <th class="text-center">Stock Qty</th>
                                    <th class="text-right">Unit Cost</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">Total Asset Value</th>
                                    <th class="text-right">Profit Potential</th>
                                </tr>
                            </thead>
                            <tbody id="stock-table-body">
                                @include('backend.reports.partials.stock_table_rows')
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-right text-dark">GRAND TOTAL:</td>
                                    <td class="text-right text-primary"><span id="span-grand-total-value">{{ $settings->currency_icon }}{{ number_format($totalValue, 2) }}</span></td>
                                    <td class="text-right text-success"><span id="span-grand-total-profit">{{ $settings->currency_icon }}{{ number_format($potentialProfit, 2) }}</span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <p class="pp-info-text">
                        Showing
                        <span class="text-dark font-weight-bold">{{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}</span>
                        of
                        <span class="text-dark font-weight-bold">{{ $products->total() }}</span>
                        products
                    </p>
                    <div class="d-flex justify-content-center flex-wrap pp-pagination">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const exportMeta = {
            site_name: @json($settings->site_name ?? 'Inventory Management System'),
            contact_email: @json($settings->contact_email ?? 'N/A'),
            address: @json($settings->address ?? 'N/A'),
            generated_on: @json(date('F d, Y h:i A'))
        };
        const exportFileName = 'stock-report-' + @json(date('Y-m-d'));

        function buildExportHeaderText() {
            return [
                exportMeta.site_name,
                'Email: ' + exportMeta.contact_email,
                'Address: ' + exportMeta.address,
                '-------------------------------------------',
                'Stock Valuation Report',
                'Generated on: ' + exportMeta.generated_on,
                '-------------------------------------------',
                ''
            ].join('\n');
        }

        function buildPdfHeaderBlocks() {
            return [
                { text: exportMeta.site_name + '\n', fontSize: 16, bold: true, alignment: 'center' },
                { text: 'Email: ' + exportMeta.contact_email + '\n', fontSize: 10, alignment: 'center' },
                { text: 'Address: ' + exportMeta.address + '\n\n', fontSize: 10, alignment: 'center' },
                { text: 'Stock Valuation Report\n', fontSize: 14, bold: true, alignment: 'center', color: '#007bff' },
                { text: 'Generated on: ' + exportMeta.generated_on + '\n\n', fontSize: 10, alignment: 'center' }
            ];
        }

        function initStockDataTable() {
            const cleanCellText = function (value) {
                return $('<div>').html(value).text().replace(/\s+/g, ' ').trim();
            };
            const commonExportOptions = {
                format: {
                    body: function (data, row, column, node) {
                        if (column === 0) {
                            const nameElement = $(node).find('.font-weight-bold').first();
                            if (nameElement.length) return nameElement.text().trim();
                        }
                        if (column === 1) {
                            const category = $(node).find('.badge').first().text().trim() || '-';
                            const brand = $(node).find('.text-muted').first().text().trim() || '-';
                            return 'Category: ' + category + '\nBrand: ' + brand;
                        }
                        return cleanCellText(data);
                    }
                }
            };
            const table = $('#table-stock').DataTable({
                dom: 'Brt',
                paging: false,
                info: false,
                searching: false,
                buttons: [
                    { extend: 'copy', messageTop: buildExportHeaderText(), exportOptions: commonExportOptions },
                    { extend: 'csv', messageTop: buildExportHeaderText(), filename: exportFileName, exportOptions: commonExportOptions },
                    { extend: 'excel', messageTop: buildExportHeaderText(), title: 'Stock Report', filename: exportFileName, exportOptions: commonExportOptions },
                    {
                        extend: 'pdf', messageTop: '', title: 'Stock Report', filename: exportFileName,
                        exportOptions: commonExportOptions,
                        customize: function (doc) { doc.content.splice(0, 0, { text: buildPdfHeaderBlocks() }); }
                    },
                    {
                        extend: 'print', messageTop: function () { return $('.export-header').html(); }, title: ''
                    }
                ]
            });
            $('#table-stock_wrapper .dt-buttons').hide();
            return table;
        }

        let table = initStockDataTable();

        function initExportButtons() {
            $('#btn-export-excel').off('click').on('click', function () { table.button(2).trigger(); });
            $('#btn-export-pdf').off('click').on('click', function () { table.button(3).trigger(); });
            $('#btn-print').off('click').on('click', function () { table.button(4).trigger(); });
        }
        initExportButtons();

        $(document).on('change select2:select', '#stock-filter-form select[name="category_id"], #stock-filter-form select[name="brand_id"]', function () {
            $('#stock-filter-form').trigger('submit');
        });
    </script>
@endpush