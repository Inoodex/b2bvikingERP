@extends('backend.layouts.master')
@section('title', 'Best Seller Products')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-fire mr-2 text-danger"></i>Best Seller Products</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Best Sellers</div>
            </div>
        </div>

        <div class="section-body">

            {{-- Summary Cards --}}
            <div class="row mb-4">
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Unique Products</h4></div>
                            <div class="card-body" id="summary-total-products">{{ number_format($products->total()) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total Qty Ordered</h4></div>
                            <div class="card-body" id="summary-grand-total-qty">{{ number_format($grandTotals->grand_total_qty ?? 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-success">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total Order Value</h4></div>
                            <div class="card-body" id="summary-grand-total-value">{!! formatWithCurrency($grandTotals->grand_total_value ?? 0) !!}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Card --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h4><i class="fas fa-filter mr-2"></i>Filter Options</h4>
                </div>
                <div class="card-body">
                    <form id="best-sellers-filter-form" method="GET" action="{{ route('admin.reports.best-sellers') }}">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Year</label>
                                    <select name="year" id="year" class="form-control select2">
                                        <option value="">All Years</option>
                                        @foreach($availableYears as $yr)
                                            <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Month</label>
                                    <select name="month" id="month" class="form-control select2">
                                        <option value="">All Months</option>
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="category_id" id="category_id" class="form-control select2">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sub Category</label>
                                    <select name="sub_category_id" id="sub_category_id" class="form-control select2">
                                        <option value="">All Sub Categories</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Child Category</label>
                                    <select name="child_category_id" id="child_category_id" class="form-control select2">
                                        <option value="">All Child Categories</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Search Product</label>
                                    <input type="text" name="search" class="form-control" placeholder="Search product..." value="{{ request('search') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-right">
                                <a href="{{ route('admin.reports.best-sellers') }}" class="btn btn-danger btn-sm" id="btn-reset"><i class="fas fa-undo"></i> Reset Filters</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Navigation Tabs --}}
            <ul class="nav nav-pills mb-4" id="bestSellerReportTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link font-weight-bold active py-2.5 px-4 shadow-sm" id="ranking-tab" data-toggle="pill" href="#tab-ranking" role="tab" style="border-radius: 8px;">
                        <i class="fas fa-fire mr-1.5 text-danger"></i> 1. Best Seller Demand & Velocity Ranking
                    </a>
                </li>
                <li class="nav-item ml-md-2 mt-2 mt-md-0">
                    <a class="nav-link font-weight-bold py-2.5 px-4 shadow-sm" id="calculator-tab" data-toggle="pill" href="#tab-calculator" role="tab" style="border-radius: 8px;">
                        <i class="fas fa-calculator mr-1.5 text-primary"></i> 2. Reorder Volume Split Calculator
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="bestSellerReportTabsContent">
                {{-- TAB 1: Ranking --}}
                <div class="tab-pane fade show active" id="tab-ranking" role="tabpanel">
                    <div class="card border shadow-sm" style="border-radius: 12px;">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
                            <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-list-ol mr-2 text-danger"></i>All Products Demand Ranking</h5>
                            <span class="badge badge-light border text-muted">Sorted by Order Frequency & Sold Volume</span>
                        </div>

                        <div class="card-body p-0" id="best-sellers-table-container">
                            @include('backend.reports.partials.best_sellers_table')
                        </div>
                    </div>
                </div>

                {{-- TAB 2: Calculator --}}
                <div class="tab-pane fade" id="tab-calculator" role="tabpanel">
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-left: 5px solid #2563eb !important;">
                        <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                            <div>
                                <h5 class="mb-0 font-weight-bold text-dark">
                                    <i class="fas fa-calculator mr-2 text-primary"></i> Interactive Reorder Volume Split Calculator
                                </h5>
                                <small class="text-muted">
                                    Automatically compute recommended purchase quantities per best seller based on sales velocity ratios.
                                </small>
                            </div>
                            <span class="badge badge-primary px-3 py-2 font-weight-bold" style="font-size: 11px;">
                                <i class="fas fa-bolt mr-1"></i> Velocity Driven
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <div class="row align-items-end mb-4">
                                <div class="col-md-4">
                                    <label class="font-weight-bold text-dark small mb-1">
                                        Planned Total Order Volume (Units / Pcs):
                                    </label>
                                    <div class="input-group">
                                        <input type="number" id="calc_target_volume" class="form-control font-weight-bold text-primary" style="font-size: 1.15rem; border-radius: 8px 0 0 8px;" value="{{ $targetVolume ?? 2000 }}" min="10" step="50">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light font-weight-bold text-dark">pcs</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="font-weight-bold text-dark small mb-1">
                                        Top Scope Allocation:
                                    </label>
                                    <select id="calc_top_scope" class="form-control" style="border-radius: 8px;">
                                        <option value="5" {{ ($scope ?? 5) == 5 ? 'selected' : '' }}>Top 5 Best Sellers</option>
                                        <option value="10" {{ ($scope ?? 5) == 10 ? 'selected' : '' }}>Top 10 Best Sellers</option>
                                        <option value="15" {{ ($scope ?? 5) == 15 ? 'selected' : '' }}>Top 15 Best Sellers</option>
                                        <option value="20" {{ ($scope ?? 5) == 20 ? 'selected' : '' }}>Top 20 Best Sellers</option>
                                    </select>
                                </div>
                                <div class="col-md-5 text-md-right mt-3 mt-md-0">
                                    <button type="button" class="btn btn-warning font-weight-bold px-4 py-2.5 shadow-sm text-dark" id="btn_add_scope_to_basket" style="border-radius: 8px; font-size: 0.95rem; background: #f59e0b; border: none;">
                                        <i class="fas fa-shopping-basket mr-1.5"></i> Add Scope to Procurement Cart
                                    </button>
                                </div>
                            </div>

                            {{-- Breakdown Table --}}
                            <div class="table-responsive rounded border bg-white shadow-sm">
                                <table class="table table-hover align-middle mb-0" id="calc_breakdown_table">
                                    <thead class="bg-light" style="font-size: 0.75rem; text-transform: uppercase; color: #475569; letter-spacing: 0.5px;">
                                        <tr>
                                            <th style="width: 70px;" class="text-center py-3">Rank</th>
                                            <th class="py-3">Product Name</th>
                                            <th style="width: 140px;" class="text-center py-3">Times Ordered</th>
                                            <th style="width: 140px;" class="text-center py-3">Total Sold Qty</th>
                                            <th style="width: 180px;" class="text-center py-3 text-primary font-weight-bold">Suggested Order Qty</th>
                                            <th style="width: 120px;" class="text-right py-3">Allocation %</th>
                                            <th style="width: 80px;" class="text-center pr-3 py-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="calc_breakdown_body">
                                        {{-- Dynamically populated via JS --}}
                                    </tbody>
                                    <tfoot class="bg-light font-weight-bold" style="font-size: 0.9rem;">
                                        <tr>
                                            <td colspan="4" class="text-right pr-3 py-3 text-dark">Total Recommended Units:</td>
                                            <td class="text-center text-primary py-3" id="calc_total_units_footer" style="font-size: 1.05rem;">0 pcs</td>
                                            <td class="text-right py-3 text-success">100.0%</td>
                                            <td class="pr-3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('scripts')
    <script>
        var filtering = false;
        var topItems = @json($topCalculatorItems ?? []);
        var noImageFallback = "{{ asset('uploads/no-image.svg') }}";

        function refreshSelect2(selector) {
            $(selector).trigger('change');
        }

        function loadSubCategories(categoryId, selectedSubCategoryId, callback) {
            if (!categoryId) {
                filtering = true;
                $('#sub_category_id').empty().append('<option value="">All Sub Categories</option>');
                $('#child_category_id').empty().append('<option value="">All Child Categories</option>');
                refreshSelect2('#sub_category_id');
                refreshSelect2('#child_category_id');
                filtering = false;
                if (callback) callback();
                return;
            }
            $.get('{{ route("admin.get-subCategories") }}', { id: categoryId }, function (data) {
                filtering = true;
                $('#sub_category_id').empty().append('<option value="">All Sub Categories</option>');
                $.each(data, function (i, item) {
                    var selected = selectedSubCategoryId && parseInt(selectedSubCategoryId) === item.id ? 'selected' : '';
                    $('#sub_category_id').append('<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>');
                });
                $('#child_category_id').empty().append('<option value="">All Child Categories</option>');
                refreshSelect2('#sub_category_id');
                refreshSelect2('#child_category_id');
                filtering = false;
                if (callback) callback();
            });
        }

        function loadChildCategories(subCategoryId, selectedChildCategoryId, callback) {
            if (!subCategoryId) {
                filtering = true;
                $('#child_category_id').empty().append('<option value="">All Child Categories</option>');
                refreshSelect2('#child_category_id');
                filtering = false;
                if (callback) callback();
                return;
            }
            $.get('{{ route("admin.get-child-categories") }}', { id: subCategoryId }, function (data) {
                filtering = true;
                $('#child_category_id').empty().append('<option value="">All Child Categories</option>');
                $.each(data, function (i, item) {
                    var selected = selectedChildCategoryId && parseInt(selectedChildCategoryId) === item.id ? 'selected' : '';
                    $('#child_category_id').append('<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>');
                });
                refreshSelect2('#child_category_id');
                filtering = false;
                if (callback) callback();
            });
        }

        function syncCalculatorBasketButtons() {
            $.get("{{ route('admin.cart.product-ids') }}", { cart_type: 'booking' }, function(res) {
                if (res && res.ids) {
                    var ids = res.ids.map(Number);
                    $('.btn-calc-add-basket').each(function() {
                        var pid = Number($(this).data('id'));
                        if (ids.includes(pid)) {
                            $(this).addClass('added btn-success text-white').removeClass('btn-outline-warning').html('<i class="fas fa-check"></i>');
                        }
                    });
                }
            });
        }

        function renderCalculator() {
            var targetVolume = parseFloat($('#calc_target_volume').val()) || 0;
            var scope = parseInt($('#calc_top_scope').val()) || 5;
            var activeItems = (topItems || []).slice(0, scope);
            var $body = $('#calc_breakdown_body');
            $body.empty();

            if (activeItems.length === 0 || targetVolume <= 0) {
                $body.html('<tr><td colspan="7" class="text-center py-4 text-muted">No sales order data available to calculate proportions.</td></tr>');
                $('#calc_total_units_footer').text('0 pcs');
                return;
            }

            var groupTotal = activeItems.reduce(function(sum, item) {
                return sum + (parseFloat(item.total_qty) || 0);
            }, 0);

            var totalAssigned = 0;

            activeItems.forEach(function(item, idx) {
                var soldQty = parseFloat(item.total_qty) || 0;
                var ratio = groupTotal > 0 ? (soldQty / groupTotal) : 0;
                var recommendedQty = Math.max(1, Math.round(targetVolume * ratio));
                totalAssigned += recommendedQty;

                var medal = idx === 0 
                    ? '<span class="badge badge-warning text-dark font-weight-bold px-2 py-0.5" style="font-size: 11px; background: #fbbf24;"><i class="fas fa-crown mr-0.5"></i>#1</span>'
                    : (idx === 1 
                        ? '<span class="badge badge-secondary font-weight-bold px-2 py-0.5" style="font-size: 11px;"><i class="fas fa-medal mr-0.5"></i>#2</span>'
                        : (idx === 2 
                            ? '<span class="badge font-weight-bold px-2 py-0.5 text-white" style="font-size: 11px; background: #b45309;"><i class="fas fa-medal mr-0.5"></i>#3</span>'
                            : '<span class="text-muted font-weight-bold">#' + (idx + 1) + '</span>'));

                var imgSrc = (item.thumb_image && item.thumb_image !== 'null' && String(item.thumb_image).trim() !== '') 
                    ? ('/' + String(item.thumb_image).replace(/^\//, '')) 
                    : noImageFallback;

                var categoryBadge = item.category_name 
                    ? '<span class="badge badge-light border" style="font-size: 10.5px;">' + item.category_name + '</span>' 
                    : '';

                var rowHtml = '<tr>' +
                    '<td class="text-center font-weight-bold">' + medal + '</td>' +
                    '<td>' +
                        '<div class="d-flex align-items-center">' +
                            '<img src="' + imgSrc + '" onerror="this.onerror=null;this.src=\'' + noImageFallback + '\';" style="width: 38px; height: 38px; object-fit: cover; border-radius: 6px;" class="mr-2.5 border" alt="">' +
                            '<div>' +
                                '<strong class="text-dark d-block">' + item.product_name + '</strong>' +
                                categoryBadge +
                            '</div>' +
                        '</div>' +
                    '</td>' +
                    '<td class="text-center">' +
                        '<span class="badge badge-danger px-2.5 py-1 font-weight-bold" style="font-size: 11px;">' +
                            '<i class="fas fa-shopping-cart mr-1"></i>' + Number(item.times_ordered).toLocaleString() + ' orders' +
                        '</span>' +
                    '</td>' +
                    '<td class="text-center font-weight-bold text-dark">' + Number(soldQty).toLocaleString() + ' pcs</td>' +
                    '<td class="text-center">' +
                        '<span class="badge badge-success px-3 py-1.5 font-weight-bold" style="font-size: 0.95rem; border-radius: 6px;">' +
                            recommendedQty.toLocaleString() + ' pcs' +
                        '</span>' +
                    '</td>' +
                    '<td class="text-right py-3 font-weight-bold text-primary">' +
                        (ratio * 100).toFixed(1) + '%' +
                    '</td>' +
                    '<td class="text-center pr-3">' +
                        '<button type="button" class="btn btn-sm btn-outline-warning btn-calc-add-basket font-weight-bold shadow-sm" data-id="' + item.product_id + '" data-qty="' + recommendedQty + '" title="Add to Procurement Basket" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px;">' +
                            '<i class="fas fa-shopping-basket"></i>' +
                        '</button>' +
                    '</td>' +
                '</tr>';

                $body.append(rowHtml);
            });

            $('#calc_total_units_footer').text(totalAssigned.toLocaleString() + ' pcs');
            syncCalculatorBasketButtons();
        }

        function fetchBestSellers(page) {
            var form = $('#best-sellers-filter-form');
            var url = form.attr('action');
            var data = form.serialize();
            if (page) data += '&page=' + page;

            $.ajax({
                url: url,
                type: 'GET',
                data: data,
                dataType: 'json',
                beforeSend: function () {
                    $('#best-sellers-table-container').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i></div>');
                },
                success: function (res) {
                    $('#best-sellers-table-container').html(res.html);
                    $('#summary-total-products').text(res.total_products);
                    $('#summary-grand-total-qty').text(res.grand_total_qty);
                    $('#summary-grand-total-value').html(res.grand_total_value);

                    if (res.items && res.items.length) {
                        topItems = res.items.slice(0, 20);
                        renderCalculator();
                    }
                }
            });
        }

        $(document).ready(function () {
            // Check if URL has tab parameter
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('tab') === 'calculator') {
                $('#calculator-tab').tab('show');
            }

            renderCalculator();

            $('#calc_target_volume, #calc_top_scope').on('input change', function() {
                renderCalculator();
            });

            // Single row Add to Procurement Basket
            $(document).on('click', '.btn-calc-add-basket', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var pId = Number($btn.data('id'));
                var qty = parseFloat($btn.data('qty')) || 1;
                var isAdded = $btn.hasClass('added');
                var action = isAdded ? 'remove' : 'add';

                $btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.cart.add') }}",
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        product_id: pId,
                        cart_type: 'booking',
                        quantity: qty,
                        action: action
                    },
                    success: function(res) {
                        $btn.prop('disabled', false);
                        if (res.success) {
                            if (action === 'add') {
                                $btn.addClass('added btn-success text-white').removeClass('btn-outline-warning').html('<i class="fas fa-check"></i>');
                                if (window.toastr) toastr.success(res.message || ('Added to Procurement basket (' + qty + ' pcs)'));
                            } else {
                                $btn.removeClass('added btn-success text-white').addClass('btn-outline-warning').html('<i class="fas fa-shopping-basket"></i>');
                                if (window.toastr) toastr.info(res.message || 'Removed from Procurement basket');
                            }
                            if (window.updateGlobalCartBadges && res.count !== undefined) {
                                window.updateGlobalCartBadges(res.count, undefined);
                            }
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false);
                        if (window.toastr) toastr.error('Failed to update procurement basket');
                    }
                });
            });

            // Bulk Add Active Scope to Procurement Basket with calculated ratios
            $('#btn_add_scope_to_basket').on('click', function() {
                var $btn = $(this);
                var targetVolume = parseFloat($('#calc_target_volume').val()) || 0;
                var scope = parseInt($('#calc_top_scope').val()) || 5;
                var activeItems = (topItems || []).slice(0, scope);

                if (activeItems.length === 0 || targetVolume <= 0) {
                    if (window.toastr) toastr.warning('No items to add to Procurement Basket.');
                    return;
                }

                var groupTotal = activeItems.reduce(function(sum, item) {
                    return sum + (parseFloat(item.total_qty) || 0);
                }, 0);

                var itemsPayload = activeItems.map(function(item) {
                    var soldQty = parseFloat(item.total_qty) || 0;
                    var ratio = groupTotal > 0 ? (soldQty / groupTotal) : 0;
                    var recommendedQty = Math.max(1, Math.round(targetVolume * ratio));
                    return {
                        product_id: Number(item.product_id),
                        quantity: recommendedQty
                    };
                });

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Adding to Cart...');

                $.ajax({
                    url: "{{ route('admin.cart.bulk-add-products') }}",
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        cart_type: 'booking',
                        items: itemsPayload
                    },
                    success: function(res) {
                        $btn.prop('disabled', false).html('<i class="fas fa-check mr-1.5"></i> Added to Procurement Cart');
                        setTimeout(function() {
                            $btn.html('<i class="fas fa-shopping-basket mr-1.5"></i> Add Scope to Procurement Cart');
                        }, 3000);

                        if (window.toastr) toastr.success('Successfully added ' + itemsPayload.length + ' Best Sellers to Procurement Basket with calculated ratios!');
                        if (window.updateGlobalCartBadges && res.count !== undefined) {
                            window.updateGlobalCartBadges(res.count, undefined);
                        }
                        $('.btn-calc-add-basket').addClass('added btn-success text-white').removeClass('btn-outline-warning').html('<i class="fas fa-check mr-1"></i> Added');
                    },
                    error: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-shopping-basket mr-1.5"></i> Add Scope to Procurement Cart');
                        if (window.toastr) toastr.error('Failed to add items to procurement basket');
                    }
                });
            });

            var selectedCategory = '{{ request("category_id") }}';
            var selectedSubCategory = '{{ request("sub_category_id") }}';
            var selectedChildCategory = '{{ request("child_category_id") }}';

            if (selectedCategory) {
                loadSubCategories(selectedCategory, selectedSubCategory || null);
            }
            if (selectedSubCategory) {
                loadChildCategories(selectedSubCategory, selectedChildCategory || null);
            }

            $('#year, #month').on('change', function () {
                fetchBestSellers();
            });

            $('#category_id').on('change', function () {
                var val = $(this).val();
                loadSubCategories(val, null, function () {
                    fetchBestSellers();
                });
            });

            $('#sub_category_id').on('change', function () {
                if (filtering) return;
                var val = $(this).val();
                loadChildCategories(val, null, function () {
                    fetchBestSellers();
                });
            });

            $('#child_category_id').on('change', function () {
                if (filtering) return;
                fetchBestSellers();
            });

            var searchTimeout;
            $('#best-sellers-filter-form input[name="search"]').on('input keyup', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    fetchBestSellers();
                }, 400);
            });

            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();
                var url = new URL($(this).attr('href'));
                var page = url.searchParams.get('page') || 1;
                window.history.pushState(null, '', window.location.pathname + '?' + $('#best-sellers-filter-form').serialize() + '&page=' + page);
                fetchBestSellers(page);
            });
        });
    </script>
@endpush
