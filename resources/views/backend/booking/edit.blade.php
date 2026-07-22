@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Order Place</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <form action="{{ route('admin.bookings.update', $targetBooking->id) }}" method="POST" id="booking_form">
                        @csrf
                        @method('PUT')
                        
                        <div class="card mb-4 shadow-sm border-0">
                            <div class="card-header bg-primary text-white d-flex justify-content-between">
                                <h4>Edit Order Place ({{ $targetBooking->booking_no }})</h4>
                                <a href="{{ route('admin.bookings.index') }}" class="btn btn-light btn-sm text-primary font-weight-bold">
                                    <i class="fas fa-arrow-left mr-1"></i> Back
                                </a>
                            </div>
                            <div class="card-body bg-light-gray">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold">Select Vendor <span class="text-danger">*</span></label>
                                            <select class="form-control select2" name="vendor_id" required>
                                                <option value="">Choose a Vendor</option>
                                                @foreach ($vendors as $vendor)
                                                    <option {{ $targetBooking->vendor_id == $vendor->id ? 'selected' : '' }} value="{{ $vendor->id }}">{{ $vendor->shop_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold">Shipping Method</label>
                                            <select class="form-control" name="shipping_method">
                                                <option value="">-- Select Shipping --</option>
                                                <option value="Air" {{ $targetBooking->shipping_method == 'Air' ? 'selected' : '' }}>Air</option>
                                                <option value="Ship" {{ $targetBooking->shipping_method == 'Ship' ? 'selected' : '' }}>Ship</option>
                                                <option value="Normal" {{ $targetBooking->shipping_method == 'Normal' ? 'selected' : '' }}>Normal</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold">Order Status</label>
                                            <select class="form-control" name="status">
                                                <option {{ $targetBooking->status == 'pending' ? 'selected' : '' }} value="pending">Pending</option>
                                                <option {{ $targetBooking->status == 'complete' ? 'selected' : '' }} value="complete">Complete</option>
                                                <option {{ $targetBooking->status == 'cancelled' ? 'selected' : '' }} value="cancelled">Cancelled</option>
                                                <option {{ $targetBooking->status == 'missing' ? 'selected' : '' }} value="missing">Missing</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold text-muted">Booking No</label>
                                            <div class="h5 mt-2 font-weight-bold text-primary">{{ $targetBooking->booking_no }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 1: Product Selection & Filters -->
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-whitesmoke">
                                <h5><i class="fas fa-search mr-2 text-primary"></i> Add Products to Basket</h5>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-end">
                                    <div class="col-md-3">
                                        <label class="small font-weight-bold">Category</label>
                                        <select class="form-control select2" id="filter_category">
                                            <option value="">All Categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small font-weight-bold">Sub Category</label>
                                        <select class="form-control select2" id="filter_sub_category">
                                            <option value="">Select Sub Category</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small font-weight-bold">Child Category</label>
                                        <select class="form-control select2" id="filter_child_category">
                                            <option value="">Select Child Category</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-outline-secondary btn-block" id="reset_filters">
                                            <i class="fas fa-undo"></i> Reset Filters
                                        </button>
                                    </div>
                                </div>

                                <div class="row mt-4 align-items-end p-3 bg-light rounded border mx-1">
                                    <div class="col-md-9">
                                        <label class="font-weight-bold text-primary">Find Product</label>
                                        <select class="form-control select2" id="product_search">
                                            <option value="">Search by Name or SKU...</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }} - #{{ $product->product_number }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-success btn-block py-2 font-weight-bold" id="add_to_basket">
                                            <i class="fas fa-plus-circle mr-1"></i> Add to Basket
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Booking Basket -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-whitesmoke d-flex justify-content-between">
                                <h5><i class="fas fa-shopping-basket mr-2 text-success"></i> Selected Products</h5>
                                <span class="badge badge-primary px-3" id="item_count">0 Items</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="basket_table">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="60" class="text-center">IMG</th>
                                                <th width="250">Product Information</th>
                                                <th width="70" class="text-center">Stock</th>
                                                <th width="120">Unit</th>
                                                <th width="100" class="text-center">Order Qty</th>
                                                <th width="280">Variant Breakdown</th>
                                                <th width="50" class="text-center"><i class="fas fa-trash"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody id="basket_body">
                                            <tr id="empty_basket_row">
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <i class="fas fa-box-open fa-3x mb-3 d-block opacity-25"></i>
                                                    Basket is empty. Select products above to add them.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Custom Fields -->
                        <div class="card mt-3 shadow-sm border">
                            <div class="card-header bg-whitesmoke d-flex justify-content-between align-items-center">
                                <h5 class="text-success mb-0"><i class="fas fa-plus-circle mr-2"></i> Custom Fields (Optional)</h5>
                                <div class="card-header-action">
                                    <button type="button" class="btn btn-sm btn-success" id="add-custom-field">
                                        <i class="fas fa-plus"></i> Add Field
                                    </button>
                                </div>
                            </div>
                            <div class="card-body" id="custom-fields-container">
                                @if($targetBooking->custom_fields)
                                    @foreach($targetBooking->custom_fields as $index => $field)
                                        <div class="row mb-2 align-items-center" id="custom-field-{{$index}}">
                                            <div class="col-md-5">
                                                <input type="text" name="custom_fields[{{$index}}][key]" class="form-control" value="{{ $field['key'] }}" placeholder="Field Name">
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" name="custom_fields[{{$index}}][value]" class="form-control" value="{{ $field['value'] }}" placeholder="Value">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-block" onclick="$('#custom-field-{{$index}}').remove()"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Section 3: Notes & Action -->
                        <div class="card mt-4 shadow-sm border-0 bg-whitesmoke">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold">Internal Notes / Description</label>
                                            <textarea name="description" class="form-control" rows="3" placeholder="Enter any specific instructions or notes for this order...">{{ $targetBooking->description }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-right d-flex flex-column justify-content-end">
                                         <button type="submit" class="btn btn-primary btn-lg btn-block shadow-sm py-3">
                                            <i class="fas fa-save mr-1"></i> Update Booking Order
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <style>
        .basket-product-name { font-weight: 700; font-size: 1rem; color: #34395e; margin-bottom: 0px; }
        .basket-product-sku { font-size: 0.8rem; color: #98a6ad; }
        .variant-input-group { display: flex; align-items: center; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px; padding: 4px 8px; margin-bottom: 4px; }
        .variant-label { flex-grow: 1; font-size: 0.75rem; font-weight: 600; color: #495057; }
        .variant-qty { width: 70px !important; height: 28px !important; padding: 0 5px; font-size: 0.85rem; }
        .bg-light-gray { background-color: #fbfbfb; }
        .italic { font-style: italic; }
        .tiny { font-size: 0.6rem; }

        /* Mobile Responsive Table Breakdown */
        /* @media (max-width: 767.98px) {
            #basket_table thead { display: none; }
            #basket_table, #basket_table tbody, #basket_table tr, #basket_table td { 
                display: block; 
                width: 100%; 
            }
            #basket_table tr.basket-row { 
                margin-bottom: 20px; 
                border: 1px solid #e4e6fc !important; 
                border-radius: 10px; 
                padding: 15px; 
                background: #fff; 
                box-shadow: 0 4px 6px rgba(0,0,0,0.04);
            }
            #basket_table td { 
                border: none !important; 
                padding: 10px 0 !important; 
                display: flex; 
                align-items: center; 
                justify-content: space-between;
                text-align: right !important;
            }
            #basket_table td:before { 
                content: attr(data-label); 
                font-weight: 800; 
                flex-basis: 40%; 
                text-align: left; 
                font-size: 11px; 
                color: #888;
                text-transform: uppercase;
            }
            #basket_table td:first-child { 
                justify-content: center; 
                border-bottom: 1px solid #eee !important; 
                padding-bottom: 15px !important;
                margin-bottom: 10px;
            }
            #basket_table td:first-child:before { display: none; }
            #basket_table td .basket-product-name { font-size: 16px; }
            #basket_table td .form-control { width: 150px !important; max-width: 60%; }
            #basket_table td.text-center { justify-content: space-between; }
        } */
    </style>
@endsection

@push('scripts')
    <script>
        const products = @json($products);
        const units = @json($units);
        let rowCount = 0;

        $(document).ready(function() {
            // Pre-populate basket with existing items
            const orderGroup = @json($orderGroup);
            orderGroup.forEach(item => {
                let product = products.find(p => p.id == item.product_id);
                if(product) {
                    addProductRow(product, item);
                }
            });

            // Filtering Logic
            $('#filter_category').on('change', function() {
                let catId = $(this).val();
                $('#filter_sub_category', '#filter_child_category').html('<option value="">Wait...</option>');
                
                $.get("{{ route('admin.bookings.get-subcategories') }}", { id: catId }, function(data) {
                    let html = '<option value="">Select Sub Category</option>';
                    data.forEach(sc => html += `<option value="${sc.id}">${sc.name}</option>`);
                    $('#filter_sub_category').html(html);
                    filterProducts();
                });
            });

            $('#filter_sub_category').on('change', function() {
                let subId = $(this).val();
                $.get("{{ route('admin.bookings.get-childcategories') }}", { id: subId }, function(data) {
                    let html = '<option value="">Select Child Category</option>';
                    data.forEach(cc => html += `<option value="${cc.id}">${cc.name}</option>`);
                    $('#filter_child_category').html(html);
                    filterProducts();
                });
            });

            $('#filter_child_category').on('change', filterProducts);

            function filterProducts() {
                let catId = $('#filter_category').val();
                let subId = $('#filter_sub_category').val();
                let childId = $('#filter_child_category').val();

                let filtered = products.filter(p => {
                    return (!catId || p.category_id == catId) && 
                           (!subId || p.sub_category_id == subId) &&
                           (!childId || p.child_category_id == childId);
                });

                let html = '<option value="">Search by Name or SKU...</option>';
                filtered.forEach(p => html += `<option value="${p.id}">${p.name} - #${p.product_number}</option>`);
                $('#product_search').html(html);
            }

            $('#reset_filters').on('click', function() {
                $('#filter_category, #filter_sub_category, #filter_child_category').val('').trigger('change');
                filterProducts();
            });

            // Basket Logic
            $('#add_to_basket').on('click', function() {
                let productId = $('#product_search').val();
                if(!productId) {
                    toastr.warning('Please select a product first.');
                    return;
                }

                if($(`input[value="${productId}"][name*="product_id"]`).length > 0) {
                    toastr.error('Product already in basket.');
                    return;
                }

                let product = products.find(p => p.id == productId);
                addProductRow(product);
                toastr.success(`${product.name} added to basket.`);
            });

            function addProductRow(product, existingItem = null) {
                $('#empty_basket_row').hide();
                
                let imageHtml = product.thumb_image 
                    ? `<img src="{{ asset('storage') }}/${product.thumb_image}" class="rounded border shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">`
                    : `<div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted tiny" style="width: 50px; height: 50px;">NO IMG</div>`;

                let variantHtml = '<div class="row">';
                let hasVariants = false;
                
                let savedVariants = existingItem ? existingItem.variant_info : null;

                if(product.variants && product.variants.length > 0) {
                    product.variants.forEach(v => {
                        let colorName = v.color ? v.color.name : '';
                        let sizeName = v.size ? v.size.name : '';
                        let name = (colorName + ' ' + sizeName).trim() || 'Default';
                        if(name) {
                            hasVariants = true;
                            let safeName = name.replace(/"/g, '&quot;');
                            let qtyValue = (savedVariants && savedVariants[name]) ? savedVariants[name] : 0;

                            variantHtml += `
                                <div class="col-12">
                                    <div class="variant-input-group">
                                        <span class="variant-label" title="${name}">${name}</span>
                                        <input type="number" class="form-control form-control-sm variant-qty" 
                                               name="items[${rowCount}][variant_quantities][${safeName}]" 
                                               data-row="${rowCount}" value="${qtyValue}" min="0">
                                    </div>
                                </div>`;
                        }
                    });
                }
                variantHtml += '</div>';

                if(!hasVariants) variantHtml = '<span class="text-muted italic small ml-2">No variants available for this item</span>';

                let rowQty = existingItem ? existingItem.qty : 1;
                let rowUnitId = existingItem ? (existingItem.unit_id || product.unit_id) : product.unit_id;

                let rowHtml = `
                    <tr id="row_${rowCount}" class="basket-row">
                        <td class="text-center align-middle" data-label="Image">${imageHtml}</td>
                        <td class="align-middle" data-label="Product">
                            <input type="hidden" name="items[${rowCount}][product_id]" value="${product.id}">
                            <p class="basket-product-name">${product.name}</p>
                            <span class="basket-product-sku">#${product.product_number || 'UNKNOWN'}</span>
                        </td>
                        <td class="align-middle text-center" data-label="Stock">
                            <span class="badge badge-${(product.inventory_stocks_sum_quantity || 0) > 0 ? 'success' : 'danger'}" style="font-size:13px;">
                                ${product.inventory_stocks_sum_quantity || 0}
                            </span>
                        </td>
                        <td class="align-middle" data-label="Unit">
                            <select class="form-control" name="items[${rowCount}][unit_id]">
                                <option value="">Select Unit</option>
                                ${units.map(u => `<option value="${u.id}" ${rowUnitId == u.id ? 'selected' : ''}>${u.name}</option>`).join('')}
                            </select>
                        </td>
                        <td class="align-middle text-center" data-label="Qty">
                            <input type="number" class="form-control main-qty font-weight-bold" name="items[${rowCount}][qty]" 
                                   id="qty_${rowCount}" value="${rowQty}" min="1" required style="text-align:center;">
                        </td>
                        <td class="align-middle" data-label="Variants">
                            ${variantHtml}
                        </td>
                        <td class="text-center align-middle" data-label="Delete">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-row border-0" data-id="${rowCount}">
                                <i class="fas fa-times-circle fa-lg"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#basket_body').append(rowHtml);
                rowCount++;
                updateBasketStats();
            }

            $(document).on('click', '.remove-row', function() {
                let id = $(this).data('id');
                $(`#row_${id}`).remove();
                if($('.basket-row').length === 0) {
                    $('#empty_basket_row').show();
                }
                updateBasketStats();
            });

            $(document).on('input', '.variant-qty', function() {
                let rowId = $(this).data('row');
                let row = $(`#row_${rowId}`);
                let total = 0;
                
                row.find('.variant-qty').each(function() {
                    total += parseInt($(this).val()) || 0;
                });

                if(total > 0) {
                   row.find('.main-qty').val(total).attr('min', total);
                } else {
                    row.find('.main-qty').attr('min', 1);
                }
            });

            function updateBasketStats() {
                let count = $('.basket-row').length;
                $('#item_count').text(`${count} Item${count !== 1 ? 's' : ''}`);
            }

            // --- Custom Fields Logic ---
            let fieldCount = {{ $targetBooking->custom_fields ? count($targetBooking->custom_fields) : 0 }};
            $('#add-custom-field').on('click', function() {
                let html = `
                    <div class="row mb-2" id="custom-field-${fieldCount}">
                        <div class="col-md-5">
                            <input type="text" name="custom_fields[${fieldCount}][key]" class="form-control" placeholder="Field Name (e.g. Fabric)">
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="custom_fields[${fieldCount}][value]" class="form-control" placeholder="Value (e.g. Cotton)">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-block" onclick="$('#custom-field-${fieldCount}').remove()">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>`;
                $('#custom-fields-container').append(html);
                fieldCount++;
            });

            $('#booking_form').on('submit', function(e) {
                if($('.basket-row').length === 0) {
                    e.preventDefault();
                    toastr.error('Product basket must contain at least one item.', 'Basket Empty');
                }
            });
        });
    </script>
@endpush
