@extends('backend.layouts.master')
@section('title', 'Stock Ledger & Inventory Movement History')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-boxes text-primary mr-2"></i> Stock Ledger & Movement History</h1>
            <p class="text-muted mb-0 small">Comprehensive inbound and outbound stock movement tracking with source and destination party details</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.inventory-reports.index') }}">Inventory</a></div>
            <div class="breadcrumb-item">Stock Ledger & Movements</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                {{-- Filter Card --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-filter text-primary mr-2"></i> Filter Ledger Movements</h4>
                    </div>
                    <div class="card-body">
                        {{-- Row 1: Category Hierarchy & Product --}}
                        <div class="row">
                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filter-category" class="font-weight-bold text-dark"><i class="fas fa-folder text-primary mr-1"></i> Category</label>
                                <select id="filter-category" class="form-control select2">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filter-sub-category" class="font-weight-bold text-dark"><i class="fas fa-folder-open text-info mr-1"></i> Sub Category</label>
                                <select id="filter-sub-category" class="form-control select2">
                                    <option value="">All Sub Categories</option>
                                </select>
                            </div>

                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filter-child-category" class="font-weight-bold text-dark"><i class="fas fa-level-down-alt text-secondary mr-1"></i> Child Category</label>
                                <select id="filter-child-category" class="form-control select2">
                                    <option value="">All Child Categories</option>
                                </select>
                            </div>

                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filter-product" class="font-weight-bold text-dark"><i class="fas fa-box text-success mr-1"></i> Product</label>
                                <select id="filter-product" class="form-control select2">
                                    <option value="">All Products</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Row 2: Variant, Movement Type, Target Outlet --}}
                        <div class="row mt-1">
                            <div class="col-md-4 col-sm-6 form-group">
                                <label for="filter-variant" class="font-weight-bold text-dark"><i class="fas fa-tags text-warning mr-1"></i> Variant</label>
                                <select id="filter-variant" class="form-control select2">
                                    <option value="">All Variants</option>
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-6 form-group">
                                <label for="filter-movement-type" class="font-weight-bold text-dark"><i class="fas fa-exchange-alt text-primary mr-1"></i> Movement Type</label>
                                <select id="filter-movement-type" class="form-control select2">
                                    <option value="">All Types (IN & OUT)</option>
                                    <option value="in">📥 IN (Received into WH)</option>
                                    <option value="out">📤 OUT (Dispatched to Stores)</option>
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-12 form-group">
                                <label for="filter-outlet" class="font-weight-bold text-dark"><i class="fas fa-store text-danger mr-1"></i> Outlet / Recipient</label>
                                <select id="filter-outlet" class="form-control select2">
                                    <option value="">All Outlets / Stores</option>
                                    @foreach ($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} {{ $u->outlet_name ? '(' . $u->outlet_name . ')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Row 3: Reference Type, Date Range & Reset (Bottom Row) --}}
                        <div class="row mt-1 pt-2 border-top">
                            <div class="col-md-4 col-sm-6 form-group">
                                <label for="filter-reference-type" class="font-weight-bold text-dark"><i class="fas fa-file-alt text-info mr-1"></i> Reference Type</label>
                                <select id="filter-reference-type" class="form-control select2">
                                    <option value="">All References</option>
                                    @foreach ($referenceTypes as $referenceType)
                                        @php
                                            $label = match(strtolower($referenceType)) {
                                                'deliveryorder' => 'Delivery Order (DO)',
                                                'issue' => 'Orders / Fulfillment',
                                                'purchase' => 'Purchase (PO)',
                                                'adjustment' => 'Stock Adjustment',
                                                'opening' => 'Opening Stock',
                                                'bin_relocation_in' => 'Bin Transfer (IN)',
                                                'bin_relocation_out' => 'Bin Transfer (OUT)',
                                                'goods_receipt' => 'Goods Receipt',
                                                default => ucfirst(str_replace('_', ' ', $referenceType))
                                            };
                                        @endphp
                                        <option value="{{ $referenceType }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-5 col-sm-12 form-group">
                                <label class="font-weight-bold text-dark"><i class="fas fa-calendar-alt text-primary mr-1"></i> Date Range</label>
                                <div class="d-flex align-items-center" style="gap: 10px;">
                                    <input type="date" id="filter-date-from" class="form-control" title="Date From">
                                    <span class="text-muted font-weight-bold">to</span>
                                    <input type="date" id="filter-date-to" class="form-control" title="Date To">
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-12 form-group">
                                <label class="d-none d-md-block">&nbsp;</label>
                                <button type="button" class="btn btn-outline-danger btn-block font-weight-bold shadow-sm" id="reset-ledger-filters" style="height: 38px;">
                                    <i class="fas fa-undo mr-1"></i> Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Table Card --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-history text-primary mr-2"></i> Inventory Movement History</h4>
                        <div>
                            <span class="badge badge-success font-weight-bold mr-2"><i class="fas fa-arrow-down mr-1"></i> Inflows: Supplier & PO</span>
                            <span class="badge badge-danger font-weight-bold"><i class="fas fa-arrow-up mr-1"></i> Outflows: Outlet & Orders</span>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-striped table-bordered align-middle" id="table-ledger" style="width: 100%;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 110px;">Date & Time</th>
                                    <th>Product Details & Category</th>
                                    <th style="width: 90px;">Variant</th>
                                    <th class="text-center" style="width: 75px;">Type</th>
                                    <th>Source / Destination (Party)</th>
                                    <th style="width: 110px;">Reference</th>
                                    <th style="width: 110px;">Bin Location</th>
                                    <th class="text-center" style="width: 75px;">In Qty</th>
                                    <th class="text-center" style="width: 75px;">Out Qty</th>
                                    <th class="text-center" style="width: 80px;">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    const ledgerProducts = @json($ledgerProducts);

    function renderFilteredProducts() {
        const selectedCat = $('#filter-category').val();
        const selectedSub = $('#filter-sub-category').val();
        const selectedChild = $('#filter-child-category').val();
        const $product = $('#filter-product');

        $product.empty().append('<option value="">All Products</option>');

        Object.values(ledgerProducts).forEach(function (p) {
            let match = true;
            if (selectedCat && String(p.category_id) !== String(selectedCat)) {
                match = false;
            }
            if (selectedSub && String(p.sub_category_id) !== String(selectedSub)) {
                match = false;
            }
            if (selectedChild && String(p.child_category_id) !== String(selectedChild)) {
                match = false;
            }
            if (match) {
                $product.append(new Option(p.name, p.id));
            }
        });

        $product.trigger('change.select2');
    }

    function renderVariantOptions(productId) {
        const $variant = $('#filter-variant');
        const p = productId && Object.prototype.hasOwnProperty.call(ledgerProducts, String(productId))
            ? ledgerProducts[String(productId)]
            : null;
        const variants = p && p.variants ? p.variants : [];

        $variant.empty().append('<option value="">All Variants</option>');

        variants.forEach(function (variant) {
            $variant.append(new Option(variant.label, variant.id));
        });

        $variant.trigger('change.select2');
    }

    const ledgerTable = $("#table-ledger").DataTable({
        dom: '<"row mb-3"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'excel',
                className: 'btn btn-primary btn-sm mr-1',
                title: 'Stock Ledger Report'
            },
            {
                extend: 'csv',
                className: 'btn btn-primary btn-sm mr-1',
                title: 'Stock Ledger Report'
            },
            {
                extend: 'pdf',
                className: 'btn btn-primary btn-sm mr-1',
                title: 'Stock Ledger Report'
            },
            {
                extend: 'print',
                className: 'btn btn-primary btn-sm',
                title: 'Stock Ledger Report'
            }
        ],
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.stock-ledger.index') }}",
            data: function (d) {
                d.category_id = $('#filter-category').val();
                d.sub_category_id = $('#filter-sub-category').val();
                d.child_category_id = $('#filter-child-category').val();
                d.product_id = $('#filter-product').val();
                d.variant_id = $('#filter-variant').val();
                d.movement_type = $('#filter-movement-type').val();
                d.outlet_id = $('#filter-outlet').val();
                d.reference_type = $('#filter-reference-type').val();
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
            }
        },
        columns: [
            {data: 'date', name: 'created_at', width: '110px'},
            {data: 'product_info', name: 'product_info', orderable: false},
            {data: 'variant_name', name: 'variant_name', width: '90px'},
            {data: 'type', name: 'type', orderable: false, searchable: false, className: 'text-center', width: '75px'},
            {data: 'party_destination', name: 'party_destination', orderable: false, searchable: false},
            {data: 'reference', name: 'reference', width: '110px'},
            {data: 'bin_location', name: 'bin_location', width: '110px'},
            {data: 'in_qty', name: 'in_qty', className: 'text-center font-weight-bold', width: '75px'},
            {data: 'out_qty', name: 'out_qty', className: 'text-center font-weight-bold', width: '75px'},
            {data: 'balance_qty', name: 'balance_qty', className: 'text-center font-weight-bold', width: '80px'}
        ],
        order: [[0, "desc"]],
        pageLength: 10
    });

    // 1. Cascading Category -> Sub-Category
    $('#filter-category').on('change', function () {
        const catId = $(this).val();
        const $sub = $('#filter-sub-category');
        const $child = $('#filter-child-category');

        $sub.empty();
        $child.empty();

        if (catId) {
            $sub.append('<option value="">Loading Sub Categories...</option>');
            $child.append('<option value="">Select Child Category</option>');
            $sub.trigger('change.select2');
            $child.trigger('change.select2');

            $.ajax({
                method: 'GET',
                url: "{{ route('admin.get-subCategories') }}",
                data: { id: catId },
                success: function (data) {
                    $sub.empty();
                    if (data && data.length > 0) {
                        $sub.append('<option value="">Select Sub Category</option>');
                        $.each(data, function (i, item) {
                            $sub.append(new Option(item.name, item.id));
                        });
                    } else {
                        $sub.append('<option value="">No Sub Category Available</option>');
                    }
                    $sub.trigger('change.select2');
                },
                error: function () {
                    $sub.empty().append('<option value="">All Sub Categories</option>').trigger('change.select2');
                }
            });
        } else {
            $sub.append('<option value="">All Sub Categories</option>');
            $child.append('<option value="">All Child Categories</option>');
            $sub.trigger('change.select2');
            $child.trigger('change.select2');
        }

        renderFilteredProducts();
        ledgerTable.ajax.reload();
    });

    // 2. Cascading Sub-Category -> Child-Category
    $('#filter-sub-category').on('change', function () {
        const subId = $(this).val();
        const catId = $('#filter-category').val();
        const $child = $('#filter-child-category');

        $child.empty();

        if (subId) {
            $child.append('<option value="">Loading Child Categories...</option>');
            $child.trigger('change.select2');

            $.ajax({
                method: 'GET',
                url: "{{ route('admin.get-child-categories') }}",
                data: { id: subId },
                success: function (data) {
                    $child.empty();
                    if (data && data.length > 0) {
                        $child.append('<option value="">Select Child Category</option>');
                        $.each(data, function (i, item) {
                            $child.append(new Option(item.name, item.id));
                        });
                    } else {
                        $child.append('<option value="">No Child Category Available</option>');
                    }
                    $child.trigger('change.select2');
                },
                error: function () {
                    $child.empty().append('<option value="">All Child Categories</option>').trigger('change.select2');
                }
            });
        } else {
            if (catId) {
                $child.append('<option value="">Select Child Category</option>');
            } else {
                $child.append('<option value="">All Child Categories</option>');
            }
            $child.trigger('change.select2');
        }

        renderFilteredProducts();
        ledgerTable.ajax.reload();
    });

    // 3. Child Category change
    $('#filter-child-category').on('change', function () {
        renderFilteredProducts();
        ledgerTable.ajax.reload();
    });

    // 4. Product change
    $('#filter-product').on('change', function () {
        renderVariantOptions($(this).val());
        ledgerTable.ajax.reload();
    });

    // 5. Other filters change
    $('#filter-variant, #filter-movement-type, #filter-outlet, #filter-reference-type, #filter-date-from, #filter-date-to').on('change', function () {
        ledgerTable.ajax.reload();
    });

    // 6. Reset filters
    $('#reset-ledger-filters').on('click', function () {
        $('#filter-category').val('').trigger('change.select2');
        $('#filter-sub-category').empty().append('<option value="">All Sub Categories</option>').trigger('change.select2');
        $('#filter-child-category').empty().append('<option value="">All Child Categories</option>').trigger('change.select2');
        $('#filter-product').val('').trigger('change.select2');
        $('#filter-variant').val('').trigger('change.select2');
        $('#filter-movement-type').val('').trigger('change.select2');
        $('#filter-outlet').val('').trigger('change.select2');
        $('#filter-reference-type').val('').trigger('change.select2');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        renderFilteredProducts();
        renderVariantOptions('');
        ledgerTable.ajax.reload();
    });
</script>
@endpush