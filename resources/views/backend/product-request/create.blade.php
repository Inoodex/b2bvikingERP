@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Product Request</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Create Product Request</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.product-requests.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-9">
                                        <div class="card border shadow-sm">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                <h4 class="mb-0 text-primary"><i class="fas fa-shopping-basket mr-2"></i>Select Products</h4>
                                                <div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill mr-2" id="clear-all-items">
                                                        <i class="fas fa-trash-alt mr-1"></i> Clear All
                                                    </button>
                                                    <button type="button" class="btn btn-primary btn-sm rounded-pill" id="add-item">
                                                        <i class="fas fa-plus mr-1"></i> Add Product
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0" id="items-table">
                                                        <thead class="bg-whitesmoke text-uppercase small font-weight-bold">
                                                            <tr>
                                                                <th width="50%">Product Details</th>
                                                                 <th width="15%" class="text-right">Buying Price</th>
                                                                 <th width="10%" class="text-center">Selling Price</th>
                                                                 <th width="10%" class="text-center">Total Qty</th>
                                                                 <th width="20%" class="text-right">Local Total Price</th>
                                                                <th width="5%"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="items-container">
                                                            <!-- Dynamic Rows -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="col-md-3">
                                        <div class="card border shadow-sm sticky-top" style="top: 80px;">
                                            <div class="card-header bg-primary text-white">
                                                <h4 class="mb-0 text-white">Summary</h4>
                                            </div>
                                            <div class="card-body bg-light">
                                                @if(Auth::user()->hasRole('Admin'))
                                                    <div class="form-group mb-4">
                                                        <label class="font-weight-bold text-dark">Select Outlet / User</label>
                                                        <select name="user_id" class="form-control select2" required>
                                                            <option value="" disabled selected>Choose Outlet/User...</option>
                                                            @foreach($users as $u)
                                                                <option value="{{ $u->id }}">{{ $u->outlet_name ?? $u->name }} ({{ $u->name }})</option>
                                                            @endforeach
                                                        </select>
                                                        <small class="form-text text-muted">Select target Outlet/User. Stock will not be reduced on Request create.</small>
                                                    </div>
                                                    <hr>
                                                @endif
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted">Total Products:</span>
                                                    <span id="total-items-count" class="font-weight-bold">0</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                                    <span class="text-muted">Grand Total Qty:</span>
                                                    <span id="total-qty-display" class="font-weight-bold">0</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="mb-0 text-dark">Grand Total:</h5>
                                                     <h4 class="mb-0 text-primary">{{ $settings->base_currency_icon }}<span id="grand-total-display">0.00</span></h4>
                                                </div>
                                                <hr>
                                                <div class="form-group">
                                                    <label class="font-weight-bold text-dark">Required Days <span class="text-muted">(Optional)</span></label>
                                                    <input type="number" name="required_days" class="form-control" min="1" placeholder="e.g., 5">
                                                    <small class="form-text text-muted">How many days until you need these products?</small>
                                                </div>
                                                <div class="form-group">
                                                    <label class="font-weight-bold text-dark">Note <span class="text-muted">(Optional)</span></label>
                                                    <textarea name="note" class="form-control" rows="3" placeholder="Add any special instructions..."></textarea>
                                                </div>
                                                <hr>
                                                <div class="text-right">
                                                    <button type="submit" class="btn btn-success shadow-sm px-4" id="submit-btn" disabled>
                                                        <i class="fas fa-paper-plane mr-2"></i> Submit Request
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- <style>
        /* Mobile Responsive Table Breakdown */
        @media (max-width: 991.98px) {
            #items-table thead { display: none; }
            #items-table, #items-container, #items-table tr, #items-table td { 
                display: block; 
                width: 100%; 
            }
            #items-table tr.product-row { 
                margin-bottom: 25px; 
                border: 2px solid #e4e6fc !important; 
                border-radius: 12px; 
                padding: 15px; 
                background: #fff; 
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            }
            #items-table td { 
                border: none !important; 
                padding: 10px 0 !important; 
                display: flex; 
                align-items: center; 
                justify-content: space-between;
                text-align: right !important;
                min-height: 45px;
            }
            #items-table td:before { 
                content: attr(data-label); 
                font-weight: 800; 
                flex-basis: 40%; 
                text-align: left; 
                font-size: 11px; 
                color: #888;
                text-transform: uppercase;
                padding-right: 10px;
            }
            #items-table td:first-child { 
                display: block;
                border-bottom: 2px solid #f0f0f0 !important; 
                padding-bottom: 20px !important;
                margin-bottom: 15px;
                text-align: left !important;
            }
            #items-table td:first-child:before { display: none; }
            #items-table td .product-image-container { margin-bottom: 15px; }
            #items-table td .variant-price-breakdown { text-align: right; width: 100%; }
        }
    </style> --}}
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let rowCounter = 0;
            const currencyIcon = "{{ $settings->base_currency_icon }}";
            const products = @json($products);

            // Start with pre-selected rows or one empty row
            const selectedIds = @json($selectedIds ?? []);
            
            if (selectedIds && selectedIds.length > 0) {
                selectedIds.forEach(id => {
                    addProductRow(id);
                });
            } else {
                addProductRow();
            }

            $('#add-item').on('click', function() { addProductRow(); });

            $('#clear-all-items').on('click', function() {
                Swal.fire({
                    title: 'Clear all items?',
                    text: 'Are you sure you want to remove all products from this request?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, clear all!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#items-container').empty();
                        // Clear database cart
                        $.ajax({
                            url: "{{ route('admin.cart.clear') }}",
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: { cart_type: 'request' },
                            success: function() {
                                addProductRow();
                                updateGlobalSummary();
                                toastr.info('All items and basket cleared');
                            }
                        });
                    }
                });
            });

            function addProductRow(productId = null) {
                let options = '<option value=""></option>';
                products.forEach(p => {
                    options += `<option value="${p.id}">${p.name}</option>`;
                });

                let html = `
                    <tr id="row-${rowCounter}" class="product-row border-bottom">
                        <td class="p-4" style="vertical-align: top;" data-label="Product">
                            <div class="d-flex align-items-start mb-3">
                                <div class="product-image-container mr-3" style="width: 60px; height: 60px; border: 1px solid #e4e6fc; border-radius: 6px; overflow: hidden; background: #fbfbfb; flex-shrink: 0;">
                                    <img src="" class="product-thumb w-100 h-100" style="object-fit: cover; display: none;">
                                    <div class="no-image-placeholder h-100 d-flex align-items-center justify-content-center text-muted small">
                                        <i class="fas fa-image"></i>
                                    </div>
                                </div>
                                <div class="form-group mb-0 flex-grow-1">
                                    <select class="form-control select2 product-selector" data-placeholder="Choose Product">
                                        ${options}
                                    </select>
                                </div>
                            </div>
                            <div class="variant-entry-area mt-4" style="display:none;">
                                <div class="variant-list d-flex flex-wrap" style="gap: 15px;">
                                    <!-- Variants will be injected here -->
                                </div>
                            </div>
                        </td>
                        <td class="text-left align-middle px-4" style="vertical-align: top;" data-label="Buying Price">
                            <div class="variant-price-breakdown outlet-prices">
                                <!-- Variant outlet prices will be shown here -->
                            </div>
                        </td>
                        <td class="text-left align-middle px-4" style="vertical-align: top;" data-label="Selling Price">
                            <div class="variant-price-breakdown sell-prices">
                                <!-- Variant sell prices will be shown here -->
                            </div>
                        </td>
                        <td class="text-center align-middle px-4" data-label="Total Qty">
                            <div class="row-qty-text font-weight-bold h6 mb-0">0</div>
                        </td>
                        <td class="text-right align-middle px-4" data-label="Local Total Price">
                            <div class="row-total-text font-weight-bold h6 mb-0 text-primary">0.00</div>
                        </td>
                        <td class="text-center align-middle pr-4" data-label="Action">
                            <button type="button" class="btn btn-light btn-sm text-danger remove-row" data-id="${rowCounter}">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `;
                
                $('#items-container').append(html);
                const newRow = $(`#row-${rowCounter}`);
                const selector = newRow.find('.product-selector');
                
                selector.select2({ width: '100%', dropdownAutoWidth: true });
                
                if (productId) {
                    selector.val(productId);
                    // Triggering change after a tiny timeout to ensure select2 is completely ready
                    setTimeout(() => {
                        selector.trigger('change');
                    }, 50);
                }

                rowCounter++;
                updateGlobalSummary();
            }


            $(document).on('click', '.remove-row', function() {
                const id = $(this).data('id');
                $(`#row-${id}`).fadeOut(200, function() {
                    $(this).remove();
                    reindexFormInputs();
                    updateGlobalSummary();
                });
            });

            $(document).on('change', '.product-selector', function() {
                const productId = $(this).val();
                const row = $(this).closest('tr');
                const product = products.find(p => p.id == productId);
                const variantArea = row.find('.variant-entry-area');
                const variantList = row.find('.variant-list');
                const imageTag = row.find('.product-thumb');
                const noImagePlaceholder = row.find('.no-image-placeholder');
                const outletPricesDiv = row.find('.outlet-prices');
                const sellPricesDiv = row.find('.sell-prices');
                
                variantList.empty();
                outletPricesDiv.empty();
                sellPricesDiv.empty();
                
                if (product) {
                    // Update Image
                    if (product.thumb_image) {
                        imageTag.attr('src', `/storage/${product.thumb_image}`).show();
                        noImagePlaceholder.hide();
                    } else {
                        imageTag.hide();
                        noImagePlaceholder.show();
                    }

                    if (product.variants && product.variants.length > 0) {
                        product.variants.forEach((v, index) => {
                            const stocks = v.inventory_stocks || v.inventory_stock || [];
                            const stockObj = Array.isArray(stocks) ? stocks.find(s => s.outlet_id == 1) : null;
                            const stock = stockObj ? (stockObj.quantity || 0) : 0;
                            
                            // Get variant prices with fallback to product prices
                            const variantOutletPrice = parseFloat((v.outlet_price && v.outlet_price > 0) ? v.outlet_price : product.outlet_price) || 0;
                            const variantSellPrice = parseFloat((v.price && v.price > 0) ? v.price : product.price) || 0;
                            
                            // Add to price breakdown columns
                            outletPricesDiv.append(`
                                <div class="mb-1">
                                    <small class="badge badge-secondary mr-1" style="font-size: 9px;">${v.name}</small>
                                    <span class="font-weight-bold text-primary">${variantOutletPrice.toFixed(2)}</span>
                                </div>
                            `);
                            
                            sellPricesDiv.append(`
                                <div class="mb-1">
                                    <small class="badge badge-secondary mr-1" style="font-size: 9px;">${v.name}</small>
                                    <span class="font-weight-bold text-success">${variantSellPrice.toFixed(2)}</span>
                                </div>
                            `);
                            
                            variantList.append(`
                                <div class="variant-item bg-white p-2 border rounded text-center shadow-sm" style="min-width: 120px;">
                                    <div class="small font-weight-bold text-dark mb-1">${v.name}</div>
                                    <div class="text-muted small mb-1">Stock: ${stock}</div>
                                    <div class="mb-1">
                                        <small class="text-primary d-block" style="font-size: 10px;">Buy: ${variantOutletPrice.toFixed(2)}</small>
                                        <small class="text-success d-block" style="font-size: 10px;">Sell: ${variantSellPrice.toFixed(2)}</small>
                                    </div>
                                    <input type="number" class="form-control form-control-sm variant-qty-input text-center mx-auto" 
                                           style="width: 70px; height: 32px;"
                                           data-product-id="${product.id}" 
                                           data-variant-id="${v.id}" 
                                           data-variant-outlet-price="${variantOutletPrice}"
                                           data-variant-sell-price="${variantSellPrice}"
                                           data-max="${stock}" 
                                           min="0" max="${stock}" value="0">
                                </div>
                            `);
                        });
                        
                        variantArea.show();
                    } else {
                        const stocks = product.inventory_stocks || product.inventory_stock || [];
                        const stockObj = Array.isArray(stocks) ? stocks.find(s => s.outlet_id == 1) : null;
                        const stock = stockObj ? (stockObj.quantity || 0) : 0;
                        
                        const productOutletPrice = parseFloat(product.outlet_price) || 0;
                        const productSellPrice = parseFloat(product.price) || 0;
                        
                        // Add to price breakdown columns
                        outletPricesDiv.append(`
                            <div class="mb-1">
                                <small class="badge badge-secondary mr-1" style="font-size: 9px;">Standard</small>
                                <span class="font-weight-bold text-primary">${productOutletPrice.toFixed(2)}</span>
                            </div>
                        `);
                        
                        sellPricesDiv.append(`
                            <div class="mb-1">
                                <small class="badge badge-secondary mr-1" style="font-size: 9px;">Standard</small>
                                <span class="font-weight-bold text-success">${productSellPrice.toFixed(2)}</span>
                            </div>
                        `);

                        variantList.append(`
                            <div class="variant-item bg-white p-2 border rounded text-center shadow-sm" style="min-width: 120px;">
                                <div class="small font-weight-bold text-dark mb-1">Standard</div>
                                <div class="text-muted small mb-1">Stock: ${stock}</div>
                                <div class="mb-1">
                                    <small class="text-primary d-block" style="font-size: 10px;">Buy: ${productOutletPrice.toFixed(2)}</small>
                                    <small class="text-success d-block" style="font-size: 10px;">Sell: ${productSellPrice.toFixed(2)}</small>
                                </div>
                                <input type="number" class="form-control form-control-sm variant-qty-input text-center mx-auto" 
                                       style="width: 70px; height: 32px;"
                                       data-product-id="${product.id}" 
                                       data-variant-id="" 
                                       data-variant-outlet-price="${productOutletPrice}"
                                       data-variant-sell-price="${productSellPrice}"
                                       data-max="${stock}" 
                                       min="0" max="${stock}" value="0">
                            </div>
                        `);
                        variantArea.show();
                    }
                } else {
                    outletPricesDiv.empty();
                    sellPricesDiv.empty();
                    variantArea.hide();
                }
                calculateRowTotals(row);
            });

            $(document).on('input', '.variant-qty-input', function() {
                const val = parseInt($(this).val()) || 0;
                const max = parseInt($(this).data('max')) || 0;
                
                if (val > max) {
                    $(this).addClass('border-danger text-danger');
                } else {
                    $(this).removeClass('border-danger text-danger');
                }
                
                const row = $(this).closest('.product-row');
                calculateRowTotals(row);
            });

            function calculateRowTotals(row) {
                let totalQty = 0;
                let totalAmount = 0;
                
                // Calculate using variant-specific prices
                row.find('.variant-qty-input').each(function() {
                    const qty = parseInt($(this).val()) || 0;
                    const variantOutletPrice = parseFloat($(this).data('variant-outlet-price')) || 0;
                    
                    totalQty += qty;
                    totalAmount += (qty * variantOutletPrice);
                });
                
                row.find('.row-qty-text').text(totalQty);
                row.find('.row-total-text').text(totalAmount.toFixed(2));
                
                updateGlobalSummary();
            }

            function updateGlobalSummary() {
                let grandTotal = 0;
                let grandTotalQty = 0;
                let hasInvalid = false;
                let hasItems = false;

                $('.product-row').each(function() {
                    let qty = parseInt($(this).find('.row-qty-text').text()) || 0;
                    let total = parseFloat($(this).find('.row-total-text').text()) || 0;
                    
                    if (qty > 0) hasItems = true;
                    
                    $(this).find('.variant-qty-input').each(function() {
                        if (parseInt($(this).val()) > parseInt($(this).data('max'))) hasInvalid = true;
                    });

                    grandTotalQty += qty;
                    grandTotal += total;
                });

                $('#total-items-count').text($('.product-row').length);
                $('#total-qty-display').text(grandTotalQty);
                $('#grand-total-display').text(grandTotal.toFixed(2));
                
                $('#submit-btn').prop('disabled', hasInvalid || !hasItems);
                
                // Regenerate hidden inputs for submission
                reindexFormInputs();
            }

            function reindexFormInputs() {
                // Remove all previous hidden inputs
                $('#hidden-inputs-container').remove();
                
                const container = $('<div id="hidden-inputs-container"></div>');
                let index = 0;
                
                $('.variant-qty-input').each(function() {
                    const qty = parseInt($(this).val()) || 0;
                    if (qty > 0) {
                        const pid = $(this).data('product-id');
                        const vid = $(this).data('variant-id');
                        
                        container.append(`<input type="hidden" name="items[${index}][product_id]" value="${pid}">`);
                        if (vid) container.append(`<input type="hidden" name="items[${index}][variant_id]" value="${vid}">`);
                        container.append(`<input type="hidden" name="items[${index}][qty]" value="${qty}">`);
                        index++;
                    }
                });
                
                $('form').append(container);
            }
        });
    </script>
@endpush


