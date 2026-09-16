@extends('backend.layouts.master')
@section('title', ($settings->site_name ?? 'B2B Viking ERP') . ' | Low Stock Alert')

@push('css')
<style>
    :root {
        --sr-amber: #d4a24e;
        --sr-amber-bright: #ecc78b;
        --sr-amber-deep: #b8852a;
        --sr-amber-soft: rgba(212, 162, 78, 0.08);
        --sr-border: rgba(11, 17, 32, 0.07);
        --sr-border-hover: rgba(212, 162, 78, 0.18);
        --sr-ink: #161e2e;
        --sr-ink-soft: #2d3748;
        --sr-muted: #6b788e;
        --sr-surface: #f8f9fc;
        --sr-radius: 14px;
        --sr-radius-lg: 20px;
        --sr-shadow: 0 1px 3px rgba(11,17,32,0.04), 0 8px 20px -12px rgba(11,17,32,0.12);
        --sr-shadow-hover: 0 12px 32px -12px rgba(11,17,32,0.16), 0 0 0 1px rgba(212,162,78,0.06);
        --sr-font: 'Inter', 'Segoe UI', system-ui, sans-serif;
    }

    /* ============================
       PAGE HEADER
       ============================ */
    .sr-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 22px;
        padding-top: 18px;
    }
    .sr-page-header h1 {
        display: flex;
        align-items: center;
        gap: 12px;
        font-family: var(--sr-font);
        font-weight: 800;
        font-size: 21px;
        color: var(--sr-ink);
        letter-spacing: -0.3px;
        margin: 0;
    }
    .sr-page-header .sr-icon-badge {
        width: 36px; height: 36px; min-width: 36px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 11px;
        font-size: 14px;
        color: #fff;
        background: linear-gradient(145deg, #f59e0b, #d97706);
        box-shadow: 0 4px 14px rgba(217, 119, 6, 0.35);
    }
    .sr-breadcrumb {
        display: flex; align-items: center; gap: 6px;
        font-size: 12.5px; font-weight: 600;
    }
    .sr-breadcrumb span { color: var(--sr-muted); position: relative; padding-right: 14px; }
    .sr-breadcrumb span + span { padding-left: 14px; }
    .sr-breadcrumb span + span::before {
        content: '/'; position: absolute; left: 0; color: rgba(11,17,32,0.15);
    }
    .sr-breadcrumb a { color: var(--sr-amber); text-decoration: none; transition: color 0.2s; }
    .sr-breadcrumb a:hover { color: var(--sr-amber-deep); }
    .sr-breadcrumb .active { color: var(--sr-amber-deep); }

    /* ============================
       FILTER CARD (Matching Stock Report Design)
       ============================ */
    .sr-filter-card {
        background: #fff;
        border: 1px solid var(--sr-border);
        border-radius: var(--sr-radius);
        box-shadow: var(--sr-shadow);
        overflow: hidden;
        margin-bottom: 22px;
    }
    .sr-filter-head {
        padding: 14px 20px;
        background: linear-gradient(135deg, #fafbfc, #f4f5f8);
        border-bottom: 1px solid var(--sr-border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .sr-filter-head h4 {
        font-family: var(--sr-font);
        font-weight: 800; font-size: 13.5px;
        color: var(--sr-ink); margin: 0;
        display: flex; align-items: center; gap: 8px;
    }
    .sr-filter-head h4 i { color: var(--sr-amber); font-size: 13px; }
    .sr-filter-head .sr-collapse-btn {
        width: 28px; height: 28px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        background: var(--sr-surface);
        border: 1px solid var(--sr-border);
        color: var(--sr-muted);
        cursor: pointer;
        transition: all 0.2s;
        font-size: 11px;
    }
    .sr-filter-head .sr-collapse-btn:hover { background: var(--sr-amber-soft); color: var(--sr-amber); border-color: var(--sr-border-hover); }
    .sr-filter-body { padding: 18px 20px; }
    .sr-filter-body label {
        font-family: var(--sr-font);
        font-weight: 700; font-size: 11.5px;
        color: var(--sr-ink-soft);
        margin-bottom: 5px;
        display: block;
    }
    .sr-filter-body .select2-selection--single {
        height: 38px !important;
        border-radius: 10px !important;
        border: 1.5px solid var(--sr-border) !important;
        display: flex !important; align-items: center;
    }
    .sr-filter-body .select2-selection--single .select2-selection__rendered {
        color: var(--sr-ink); font-size: 12.5px; font-weight: 500; padding-left: 14px; line-height: 36px;
    }
    .sr-filter-body .select2-selection--single .select2-selection__arrow { height: 36px; right: 10px; }
    .sr-filter-body .select2-container--focus .select2-selection--single,
    .sr-filter-body .select2-container--open .select2-selection--single {
        border-color: var(--sr-amber) !important;
        box-shadow: 0 0 0 3px var(--sr-amber-soft) !important;
    }

    /* ============================
       BUTTONS
       ============================ */
    .sr-btn {
        border: none;
        font-family: var(--sr-font);
        font-weight: 700;
        font-size: 11px;
        border-radius: var(--sr-radius-lg);
        padding: 7px 16px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(.2,.8,.2,1);
        text-decoration: none;
        white-space: nowrap;
    }
    .sr-btn:hover { transform: translateY(-1.5px); }
    .sr-btn.rose { background: linear-gradient(145deg, #fb7185, #e11d48); color: #fff; box-shadow: 0 4px 12px -4px rgba(225,29,72,0.3); }
    .sr-btn.rose:hover { filter: brightness(1.06); box-shadow: 0 6px 18px -4px rgba(225,29,72,0.4); color: #fff; }
    .sr-btn.amber { background: linear-gradient(145deg, var(--sr-amber-bright), var(--sr-amber-deep)); color: #1a1306; box-shadow: 0 4px 12px -4px rgba(212,162,78,0.3); }
    .sr-btn.amber:hover { filter: brightness(1.06); box-shadow: 0 6px 18px -4px rgba(212,162,78,0.4); color: #1a1306; }

    /* ============================
       TABLE CARD
       ============================ */
    .sr-table-card {
        background: #fff;
        border: 1px solid var(--sr-border);
        border-radius: var(--sr-radius);
        box-shadow: var(--sr-shadow);
        overflow: hidden;
    }
    .sr-table-head {
        padding: 14px 20px;
        background: linear-gradient(135deg, #fafbfc, #f4f5f8);
        border-bottom: 1px solid var(--sr-border);
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
        min-height: 60px; /* Lock height to prevent vertical jitter on cart button toggle */
    }
    .sr-table-head h4 {
        font-family: var(--sr-font);
        font-weight: 800; font-size: 13.5px;
        color: var(--sr-ink); margin: 0;
        display: flex; align-items: center; gap: 8px;
    }
    .sr-table-head h4 i { color: #f59e0b; font-size: 13px; }
    .sr-table-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 34px; /* Ensure action wrapper preserves vertical geometry */
    }

    /* ============================
       STABLE TABLE ARCHITECTURE
       Prevents column shaking, layout recalculation, and jittering on click
       ============================ */
    .sr-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        width: 100%;
    }
    #low-stock-table {
        table-layout: fixed !important;
        width: 100% !important;
        min-width: 820px;
        margin: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }
    #low-stock-table th,
    #low-stock-table td {
        min-width: 0 !important; /* Neutralizes custom.css 80px min-width rule */
        vertical-align: middle !important;
        box-sizing: border-box !important;
    }
    #low-stock-table thead th {
        background: linear-gradient(135deg, #fafbfc, #f4f5f8);
        color: var(--sr-ink-soft);
        font-family: var(--sr-font);
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 14px;
        border: none;
        border-bottom: 2px solid var(--sr-border);
        white-space: nowrap;
        user-select: none;
    }
    #low-stock-table tbody td {
        padding: 10px 14px;
        color: var(--sr-ink);
        border-bottom: 1px solid var(--sr-border);
        background: #fff;
        font-size: 12.5px;
        font-weight: 500;
        transition: background 0.15s;
    }
    #low-stock-table tbody tr:hover td {
        background: var(--sr-amber-soft) !important;
    }
    #low-stock-table tbody tr.table-danger td {
        background: #fff5f5;
    }
    #low-stock-table tbody tr.table-danger:hover td {
        background: #fee2e2 !important;
    }
    #low-stock-table tbody tr.table-warning-soft td {
        background: #fffdf5;
    }
    #low-stock-table tbody tr.table-warning-soft:hover td {
        background: #fef9c3 !important;
    }
    #low-stock-table tr.variant-drawer-row td {
        overflow: visible !important;
        white-space: normal !important;
    }
    .drawer-chevron {
        display: inline-block;
        transition: transform 0.2s ease-in-out;
    }
    .drawer-chevron.rotated {
        transform: rotate(180deg);
    }
    .btn-xs {
        padding: 1px 6px;
        font-size: 11px;
        line-height: 1.5;
        border-radius: 4px;
    }
    .add-variant-to-basket.added {
        background: #10b981 !important;
        color: #fff !important;
        border-color: #10b981 !important;
    }

    #low-stock-table td {
        overflow: hidden; /* Guarantee that no cell content can ever bleed across columns */
    }

    /* Specific column width geometry */
    .th-check { width: 44px; text-align: center; }
    .th-img   { width: 54px; text-align: center; }
    .th-name  { width: 24%; }
    .th-cat   { width: 15%; }
    .th-brand { width: 18%; }
    .th-stock { width: 13%; text-align: center; }
    .th-stat  { width: 13%; text-align: center; }
    .th-act   { width: 64px; text-align: center; }

    .product-title {
        font-weight: 600;
        color: var(--sr-ink);
        line-height: 1.35;
        margin-bottom: 2px;
        word-break: break-word;
    }
    .product-meta {
        font-size: 11px;
        color: var(--sr-muted);
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    /* Modern category & brand badge pills with strict boundary overflow containment */
    .sr-badge-pill {
        display: inline-block;
        max-width: 100%;
        font-size: 11px;
        font-weight: 600;
        color: #475569;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        padding: 3px 8px;
        border-radius: 6px;
        line-height: 1.35;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
        box-sizing: border-box;
    }
    .sr-badge-pill.brand {
        color: #334155;
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    /* Cart Action Button State Transitions */
    .add-to-basket.added {
        background-color: #28a745 !important;
        border-color: #28a745 !important;
        color: #fff !important;
    }

    #add_to_booking_btn {
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    /* AJAX container transition */
    #products-container {
        transition: opacity 0.2s ease-in-out;
    }

    /* Mobile / Responsive */
    @media (max-width: 767.98px) {
        .sr-filter-head, .sr-table-head { padding: 12px 16px; }
        .sr-filter-body { padding: 14px 16px; }
        .sr-filter-body .col-md-4,
        .sr-filter-body .col-md-3,
        .sr-filter-body .col-md-2 { margin-bottom: 12px; }
        .sr-table-actions { width: 100%; justify-content: flex-end; }
    }
</style>
@endpush

@section('content')
    <section class="section">
        <div class="sr-page-header">
            <h1>
                <div class="sr-icon-badge">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                Low Stock Alert
            </h1>
            <div class="sr-breadcrumb">
                <span><a href="{{ route('admin.reports.index') }}">Reports</a></span>
                <span class="active">Low Stock Alert</span>
            </div>
        </div>

        <div class="section-body">
            {{-- Filter Section (Automatic AJAX Filtering — No manual Filter button needed) --}}
            <div class="sr-filter-card">
                <div class="sr-filter-head">
                    <h4><i class="fas fa-filter"></i> Filter Options</h4>
                    <a data-collapse="#low-stock-filter-collapse" class="sr-collapse-btn" href="#"><i class="fas fa-minus"></i></a>
                </div>
                <div class="collapse show" id="low-stock-filter-collapse">
                    <div class="sr-filter-body">
                        <form id="low-stock-filter-form" action="{{ route('admin.reports.low-stock') }}" method="GET" onsubmit="return false;">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4 col-sm-6">
                                    <label>Category</label>
                                    <select name="category_id" id="category_filter" class="form-control select2">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label>Brand</label>
                                    <select name="brand_id" id="brand_filter" class="form-control select2">
                                        <option value="">All Brands</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label>Vendor</label>
                                    <select name="vendor_id" id="vendor_filter" class="form-control select2">
                                        <option value="">All Vendors</option>
                                        @foreach ($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->shop_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-6">
                                    <button type="button" id="btn-reset-filter" class="sr-btn rose w-100" style="margin-top: 4px; justify-content: center; height: 38px;">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Main Table Card --}}
            <div class="sr-table-card">
                <div class="sr-table-head">
                    <h4>
                        <i class="fas fa-boxes text-warning"></i>
                        Products Below Minimum Inventory Level
                    </h4>
                    <div class="sr-table-actions">
                        <button type="button" class="sr-btn amber font-weight-bold" id="add_to_booking_btn" style="display: none;">
                            <i class="fas fa-shopping-basket"></i> Add to Procurement Cart (<span id="selected_count">0</span>)
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="products-container" class="p-3">
                        @include('backend.reports.partials.low_stock_table')
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Variant Selection Modal -->
    <div class="modal fade" id="quickVariantModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 540px; width: 95%;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-white border-bottom py-3 px-4">
                    <h5 class="modal-title font-weight-bold text-dark mb-0" id="quickVariantModalTitle" style="font-size: 15px;">
                        <i class="fas fa-layer-group text-warning mr-2"></i>Select Variants for Procurement
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3 p-md-4" style="max-height: 70vh; overflow-y: auto;">
                    <div class="d-flex align-items-center mb-3 p-2 bg-light rounded" style="gap: 12px; border: 1px solid #e2e8f0;">
                        <img id="variant_modal_product_img" src="{{ asset('uploads/no-image.svg') }}" onerror="this.onerror=null; this.src='{{ asset('uploads/no-image.svg') }}';" style="width: 48px; height: 48px; object-fit: contain; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; padding: 2px;">
                        <div class="flex-fill" style="min-width: 0;">
                            <h6 id="variant_modal_product_name" class="mb-0 text-truncate font-weight-bold text-dark" style="font-size: 14px;">Product Name</h6>
                            <small class="text-muted d-block text-truncate" id="variant_modal_product_vendor">Supplier</small>
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: hidden;">
                        <table class="table table-sm table-bordered mb-0" style="table-layout: fixed; width: 100%;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 48%;">Variant Name</th>
                                    <th class="text-center" style="width: 24%;">Stock</th>
                                    <th class="text-center" style="width: 28%;">Order Qty</th>
                                </tr>
                            </thead>
                            <tbody id="quick_variant_modal_tbody">
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">
                                        <div class="spinner-border spinner-border-sm text-warning mr-1"></div> Loading variants...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Cancel</button>
                    <button type="button" class="btn btn-amber btn-sm px-4 font-weight-bold shadow-sm" id="btn_submit_quick_variants" style="border-radius: 6px;">
                        <i class="fas fa-check mr-1"></i> Update Basket
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize searchable Select2 on all filters
            $('.select2').select2({ width: '100%' });

            // --- Cross-page Persisted Selections (localStorage) ---
            const STORAGE_KEY = 'low_stock_selected_ids';

            function getPersistedIds() {
                try {
                    return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
                } catch(e) {
                    return [];
                }
            }

            function savePersistedIds(ids) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
            }

            function updatePersistedUI() {
                const ids = getPersistedIds();
                $('#selected_count').text(ids.length);
                if (ids.length > 0) {
                    $('#add_to_booking_btn').css({
                        'display': 'inline-flex'
                    });
                } else {
                    $('#add_to_booking_btn').css({
                        'display': 'none'
                    });
                }
            }

            // Restore checkboxes from persisted selections
            function restoreSelections() {
                const ids = getPersistedIds();
                if (ids.length === 0) {
                    $('.product-checkbox').prop('checked', false);
                    $('#select_all').prop('checked', false);
                    updatePersistedUI();
                    return;
                }
                $('.product-checkbox').each(function() {
                    if (ids.indexOf(Number($(this).val())) !== -1 || ids.indexOf($(this).val().toString()) !== -1) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked', false);
                    }
                });
                $('#select_all').prop('checked', $('.product-checkbox:checked').length === $('.product-checkbox').length && $('.product-checkbox').length > 0);
                updatePersistedUI();
            }

            // --- Unified Cart Synchronization Logic ---
            let activeBookingIds = [];

            function syncCartState() {
                if (window.cartStore && window.cartStore.booking && window.cartStore.booking.ids.length > 0) {
                    activeBookingIds = (window.cartStore.booking.ids || []).map(Number);
                    applyButtonStates();
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.cart.all-state') }}",
                    method: 'GET',
                    success: function(data) {
                        if (data && data.booking) {
                            if (window.cartStore) {
                                if (!window.cartStore.booking.items.length) window.cartStore.booking = data.booking;
                            }
                            activeBookingIds = (data.booking.ids || []).map(Number);
                            if (window.updateGlobalCartBadges) {
                                window.updateGlobalCartBadges(data.booking.count, data.request ? data.request.count : undefined);
                            }
                            applyButtonStates();
                        }
                    }
                });
            }

            function applyButtonStates() {
                const bIds = (window.cartStore && window.cartStore.booking && window.cartStore.booking.ids) ? window.cartStore.booking.ids.map(Number) : activeBookingIds;
                const storeItems = (window.cartStore && window.cartStore.booking && window.cartStore.booking.items) ? window.cartStore.booking.items : [];

                $('.add-to-basket').each(function() {
                    const id = Number($(this).data('id'));
                    const hasVariants = Number($(this).data('has-variants')) === 1;
                    if (bIds.includes(id)) {
                        $(this).addClass('added').html('<i class="fas fa-check"></i>').attr('title', 'Added to Procurement Basket');
                    } else {
                        $(this).removeClass('added').html(`<i class="fas ${hasVariants ? 'fa-layer-group' : 'fa-shopping-basket'}"></i>`).attr('title', hasVariants ? 'Select Variants for Procurement' : 'Add to Procurement Basket');
                    }
                });

                $('.add-variant-to-basket').each(function() {
                    const pId = Number($(this).data('product-id'));
                    const vId = Number($(this).data('variant-id'));
                    const isAdded = storeItems.some(i => Number(i.product_id) === pId && Number(i.variant_id) === vId);
                    if (isAdded) {
                        $(this).addClass('added btn-success').removeClass('btn-outline-warning').html('<i class="fas fa-check mr-1"></i> In Basket');
                    } else {
                        $(this).removeClass('added btn-success').addClass('btn-outline-warning').html('<i class="fas fa-cart-plus mr-1"></i> Reorder');
                    }
                });
            }

            window.reapplyCartButtonStates = applyButtonStates;

            // Initial UI sync
            restoreSelections();
            syncCartState();

            // --- AJAX Auto-Filtering Engine ---
            let isAjaxLoading = false;

            function fetchFilteredProducts(targetUrl = null) {
                if (isAjaxLoading) return;
                isAjaxLoading = true;

                const $container = $('#products-container');
                $container.css({ 'opacity': '0.5', 'pointer-events': 'none' });

                const categoryId = $('#category_filter').val() || '';
                const brandId = $('#brand_filter').val() || '';
                const vendorId = $('#vendor_filter').val() || '';

                let url = targetUrl || "{{ route('admin.reports.low-stock') }}";
                let finalUrl = url;

                if (targetUrl) {
                    const parsedUrl = new URL(targetUrl, window.location.origin);
                    if (categoryId) parsedUrl.searchParams.set('category_id', categoryId);
                    else parsedUrl.searchParams.delete('category_id');

                    if (brandId) parsedUrl.searchParams.set('brand_id', brandId);
                    else parsedUrl.searchParams.delete('brand_id');

                    if (vendorId) parsedUrl.searchParams.set('vendor_id', vendorId);
                    else parsedUrl.searchParams.delete('vendor_id');

                    finalUrl = parsedUrl.toString();
                } else {
                    const params = new URLSearchParams();
                    if (categoryId) params.set('category_id', categoryId);
                    if (brandId) params.set('brand_id', brandId);
                    if (vendorId) params.set('vendor_id', vendorId);
                    const qs = params.toString();
                    finalUrl = url + (qs ? '?' + qs : '');
                }

                $.ajax({
                    url: finalUrl,
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response && response.html) {
                            $container.html(response.html);

                            // Seamlessly update URL in browser without full reload
                            if (window.history && window.history.pushState) {
                                window.history.pushState({ path: finalUrl }, '', finalUrl);
                            }

                            // Re-apply preserved state
                            restoreSelections();
                            applyButtonStates();
                        }
                    },
                    error: function() {
                        if (window.toastr) {
                            toastr.error('Failed to load low stock products');
                        }
                    },
                    complete: function() {
                        $container.css({ 'opacity': '1', 'pointer-events': 'auto' });
                        isAjaxLoading = false;
                    }
                });
            }

            // Auto-trigger AJAX filter whenever any Select2 option changes
            $(document).on('change select2:select', '#category_filter, #brand_filter, #vendor_filter', function () {
                fetchFilteredProducts();
            });

            // Reset button click (Clears all dropdowns and smoothly reloads via AJAX)
            $(document).on('click', '#btn-reset-filter', function (e) {
                e.preventDefault();
                $('#category_filter').val('').trigger('change.select2');
                $('#brand_filter').val('').trigger('change.select2');
                $('#vendor_filter').val('').trigger('change.select2');
                fetchFilteredProducts("{{ route('admin.reports.low-stock') }}");
            });

            // Intercept pagination clicks for seamless AJAX page navigation
            $(document).on('click', '#products-container .pagination a', function (e) {
                e.preventDefault();
                const pageUrl = $(this).attr('href');
                if (pageUrl) {
                    fetchFilteredProducts(pageUrl);
                    $('html, body').animate({
                        scrollTop: $('#products-container').offset().top - 120
                    }, 200);
                }
            });

            // Handle browser Back/Forward buttons
            window.addEventListener('popstate', function () {
                const currentUrl = window.location.href;
                const parsed = new URL(currentUrl);
                $('#category_filter').val(parsed.searchParams.get('category_id') || '').trigger('change.select2');
                $('#brand_filter').val(parsed.searchParams.get('brand_id') || '').trigger('change.select2');
                $('#vendor_filter').val(parsed.searchParams.get('vendor_id') || '').trigger('change.select2');
                fetchFilteredProducts(currentUrl);
            });

            // Add/Toggle Procurement Basket Click (Single Item)
            $(document).on('click', '.add-to-basket', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const $btn = $(this);
                if ($btn.data('is-busy')) return;

                const productId = Number($btn.data('id'));
                if (!productId) return;

                const hasVariants = Number($btn.data('has-variants')) === 1;

                // If product has variants, open Quick Variant Modal
                if (hasVariants) {
                    const productName = $btn.data('product-name') || $btn.closest('tr').find('.product-title').text().trim();
                    const productImg = $btn.data('product-img') || $btn.closest('tr').find('img').attr('src');
                    const vendorName = $btn.data('vendor-name') || 'Primary Supplier';
                    openQuickVariantModal(productId, productName, productImg, vendorName, $btn);
                    return;
                }

                // Simple product direct toggle
                $btn.data('is-busy', true);
                const isAlreadyAdded = $btn.hasClass('added');
                const desiredAction = isAlreadyAdded ? 'remove' : 'add';

                // Optimistic UI update
                if (isAlreadyAdded) {
                    $btn.removeClass('added').html('<i class="fas fa-shopping-basket"></i>');
                    if (window.toastr) toastr.info('Removed from Procurement basket');

                    if (window.cartStore && window.cartStore.booking) {
                        window.cartStore.booking.ids = window.cartStore.booking.ids.filter(id => Number(id) !== productId);
                        window.cartStore.booking.items = window.cartStore.booking.items.filter(i => Number(i.product_id) !== productId);
                        window.cartStore.booking.count = window.cartStore.booking.items.length;
                        if (window.updateGlobalCartBadges) {
                            window.updateGlobalCartBadges(window.cartStore.booking.count, undefined);
                        }
                    }
                } else {
                    $btn.addClass('added').html('<i class="fas fa-check"></i>');
                    if (window.toastr) toastr.success('Added to Procurement basket');

                    if (window.cartStore && window.cartStore.booking) {
                        if (!window.cartStore.booking.ids.map(Number).includes(productId)) {
                            window.cartStore.booking.ids.push(productId);
                        }
                        const exists = window.cartStore.booking.items.some(i => Number(i.product_id) === productId);
                        if (!exists) {
                            window.cartStore.booking.items.push({
                                id: `temp_${productId}`,
                                cart_id: `temp_${productId}`,
                                product_id: productId,
                                variant_id: null,
                                variant_name: '',
                                product_name: $btn.closest('tr').find('.product-title').text().trim() || 'Product',
                                thumb_image: $btn.closest('tr').find('img').attr('src') || "{{ asset('uploads/no-image.svg') }}",
                                vendor_name: 'Primary Supplier',
                                sku: '',
                                price: 0,
                                quantity: 1
                            });
                        }
                        window.cartStore.booking.count = window.cartStore.booking.items.length;
                        if (window.updateGlobalCartBadges) {
                            window.updateGlobalCartBadges(window.cartStore.booking.count, undefined);
                        }
                    }
                }

                $.ajax({
                    url: "{{ route('admin.cart.add') }}",
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        product_id: productId,
                        cart_type: 'booking',
                        action: desiredAction
                    },
                    success: function(response) {
                        if (response.success && response.item && window.cartStore && window.cartStore.booking) {
                            const idx = window.cartStore.booking.items.findIndex(i => Number(i.product_id) === productId && !i.variant_id);
                            if (idx !== -1) {
                                window.cartStore.booking.items[idx] = response.item;
                            }
                        }
                        applyButtonStates();
                    },
                    error: function(xhr) {
                        if (isAlreadyAdded) {
                            $btn.addClass('added').html('<i class="fas fa-check"></i>');
                        } else {
                            $btn.removeClass('added').html('<i class="fas fa-shopping-basket"></i>');
                        }
                        if (window.toastr) toastr.error('Error updating basket');
                    },
                    complete: function() {
                        $btn.data('is-busy', false);
                    }
                });
            });

            // --- Toggle Variant Drawer (Accordion) ---
            $(document).on('click', '.toggle-variant-drawer', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const targetId = $(this).data('target');
                const $drawer = $(targetId);
                const $chevron = $(this).find('.drawer-chevron');
                $drawer.toggle();
                if ($drawer.is(':visible')) {
                    $chevron.addClass('rotated');
                } else {
                    $chevron.removeClass('rotated');
                }
            });

            // --- Quick Variant Modal Logic ---
            let currentModalContext = null;

            function openQuickVariantModal(productId, productName, productImg, vendorName, $btn) {
                currentModalContext = { productId, productName, productImg, vendorName, $btn };
                $('#variant_modal_product_name').text(productName);
                const defaultImg = "{{ asset('uploads/no-image.svg') }}";
                $('#variant_modal_product_img').attr('src', productImg || defaultImg);
                $('#variant_modal_product_vendor').text(vendorName);
                $('#quickVariantModalTitle').html('<i class="fas fa-layer-group text-warning mr-2"></i>Select Variants for Procurement');
                
                $('#quick_variant_modal_tbody').html('<tr><td colspan="3" class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm text-warning mr-1"></div> Loading variants...</td></tr>');
                $('#quickVariantModal').modal('show');

                $.ajax({
                    url: `/admin/products/${productId}/variants`,
                    type: 'GET',
                    success: function(res) {
                        if (res && res.status === 'success' && res.variants && res.variants.length > 0) {
                            if (res.product) {
                                if (res.product.name) $('#variant_modal_product_name').text(res.product.name);
                                if (res.product.thumb_image) $('#variant_modal_product_img').attr('src', res.product.thumb_image);
                                if (res.product.vendor || res.product.category) $('#variant_modal_product_vendor').text(res.product.vendor || res.product.category);
                            }

                            let rows = '';
                            const storeItems = (window.cartStore && window.cartStore.booking && window.cartStore.booking.items) ? window.cartStore.booking.items : [];
                            
                            res.variants.forEach(function(v) {
                                let vName = v.name;
                                if (!vName) {
                                    const parts = [];
                                    if (v.color) parts.push(v.color);
                                    if (v.size) parts.push(v.size);
                                    vName = parts.length > 0 ? parts.join(' - ') : 'Variant #' + v.id;
                                }

                                const existingItem = storeItems.find(i => Number(i.product_id) === productId && Number(i.variant_id) === Number(v.id));
                                const defaultQty = existingItem ? (parseFloat(existingItem.quantity) || 0) : 0;
                                const vStock = parseFloat(v.qty || 0);

                                rows += `
                                    <tr>
                                        <td class="align-middle text-truncate" title="${vName}" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <strong style="font-size: 13px;">${vName}</strong>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge ${vStock > 10 ? 'badge-success' : (vStock > 0 ? 'badge-warning text-dark' : 'badge-danger')} font-weight-bold" style="font-size: 11px; padding: 3px 7px;">${vStock}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <input type="number" step="any" min="0" class="form-control form-control-sm text-center variant-bulk-qty font-weight-bold" data-variant-id="${v.id}" data-variant-name="${vName}" data-price="${v.price || 0}" value="${defaultQty}" onfocus="if(this.value==='0') this.select();" style="border-radius: 5px;">
                                        </td>
                                    </tr>
                                `;
                            });
                            $('#quick_variant_modal_tbody').html(rows);
                        } else {
                            $('#quick_variant_modal_tbody').html('<tr><td colspan="3" class="text-center py-3 text-muted">No variants found for this product.</td></tr>');
                        }
                    },
                    error: function() {
                        $('#quick_variant_modal_tbody').html('<tr><td colspan="3" class="text-center py-3 text-danger">Failed to load variants.</td></tr>');
                    }
                });
            }

            // Submit Quick Variants Modal
            $(document).on('click', '#btn_submit_quick_variants', function(e) {
                e.preventDefault();
                if (!currentModalContext) return;

                const { productId, productName, productImg, vendorName, $btn } = currentModalContext;
                const variantsToAdd = [];

                $('.variant-bulk-qty').each(function() {
                    const qty = parseFloat($(this).val()) || 0;
                    const vId = Number($(this).data('variant-id'));
                    const vName = $(this).data('variant-name') || '';
                    const price = parseFloat($(this).data('price')) || 0;

                    if (vId > 0 && qty > 0) {
                        variantsToAdd.push({
                            variant_id: vId,
                            variant_name: vName,
                            quantity: qty,
                            price: price
                        });
                    }
                });

                if (variantsToAdd.length === 0) {
                    if (window.cartStore && window.cartStore.booking) {
                        window.cartStore.booking.items = window.cartStore.booking.items.filter(i => Number(i.product_id) !== productId);
                        window.cartStore.booking.ids = window.cartStore.booking.ids.filter(id => Number(id) !== productId);
                        window.cartStore.booking.count = window.cartStore.booking.items.length;
                        if (window.updateGlobalCartBadges) {
                            window.updateGlobalCartBadges(window.cartStore.booking.count, undefined);
                        }
                    }
                    $(`.add-to-basket[data-id="${productId}"]`).removeClass('added').html('<i class="fas fa-layer-group"></i>');
                    if (window.toastr) toastr.info('Removed from Procurement basket');
                    $('#quickVariantModal').modal('hide');

                    $.ajax({
                        url: "{{ route('admin.cart.add') }}",
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        data: {
                            product_id: productId,
                            cart_type: 'booking',
                            action: 'bulk_variants',
                            is_bulk_variants: 1,
                            variants: []
                        },
                        complete: function() {
                            applyButtonStates();
                        }
                    });
                    return;
                }

                // Optimistic Store Update
                if (window.cartStore && window.cartStore.booking) {
                    window.cartStore.booking.items = window.cartStore.booking.items.filter(i => Number(i.product_id) !== productId);
                    variantsToAdd.forEach(function(v) {
                        window.cartStore.booking.items.push({
                            id: `temp_${productId}_${v.variant_id}`,
                            cart_id: `temp_${productId}_${v.variant_id}`,
                            product_id: productId,
                            variant_id: v.variant_id,
                            variant_name: v.variant_name,
                            product_name: productName,
                            thumb_image: productImg,
                            vendor_name: vendorName,
                            sku: '',
                            price: v.price,
                            quantity: v.quantity
                        });
                    });
                    if (!window.cartStore.booking.ids.map(Number).includes(productId)) {
                        window.cartStore.booking.ids.push(productId);
                    }
                    window.cartStore.booking.count = window.cartStore.booking.items.length;
                    if (window.updateGlobalCartBadges) {
                        window.updateGlobalCartBadges(window.cartStore.booking.count, undefined);
                    }
                }

                $(`.add-to-basket[data-id="${productId}"]`).addClass('added').html('<i class="fas fa-check"></i>');
                if (window.toastr) toastr.success(`Added ${variantsToAdd.length} variant(s) to Procurement basket`);
                $('#quickVariantModal').modal('hide');

                $.ajax({
                    url: "{{ route('admin.cart.add') }}",
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        product_id: productId,
                        cart_type: 'booking',
                        action: 'bulk_variants',
                        is_bulk_variants: 1,
                        variants: variantsToAdd.map(v => ({ variant_id: v.variant_id, quantity: v.quantity }))
                    },
                    complete: function() {
                        applyButtonStates();
                    }
                });
            });

            // --- Direct Variant Reorder from Expanded Drawer ---
            $(document).on('click', '.add-variant-to-basket', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const $btn = $(this);
                if ($btn.data('is-busy')) return;
                $btn.data('is-busy', true);

                const productId = Number($btn.data('product-id'));
                const variantId = Number($btn.data('variant-id'));
                const variantName = $btn.data('variant-name') || 'Variant';
                const productName = $btn.data('product-name') || 'Product';
                const price = parseFloat($btn.data('price')) || 0;

                const isAlreadyAdded = $btn.hasClass('added');
                const desiredAction = isAlreadyAdded ? 'remove' : 'add';

                if (isAlreadyAdded) {
                    $btn.removeClass('added btn-success').addClass('btn-outline-warning').html('<i class="fas fa-cart-plus mr-1"></i> Reorder');
                    if (window.toastr) toastr.info(`Removed ${variantName} from Procurement basket`);

                    if (window.cartStore && window.cartStore.booking) {
                        window.cartStore.booking.items = window.cartStore.booking.items.filter(i => !(Number(i.product_id) === productId && Number(i.variant_id) === variantId));
                        const hasOtherVariants = window.cartStore.booking.items.some(i => Number(i.product_id) === productId);
                        if (!hasOtherVariants) {
                            window.cartStore.booking.ids = window.cartStore.booking.ids.filter(id => Number(id) !== productId);
                        }
                        window.cartStore.booking.count = window.cartStore.booking.items.length;
                        if (window.updateGlobalCartBadges) {
                            window.updateGlobalCartBadges(window.cartStore.booking.count, undefined);
                        }
                    }
                } else {
                    $btn.addClass('added btn-success').removeClass('btn-outline-warning').html('<i class="fas fa-check mr-1"></i> In Basket');
                    if (window.toastr) toastr.success(`Added ${variantName} to Procurement basket`);

                    if (window.cartStore && window.cartStore.booking) {
                        if (!window.cartStore.booking.ids.map(Number).includes(productId)) {
                            window.cartStore.booking.ids.push(productId);
                        }
                        const exists = window.cartStore.booking.items.some(i => Number(i.product_id) === productId && Number(i.variant_id) === variantId);
                        if (!exists) {
                            window.cartStore.booking.items.push({
                                id: `temp_${productId}_${variantId}`,
                                cart_id: `temp_${productId}_${variantId}`,
                                product_id: productId,
                                variant_id: variantId,
                                variant_name: variantName,
                                product_name: productName,
                                thumb_image: "{{ asset('uploads/no-image.svg') }}",
                                vendor_name: 'Primary Supplier',
                                sku: '',
                                price: price,
                                quantity: 1
                            });
                        }
                        window.cartStore.booking.count = window.cartStore.booking.items.length;
                        if (window.updateGlobalCartBadges) {
                            window.updateGlobalCartBadges(window.cartStore.booking.count, undefined);
                        }
                    }
                }

                $.ajax({
                    url: "{{ route('admin.cart.add') }}",
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        product_id: productId,
                        variant_id: variantId,
                        cart_type: 'booking',
                        action: desiredAction,
                        quantity: 1
                    },
                    success: function() {
                        applyButtonStates();
                    },
                    error: function() {
                        if (isAlreadyAdded) {
                            $btn.addClass('added btn-success').removeClass('btn-outline-warning').html('<i class="fas fa-check mr-1"></i> In Basket');
                        } else {
                            $btn.removeClass('added btn-success').addClass('btn-outline-warning').html('<i class="fas fa-cart-plus mr-1"></i> Reorder');
                        }
                        if (window.toastr) toastr.error('Error updating variant basket');
                    },
                    complete: function() {
                        $btn.data('is-busy', false);
                    }
                });
            });

            // Select All checkbox
            $(document).on('change', '#select_all', function() {
                const checked = $(this).is(':checked');
                let ids = getPersistedIds();
                const visibleIds = [];
                $('.product-checkbox').each(function() {
                    const vid = Number($(this).val());
                    visibleIds.push(vid);
                    $(this).prop('checked', checked);
                });
                if (checked) {
                    visibleIds.forEach(function(vid) {
                        if (ids.indexOf(vid) === -1) ids.push(vid);
                    });
                } else {
                    ids = ids.filter(function(id) { return visibleIds.indexOf(Number(id)) === -1; });
                }
                savePersistedIds(ids);
                updatePersistedUI();
            });

            // Individual checkbox change
            $(document).on('change', '.product-checkbox', function() {
                let ids = getPersistedIds();
                const id = Number($(this).val());
                if ($(this).is(':checked')) {
                    if (ids.indexOf(id) === -1) ids.push(id);
                } else {
                    ids = ids.filter(function(i) { return Number(i) !== id; });
                }
                savePersistedIds(ids);
                updatePersistedUI();
                $('#select_all').prop('checked', $('.product-checkbox:checked').length === $('.product-checkbox').length && $('.product-checkbox').length > 0);
            });

            // Bulk Add to Procurement Basket
            $('#add_to_booking_btn').on('click', function() {
                const ids = getPersistedIds().map(Number);
                if (ids.length === 0) {
                    if (window.toastr) toastr.warning('Please select at least one product');
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Adding...');

                $.ajax({
                    url: "{{ route('admin.cart.bulk-add-products') }}",
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        product_ids: ids,
                        cart_type: 'booking'
                    },
                    success: function(res) {
                        if (res.success) {
                            if (window.toastr) toastr.success(res.message);
                            if (window.cartStore && window.cartStore.booking) {
                                (res.product_ids || []).forEach(function(pid) {
                                    if (!window.cartStore.booking.ids.includes(pid)) {
                                        window.cartStore.booking.ids.push(pid);
                                    }
                                });
                                window.cartStore.booking.count = res.count;
                                if (window.updateGlobalCartBadges) {
                                    window.updateGlobalCartBadges(res.count, undefined);
                                }
                            }
                            applyButtonStates();

                            // Clear selections
                            localStorage.removeItem(STORAGE_KEY);
                            $('.product-checkbox').prop('checked', false);
                            $('#select_all').prop('checked', false);
                            updatePersistedUI();
                        }
                    },
                    error: function() {
                        if (window.toastr) toastr.error('Failed to add products to cart');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-shopping-basket mr-1"></i> Add to Procurement Cart (<span id="selected_count">0</span>)');
                    }
                });
            });
        });
    </script>
@endpush
