@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Order Place</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Order Place</a></div>
                <div class="breadcrumb-item">Create</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <form action="{{ route('admin.bookings.store') }}" method="POST" id="booking_form">
                        @csrf
                        
                        <!-- Section 1: Vendor & Global Status -->
                        <div class="card card-primary">
                            <div class="card-header">
                                <h4>General Information</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold">Select Vendor <span class="text-danger">*</span></label>
                                        <select class="form-control select2" name="vendor_id" required>
                                            <option value="">-- Select Vendor --</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->shop_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Shipping Method</label>
                                        <select class="form-control" name="shipping_method">
                                            <option value="">-- Select Shipping --</option>
                                            <option value="Air">Air</option>
                                            <option value="Ship">Ship</option>
                                            <option value="Normal">Normal</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Category-wise Product Filtering -->
                        <div class="card card-info shadow-none border">
                            <div class="card-header bg-light">
                                <h4 class="text-info">Search & Add Product</h4>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="form-group col-md-4">
                                        <label>Category</label>
                                        <select class="form-control select2" id="category_filter">
                                            <option value="">All Categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Sub Category</label>
                                        <select class="form-control select2" id="sub_category_filter">
                                            <option value="">Select Sub Category</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Child Category</label>
                                        <select class="form-control select2" id="child_category_filter">
                                            <option value="">Select Child Category</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row align-items-end">
                                    <div class="form-group col-md-9 mb-0">
                                        <label class="font-weight-bold text-primary">Select Product</label>
                                        <select class="form-control select2" id="product_selector">
                                            <option value="">-- Search Product --</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->product_number }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-info btn-block py-2 h-100" id="add_product_btn">
                                            <i class="fas fa-cart-plus mr-1"></i> Add to Basket
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Order Basket -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Order Basket</h4>
                                <button type="button" class="btn btn-sm btn-danger shadow-sm" id="clear_basket_btn" style="display: none;">
                                    <i class="fas fa-trash-alt mr-1"></i> Clear Basket
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md mb-0 shadow-sm" id="basket_table">
                                        <thead class="bg-whitesmoke text-uppercase small font-weight-bold">
                                            <tr>
                                                <th width="5%" class="text-center">Image</th>
                                                <th width="25%">Product & SKU</th>
                                                <th width="8%" class="text-center">Stock</th>
                                                <th width="12%">Unit</th>
                                                <th width="10%" class="text-center">Main Qty</th>
                                                <th width="30%">Variants (Color/Size) & Qtys</th>
                                                <th width="5%" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="basket_body">
                                            <tr id="empty_basket_row">
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <i class="fas fa-shopping-basket fa-3x mb-3 opacity-25"></i>
                                                    <p>Your basket is empty. Use the search filters above to add products.</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <!-- Section 4: Custom Fields -->
                        <div class="card mt-3">
                            <div class="card-header bg-whitesmoke d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">Custom Fields (Optional)</h4>
                                <div class="card-header-action">
                                    <button type="button" class="btn btn-sm btn-success" id="add-custom-field">
                                        <i class="fas fa-plus"></i> Add Field
                                    </button>
                                </div>
                            </div>
                            <div class="card-body" id="custom-fields-container">
                                <!-- Custom fields will appear here -->
                            </div>
                        </div>

                        <!-- Section 3: Order Basket (Footer Moved Below Custom Fields) -->
                            <div class="card-footer bg-whitesmoke mt-3 border rounded shadow-sm">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group mb-0">
                                            <label>General Notes for this entire order</label>
                                            <textarea name="description" class="form-control" rows="2" placeholder="Write any specific instructions for the vendor here..."></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-right d-flex flex-column justify-content-end">
                                         <button type="submit" class="btn btn-primary btn-lg btn-block shadow-sm py-3">
                                            <i class="fas fa-check-double mr-1"></i> Confirm & Place Order
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
@endsection

@push('scripts')
    <style>
        .variant-input-group { display: flex; align-items: center; gap: 10px; margin-bottom: 5px; background: #f9f9f9; padding: 5px 10px; border-radius: 4px; border: 1px solid #eee; }
        .variant-label { flex-grow: 1; font-size: 11px; font-weight: 600; color: #666; }
        .variant-qty-input { width: 70px !important; height: 26px !important; text-align: center; border-radius: 3px !important; }
        .basket-product-name { font-size: 14px; font-weight: 700; color: #333; margin-bottom: 0; }
        .basket-product-sku { font-size: 11px; color: #888; text-transform: uppercase; }
        #basket_table thead th { border-top: none; }

        /* Mobile Responsive Table Breakdown */
        /* @media (max-width: 767.98px) {
            #basket_table thead { display: none; }
            #basket_table, #basket_table tbody, #basket_table tr, #basket_table td { 
                display: block; 
                width: 100%; 
            }
            #basket_table tr.basket-row { 
                margin-bottom: 15px; 
                border: 1px solid #e4e6fc !important; 
                border-radius: 10px; 
                padding: 15px; 
                background: #fff; 
                box-shadow: 0 4px 6px rgba(0,0,0,0.04);
            }
            #basket_table td { 
                border: none !important; 
                padding: 8px 0 !important; 
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
            #basket_table td .variant-qty-input { width: 100px !important; }
            #basket_table td.text-center { justify-content: space-between; }
        } */
    </style>
    <script>
        const products = @json($products);
        const units = @json($units);
        const selectedIds = @json($selectedIds ?? []);
        let rowCount = 0;

        $(document).ready(function() {
            
            // --- Auto-select vendor from cart ---
            // Check if there are product IDs in the URL (from cart)
            const urlParams = new URLSearchParams(window.location.search);
            const productIds = urlParams.get('ids');
            
            if (productIds) {
                // Get vendor from cart
                $.ajax({
                    url: "{{ route('admin.cart.vendor') }}?cart_type=booking",
                    method: 'GET',
                    success: function(data) {
                        if (data.vendor_id) {
                            $('select[name="vendor_id"]').val(data.vendor_id).trigger('change');
                        }
                    }
                });
            }
            
            // --- Cascading Filter Logic ---
            $('#category_filter').on('change', function() {
                let categoryId = $(this).val();
                $('#sub_category_filter').html('<option value="">Select Sub Category</option>');
                $('#child_category_filter').html('<option value="">Select Child Category</option>');
                
                if (categoryId) {
                    $.ajax({
                        url: "{{ route('admin.bookings.get-subcategories') }}",
                        method: 'GET',
                        data: { id: categoryId },
                        success: function(data) {
                            let html = '<option value="">All Sub Categories</option>';
                            data.forEach(sub => html += `<option value="${sub.id}">${sub.name}</option>`);
                            $('#sub_category_filter').html(html);
                        }
                    });
                }
                filterProductDropdown();
            });

            $('#sub_category_filter').on('change', function() {
                let subCategoryId = $(this).val();
                $('#child_category_filter').html('<option value="">Select Child Category</option>');
                
                if (subCategoryId) {
                    $.ajax({
                        url: "{{ route('admin.bookings.get-childcategories') }}",
                        method: 'GET',
                        data: { id: subCategoryId },
                        success: function(data) {
                            let html = '<option value="">All Child Categories</option>';
                            data.forEach(child => html += `<option value="${child.id}">${child.name}</option>`);
                            $('#child_category_filter').html(html);
                        }
                    });
                }
                filterProductDropdown();
            });

            $('#child_category_filter').on('change', filterProductDropdown);

            function filterProductDropdown() {
                let cat = $('#category_filter').val();
                let sub = $('#sub_category_filter').val();
                let child = $('#child_category_filter').val();

                let html = '<option value="">-- Search Product --</option>';
                products.forEach(p => {
                    let match = true;
                    if(cat && p.category_id != cat) match = false;
                    if(sub && p.sub_category_id != sub) match = false;
                    if(child && p.child_category_id != child) match = false;

                    if(match) {
                        html += `<option value="${p.id}">${p.name} (${p.product_number})</option>`;
                    }
                });
                $('#product_selector').html(html).trigger('change');
            }

            // --- Basket Logic ---
            $('#add_product_btn').on('click', function() {
                let productId = $('#product_selector').val();
                if(!productId) {
                    toastr.warning('Please select a product first.');
                    return;
                }

                let product = products.find(p => p.id == productId);
                if(product) {
                    addProductRow(product);
                    $('#product_selector').val('').trigger('change');
                }
            });

            function addProductRow(product) {
                $('#empty_basket_row').hide();
                
                let imageHtml = product.thumb_image 
                    ? `<img src="{{ asset('storage') }}/${product.thumb_image}" class="rounded border shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">`
                    : `<div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted tiny" style="width: 50px; height: 50px;">NO IMG</div>`;

                let variantHtml = '<div class="row">';
                let hasVariants = false;
                
                if(product.variants && product.variants.length > 0) {
                    product.variants.forEach(v => {
                        let colorName = v.color ? v.color.name : '';
                        let sizeName = v.size ? v.size.name : '';
                        let name = (colorName + ' ' + sizeName).trim() || 'Default';
                        if(name) {
                            hasVariants = true;
                            let safeName = name.replace(/"/g, '&quot;');
                            variantHtml += `
                                <div class="col-12">
                                    <div class="variant-input-group">
                                        <span class="variant-label" title="${name}">${name}</span>
                                        <input type="number" class="form-control form-control-sm variant-qty" 
                                               name="items[${rowCount}][variant_quantities][${safeName}]" 
                                               data-row="${rowCount}" value="0" min="0">
                                    </div>
                                </div>`;
                        }
                    });
                }
                variantHtml += '</div>';

                if(!hasVariants) variantHtml = '<span class="text-muted italic small ml-2">No variants available for this item</span>';

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
                            <select class="form-control select2-basic" name="items[${rowCount}][unit_id]">
                                <option value="">Select Unit</option>
                                ${units.map(u => `<option value="${u.id}" ${product.unit_id == u.id ? 'selected' : ''}>${u.name}</option>`).join('')}
                            </select>
                        </td>
                        <td class="align-middle text-center" data-label="Main Qty">
                            <input type="number" class="form-control main-qty font-weight-bold" name="items[${rowCount}][qty]" 
                                   id="qty_${rowCount}" value="1" min="1" required style="text-align:center;">
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
            }

            $(document).on('click', '.remove-row', function() {
                let id = $(this).data('id');
                $(`#row_${id}`).remove();
                if($('.basket-row').length === 0) {
                    $('#empty_basket_row').show();
                }
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

            // --- Custom Fields Logic ---
            let fieldCount = 0;
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

            // --- Basket Logic: Auto-Load & Clear (Database Cart System) ---
            function loadBasket() {
                try {
                    let source = [];
                    
                    // First check URL selectedIds
                    if (selectedIds && selectedIds.length > 0) {
                        source = selectedIds;
                        console.log('Using URL IDs:', source);
                    } else {
                        // Fallback: fetch from database cart
                        $.ajax({
                            url: "{{ route('admin.cart.product-ids') }}?cart_type=booking",
                            method: 'GET',
                            async: false, // Make synchronous for this flow
                            success: function(data) {
                                source = data.ids;
                                console.log('Using Database Cart IDs:', source);
                            }
                        });
                    }

                    if (source.length > 0) {
                        let loadedCount = 0;
                        
                        source.forEach(id => {
                            // Ensure ID comparison works (string vs int)
                            let product = products.find(p => p.id == id);
                        
                            if (product) {
                                // Prevent duplicates
                                let alreadyAdded = false;
                                $('input[name^="items"][name$="[product_id]"]').each(function() {
                                    if($(this).val() == product.id) alreadyAdded = true;
                                });

                                if(!alreadyAdded) {
                                    console.log('Adding product:', product.name);
                                    addProductRow(product);
                                    loadedCount++;
                                }
                            } else {
                                console.warn('Product ID ' + id + ' not found in available products list.');
                            }
                        });

                        if(loadedCount > 0) {
                            toastr.success(`${loadedCount} items loaded from your basket.`);
                            $('#clear_basket_btn').show();
                        }
                    }
                } catch (e) {
                    console.error('Error loading basket:', e);
                }
            }

            // Clear Basket Button Logic
            $('#clear_basket_btn').on('click', function() {
                // Clear database cart
                $.ajax({
                    url: "{{ route('admin.cart.clear') }}",
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: { cart_type: 'booking' },
                    success: function() {
                        $('#basket_body').empty();
                        rowCount = 0;
                        $('#clear_basket_btn').fadeOut();
                        toastr.info('Basket cleared.');
                    }
                });
            });

            // Execute Load
            setTimeout(loadBasket, 500); // Small delay to ensure DOM is ready
            // --- End Basket Logic ---

            $('#booking_form').on('submit', function(e) {
                if($('.basket-row').length === 0) {
                    e.preventDefault();
                    toastr.error('Product basket must contain at least one item.', 'Basket Empty');
                }
            });
        });
    </script>
@endpush
