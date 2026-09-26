@extends('backend.layouts.master')

@section('title', 'Barcode Labels — WMS & Retail Studio')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-barcode text-primary mr-2"></i> Barcode Labels Hub</h1>
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                        <div class="breadcrumb-item">Inventory & WMS</div>
                        <div class="breadcrumb-item active">Barcode Labels</div>
                    </div>
                </div>
                <div class="header-badges d-none d-md-flex gap-2">
                    <span class="badge badge-primary px-3 py-2"><i class="fas fa-check-circle mr-1"></i> Pure Vector
                        SVG</span>
                    <span class="badge badge-info px-3 py-2"><i class="fas fa-print mr-1"></i> Zebra / Thermal Ready</span>
                    <span class="badge badge-dark px-3 py-2"><i class="fas fa-file-alt mr-1"></i> A4 Laser Sheet
                        Ready</span>
                </div>
            </div>

            <div class="section-body">
                <form id="barcodePrintForm" action="{{ route('admin.barcode-labels.print') }}" method="POST"
                    target="_blank">
                    @csrf
                    <input type="hidden" name="items" id="formItemsJson" value="[]">

                    <div class="row">
                        {{-- LEFT COLUMN: Configuration & Items Selection --}}
                        <div class="col-lg-7 col-xl-7">

                            {{-- 1. Item Search & Selection Card --}}
                            <div class="card shadow-sm border-0 mb-4">
                                <div
                                    class="card-header bg-white border-bottom-0 pb-0 pt-3 d-flex justify-content-between align-items-center">
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-search text-info mr-1"></i> Search & Queue Items
                                    </h6>
                                    <span class="badge badge-light border text-muted small" id="queueBadgeCount">
                                        0 Items Queued
                                    </span>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="row g-2 mb-3 position-relative" id="searchFilterRow">
                                        {{-- Search Input --}}
                                        <div class="col-md-7" id="searchContainer">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white"><i
                                                            class="fas fa-search text-muted"></i></span>
                                                </div>
                                                <input type="text" id="searchInput" class="form-control"
                                                    placeholder="Search product, SKU, variant size/color..."
                                                    autocomplete="off">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary d-none"
                                                        id="btnClearSearchInput" type="button" title="Clear Search"><i
                                                            class="fas fa-times"></i></button>
                                                    <button class="btn btn-outline-primary" id="btnToggleDropdown"
                                                        type="button" title="Browse all products"><i
                                                            class="fas fa-list-ul mr-1"></i> All (<span
                                                            id="btnTotalCount">{{ $totalProductsCount ?? count($initialProducts ?? []) }}</span>)</button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Category Filter using Select2 --}}
                                        <div class="col-md-5" id="categoryFilterWrapper">
                                            <select id="categoryFilter" class="form-control select2"
                                                style="width: 100%;">
                                                <option value="" selected>All Categories</option>
                                                @foreach ($categories as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->name }}
                                                        ({{ $cat->products_count ?? 0 }})</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Floating Live Search & Catalog Products Dropdown (Full Width of Search & Filter Row) --}}
                                        <div id="searchDropdownMenu"
                                            class="search-floating-dropdown shadow-lg rounded border bg-white"
                                            style="display: none;">
                                            <div
                                                class="dropdown-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center rounded-top">
                                                <span class="font-weight-bold text-dark small" id="searchDropdownTitle">
                                                    <i class="fas fa-boxes text-primary mr-1"></i> Catalog Products
                                                    (Click to add to Queue)
                                                </span>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge badge-primary font-weight-bold px-2 py-1 mr-2"
                                                        id="searchDropdownCountBadge">
                                                        {{ count($initialProducts ?? []) }} Products
                                                    </span>
                                                    <button type="button" class="btn btn-xs btn-light border text-secondary px-2 py-0"
                                                        id="btnCloseSearchDropdown" title="Close dropdown">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="searchResultsList" class="p-2"
                                                style="max-height: 420px; overflow-y: auto;">
                                                {{-- Populated dynamically via renderSearchResults() --}}
                                            </div>
                                            <div
                                                class="dropdown-footer bg-light py-2 px-3 border-top text-center text-muted small rounded-bottom">
                                                <span id="dropdownFooterHint"><i
                                                        class="fas fa-mouse-pointer text-primary mr-1"></i> Click any
                                                    product or variant to add to Queue.</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Selected Items Queue Table (Clean fluid layout with ZERO horizontal scrollbar) --}}
                                    <div class="barcode-queue-wrapper">
                                        <table class="table table-hover table-sm align-middle mb-0" id="queueTable">
                                            <thead class="bg-light text-muted small text-uppercase">
                                                <tr>
                                                    <th style="width: 52%;">Item & Barcode Spec</th>
                                                    <th class="text-center" style="width: 16%;">Stock</th>
                                                    <th class="text-center" style="width: 22%;">Copies</th>
                                                    <th class="text-center" style="width: 10%;"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="queueTableBody">
                                                <tr id="emptyRow">
                                                    <td colspan="4" class="text-center py-4 text-muted small">
                                                        <i
                                                            class="fas fa-inbox fa-2x mb-2 d-block text-secondary opacity-50"></i>
                                                        No items queued yet. Search and pick products or variants above into
                                                        this print queue.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div
                                        class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-2 border-top gap-2">
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary mr-1"
                                                id="btnClearAll">
                                                <i class="fas fa-trash-alt mr-1"></i> Clear All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                id="btnFillStockAll">
                                                <i class="fas fa-boxes mr-1"></i> Set All to Stock Qty
                                            </button>
                                        </div>
                                        <div class="small text-muted font-weight-bold" id="queueSummaryText">
                                            <span id="queueItemCountSummary">0 Items</span> (<span
                                                id="queueTotalCopiesSummary">0 Copies</span>)
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. Label Preset Formations (Physical Layouts) --}}
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-white border-bottom-0 pb-0 pt-3">
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-th-large text-primary mr-1"></i> Select Label Formation (Preset)
                                    </h6>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="row g-2" id="presetCardsContainer">
                                        @foreach ($presets as $key => $preset)
                                            <div class="col-12 col-md-6 mb-2 preset-col">
                                                <label
                                                    class="preset-card-label d-block p-3 border rounded cursor-pointer h-100 {{ $loop->first ? 'active' : '' }}">
                                                    <div class="d-flex align-items-start">
                                                        <input type="radio" name="preset" value="{{ $key }}"
                                                            class="mr-2.5 mt-1 preset-radio flex-shrink-0"
                                                            {{ $loop->first ? 'checked' : '' }}>
                                                        <div class="flex-grow-1" style="min-width: 0;">
                                                            <div class="mb-1">
                                                                <span class="badge badge-light border text-muted font-weight-bold"
                                                                    style="font-size: 9px; letter-spacing: 0.3px;">
                                                                    <i class="fas fa-th-large text-primary mr-1 opacity-75"></i> {{ $preset['badge'] }}
                                                                </span>
                                                            </div>
                                                            <div class="font-weight-bold text-dark small preset-name" style="font-size: 13px; line-height: 1.35;">
                                                                {{ $preset['name'] }}
                                                            </div>
                                                            <div class="text-muted mt-1" style="font-size: 11px; line-height: 1.4;">
                                                                {{ $preset['description'] }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- 3. Label Content Customizer --}}
                            <div class="card shadow-sm border-0 mb-4 bg-white">
                                <div
                                    class="card-header bg-white border-bottom-0 pb-1 pt-3 d-flex align-items-center justify-content-between">
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-sliders-h text-warning mr-1"></i> Label Content Customizer
                                    </h6>
                                    <span class="badge badge-light border text-muted small"
                                        style="font-size: 11px;">WYSIWYG Visible Data</span>
                                </div>
                                <div class="card-body pt-2 pb-3">
                                    <div class="row g-2">
                                        <div class="col-6 col-md-4 mb-2">
                                            <label
                                                class="customizer-chip d-flex align-items-center p-2 rounded border cursor-pointer h-100 active"
                                                for="togglePrice">
                                                <input type="checkbox" class="customizer-checkbox content-toggle mr-2"
                                                    id="togglePrice" name="show_price" value="1" checked>
                                                <span class="small font-weight-bold text-dark"><i
                                                        class="fas fa-tag text-primary mr-1 opacity-75"></i> Price
                                                    (kr.)</span>
                                            </label>
                                        </div>
                                        <div class="col-6 col-md-4 mb-2">
                                            <label
                                                class="customizer-chip d-flex align-items-center p-2 rounded border cursor-pointer h-100 active"
                                                for="toggleName">
                                                <input type="checkbox" class="customizer-checkbox content-toggle mr-2"
                                                    id="toggleName" name="show_name" value="1" checked>
                                                <span class="small font-weight-bold text-dark"><i
                                                        class="fas fa-font text-info mr-1 opacity-75"></i> Product
                                                    Title</span>
                                            </label>
                                        </div>
                                        <div class="col-6 col-md-4 mb-2">
                                            <label
                                                class="customizer-chip d-flex align-items-center p-2 rounded border cursor-pointer h-100 active"
                                                for="toggleVariantSpec">
                                                <input type="checkbox" class="customizer-checkbox content-toggle mr-2"
                                                    id="toggleVariantSpec" name="show_variant_spec" value="1"
                                                    checked>
                                                <span class="small font-weight-bold text-dark"><i
                                                        class="fas fa-layer-group text-warning mr-1 opacity-75"></i>
                                                    Variant Spec</span>
                                            </label>
                                        </div>
                                        <div class="col-6 col-md-4 mb-2">
                                            <label
                                                class="customizer-chip d-flex align-items-center p-2 rounded border cursor-pointer h-100 active"
                                                for="toggleSku">
                                                <input type="checkbox" class="customizer-checkbox content-toggle mr-2"
                                                    id="toggleSku" name="show_sku" value="1" checked>
                                                <span class="small font-weight-bold text-dark"><i
                                                        class="fas fa-barcode text-secondary mr-1 opacity-75"></i> SKU /
                                                    Code</span>
                                            </label>
                                        </div>
                                        <div class="col-6 col-md-4 mb-2">
                                            <label
                                                class="customizer-chip d-flex align-items-center p-2 rounded border cursor-pointer h-100 active"
                                                for="toggleBrand">
                                                <input type="checkbox" class="customizer-checkbox content-toggle mr-2"
                                                    id="toggleBrand" name="show_brand" value="1" checked>
                                                <span class="small font-weight-bold text-dark"><i
                                                        class="fas fa-building text-success mr-1 opacity-75"></i>
                                                    Company</span>
                                            </label>
                                        </div>
                                        <div class="col-6 col-md-4 mb-2">
                                            <label
                                                class="customizer-chip d-flex align-items-center p-2 rounded border cursor-pointer h-100 active"
                                                for="toggleBarcodeText">
                                                <input type="checkbox" class="customizer-checkbox content-toggle mr-2"
                                                    id="toggleBarcodeText" name="show_barcode_text" value="1"
                                                    checked>
                                                <span class="small font-weight-bold text-dark"><i
                                                        class="fas fa-ellipsis-h text-dark mr-1 opacity-75"></i> Barcode Digits</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Action: Enterprise Generation & Preview Card --}}
                            <div class="card shadow-sm border-0 mb-4 bg-white">
                                <div class="card-body p-3">
                                    <div class="catalog-persistence-card p-3 rounded border mb-3 cursor-pointer"
                                        id="persistCardTrigger">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div class="persistence-icon-box mr-3">
                                                    <i class="fas fa-database text-primary"></i>
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold text-dark small"
                                                        style="font-size: 13px;">
                                                        Automatically save newly generated barcodes to Product Master Catalog
                                                    </div>
                                                    <div class="text-muted small mt-0.5" style="font-size: 11px;">
                                                        Permanently stores barcodes in <code>products.barcode</code> /
                                                        <code>product_variants.barcode</code> for POS scanners and warehouse
                                                        management.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="custom-switch-wrapper ml-3">
                                                <input type="checkbox" id="persistCatalogCheckbox"
                                                    class="enterprise-switch" checked>
                                                <label for="persistCatalogCheckbox"
                                                    class="enterprise-switch-slider"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button"
                                        class="btn btn-primary btn-lg btn-block shadow-sm font-weight-bold py-3 btn-generate-cta"
                                        id="btnGenerateAndPreview">
                                        <i class="fas fa-bolt mr-2 text-warning"></i> Generate Barcodes & Preview Labels
                                    </button>
                                </div>
                            </div>

                        </div>

                        {{-- RIGHT COLUMN: Live Sticky WYSIWYG Preview --}}
                        <div class="col-lg-5 col-xl-5">
                            <div class="sticky-preview-wrapper" style="position: sticky; top: 90px;">
                                <div class="card shadow-sm border-0">
                                    <div
                                        class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2.5 px-3">
                                        <div class="font-weight-bold small d-flex align-items-center text-nowrap mr-2">
                                            <i class="fas fa-eye text-success mr-2"></i> Live Preview
                                        </div>
                                        <div class="d-flex align-items-center flex-nowrap" style="gap: 6px;">
                                            <span id="previewStatusBadge"
                                                class="badge badge-secondary font-weight-bold"
                                                style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.3px;">Empty</span>
                                            <span id="statStickerCount"
                                                class="badge badge-light text-dark font-weight-bold px-2 py-0.5"
                                                style="font-size: 10px;">0 Stickers</span>
                                        </div>
                                    </div>
                                    <div class="card-body p-3 bg-light text-center"
                                        style="min-height: 480px; max-height: 75vh; overflow-y: auto;"
                                        id="previewContainer">
                                        <div class="py-5 text-muted">
                                            <i class="fas fa-barcode fa-3x mb-3 text-secondary opacity-50"></i>
                                            <h6 class="font-weight-bold text-dark">Print Queue Empty</h6>
                                            <p class="small text-muted mb-0">Search and pick products or variants on the
                                                left to get started.</p>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-top p-3" id="previewFooterAction">
                                        <button type="button"
                                            class="btn btn-success btn-block font-weight-bold py-2.5 shadow-sm btn-print-main"
                                            id="btnFooterPrint" disabled
                                            style="font-size: 15px; border-radius: 8px; letter-spacing: 0.3px;">
                                            <i class="fas fa-print mr-2"></i> Print Labels Now
                                        </button>
                                        <div class="text-center mt-2">
                                            <small class="text-muted"><i class="fas fa-shield-alt text-info mr-1"></i>
                                                Labels must be generated and previewed before printing.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </section>
    </div>

    <style>
        .cursor-pointer {
            cursor: pointer;
        }

        .preset-card-label {
            background: #fff;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 8px !important;
            padding: 12px !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .preset-card-label:hover {
            background: #f8fafc;
            border-color: #93c5fd !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.08);
        }

        .preset-card-label.active {
            background: #eff6ff !important;
            border-color: #2563eb !important;
            border-width: 2px !important;
            box-shadow: 0 0 0 1px #2563eb, 0 4px 12px rgba(37, 99, 235, 0.12) !important;
        }

        /* Floating Live Search Dropdown - Full Fluid Width & Enterprise Polish */
        .search-floating-dropdown {
            position: absolute;
            top: 100%;
            left: 8px;
            right: 8px;
            z-index: 1050;
            margin-top: 6px;
            background: #ffffff;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.16), 0 4px 12px rgba(15, 23, 42, 0.08);
            border: 1px solid #cbd5e1 !important;
            border-radius: 10px !important;
        }

        #searchResultsList::-webkit-scrollbar {
            width: 6px;
        }
        #searchResultsList::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        #searchResultsList::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        #searchResultsList::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .search-result-wrapper {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #ffffff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .search-result-wrapper:hover {
            border-color: #93c5fd !important;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.06);
        }

        .search-result-item {
            transition: background 0.15s ease;
        }

        .search-result-item:hover {
            background: #f8fafc;
        }

        .search-result-item.in-queue {
            background: #f0fdf4;
            border-left: 3px solid #10b981 !important;
        }

        .variant-sub-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #3b82f6 !important;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .variant-sub-item:hover {
            background: #eff6ff;
            border-color: #93c5fd;
        }

        /* Select2 Matching Height */
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.25rem !important;
            padding: 6px 12px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            color: #495057 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
        }

        /* Clean Enterprise Queue Table with ZERO Horizontal Scrollbar */
        .barcode-queue-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
            overflow-y: visible !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            background: #ffffff !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        #queueTable {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed !important;
            margin-bottom: 0 !important;
            border-collapse: collapse !important;
        }

        #queueTable thead th {
            background: #f8fafc !important;
            color: #475569 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            letter-spacing: 0.5px !important;
            text-transform: uppercase !important;
            padding: 10px 12px !important;
            border-top: none !important;
            border-bottom: 1px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }

        #queueTable tbody td {
            padding: 10px 12px !important;
            vertical-align: middle !important;
            border-top: 1px solid #f1f5f9 !important;
            border-bottom: none !important;
            white-space: normal !important;
            word-break: break-word !important;
        }

        #queueTable tbody tr:hover {
            background-color: #f8fafc !important;
        }

        #queueTable .btn-dec,
        #queueTable .btn-inc {
            padding: 2px 7px !important;
            font-size: 11px !important;
            font-weight: bold !important;
            line-height: 1.2 !important;
        }

        #queueTable .item-qty-input {
            height: 30px !important;
            font-size: 12px !important;
            padding: 2px 4px !important;
        }

        #queueTable .btn-remove-item {
            width: 28px !important;
            height: 28px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 6px !important;
        }

        #btnGenerateAndPreview {
            border-radius: 8px !important;
            font-size: 15px !important;
            letter-spacing: 0.3px !important;
            transition: all 0.2s ease;
        }

        .btn-generate-cta {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            border: none !important;
            border-radius: 8px !important;
            font-size: 15px !important;
            letter-spacing: 0.3px !important;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25) !important;
            transition: all 0.2s ease !important;
        }

        .btn-generate-cta:hover:not(:disabled) {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%) !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35) !important;
        }

        .btn-generate-cta:disabled {
            opacity: 0.75 !important;
            cursor: not-allowed !important;
            box-shadow: none !important;
        }

        /* Customizer Chips */
        .customizer-chip {
            background: #ffffff;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            transition: all 0.15s ease;
            user-select: none;
            margin-bottom: 0;
        }

        .customizer-chip:hover {
            background: #f8fafc;
            border-color: #cbd5e1 !important;
        }

        .customizer-chip.active {
            background: #f0f7ff !important;
            border-color: #93c5fd !important;
        }

        .customizer-checkbox {
            width: 16px;
            height: 16px;
            accent-color: #2563eb;
            cursor: pointer;
        }

        /* Enterprise Switch & Persistence Card */
        .catalog-persistence-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            transition: all 0.2s ease;
        }

        .catalog-persistence-card:hover {
            background: #f1f5f9;
            border-color: #cbd5e1 !important;
        }

        .persistence-icon-box {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .custom-switch-wrapper {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            flex-shrink: 0;
        }

        .enterprise-switch {
            opacity: 0;
            width: 0;
            height: 0;
            position: absolute;
        }

        .enterprise-switch-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: 0.25s ease;
            border-radius: 24px;
            margin-bottom: 0;
        }

        .enterprise-switch-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.25s ease;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .enterprise-switch:checked+.enterprise-switch-slider {
            background-color: #2563eb;
        }

        .enterprise-switch:checked+.enterprise-switch-slider:before {
            transform: translateX(20px);
        }

        .btn-print-main {
            transition: all 0.2s ease;
        }

        .btn-print-main:not(:disabled) {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            border: none !important;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35) !important;
        }

        .btn-print-main:not(:disabled):hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45) !important;
        }
    </style>

    @push('scripts')
        <script>
            $(document).ready(function() {
                let queuedItems = [];
                let isPreviewGenerated = false;
                let currentCatalogItems = @json($initialProducts ?? []);
                const availableUnits = @json($units ?? []);
                let searchDebounceTimer = null;
                let previewDebounceTimer = null;

                // Initialize Select2 for Category Filter (Keep "All Categories" always visible in dropdown)
                if ($.fn.select2) {
                    $('#categoryFilter').select2({
                        width: '100%'
                    });
                }

                // Preloaded Product Check
                @if (!empty($preloadedProduct))
                    const preProd = @json($preloadedProduct);
                    if (preProd.has_variants && preProd.variants && preProd.variants.length > 0) {
                        preProd.variants.forEach(function(v) {
                            queuedItems.push({
                                target_type: 'variant',
                                id: v.id,
                                product_id: preProd.id,
                                name: preProd.name,
                                variant_spec: v.spec,
                                sku: v.sku,
                                barcode: v.barcode,
                                price: v.price,
                                stock: v.stock,
                                qty: 1,
                                use_stock: false
                            });
                        });
                    } else {
                        queuedItems.push({
                            target_type: 'product',
                            id: preProd.id,
                            product_id: preProd.id,
                            name: preProd.name,
                            variant_spec: null,
                            sku: preProd.sku,
                            barcode: preProd.barcode,
                            price: preProd.price,
                            stock: preProd.stock,
                            qty: 1,
                            use_stock: false
                        });
                    }
                    const missingCount = queuedItems.filter(q => !q.barcode).length;
                    if (queuedItems.length > 0 && missingCount === 0) {
                        isPreviewGenerated = true;
                    }
                    renderQueueTable();
                    updatePreviewState();
                @endif

                // Initial Search Dropdown Rendering
                renderSearchResults(currentCatalogItems);

                // Show Dropdown on Focus / Click of Search Input
                $('#searchInput').on('focus click', function() {
                    $('#searchDropdownMenu').fadeIn(150);
                    refreshSearchResultBadges();
                });

                // Toggle Dropdown via "All (N)" Button
                $('#btnToggleDropdown').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('#searchDropdownMenu').fadeToggle(150, function() {
                        if ($(this).is(':visible')) {
                            $('#searchInput').focus();
                            refreshSearchResultBadges();
                        }
                    });
                });

                // Close Dropdown when clicking outside or close button
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('#searchFilterRow').length && !$(e.target).closest('.select2-container').length) {
                        $('#searchDropdownMenu').fadeOut(150);
                    }
                });

                $(document).on('click', '#btnCloseSearchDropdown', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('#searchDropdownMenu').fadeOut(150);
                });

                // Preset Radio Styling
                $('input[name="preset"]').on('change', function() {
                    $('.preset-card-label').removeClass('active');
                    $(this).closest('.preset-card-label').addClass('active');
                    if (isPreviewGenerated) {
                        triggerPreviewUpdate();
                    } else {
                        updatePreviewState();
                    }
                });

                // Content Toggles
                $('.content-toggle').on('change', function() {
                    if (isPreviewGenerated) {
                        triggerPreviewUpdate();
                    }
                });

                // Search Input Debounce
                $('#searchInput').on('input', function() {
                    clearTimeout(searchDebounceTimer);
                    const query = $(this).val().trim();

                    if (query.length > 0) {
                        $('#btnClearSearchInput').removeClass('d-none');
                    } else {
                        $('#btnClearSearchInput').addClass('d-none');
                    }

                    searchDebounceTimer = setTimeout(function() {
                        performSearch(query);
                    }, 200);
                });

                // Clear Search Input Button
                $('#btnClearSearchInput').on('click', function() {
                    $('#searchInput').val('').trigger('input');
                    performSearch('');
                });

                // Category Filter Change
                $('#categoryFilter').on('change', function() {
                    performSearch($('#searchInput').val().trim());
                    $('#searchDropdownMenu').fadeIn(150);
                });

                function performSearch(query) {
                    const rawCatId = $('#categoryFilter').val();
                    const catId = (rawCatId && rawCatId !== 'all' && rawCatId !== '') ? rawCatId : null;
                    const catName = catId ? $('#categoryFilter option:selected').text().split('(')[0].trim() : '';

                    if (query.length > 0) {
                        $('#searchDropdownTitle').html(
                            `<i class="fas fa-search text-primary mr-1"></i> Search results for "${escapeHtml(query)}"`
                            );
                    } else if (catId) {
                        $('#searchDropdownTitle').html(
                            `<i class="fas fa-folder-open text-primary mr-1"></i> Products in ${escapeHtml(catName)}`
                            );
                    } else {
                        $('#searchDropdownTitle').html(
                            `<i class="fas fa-boxes text-primary mr-1"></i> Catalog Products (Click to add to Queue)`
                            );
                    }

                    const url = "{{ route('admin.barcode-labels.search-products') }}";
                    const data = {
                        q: query,
                        category_id: catId,
                        limit: 60
                    };

                    $.getJSON(url, data, function(res) {
                        currentCatalogItems = res.data || [];
                        renderSearchResults(currentCatalogItems);
                        $('#searchDropdownMenu').fadeIn(150);
                    });
                }

                function renderSearchResults(items) {
                    const list = $('#searchResultsList');
                    list.empty();

                    const query = $('#searchInput').val().trim();
                    const rawCatId = $('#categoryFilter').val();
                    const catId = (rawCatId && rawCatId !== 'all' && rawCatId !== '') ? rawCatId : null;

                    if (query.length > 0) {
                        $('#searchDropdownCountBadge').text(items.length + (items.length >= 60 ? '+ Matches' : ' Matches'));
                    } else if (catId) {
                        $('#searchDropdownCountBadge').text(items.length + (items.length >= 60 ? '+ Products' : ' Products'));
                    } else {
                        $('#searchDropdownCountBadge').text('Showing ' + items.length + ' of {{ $totalProductsCount ?? 1397 }}');
                    }

                    if (items.length === 0) {
                        list.html(
                            '<div class="p-3 text-muted small text-center"><i class="fas fa-info-circle mr-1"></i> No matching products found.</div>'
                            );
                        $('#dropdownFooterHint').html(
                            '<i class="fas fa-info-circle mr-1"></i> No matching products found.');
                        return;
                    }

                    let htmlBuffer = '';
                    items.forEach(function(item) {
                        const hasVariants = item.has_variants && item.variants && item.variants.length > 0;
                        const parentQueueItem = queuedItems.find(q => q.target_type === 'product' && q.id === item.id);
                        const isParentInQueue = !!parentQueueItem;

                        let barcodeIndicator = '';
                        if (hasVariants) {
                            barcodeIndicator =
                                `<span class="badge badge-info py-1 px-2"><i class="fas fa-sitemap mr-1"></i> ${item.variant_count} Variants</span>`;
                        } else {
                            barcodeIndicator = item.barcode ?
                                `<span class="badge badge-light border text-success font-weight-bold py-1 px-2"><i class="fas fa-barcode mr-1"></i> ${escapeHtml(item.barcode)}</span>` :
                                `<span class="badge badge-light border text-muted py-1 px-2"><i class="fas fa-minus mr-1"></i> No Barcode</span>`;
                        }

                        let actionBtns = '';
                        if (hasVariants) {
                            actionBtns = `
                    <button type="button" class="btn btn-xs btn-outline-info btn-toggle-variants mr-1" data-product-id="${item.id}">
                        <i class="fas fa-chevron-down mr-1"></i> Variants (${item.variant_count})
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-success btn-add-all-variants" data-product-id="${item.id}">
                        <i class="fas fa-plus-circle mr-1"></i> Add All
                    </button>
                `;
                        } else {
                            actionBtns = isParentInQueue ?
                                `<span class="badge badge-success py-1 px-2"><i class="fas fa-check mr-1"></i> Added (${parentQueueItem.qty})</span>` :
                                `<button type="button" class="btn btn-xs btn-outline-primary btn-add-parent px-3 font-weight-bold" data-product-id="${item.id}"><i class="fas fa-plus mr-1"></i> Add</button>`;
                        }

                        htmlBuffer += `
                <div class="search-result-wrapper border rounded mb-2 bg-white" id="prodWrapper_${item.id}">
                    <div class="search-result-item d-flex justify-content-between align-items-center p-2 rounded cursor-pointer ${isParentInQueue ? 'in-queue' : ''}" data-product='${JSON.stringify(item)}'>
                        <div class="flex-grow-1 min-w-0 mr-3">
                            <div class="d-flex align-items-center mb-1">
                                <span class="font-weight-bold text-dark text-truncate" style="font-size: 13.5px;" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</span>
                            </div>
                            <div class="small text-muted d-flex flex-wrap align-items-center" style="row-gap: 4px; column-gap: 8px;">
                                ${item.gpc_code ? `<span class="badge badge-primary font-weight-bold py-1 px-2" style="font-family: monospace;" title="GS1 GPC Brick: ${escapeHtml(item.gpc_title || item.category)}"><i class="fas fa-barcode mr-1"></i>GS1: ${escapeHtml(item.gpc_code)}</span>` : ''}
                                <span class="badge badge-light border text-secondary font-weight-normal py-1 px-2">${escapeHtml(item.category || 'General')}</span>
                                <span><small class="text-secondary font-weight-bold">SKU:</small> <span class="font-monospace text-dark font-weight-bold">${escapeHtml(item.sku)}</span></span>
                                ${barcodeIndicator}
                                <span><small class="text-secondary font-weight-bold">Stock:</small> <strong class="${item.stock > 0 ? 'text-dark' : 'text-danger'}">${item.stock}</strong></span>
                                <span><small class="text-secondary font-weight-bold">Price:</small> <strong class="text-primary">${parseFloat(item.price).toFixed(2)}</strong></span>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            ${actionBtns}
                        </div>
                    </div>

                    ${hasVariants ? renderVariantSubList(item) : ''}
                </div>
            `;
                    });

                    if (items.length >= 60) {
                        htmlBuffer += `
                            <div class="text-center py-2 border-top mt-2" id="loadMoreContainer">
                                <button type="button" class="btn btn-sm btn-outline-primary px-3 shadow-sm" id="btnLoadMoreProducts">
                                    <i class="fas fa-spinner fa-spin d-none mr-1"></i> <i class="fas fa-chevron-down mr-1"></i> Load More Products
                                </button>
                            </div>
                        `;
                    }

                    list.html(htmlBuffer);
                    $('#dropdownFooterHint').html(
                        `<i class="fas fa-mouse-pointer text-primary mr-1"></i> Showing ${items.length} products. Click any item or variant to queue.`
                        );
                }

                function renderVariantSubList(product) {
                    let subHtml =
                        `<div class="variant-sub-container p-2 border-top" id="variantList_${product.id}" style="display: none; background: #f8fafc;">`;
                    subHtml += `<div class="small text-muted font-weight-bold px-1 py-1 mb-2 border-bottom d-flex justify-content-between">
            <span><i class="fas fa-tags text-primary mr-1"></i> ${escapeHtml(product.name)} — Select Variants:</span>
            <span>Total ${product.variants.length} Sizes/Colors</span>
        </div>`;

                    product.variants.forEach(function(v) {
                        const queueItem = queuedItems.find(q => q.target_type === 'variant' && q.id === v.id);
                        const isInQueue = !!queueItem;
                        const barcodePill = v.barcode ?
                            `<span class="badge badge-light border text-success font-weight-bold py-1 px-2"><i class="fas fa-barcode mr-1"></i> ${escapeHtml(v.barcode)}</span>` :
                            `<span class="badge badge-light border text-muted py-1 px-2"><i class="fas fa-minus mr-1"></i> No Barcode</span>`;

                        const btn = isInQueue ?
                            `<span class="badge badge-success py-1 px-2"><i class="fas fa-check mr-1"></i> Added (${queueItem.qty})</span>` :
                            `<button type="button" class="btn btn-xs btn-primary btn-add-variant px-3" data-product-id="${product.id}" data-variant-id="${v.id}"><i class="fas fa-plus mr-1"></i> Add</button>`;

                        subHtml += `
                <div class="variant-sub-item d-flex justify-content-between align-items-center p-2 rounded mb-1 border bg-white cursor-pointer" data-variant='${JSON.stringify(v)}' data-parent='${JSON.stringify(product)}'>
                    <div class="flex-grow-1 min-w-0 mr-3">
                        <span class="font-weight-bold text-dark small"><i class="fas fa-tag text-info mr-1"></i> ${escapeHtml(v.spec)}</span>
                        <div class="small text-muted d-flex flex-wrap align-items-center mt-1" style="row-gap: 4px; column-gap: 8px;">
                            <span><small class="text-secondary font-weight-bold">SKU:</small> <span class="font-monospace text-dark font-weight-bold">${escapeHtml(v.sku)}</span></span>
                            ${barcodePill}
                            <span><small class="text-secondary font-weight-bold">Stock:</small> <strong class="${v.stock > 0 ? 'text-dark' : 'text-danger'}">${v.stock}</strong></span>
                            <span><small class="text-secondary font-weight-bold">Price:</small> <strong class="text-primary">${parseFloat(v.price).toFixed(2)}</strong></span>
                        </div>
                    </div>
                    <div class="flex-shrink-0">${btn}</div>
                </div>
            `;
                    });

                    subHtml += `</div>`;
                    return subHtml;
                }

                // Click on parent row to toggle variants or add to queue
                $(document).on('click', '.search-result-item', function(e) {
                    if ($(e.target).closest('button, a, .badge').length) {
                        return;
                    }
                    const prod = $(this).data('product');
                    if (!prod) return;
                    if (prod.has_variants) {
                        $(`#variantList_${prod.id}`).slideToggle(150);
                    } else {
                        addParentToQueue(prod);
                    }
                });

                // Click on variant row to add to queue
                $(document).on('click', '.variant-sub-item', function(e) {
                    if ($(e.target).closest('button, a, .badge').length) {
                        return;
                    }
                    const v = $(this).data('variant');
                    const prod = $(this).data('parent');
                    if (!v || !prod) return;
                    addVariantToQueue(prod, v);
                });

                // Load More Products Handler
                $(document).on('click', '#btnLoadMoreProducts', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const btn = $(this);
                    btn.find('.fa-spinner').removeClass('d-none');
                    btn.find('.fa-chevron-down').addClass('d-none');
                    btn.prop('disabled', true);

                    const query = $('#searchInput').val().trim();
                    const rawCatId = $('#categoryFilter').val();
                    const catId = (rawCatId && rawCatId !== 'all' && rawCatId !== '') ? rawCatId : null;
                    const nextLimit = currentCatalogItems.length + 60;

                    $.getJSON("{{ route('admin.barcode-labels.search-products') }}", {
                        q: query,
                        category_id: catId,
                        limit: nextLimit
                    }, function(res) {
                        currentCatalogItems = res.data || [];
                        renderSearchResults(currentCatalogItems);
                    }).always(function() {
                        btn.prop('disabled', false);
                    });
                });

                // Toggle Variants Accordion
                $(document).on('click', '.btn-toggle-variants', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const prodId = $(this).data('product-id');
                    $(`#variantList_${prodId}`).slideToggle(150);
                });

                // Add Simple Parent Product to Queue
                $(document).on('click', '.btn-add-parent', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const prodId = $(this).data('product-id');
                    const prod = currentCatalogItems.find(p => p.id === prodId);
                    if (!prod) return;

                    addParentToQueue(prod);
                });

                function addParentToQueue(prod) {
                    const existsIndex = queuedItems.findIndex(q => q.target_type === 'product' && q.id === prod.id);
                    if (existsIndex === -1) {
                        queuedItems.push({
                            target_type: 'product',
                            id: prod.id,
                            product_id: prod.id,
                            name: prod.name,
                            variant_spec: null,
                            sku: prod.sku,
                            barcode: prod.barcode,
                            price: prod.price,
                            stock: prod.stock,
                            qty: 1,
                            use_stock: false
                        });
                    } else {
                        queuedItems[existsIndex].qty += 1;
                    }

                    const missingCount = queuedItems.filter(q => !q.barcode).length;
                    if (missingCount === 0) {
                        isPreviewGenerated = true;
                    }
                    renderQueueTable();
                    updatePreviewState();
                    refreshSearchResultBadges();
                }

                // Add Single Variant to Queue
                $(document).on('click', '.btn-add-variant', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const vId = $(this).data('variant-id');
                    const prodId = $(this).data('product-id');
                    const prod = currentCatalogItems.find(p => p.id === prodId);
                    if (!prod || !prod.variants) return;
                    const variant = prod.variants.find(v => v.id === vId);
                    if (!variant) return;

                    addVariantToQueue(prod, variant);
                });

                function addVariantToQueue(prod, v) {
                    const existsIndex = queuedItems.findIndex(q => q.target_type === 'variant' && q.id === v.id);
                    if (existsIndex === -1) {
                        queuedItems.push({
                            target_type: 'variant',
                            id: v.id,
                            product_id: prod.id,
                            name: prod.name,
                            variant_spec: v.spec,
                            sku: v.sku,
                            barcode: v.barcode,
                            price: v.price,
                            stock: v.stock,
                            qty: 1,
                            use_stock: false
                        });
                    } else {
                        queuedItems[existsIndex].qty += 1;
                    }

                    const missingCount = queuedItems.filter(q => !q.barcode).length;
                    if (missingCount === 0) {
                        isPreviewGenerated = true;
                    }
                    renderQueueTable();
                    updatePreviewState();
                    refreshSearchResultBadges();
                }

                // Add All Variants of a Product to Queue
                $(document).on('click', '.btn-add-all-variants', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const prodId = $(this).data('product-id');
                    const prod = currentCatalogItems.find(p => p.id === prodId);
                    if (!prod || !prod.variants) return;

                    prod.variants.forEach(function(v) {
                        addVariantToQueue(prod, v);
                    });
                });

                // Refresh Queue Badges in Search Dropdown
                function refreshSearchResultBadges() {
                    $('.search-result-item').each(function() {
                        const prod = $(this).data('product');
                        if (!prod) return;

                        if (!prod.has_variants) {
                            const queueItem = queuedItems.find(q => q.target_type === 'product' && q.id === prod
                                .id);
                            if (queueItem) {
                                $(this).addClass('in-queue');
                                $(this).find('.btn-add-parent').parent().html(
                                    `<span class="badge badge-success py-1 px-2"><i class="fas fa-check mr-1"></i> Added (${queueItem.qty})</span>`
                                    );
                            } else {
                                $(this).removeClass('in-queue');
                                $(this).find('.badge-success').parent().html(
                                    `<button type="button" class="btn btn-xs btn-outline-primary btn-add-parent px-3 font-weight-bold" data-product-id="${prod.id}"><i class="fas fa-plus mr-1"></i> Add</button>`
                                    );
                            }
                        }
                    });

                    $('.variant-sub-item').each(function() {
                        const v = $(this).data('variant');
                        const prod = $(this).data('parent');
                        if (!v || !prod) return;

                        const queueItem = queuedItems.find(q => q.target_type === 'variant' && q.id === v.id);
                        if (queueItem) {
                            $(this).addClass('bg-light');
                            $(this).find('.btn-add-variant').parent().html(
                                `<span class="badge badge-success py-1 px-2"><i class="fas fa-check mr-1"></i> Added (${queueItem.qty})</span>`
                                );
                        } else {
                            $(this).removeClass('bg-light');
                            $(this).find('.badge-success').parent().html(
                                `<button type="button" class="btn btn-xs btn-primary btn-add-variant px-3" data-product-id="${prod.id}" data-variant-id="${v.id}"><i class="fas fa-plus mr-1"></i> Add</button>`
                                );
                        }
                    });
                }

                // Render Clean Queue Table (Zero Horizontal Scrollbar, Zero repetitive buttons)
                function renderQueueTable() {
                    const tbody = $('#queueTableBody');
                    tbody.empty();

                    if (queuedItems.length === 0) {
                        tbody.html(`
                <tr id="emptyRow">
                    <td colspan="4" class="text-center py-4 text-muted small">
                        <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary opacity-50"></i>
                        No items queued yet. Search and pick products or variants above into this print queue.
                    </td>
                </tr>
            `);
                        $('#statStickerCount').text('0 Stickers');
                        $('#queueBadgeCount').text('0 Items Queued');
                        return;
                    }

                    let totalStickers = 0;

                    queuedItems.forEach(function(item, idx) {
                        const copies = item.use_stock ? Math.max(1, item.stock) : Math.max(1, item.qty);
                        totalStickers += copies;

                        const isVariant = item.target_type === 'variant';
                        const variantBadge = isVariant ?
                            `<span class="badge badge-dark text-white font-weight-bold py-0 px-2 mr-1" style="font-size: 10px;"><i class="fas fa-tag text-warning mr-1"></i> ${escapeHtml(item.variant_spec)}</span>` :
                            `<span class="badge badge-light border text-muted py-0 px-1 mr-1" style="font-size: 10px;">Item</span>`;

                        // Clean barcode status: Green if in DB, or subtle Needs Generation if pending
                        const barcodeStatus = item.barcode ?
                            `<span class="badge badge-success text-white font-monospace font-weight-bold py-0 px-2" style="font-size: 10px;"><i class="fas fa-barcode mr-1"></i> ${escapeHtml(item.barcode)}</span>` :
                            `<span class="badge badge-light border text-warning font-weight-bold py-0 px-2" style="font-size: 10px;" title="Click Generate to assign barcode"><i class="fas fa-magic mr-1"></i> Needs Generation</span>`;

                        tbody.append(`
                <tr class="queue-row" data-index="${idx}">
                    <td style="width: 52%;">
                        <div class="font-weight-bold small text-dark text-truncate" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</div>
                        <div class="small text-muted mt-1 d-flex flex-wrap align-items-center gap-1">
                            ${variantBadge}
                            ${item.sku ? '<span class="font-monospace text-muted mr-1" style="font-size: 10px;">' + escapeHtml(item.sku) + '</span>' : ''}
                            ${barcodeStatus}
                        </div>
                    </td>
                    <td class="text-center align-middle" style="width: 16%;">
                        <span class="badge badge-light border text-dark font-weight-bold">${item.stock} pcs</span>
                    </td>
                    <td class="text-center align-middle" style="width: 22%;">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <button class="btn btn-outline-secondary btn-dec" type="button" data-index="${idx}">-</button>
                            </div>
                            <input type="number" min="1" max="9999" class="form-control text-center font-weight-bold item-qty-input px-1" data-index="${idx}" value="${copies}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-inc" type="button" data-index="${idx}">+</button>
                            </div>
                        </div>
                    </td>
                    <td class="text-center align-middle" style="width: 10%;">
                        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-item" data-index="${idx}" title="Remove Item">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `);
                    });

                    const readyItems = queuedItems.filter(q => q.barcode);
                    const readyCopies = readyItems.reduce((acc, q) => acc + parseInt(q.qty || 1, 10), 0);
                    const missingCount = queuedItems.length - readyItems.length;
                    const copyLabel = totalStickers === 1 ? 'Sticker' : 'Stickers';

                    if (readyItems.length > 0) {
                        if (missingCount > 0) {
                            $('#previewStatusBadge').removeClass('badge-secondary badge-success').addClass('badge-warning').html('<i class="fas fa-bolt mr-1"></i> ' + missingCount + ' Missing');
                            $('#statStickerCount').text(readyCopies + ' of ' + totalStickers + ' Ready');
                        } else {
                            $('#previewStatusBadge').removeClass('badge-warning badge-secondary').addClass('badge-success').html('<i class="fas fa-check-circle mr-1"></i> Ready');
                            $('#statStickerCount').text(readyCopies + ' ' + copyLabel);
                        }
                    } else {
                        $('#previewStatusBadge').removeClass('badge-success badge-secondary').addClass('badge-warning').text('Needs Gen');
                        $('#statStickerCount').text('0 of ' + totalStickers + ' Ready');
                    }
                    $('#queueBadgeCount').text(queuedItems.length + ' Items (' + totalStickers + ' ' + copyLabel + ')');
                    $('#queueItemCountSummary').text(queuedItems.length + ' Items');
                    $('#queueTotalCopiesSummary').text(totalStickers + ' Copies');
                }

                // Adjust Qty Stepper
                $(document).on('click', '.btn-inc', function() {
                    const idx = $(this).data('index');
                    queuedItems[idx].qty = (parseInt(queuedItems[idx].qty) || 1) + 1;
                    queuedItems[idx].use_stock = false;
                    renderQueueTable();
                    if (isPreviewGenerated) {
                        triggerPreviewUpdate();
                    } else {
                        updatePreviewState();
                    }
                    refreshSearchResultBadges();
                });

                $(document).on('click', '.btn-dec', function() {
                    const idx = $(this).data('index');
                    let q = (parseInt(queuedItems[idx].qty) || 1);
                    if (q > 1) {
                        queuedItems[idx].qty = q - 1;
                        queuedItems[idx].use_stock = false;
                        renderQueueTable();
                        if (isPreviewGenerated) {
                            triggerPreviewUpdate();
                        } else {
                            updatePreviewState();
                        }
                    }
                    refreshSearchResultBadges();
                });

                $(document).on('change input', '.item-qty-input', function() {
                    const idx = $(this).data('index');
                    queuedItems[idx].qty = Math.max(1, parseInt($(this).val()) || 1);
                    queuedItems[idx].use_stock = false;
                    renderQueueTable();
                    if (isPreviewGenerated) {
                        triggerPreviewUpdate();
                    } else {
                        updatePreviewState();
                    }
                });

                // Remove Item
                $(document).on('click', '.btn-remove-item', function() {
                    const idx = $(this).data('index');
                    queuedItems.splice(idx, 1);
                    if (queuedItems.length === 0) {
                        isPreviewGenerated = false;
                    } else {
                        const missingCount = queuedItems.filter(q => !q.barcode).length;
                        if (missingCount === 0) {
                            isPreviewGenerated = true;
                        }
                    }
                    renderQueueTable();
                    updatePreviewState();
                    refreshSearchResultBadges();
                });

                // Clear All
                $('#btnClearAll').on('click', function() {
                    queuedItems = [];
                    isPreviewGenerated = false;
                    renderQueueTable();
                    updatePreviewState();
                    refreshSearchResultBadges();
                });

                // Set All to On-Hand Stock Qty
                $('#btnFillStockAll').on('click', function() {
                    queuedItems.forEach(function(item) {
                        item.qty = Math.max(1, item.stock);
                        item.use_stock = false;
                    });
                    renderQueueTable();
                    if (isPreviewGenerated) {
                        triggerPreviewUpdate();
                    } else {
                        updatePreviewState();
                    }
                    refreshSearchResultBadges();
                });

                // Enterprise Preview State Controller
                function updatePreviewState() {
                    const totalCopies = queuedItems.reduce((acc, q) => acc + parseInt(q.qty || 1, 10), 0);
                    const missingCount = queuedItems.filter(q => !q.barcode).length;

                    $('#queueItemCountSummary').text(queuedItems.length + ' Items');
                    $('#queueTotalCopiesSummary').text(totalCopies + ' Copies');

                    if (queuedItems.length === 0) {
                        isPreviewGenerated = false;
                        $('#statStickerCount').text('0 Stickers');
                        $('#previewStatusBadge').removeClass('badge-success badge-warning').addClass('badge-secondary')
                            .text('Queue Empty');
                        $('#btnFooterPrint').prop('disabled', true).addClass('disabled').html(
                            '<i class="fas fa-print mr-2"></i> Print Labels Now');
                        $('#btnGenerateAndPreview').prop('disabled', true);
                        $('#previewContainer').html(`
                <div class="py-5 text-muted">
                    <i class="fas fa-barcode fa-3x mb-3 text-secondary opacity-50"></i>
                    <h6 class="font-weight-bold text-dark">Print Queue Empty</h6>
                    <p class="small text-muted mb-0">Search and pick products or variants above to get started.</p>
                </div>
            `);
                        $('#formItemsJson').val('[]');
                        return;
                    }

                    $('#btnGenerateAndPreview').prop('disabled', false);

                    // If items in queue have barcodes ready in DB, activate preview for them!
                    const readyItems = queuedItems.filter(q => q.barcode);
                    if (readyItems.length > 0) {
                        isPreviewGenerated = true;
                    } else {
                        isPreviewGenerated = false;
                    }

                    const readyCopies = readyItems.reduce((acc, q) => acc + parseInt(q.qty || 1, 10), 0);
                    const copyLabel = readyCopies === 1 ? 'Sticker' : 'Stickers';

                    if (!isPreviewGenerated) {
                        $('#btnFooterPrint').prop('disabled', true).addClass('disabled').html(
                            '<i class="fas fa-print mr-2"></i> Print Labels Now');
                        $('#statStickerCount').text(totalCopies + ' Copies (0 Ready)');
                        $('#previewStatusBadge').removeClass('badge-success badge-secondary').addClass('badge-warning')
                            .text('Needs Gen');

                        const activePresetName = $('input[name="preset"]:checked').closest('.preset-card-label').find(
                            '.preset-name').text().trim() || 'Selected Layout';

                        $('#btnGenerateAndPreview').html(
                            '<i class="fas fa-bolt mr-2 text-warning"></i> Generate Barcodes & Preview Labels'
                        );

                        $('#previewContainer').html(`
                <div class="py-4 text-center px-3" id="previewPendingPrompt">
                    <div class="mb-2.5">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 56px; height: 56px; background: rgba(245, 158, 11, 0.1);">
                            <i class="fas fa-barcode fa-lg text-warning"></i>
                        </span>
                    </div>
                    <h6 class="font-weight-bold text-dark mb-1" style="font-size: 15px;">Barcodes Not Generated Yet</h6>
                    <p class="text-muted small mb-2.5" style="font-size: 12px;">
                        You have <strong class="text-primary">${queuedItems.length}</strong> item(s) in queue without assigned barcodes in the catalog.
                        <br><span class="badge badge-warning text-dark mt-1 font-weight-bold" style="font-size: 11px;"><i class="fas fa-bolt mr-1"></i> ${missingCount} item(s) need barcodes generated</span>
                    </p>
                    <div class="alert alert-light border small text-muted text-left mb-3 mx-auto shadow-xs py-2 px-3" style="max-width: 320px; font-size: 11.5px; border-radius: 8px;">
                        <div class="d-flex justify-content-between">
                            <span>Selected Formation:</span>
                            <strong class="text-dark">${escapeHtml(activePresetName)}</strong>
                        </div>
                    </div>
                    <p class="small text-muted mb-3" style="font-size: 11.5px;">Click below to generate barcodes in the Product Master Catalog and view the live preview.</p>
                    <button type="button" class="btn btn-primary font-weight-bold px-4 py-2.5 shadow-sm btn-generate-cta" id="btnPreviewGenerateTrigger">
                        <i class="fas fa-bolt mr-2 text-warning"></i> Generate Barcodes Now
                    </button>
                </div>
            `);
                    } else {
                        if (missingCount > 0) {
                            $('#btnFooterPrint').prop('disabled', false).removeClass('disabled').html(
                                `<i class="fas fa-print mr-2"></i> Print Labels Now (${readyCopies} ${copyLabel})`);
                            $('#previewStatusBadge').removeClass('badge-secondary badge-success').addClass('badge-warning')
                                .html('<i class="fas fa-bolt mr-1"></i> ' + missingCount + ' Missing');
                            $('#statStickerCount').text(readyCopies + ' of ' + totalCopies + ' Ready');
                        } else {
                            $('#btnFooterPrint').prop('disabled', false).removeClass('disabled').html(
                                `<i class="fas fa-print mr-2"></i> Print Labels Now (${readyCopies} ${copyLabel})`);
                            $('#previewStatusBadge').removeClass('badge-warning badge-secondary').addClass('badge-success')
                                .html('<i class="fas fa-check-circle mr-1"></i> Ready');
                            $('#statStickerCount').text(readyCopies + ' ' + copyLabel);
                        }

                        $('#btnGenerateAndPreview').html(
                            missingCount > 0
                                ? '<i class="fas fa-bolt mr-2 text-warning"></i> Generate ' + missingCount + ' Missing Barcode(s)'
                                : '<i class="fas fa-sync-alt mr-2 text-warning"></i> Refresh Preview'
                        );

                        fetchLivePreview();
                    }
                }

                // Enterprise Action: Generate Barcodes & Preview
                $(document).on('click', '#btnGenerateAndPreview, #btnPreviewGenerateTrigger', function(e) {
                    e.preventDefault();
                    if (queuedItems.length === 0) {
                        alert('Please search and add at least one item or variant to the queue first.');
                        return;
                    }

                    const btn = $('#btnGenerateAndPreview');
                    btn.prop('disabled', true).html(
                        '<i class="fas fa-circle-notch fa-spin mr-2"></i> Generating Barcodes & Rendering...'
                        );
                    $('#btnPreviewGenerateTrigger').prop('disabled', true).html(
                        '<i class="fas fa-circle-notch fa-spin mr-2"></i> Generating...');

                    const missingItems = queuedItems.filter(q => !q.barcode);
                    const shouldPersist = $('#persistCatalogCheckbox').is(':checked');

                    if (missingItems.length > 0 && shouldPersist) {
                        $.ajax({
                            url: "{{ route('admin.barcode-labels.generate-all-missing') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                items: missingItems.map(m => ({
                                    target_type: m.target_type,
                                    id: m.id
                                }))
                            },
                            success: function(response) {
                                if (response.success && response.items) {
                                    response.items.forEach(function(updated) {
                                        queuedItems.forEach(function(q) {
                                            if (q.target_type === updated
                                                .target_type && q.id === updated.id
                                                ) {
                                                q.barcode = updated.barcode;
                                            }
                                        });
                                        currentCatalogItems.forEach(function(p) {
                                            if (updated.target_type === 'product' &&
                                                p.id === updated.id) {
                                                p.barcode = updated.barcode;
                                                p.has_barcode = true;
                                            } else if (updated.target_type ===
                                                'variant' && p.variants) {
                                                p.variants.forEach(function(v) {
                                                    if (v.id === updated
                                                        .id) {
                                                        v.barcode = updated
                                                            .barcode;
                                                    }
                                                });
                                            }
                                        });
                                    });
                                }
                                completeGeneration();
                            },
                            error: function(xhr) {
                                btn.prop('disabled', false).html(
                                    '<i class="fas fa-bolt mr-2 text-warning"></i> Generate Barcodes & Preview Labels'
                                    );
                                $('#btnPreviewGenerateTrigger').prop('disabled', false).html(
                                    '<i class="fas fa-bolt mr-2 text-warning"></i> Generate & Preview Labels'
                                    );
                                alert('Generation error: ' + (xhr.responseJSON?.message ||
                                    'Failed to generate barcodes.'));
                            }
                        });
                    } else {
                        completeGeneration();
                    }

                    function completeGeneration() {
                        try {
                            renderQueueTable();
                            isPreviewGenerated = true;
                            const totalCopies = queuedItems.reduce((acc, q) => acc + parseInt(q.qty || 1, 10),
                                0);
                            const copyLabel = totalCopies === 1 ? 'Sticker' : 'Stickers';

                            $('#previewStatusBadge').removeClass('badge-warning badge-secondary').addClass(
                                'badge-success').text('Preview Ready');
                            $('#statStickerCount').text(totalCopies + ' ' + copyLabel);
                            $('#btnFooterPrint').prop('disabled', false).removeClass('disabled').html(
                                `<i class="fas fa-print mr-2"></i> Print Labels Now (${totalCopies} ${copyLabel})`
                                );

                            btn.prop('disabled', false).html(
                                '<i class="fas fa-sync-alt mr-2 text-warning"></i> Regenerate & Update Preview'
                                );
                            $('#btnPreviewGenerateTrigger').prop('disabled', false).html(
                                '<i class="fas fa-bolt mr-2 text-warning"></i> Generate & Preview Labels');

                            fetchLivePreview();
                            if (typeof toastr !== 'undefined') {
                                toastr.success('Barcodes generated and preview ready for printing!');
                            }
                        } catch (err) {
                            console.error("completeGeneration error", err);
                            btn.prop('disabled', false).html(
                                '<i class="fas fa-sync-alt mr-2 text-warning"></i> Regenerate & Update Preview'
                                );
                            $('#btnPreviewGenerateTrigger').prop('disabled', false).html(
                                '<i class="fas fa-bolt mr-2 text-warning"></i> Generate & Preview Labels');
                        }
                    }
                });

                // Footer Print Button
                $(document).on('click', '#btnFooterPrint', function(e) {
                    e.preventDefault();
                    if (!isPreviewGenerated || queuedItems.length === 0) {
                        alert(
                            'Please click "Generate Barcodes & Preview Labels" first to generate barcodes and inspect the layout before printing.');
                        return;
                    }
                    $('#barcodePrintForm').submit();
                });

                // Live Preview Trigger (Debounced WYSIWYG)
                function triggerPreviewUpdate() {
                    clearTimeout(previewDebounceTimer);
                    previewDebounceTimer = setTimeout(function() {
                        if (isPreviewGenerated) {
                            fetchLivePreview();
                        } else {
                            updatePreviewState();
                        }
                    }, 120);
                }

                function fetchLivePreview() {
                    if (queuedItems.length === 0 || !isPreviewGenerated) {
                        updatePreviewState();
                        return;
                    }

                    const itemsPayload = queuedItems.map(q => ({
                        target_type: q.target_type,
                        id: q.id,
                        qty: q.qty,
                        use_stock: q.use_stock
                    }));

                    $('#formItemsJson').val(JSON.stringify(itemsPayload));

                    $('#previewContainer').html(`
            <div class="py-5 text-center text-muted">
                <i class="fas fa-spinner fa-spin fa-2x mb-3 text-primary"></i>
                <div class="font-weight-bold text-dark">Rendering High-Resolution Vector Labels...</div>
                <div class="small text-muted mt-1">Calculating centered physical geometries & margins</div>
            </div>
        `);

                    const postData = {
                        _token: "{{ csrf_token() }}",
                        preset: $('input[name="preset"]:checked').val(),
                        show_price: $('#togglePrice').is(':checked') ? 1 : 0,
                        show_name: $('#toggleName').is(':checked') ? 1 : 0,
                        show_sku: $('#toggleSku').is(':checked') ? 1 : 0,
                        show_brand: $('#toggleBrand').is(':checked') ? 1 : 0,
                        show_barcode_text: $('#toggleBarcodeText').is(':checked') ? 1 : 0,
                        show_variant_spec: $('#toggleVariantSpec').is(':checked') ? 1 : 0,
                        show_qr_code: 0,
                        label_format: '1d',
                        items: itemsPayload
                    };

                    $.ajax({
                        url: "{{ route('admin.barcode-labels.preview') }}",
                        type: "POST",
                        data: postData,
                        success: function(responseHtml) {
                            $('#previewContainer').html(responseHtml);
                            const readyItems = queuedItems.filter(q => q.barcode);
                            const readyCopies = readyItems.reduce((acc, q) => acc + parseInt(q.qty || 1, 10), 0);
                            const missingCount = queuedItems.length - readyItems.length;
                            const copyLabel = readyCopies === 1 ? 'Sticker' : 'Stickers';

                            if (readyItems.length > 0) {
                                if (missingCount > 0) {
                                    $('#statStickerCount').text(readyCopies + ' of ' + totalCopies + ' Ready');
                                    $('#previewStatusBadge').removeClass('badge-secondary badge-success').addClass('badge-warning').html('<i class="fas fa-bolt mr-1"></i> ' + missingCount + ' Missing');
                                } else {
                                    $('#statStickerCount').text(readyCopies + ' ' + copyLabel);
                                    $('#previewStatusBadge').removeClass('badge-warning badge-secondary').addClass('badge-success').html('<i class="fas fa-check-circle mr-1"></i> Ready');
                                }
                                $('#btnFooterPrint').prop('disabled', false).removeClass('disabled').html(
                                    `<i class="fas fa-print mr-2"></i> Print Labels Now (${readyCopies} ${copyLabel})`
                                );
                            } else {
                                $('#statStickerCount').text('0 of ' + totalCopies + ' Ready');
                                $('#previewStatusBadge').removeClass('badge-success badge-secondary').addClass('badge-warning').text('Needs Gen');
                                $('#btnFooterPrint').prop('disabled', true).addClass('disabled').html(
                                    '<i class="fas fa-print mr-2"></i> Print Labels Now'
                                );
                            }

                            $('#btnGenerateAndPreview').prop('disabled', false).html(
                                missingCount > 0
                                    ? '<i class="fas fa-bolt mr-2 text-warning"></i> Generate ' + missingCount + ' Missing Barcode(s)'
                                    : '<i class="fas fa-sync-alt mr-2 text-warning"></i> Refresh Preview'
                            );
                        },
                        error: function(xhr) {
                            console.error("Preview error", xhr);
                            $('#btnGenerateAndPreview').prop('disabled', false).html(
                                '<i class="fas fa-sync-alt mr-2 text-warning"></i> Regenerate & Update Preview'
                                );
                            $('#previewContainer').html(`
                    <div class="py-4 text-center text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <h6 class="font-weight-bold">Preview Rendering Error</h6>
                        <p class="small mb-2">Unable to render vector preview. Please click generate again.</p>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnRetryPreview">Retry Preview</button>
                    </div>
                `);
                        }
                    });
                }

                // Chip Active State Sync
                $(document).on('change', '.customizer-checkbox', function() {
                    if ($(this).is(':checked')) {
                        $(this).closest('.customizer-chip').addClass('active');
                    } else {
                        $(this).closest('.customizer-chip').removeClass('active');
                    }
                    triggerPreviewUpdate();
                });

                // Label Format Radio Change
                $(document).on('change', 'input[name="label_format"]', function() {
                    triggerPreviewUpdate();
                });

                // Clicking persistence card toggles switch
                $(document).on('click', '#persistCardTrigger', function(e) {
                    if (!$(e.target).is('#persistCatalogCheckbox') && !$(e.target).is(
                            '.enterprise-switch-slider')) {
                        const chk = $('#persistCatalogCheckbox');
                        chk.prop('checked', !chk.is(':checked')).trigger('change');
                    }
                });

                $(document).on('click', '#btnRetryPreview', function() {
                    fetchLivePreview();
                });

                function escapeHtml(text) {
                    if (!text) return '';
                    return String(text)
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#039;");
                }

                // Submit Validation
                $('#barcodePrintForm').on('submit', function(e) {
                    if (queuedItems.length === 0) {
                        e.preventDefault();
                        alert('Please search and add at least one item or variant to the print queue first.');
                        return false;
                    }

                    if (!isPreviewGenerated) {
                        e.preventDefault();
                        alert(
                            'Please click "Generate Barcodes & Preview Labels" first to inspect labels before printing.');
                        return false;
                    }
                });
            });
        </script>
    @endpush
@endsection
