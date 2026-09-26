@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center">
            <h1><i class="fas fa-boxes text-primary mr-2"></i> Pack New Handling Unit (WMS)</h1>
            <div class="section-header-breadcrumb">
                <a href="{{ route('admin.packaging-units.index') }}" class="btn btn-outline-secondary mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Handling Units
                </a>
                <a href="{{ route('admin.barcode-labels.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-barcode mr-1"></i> Barcode Labels Hub
                </a>
            </div>
        </div>

        <div class="section-body">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible show fade mb-4 shadow-sm">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        <div class="alert-title font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Please resolve the following errors:</div>
                        <ul class="mb-0 mt-2 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.packaging-units.store') }}" method="POST" id="packingForm">
                @csrf

                <div class="row">
                    {{-- Left Column: Container Logistics Setup --}}
                    <div class="col-lg-4 col-md-5">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-white border-bottom py-3">
                                <h6 class="text-dark font-weight-bold mb-0">
                                    <i class="fas fa-box text-primary mr-1"></i> Container Logistics Setup
                                </h6>
                            </div>
                            <div class="card-body">
                                {{-- Packaging Type & Level (No required attr to prevent Select2 hidden focus blockage) --}}
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold small text-dark">
                                        Packaging Type & Level <span class="text-danger">*</span>
                                    </label>
                                    <select name="packaging_type_id" id="packaging_type_id" class="form-control select2">
                                        @foreach ($packagingTypes as $pt)
                                            <option value="{{ $pt->id }}" data-code="{{ $pt->code }}" data-tare="{{ $pt->tare_weight }}" data-max="{{ $pt->max_weight }}" {{ $pt->code === 'CTN' ? 'selected' : '' }}>
                                                Level {{ $pt->level_order }}: {{ $pt->name }} ({{ $pt->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Defines hierarchy level and barcode prefix (e.g. CTN, BOX, PLT).</small>
                                </div>

                                {{-- Carton Category / Commodity Code --}}
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold small text-dark">
                                        <i class="fas fa-tags text-primary mr-1"></i> Carton Category / Commodity <span class="text-muted font-weight-normal">(GS1 GPC Category Classification)</span>
                                    </label>
                                    <select name="category_id" id="category_id" class="form-control select2">
                                        <option value="">General / Mixed Goods (10000000)</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}" data-gpc="{{ $cat->gpc_code }}">
                                                {{ $cat->name }} (GS1: {{ $cat->gpc_code ?? '10000000' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle text-info mr-1"></i>
                                        Identifies commodity type in container barcode (e.g. Clothing, Shoes). Kept as General for mixed goods.
                                    </small>
                                </div>

                                {{-- Parent Container (Optional Nesting) --}}
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold small text-dark">
                                        Parent Container (Optional)
                                    </label>
                                    <select name="parent_handling_unit_id" id="parent_handling_unit_id" class="form-control select2">
                                        <option value="">None (Top-Level Container / Big Carton)</option>
                                        @foreach ($potentialParents as $parent)
                                            <option value="{{ $parent->id }}">
                                                {{ $parent->hu_code }} — {{ $parent->packagingType?->name }} ({{ $parent->total_quantity }} pcs)
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle text-info mr-1"></i>
                                        If creating a <strong>Small Box</strong> inside an existing <strong>Big Carton</strong>, select the Big Carton here!
                                    </small>
                                </div>

                                {{-- Pack Existing Child Boxes (Optional) --}}
                                @if(count($availableChildUnits ?? []) > 0)
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold small text-dark">
                                            <i class="fas fa-cubes text-info mr-1"></i> Pack Child Boxes Inside <span class="text-muted font-weight-normal">(Optional)</span>
                                        </label>
                                        <select name="child_unit_ids[]" id="child_unit_ids" class="form-control select2" multiple data-placeholder="Select small boxes to nest inside...">
                                            @foreach ($availableChildUnits as $child)
                                                <option value="{{ $child->id }}" data-qty="{{ $child->total_quantity }}" data-weight="{{ $child->gross_weight }}">
                                                    {{ $child->hu_code }} ({{ $child->packagingType?->name }}) - {{ $child->total_quantity }} pcs
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle text-info mr-1"></i>
                                            Select previously created Small Boxes to pack inside this Big Carton / Pallet.
                                        </small>
                                    </div>
                                @endif

                                {{-- Batch Number --}}
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold small text-dark">Batch / Lot Number</label>
                                    <input type="text" name="batch_no" class="form-control font-monospace font-weight-bold" placeholder="e.g. LOT-2026-09A">
                                </div>

                                {{-- Gross Weight --}}
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold small text-dark">Gross Weight (kg)</label>
                                    <input type="number" step="0.001" name="gross_weight" id="gross_weight" class="form-control font-weight-bold" placeholder="0.000">
                                    <div id="weightWarningAlert" class="alert alert-warning py-1 px-2 small mt-1 d-none">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> <span id="weightWarningMsg"></span>
                                    </div>
                                </div>

                                {{-- Status --}}
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold small text-dark">Status</label>
                                    <select name="status" class="form-control">
                                        <option value="packed" selected>Packed</option>
                                        <option value="sealed">Sealed</option>
                                        <option value="draft">Draft (Empty Container / In Progress)</option>
                                    </select>
                                </div>

                                {{-- Notes --}}
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold small text-dark">Notes / Instructions</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Master carton ready for small boxes or loose items"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Single Unified Products Workbench --}}
                    <div class="col-12 col-lg-8 col-md-7">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <h6 class="text-dark font-weight-bold mb-0">
                                    <i class="fas fa-boxes text-primary mr-1"></i> Pack Products into Container <span class="text-muted font-weight-normal">(Optional)</span>
                                </h6>
                                <div>
                                    <span class="badge badge-success px-3 py-2 font-weight-bold" id="totalPackedSummary">0 Items Inside</span>
                                </div>
                            </div>

                            <div class="card-body p-3">
                                {{-- Clear Logistics Helper Notice --}}
                                <div class="alert alert-light border small text-dark mb-3 py-2 px-3">
                                    <i class="fas fa-lightbulb text-warning mr-1"></i>
                                    <strong>Simple WMS Rule:</strong>
                                    If you are creating a <strong>Big Carton</strong> or <strong>Pallet</strong> first, you do <strong>not</strong> need to pack loose products now. You can leave this empty and click <strong>Generate Barcode & Create Container</strong> below!
                                </div>

                                {{-- Products Workbench --}}
                                <div id="sectionProducts">
                                    {{-- Filter & Search Bar --}}
                                    <div class="bg-light p-3 rounded mb-3 border">
                                        <div class="row">
                                            {{-- Filter by Category --}}
                                            <div class="col-md-6 mb-2">
                                                <label class="font-weight-bold small text-dark mb-1">
                                                    <i class="fas fa-filter text-primary mr-1"></i> Filter Category
                                                </label>
                                                <select id="itemCategoryFilter" class="form-control select2">
                                                    <option value="">All Categories (All Products)</option>
                                                    @foreach ($categories as $cat)
                                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Barcode Gun Scan-to-Pack Input --}}
                                            <div class="col-md-6 mb-2">
                                                <label class="font-weight-bold small text-dark mb-1">
                                                    <i class="fas fa-barcode text-success mr-1"></i> Barcode Gun (Scan-to-Pack +1)
                                                </label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-qrcode text-success"></i></span>
                                                    </div>
                                                    <input type="text" id="barcodeGunInput" class="form-control border-left-0 font-monospace" placeholder="Scan item barcode (auto +1)...">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Search Input with Live Filter --}}
                                        <div class="row mt-1">
                                            <div class="col-12">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                                    </div>
                                                    <input type="text" id="productSearchInput" class="form-control border-left-0" placeholder="Type product name, SKU, or keyword to search...">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary" id="btnClearSearch" title="Clear search"><i class="fas fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Available Products Catalog List (Visible immediately like Barcode Studio) --}}
                                    <div class="catalog-products-wrapper border rounded bg-white mb-3 shadow-sm">
                                        <div class="bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center rounded-top flex-wrap gap-2">
                                            <span class="font-weight-bold text-dark small">
                                                <i class="fas fa-boxes text-primary mr-1"></i> Available Products Catalog
                                                <span class="text-muted font-weight-normal">(Click Add to pack into container)</span>
                                            </span>
                                            <span class="badge badge-primary font-weight-bold px-2 py-1" id="catalogCountBadge">
                                                Showing {{ count($initialProducts ?? []) }} of {{ $totalProductsCount ?? 1397 }} Products
                                            </span>
                                        </div>

                                        <div id="ajaxCatalogLoading" class="text-center py-4 text-muted d-none">
                                            <i class="fas fa-spinner fa-spin mr-1"></i> Loading products...
                                        </div>

                                        <div id="ajaxCatalogList" class="p-2" style="max-height: 350px; overflow-y: auto;">
                                            {{-- Dynamically populated via renderCatalogList --}}
                                        </div>

                                        <div class="bg-light py-1 px-3 border-top text-center text-muted small rounded-bottom">
                                            <i class="fas fa-mouse-pointer text-primary mr-1"></i> Click <strong>Add</strong> to pack individual products, or expand <strong>Sizes</strong> for variants.
                                        </div>
                                    </div>

                                    {{-- Packed Items Table --}}
                                    <div class="packed-table-wrapper border rounded">
                                        <div class="bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                                            <span class="font-weight-bold text-dark small">
                                                <i class="fas fa-clipboard-check text-success mr-1"></i> Products Packed in this Container
                                            </span>
                                            <button type="button" class="btn btn-xs btn-outline-danger" id="btnClearPackedItems">
                                                <i class="fas fa-trash-alt mr-1"></i> Clear All Packed
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered mb-0" id="packedItemsTable">
                                                <thead class="bg-light text-muted small text-uppercase">
                                                    <tr>
                                                        <th>Product / Variant</th>
                                                        <th>Barcode / SKU</th>
                                                        <th class="text-center" style="width: 120px;">Available Stock</th>
                                                        <th class="text-center" style="width: 130px;">Packing Qty</th>
                                                        <th class="text-center" style="width: 50px;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="packedItemsTbody">
                                                    <tr id="emptyRow">
                                                        <td colspan="5" class="text-center py-4 text-muted small">
                                                            <i class="fas fa-box-open fa-2x mb-2 d-block text-secondary opacity-50"></i>
                                                            No products packed yet. You can add products above, or leave empty to create an empty container!
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-light border-top py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div class="small text-muted" id="packingWorkflowHint">
                                    <i class="fas fa-info-circle text-primary mr-1"></i>
                                    <span id="packingWorkflowStatusText">Products are optional. You can create an empty container first.</span>
                                </div>
                                <div>
                                    <input type="hidden" name="items" id="itemsPayload" value="[]">
                                    <a href="{{ route('admin.packaging-units.index') }}" class="btn btn-outline-secondary px-4 mr-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm" id="btnSubmitPacking">
                                        <i class="fas fa-barcode mr-1"></i> Generate Barcode & Create Container
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        const totalSystemProducts = {{ $totalProductsCount ?? 1397 }};
        const initialCatalog = @json($initialProducts ?? []);
        let currentCatalogItems = initialCatalog;
        let packedItems = [];
        let searchTimer = null;
        let activeMaxWeight = 0;
        let selectedChildQty = 0;
        let currentLimit = 60;
        let isLoadingCatalog = false;

        // Auto calculate tare weight on type change & track max weight limit
        $('#packaging_type_id').on('change', function() {
            const opt = $(this).find(':selected');
            const tare = opt.data('tare');
            activeMaxWeight = parseFloat(opt.data('max')) || 0;

            if (tare && !$('#gross_weight').val()) {
                $('#gross_weight').val(parseFloat(tare).toFixed(3));
            }
            validateWeight();
            updateTotals();
        }).trigger('change');

        // Gross Weight validation listener
        $('#gross_weight').on('input change', function() {
            validateWeight();
        });

        function validateWeight() {
            const currentWeight = parseFloat($('#gross_weight').val()) || 0;
            if (activeMaxWeight > 0 && currentWeight > activeMaxWeight) {
                $('#weightWarningMsg').text(`Gross weight (${currentWeight} kg) exceeds container max limit (${activeMaxWeight} kg)!`);
                $('#weightWarningAlert').removeClass('d-none');
            } else {
                $('#weightWarningAlert').addClass('d-none');
            }
        }

        let searchDebounceTimer = null;

        // Render initial catalog products immediately on page load
        renderCatalogList(initialCatalog);

        // Search Input Debounce (Identical to Barcode Labels Hub)
        $('#productSearchInput').on('input', function() {
            clearTimeout(searchDebounceTimer);
            const query = $(this).val().trim();

            searchDebounceTimer = setTimeout(function() {
                performSearch(query);
            }, 200);
        });

        // Clear Search Input Button
        $('#btnClearSearch').on('click', function() {
            $('#productSearchInput').val('');
            performSearch('');
        });

        // Category Filter Change
        $('#itemCategoryFilter').on('change', function() {
            performSearch($('#productSearchInput').val().trim());
        });

        // Fast AJAX Search (Identical to Barcode Labels Hub: limit 60 for ultra-fast typing response)
        function performSearch(query) {
            const catId = $('#itemCategoryFilter').val();

            $('#ajaxCatalogLoading').removeClass('d-none');

            $.getJSON("{{ route('admin.barcode-labels.search-products') }}", {
                q: query,
                category_id: catId || '',
                limit: 60
            }, function(res) {
                $('#ajaxCatalogLoading').addClass('d-none');
                currentCatalogItems = res.data || [];
                renderCatalogList(currentCatalogItems);
            }).fail(function() {
                $('#ajaxCatalogLoading').addClass('d-none');
                $('#ajaxCatalogList').html('<div class="p-3 text-danger small text-center"><i class="fas fa-exclamation-circle mr-1"></i> Failed to load products.</div>');
            });
        }

        // Load More Products Click Handler (Manual Click Only - NO Auto Scroll!)
        $(document).on('click', '#btnLoadMoreProducts', function(e) {
            e.preventDefault();
            const btn = $(this);
            btn.find('.fa-spinner').removeClass('d-none');
            btn.find('.fa-chevron-down').addClass('d-none');
            btn.prop('disabled', true);

            const query = $('#productSearchInput').val().trim();
            const catId = $('#itemCategoryFilter').val();
            const nextLimit = currentCatalogItems.length + 60;

            $.getJSON("{{ route('admin.barcode-labels.search-products') }}", {
                q: query,
                category_id: catId || '',
                limit: nextLimit
            }, function(res) {
                currentCatalogItems = res.data || [];
                renderCatalogList(currentCatalogItems);
            }).always(function() {
                btn.prop('disabled', false);
            });
        });

        // Render Catalog List styled like Barcode Labels Studio without any broken quote attributes
        function renderCatalogList(items) {
            const list = $('#ajaxCatalogList');
            list.empty();

            const query = $('#productSearchInput').val().trim();
            const catId = $('#itemCategoryFilter').val();
            const catName = catId ? $('#itemCategoryFilter option:selected').text().split('(')[0].trim() : '';

            if (query.length > 0) {
                $('#catalogCountBadge').text(items.length + (items.length >= 60 ? '+ Matches' : ' Matches'));
            } else if (catId) {
                $('#catalogCountBadge').text(items.length + (items.length >= 60 ? '+ Products in ' + catName : ' Products in ' + catName));
            } else {
                $('#catalogCountBadge').text('Showing ' + items.length + ' of {{ $totalProductsCount ?? 1397 }} Products');
            }

            if (items.length === 0) {
                list.html('<div class="p-4 text-muted small text-center"><i class="fas fa-info-circle mr-1"></i> No products match the filter or search term.</div>');
                return;
            }

            let htmlBuffer = '';

            items.forEach(function(item) {
                const hasVariants = item.has_variants && item.variants && item.variants.length > 0;
                const itemStock = parseInt(item.stock) || 0;
                const isOutOfStock = itemStock <= 0;

                // Product Image Thumbnail (Commented out for ultra-fast loading. Uncomment if needed later)
                // const imgThumbHtml = item.image ? `<img src="${item.image}" class="rounded mr-2 border" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.remove()">` : `<div class="bg-light border rounded text-secondary d-flex align-items-center justify-content-center mr-2" style="width: 40px; height: 40px;"><i class="fas fa-box text-muted"></i></div>`;

                htmlBuffer += `
                    <div class="search-result-wrapper border rounded mb-2 bg-white ${isOutOfStock && !hasVariants ? 'opacity-75 bg-light' : ''}" id="prodWrapper_${item.id}">
                        <div class="d-flex justify-content-between align-items-center p-2 rounded">
                            <div class="d-flex align-items-center flex-grow-1 min-w-0 mr-2">
                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex align-items-center">
                                        <strong class="${isOutOfStock && !hasVariants ? 'text-muted' : 'text-dark'} small text-truncate mr-1" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</strong>
                                    </div>
                                    <div class="small text-muted d-flex flex-wrap align-items-center" style="row-gap: 3px; column-gap: 6px;">
                                        ${item.gpc_code ? `<span class="badge badge-primary font-weight-bold py-0 px-1" style="font-family: monospace; font-size: 10px;" title="GS1 GPC Brick: ${escapeHtml(item.gpc_title || item.category)}"><i class="fas fa-barcode mr-1"></i>GS1: ${escapeHtml(item.gpc_code)}</span>` : ''}
                                        <span class="badge badge-light border text-secondary font-weight-normal py-0 px-1" style="font-size: 10.5px;">${escapeHtml(item.category || 'General')}</span>
                                        <span><small class="text-secondary font-weight-bold">SKU:</small> <span class="font-monospace text-dark font-weight-bold">${escapeHtml(item.sku)}</span></span>
                                        <span><small class="text-secondary font-weight-bold">Stock:</small> <strong class="${itemStock > 0 ? 'text-success' : 'text-danger'}">${itemStock} pcs</strong></span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                `;

                if (hasVariants) {
                    const hasAnyStock = item.variants.some(v => (parseInt(v.stock) || 0) > 0);
                    htmlBuffer += `
                        <button type="button" class="btn btn-xs btn-outline-info mr-1 btn-toggle-cat-variants" data-id="${item.id}">
                            <i class="fas fa-sitemap mr-1"></i> Sizes (${item.variant_count})
                        </button>
                    `;
                    if (hasAnyStock) {
                        htmlBuffer += `
                            <button type="button" class="btn btn-xs btn-outline-success btn-add-all-cat-variants" data-id="${item.id}">
                                <i class="fas fa-plus-circle mr-1"></i> Add In-Stock
                            </button>
                        `;
                    } else {
                        htmlBuffer += `
                            <button type="button" class="btn btn-xs btn-secondary text-muted" disabled title="All sizes out of stock">
                                <i class="fas fa-ban mr-1"></i> Out of Stock
                            </button>
                        `;
                    }
                } else {
                    if (isOutOfStock) {
                        htmlBuffer += `
                            <button type="button" class="btn btn-xs btn-secondary font-weight-bold" disabled title="Cannot pack: Out of stock (0 pcs)">
                                <i class="fas fa-ban mr-1"></i> Out of Stock
                            </button>
                        `;
                    } else {
                        htmlBuffer += `
                            <button type="button" class="btn btn-xs btn-primary font-weight-bold btn-add-simple-item" data-id="${item.id}">
                                <i class="fas fa-plus mr-1"></i> Add
                            </button>
                        `;
                    }
                }

                htmlBuffer += `</div></div>`;

                if (hasVariants) {
                    htmlBuffer += `<div class="cat-variants-box p-2 border-top" id="catVariants_${item.id}" style="display: none; background: #f8fafc;">`;
                    htmlBuffer += `<div class="small text-muted font-weight-bold px-1 py-1 mb-1 border-bottom d-flex justify-content-between">
                        <span><i class="fas fa-tags text-primary mr-1"></i> Select sizes to pack into container:</span>
                        <span>Total ${item.variants.length} Sizes/Colors</span>
                    </div>`;

                    item.variants.forEach(function(v) {
                        const vStock = parseInt(v.stock) || 0;
                        const vOutOfStock = vStock <= 0;

                        htmlBuffer += `
                            <div class="d-flex justify-content-between align-items-center py-1 px-2 border-bottom ${vOutOfStock ? 'opacity-75' : ''}">
                                <div>
                                    <span class="small font-weight-bold ${vOutOfStock ? 'text-muted' : 'text-secondary'}">
                                        <i class="fas fa-tag ${vOutOfStock ? 'text-muted' : 'text-info'} mr-1"></i>
                                        ${escapeHtml(v.spec || v.name)}
                                    </span>
                                    <span class="badge ${vOutOfStock ? 'badge-danger' : 'badge-light border'} ml-1" style="font-size: 10px;">Stock: ${vStock}</span>
                                    <span class="font-monospace small text-muted ml-2">${escapeHtml(v.barcode || v.sku || '')}</span>
                                </div>
                                <div>
                        `;

                        if (vOutOfStock) {
                            htmlBuffer += `
                                <button type="button" class="btn btn-xs btn-secondary text-muted" disabled title="Cannot pack: Out of stock">
                                    <i class="fas fa-ban mr-1"></i> Out of Stock
                                </button>
                            `;
                        } else {
                            htmlBuffer += `
                                <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold btn-add-variant-item"
                                    data-prod-id="${item.id}"
                                    data-variant-id="${v.id}">
                                    <i class="fas fa-plus mr-1"></i> Add
                                </button>
                            `;
                        }

                        htmlBuffer += `</div></div>`;
                    });
                    htmlBuffer += `</div>`;
                }

                htmlBuffer += `</div>`;
            });

            if (items.length >= 60) {
                htmlBuffer += `
                    <div class="text-center py-2 border-top mt-2" id="loadMoreContainer">
                        <button type="button" class="btn btn-sm btn-outline-primary px-3 shadow-sm font-weight-bold" id="btnLoadMoreProducts">
                            <i class="fas fa-spinner fa-spin d-none mr-1"></i> <i class="fas fa-chevron-down mr-1"></i> Load More Products
                        </button>
                    </div>
                `;
            }

            list.html(htmlBuffer);
        }

        // Toggle variant box
        $(document).on('click', '.btn-toggle-cat-variants', function() {
            const prodId = $(this).data('id');
            $(`#catVariants_${prodId}`).slideToggle(120);
        });

        // Add all in-stock variants of product (using safe memory lookup)
        $(document).on('click', '.btn-add-all-cat-variants', function() {
            const prodId = $(this).data('id');
            const prod = currentCatalogItems.find(p => p.id == prodId);
            if (!prod || !prod.variants) return;

            const inStockVariants = prod.variants.filter(v => (parseInt(v.stock) || 0) > 0);
            if (inStockVariants.length === 0) {
                toastr.error(`Cannot pack "${prod.name}": All sizes are currently Out of Stock (0 pcs).`);
                return;
            }

            let addedCount = 0;
            inStockVariants.forEach(function(v) {
                const added = addItemToPackedList({
                    product_id: prod.id,
                    variant_id: v.id,
                    name: `${prod.name} (${v.spec || v.name})`,
                    sku: v.sku || prod.sku,
                    barcode: v.barcode || '',
                    stock: parseInt(v.stock) || 0,
                    quantity: 1
                });
                if (added) addedCount++;
            });

            if (addedCount > 0) {
                toastr.success(`Added ${addedCount} in-stock size(s) for ${prod.name}`);
            }
        });

        // Add single simple product (using safe memory lookup)
        $(document).on('click', '.btn-add-simple-item', function() {
            const prodId = $(this).data('id');
            const prod = currentCatalogItems.find(p => p.id == prodId);
            if (!prod) return;

            const stock = parseInt(prod.stock) || 0;
            if (stock <= 0) {
                toastr.error(`Cannot pack "${prod.name}"! This item is Out of Stock (0 pcs).`);
                return;
            }

            addItemToPackedList({
                product_id: prod.id,
                variant_id: null,
                name: prod.name,
                sku: prod.sku,
                barcode: prod.barcode || '',
                stock: stock,
                quantity: 1
            });
        });

        // Add single variant item (using safe memory lookup)
        $(document).on('click', '.btn-add-variant-item', function() {
            const prodId = $(this).data('prod-id');
            const variantId = $(this).data('variant-id');
            const prod = currentCatalogItems.find(p => p.id == prodId);
            if (!prod) return;

            const v = (prod.variants || []).find(item => item.id == variantId);
            if (!v) return;

            const vStock = parseInt(v.stock) || 0;
            if (vStock <= 0) {
                toastr.error(`Cannot pack "${prod.name} (${v.spec || v.name})"! This size is Out of Stock (0 pcs).`);
                return;
            }

            addItemToPackedList({
                product_id: prod.id,
                variant_id: v.id,
                name: `${prod.name} (${v.spec || v.name})`,
                sku: v.sku || prod.sku,
                barcode: v.barcode || '',
                stock: vStock,
                quantity: 1
            });
        });

        // Barcode Gun Scan-to-Pack Input Handler
        $('#barcodeGunInput').on('keypress', function(e) {
            if (e.which === 13) { // Enter pressed by barcode gun
                e.preventDefault();
                const scannedBarcode = $(this).val().trim();
                if (!scannedBarcode) return;

                $(this).prop('disabled', true);
                $.ajax({
                    url: '{{ route('admin.barcode-labels.search-products') }}',
                    data: { q: scannedBarcode, limit: 5 },
                    success: function(resp) {
                        $('#barcodeGunInput').prop('disabled', false).val('').focus();
                        const items = resp.data || [];
                        let matched = false;

                        for (let item of items) {
                            if (item.has_variants && item.variants) {
                                for (let v of item.variants) {
                                    if (v.barcode && v.barcode.toLowerCase() === scannedBarcode.toLowerCase()) {
                                        matched = true;
                                        const vStock = parseInt(v.stock) || 0;
                                        const fullName = `${item.name} (${v.spec || v.name})`;

                                        if (vStock <= 0) {
                                            toastr.error(`Scanned "${fullName}" is Out of Stock (0 pcs)! Cannot pack.`);
                                            return;
                                        }

                                        addItemToPackedList({
                                            product_id: item.id,
                                            variant_id: v.id,
                                            name: fullName,
                                            sku: v.sku || item.sku,
                                            barcode: v.barcode,
                                            stock: vStock,
                                            quantity: 1
                                        });
                                        break;
                                    }
                                }
                            }

                            if (matched) break;

                            if (item.barcode && item.barcode.toLowerCase() === scannedBarcode.toLowerCase()) {
                                matched = true;
                                const itemStock = parseInt(item.stock) || 0;

                                if (itemStock <= 0) {
                                    toastr.error(`Scanned "${item.name}" is Out of Stock (0 pcs)! Cannot pack.`);
                                    return;
                                }

                                addItemToPackedList({
                                    product_id: item.id,
                                    variant_id: null,
                                    name: item.name,
                                    sku: item.sku,
                                    barcode: item.barcode,
                                    stock: itemStock,
                                    quantity: 1
                                });
                                break;
                            }
                        }

                        if (!matched) {
                            toastr.warning(`Barcode [${scannedBarcode}] not found in catalog.`);
                        }
                    },
                    error: function() {
                        $('#barcodeGunInput').prop('disabled', false).val('').focus();
                        toastr.error('Scan lookup failed.');
                    }
                });
            }
        });

        // Core method: Add Item to Packed Table with Duplicate Prevention & Strict Stock Validation
        function addItemToPackedList(itemData) {
            const availableStock = parseInt(itemData.stock) || 0;

            if (availableStock <= 0) {
                toastr.error(`Cannot pack "${itemData.name}"! Available warehouse stock is 0.`);
                return false;
            }

            const existingIndex = packedItems.findIndex(i =>
                i.product_id === itemData.product_id && i.variant_id === itemData.variant_id
            );

            if (existingIndex !== -1) {
                const currentQty = packedItems[existingIndex].quantity;
                const maxStock = packedItems[existingIndex].stock;
                if (currentQty >= maxStock) {
                    toastr.warning(`Cannot pack more "${itemData.name}"! Reached maximum available warehouse stock (${maxStock} pcs).`);
                    return false;
                }
                packedItems[existingIndex].quantity += 1;
                toastr.success(`Increased "${itemData.name}" to ${packedItems[existingIndex].quantity} pcs`);
            } else {
                packedItems.push({
                    product_id: itemData.product_id,
                    variant_id: itemData.variant_id,
                    name: itemData.name,
                    sku: itemData.sku,
                    barcode: itemData.barcode,
                    stock: availableStock,
                    quantity: 1
                });
                toastr.success(`Added "${itemData.name}" (1 pc)`);
            }

            renderTable();
            return true;
        }

        // Change quantity input with live stock limit clamp & validation
        $(document).on('change input', '.item-qty', function() {
            const idx = $(this).data('index');
            const item = packedItems[idx];
            if (!item) return;

            let val = parseInt($(this).val());
            if (isNaN(val) || val < 1) {
                val = 1;
                $(this).val(val);
            }

            if (val > item.stock) {
                toastr.error(`Cannot pack ${val} pcs! Maximum available stock for "${item.name}" is ${item.stock} pcs. Auto-adjusted.`);
                val = item.stock;
                $(this).val(val);
            }

            item.quantity = val;
            updateTotals();
        });

        // Remove item
        $(document).on('click', '.btn-remove-packed', function() {
            const idx = $(this).data('index');
            packedItems.splice(idx, 1);
            renderTable();
        });

        // Clear all packed items
        $('#btnClearPackedItems').on('click', function() {
            if (packedItems.length === 0) return;
            packedItems = [];
            renderTable();
            toastr.info('Cleared all loose packed products.');
        });

        // Child Containers Checkbox Selection
        $('.child-unit-checkbox').on('change', function() {
            recalculateChildUnitsQty();
        });

        $('#child_unit_ids').on('change', function() {
            recalculateChildUnitsQty();
        });

        function recalculateChildUnitsQty() {
            selectedChildQty = 0;
            $('#child_unit_ids option:selected').each(function() {
                selectedChildQty += parseInt($(this).data('qty')) || 0;
            });
            updateTotals();
        }

        function renderTable() {
            const tbody = $('#packedItemsTbody');
            tbody.empty();

            if (packedItems.length === 0) {
                tbody.html(`
                    <tr id="emptyRow">
                        <td colspan="5" class="text-center py-4 text-muted small">
                            <i class="fas fa-box-open fa-2x mb-2 d-block text-secondary opacity-50"></i>
                            No products packed yet. You can add products above, or leave empty to create an empty container!
                        </td>
                    </tr>
                `);
            } else {
                packedItems.forEach(function(item, idx) {
                    tbody.append(`
                        <tr>
                            <td class="align-middle">
                                <strong class="text-dark small">${escapeHtml(item.name)}</strong>
                            </td>
                            <td class="align-middle">
                                <span class="font-monospace small text-muted">${escapeHtml(item.barcode || item.sku || 'N/A')}</span>
                            </td>
                            <td class="text-center align-middle">
                                <span class="badge ${item.stock > 0 ? 'badge-success' : 'badge-danger'} font-weight-bold">
                                    ${item.stock} pcs
                                </span>
                            </td>
                            <td class="text-center align-middle">
                                <input type="number" min="1" max="${item.stock}" class="form-control text-center font-weight-bold item-qty" data-index="${idx}" value="${item.quantity}" style="max-width: 90px; margin: 0 auto;">
                                <small class="text-muted d-block mt-1">Max: ${item.stock}</small>
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-xs btn-outline-danger btn-remove-packed" data-index="${idx}" title="Remove item">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }

            updateTotals();
        }

        function updateTotals() {
            let looseTotal = 0;
            packedItems.forEach(i => looseTotal += i.quantity);
            const totalAll = looseTotal + selectedChildQty;

            let summaryText = `${totalAll} Items Inside`;
            if (selectedChildQty > 0 && looseTotal > 0) {
                summaryText += ` (${looseTotal} loose + ${selectedChildQty} in boxes)`;
            } else if (selectedChildQty > 0) {
                summaryText += ` (${selectedChildQty} in child boxes)`;
            }

            $('#totalPackedSummary').text(summaryText);
            $('#itemsPayload').val(JSON.stringify(packedItems));
            updateSubmitButtonText();
        }

        function updateSubmitButtonText() {
            const looseTotal = packedItems.reduce((acc, i) => acc + i.quantity, 0);
            const totalAll = looseTotal + selectedChildQty;
            const typeText = $('#packaging_type_id option:selected').data('code') || 'Container';

            if (totalAll > 0) {
                $('#btnSubmitPacking').html(`<i class="fas fa-barcode mr-1"></i> Generate Barcode & Pack ${typeText} (${totalAll} pcs)`);
                $('#packingWorkflowStatusText').text(`Packing ${totalAll} item(s) into this ${typeText}.`);
            } else {
                $('#btnSubmitPacking').html(`<i class="fas fa-barcode mr-1"></i> Generate Barcode & Create Empty ${typeText}`);
                $('#packingWorkflowStatusText').text(`Creating empty ${typeText}. You can nest small boxes into it later!`);
            }
        }

        // Form Submit Validation & Loading Indicator
        $('#packingForm').on('submit', function(e) {
            const packagingType = $('#packaging_type_id').val();
            if (!packagingType) {
                toastr.error('Please select a Packaging Type.');
                e.preventDefault();
                return false;
            }

            // Strict zero-stock & overflow check on any packed loose items
            for (let item of packedItems) {
                if (item.stock <= 0) {
                    toastr.error(`Cannot submit container! "${item.name}" has 0 available stock. Please remove it.`);
                    e.preventDefault();
                    return false;
                }
                if (item.quantity > item.stock) {
                    toastr.error(`Cannot submit container! Quantity for "${item.name}" (${item.quantity}) exceeds available warehouse stock (${item.stock}).`);
                    e.preventDefault();
                    return false;
                }
            }

            // Ensure payload is up to date
            $('#itemsPayload').val(JSON.stringify(packedItems));

            // Show generating state on submit button
            $('#btnSubmitPacking').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Generating Barcode & Container...');
        });

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
    });
</script>
<style>
    .cursor-pointer {
        cursor: pointer;
    }
    .hover-bg:hover {
        background-color: #f8fafc;
    }
    .min-w-0 {
        min-width: 0;
    }
    .font-monospace {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }
</style>
@endpush
