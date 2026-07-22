@extends('backend.layouts.master')

@section('content')
    @php
        $isOrderSource = $isOrderSource ?? false;
        $orderOutletShop = $selectedOrder
            ? ($selectedOrder->billing_outlet_name ?: ($selectedOrder->user->outlet_name ?? 'N/A'))
            : null;
    @endphp
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ $orderId ? route('admin.orders.show', $orderId) : ($requestId ? route('admin.product-requests.show', $requestId) : route('admin.issues.index')) }}"
                   class="btn btn-icon">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            <h1>Create Stock Issue</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.issues.index') }}">Issues</a></div>
                @if($orderId)
                    <div class="breadcrumb-item"><a href="{{ route('admin.orders.show', $orderId) }}">Order</a></div>
                @elseif($requestId)
                    <div class="breadcrumb-item"><a href="{{ route('admin.product-requests.show', $requestId) }}">Request</a></div>
                @endif
                <div class="breadcrumb-item">Create</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <form action="{{ route('admin.issues.store') }}" method="POST" id="issue_form">
                        @csrf
                        <input type="hidden" name="product_request_id" id="product_request_id_hidden">
                        <input type="hidden" name="order_id" id="order_id_hidden">
                        
                        {{-- Hidden container for actual form inputs --}}
                        <div id="hidden-inputs-container"></div>

                        <div class="row">
                            <div class="col-md-9">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-bottom-0 pb-0">
                                        <h4 class="text-primary mb-0"><i class="fas fa-box-open mr-2"></i>Issue Items</h4>
                                        <div class="row mt-3" @if($isOrderSource) style="display: none;" @endif>
                                            <div class="col-12 col-md-6 col-lg-3 mb-2">
                                                <select class="form-control select2" name="outlet_id" id="outlet_select" required>
                                                    <option value="" disabled selected>Select Outlet...</option>
                                                    @foreach($outletUsers as $outlet)
                                                        <option value="{{ $outlet->id }}">{{ $outlet->name }} ({{ $outlet->outlet_name ?? 'N/A' }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-3 mb-2">
                                                <select class="form-control select2" id="import_request_select" data-placeholder="Import from Request...">
                                                    <option value=""></option>
                                                    @foreach($productRequests as $pr)
                                                        <option value="{{ $pr->id }}">#{{ $pr->request_no }} - {{ $pr->user->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-4 mb-2">
                                                <select class="form-control select2" id="import_order_select" data-placeholder="Import from Outlet/Shop Order...">
                                                    <option value=""></option>
                                                    @foreach($frontendOrders as $fo)
                                                        <option value="{{ $fo->id }}">#{{ $fo->order_no }} - {{ $fo->billing_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-2 mb-2 d-flex">
                                                <button type="button" class="btn btn-primary btn-block" id="add_row_btn">
                                                    <i class="fas fa-plus"></i> Add Item
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table" id="issue_table">
                                                <thead>
                                                    <tr class="text-uppercase small text-muted font-weight-bold">
                                                        <th width="45%">Product Details</th>
                                                        <th width="15%" class="text-right">Unit Price</th>
                                                        <th width="15%" class="text-center">Total Qty</th>
                                                        <th width="15%" class="text-right">Subtotal</th>
                                                        <th width="10%" class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="issue_items_body">
                                                    <!-- Dynamic Rows -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="empty_state" class="text-center py-5 text-muted">
                                            <i class="fas fa-layer-group fa-3x mb-3 opacity-2"></i>
                                            <p>No items added yet. Add manually or import from request/order.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-4">
                                    <label class="font-weight-bold text-muted text-uppercase small">Note / Reference</label>
                                    <textarea name="note" class="form-control" rows="3" placeholder="Enter reason for issue or reference number..."></textarea>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                                    <div class="card-header bg-primary text-white">
                                        <h4 class="mb-0 text-white font-weight-600" style="font-size: 1.1rem;">Issue Summary</h4>
                                    </div>
                                    <div class="card-body bg-light">
                                        <div class="d-flex justify-content-between mb-3 px-1">
                                            <span class="text-muted">Issue Date:</span>
                                            <span class="font-weight-bold text-dark">{{ date('d M, Y') }}</span>
                                        </div>
                                        @if($isOrderSource && $selectedOrder)
                                            <div class="d-flex justify-content-between mb-3 border-top pt-3 px-1">
                                                <span class="text-muted text-uppercase small" style="font-size: 11px; letter-spacing: 0.5px;">Outlet/Shop:</span>
                                                <span class="font-weight-bold text-dark text-right">{{ $orderOutletShop }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-3 px-1">
                                                <span class="text-muted text-uppercase small" style="font-size: 11px; letter-spacing: 0.5px;">Order Number:</span>
                                                <span class="font-weight-bold text-dark">#{{ $selectedOrder->order_no }}</span>
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-between mb-3 border-top pt-3 px-1">
                                            <span class="text-muted text-uppercase small" style="font-size: 11px; letter-spacing: 0.5px;">Product Types:</span>
                                            <span id="summary_total_items" class="font-weight-bold">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-4 px-1">
                                            <span class="text-muted text-uppercase small" style="font-size: 11px; letter-spacing: 0.5px;">Total Quantity:</span>
                                            <span id="summary_total_qty" class="h5 mb-0 font-weight-bold text-primary">0</span>
                                        </div>
                                        <div class="text-right">
                                            <button type="submit" class="btn btn-success btn-lg px-4 shadow-sm py-2 font-weight-bold" id="confirm_btn" disabled>
                                                <i class="fas fa-check-circle mr-2"></i> Confirm Issue
                                            </button>
                                        </div>
                                        <p class="text-center text-muted small mt-3 mb-0">Confirming will deduct stock and generate a ledger entry.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    {{-- <style>
        /* Mobile Responsive Table Breakdown */
        @media (max-width: 767.98px) {
            #issue_table thead { display: none; }
            #issue_table, #issue_items_body, #issue_table tr, #issue_table td { 
                display: block; 
                width: 100%; 
            }
            #issue_table tr.issue-row { 
                margin-bottom: 20px; 
                border: 1px solid #e4e6fc !important; 
                border-radius: 12px; 
                padding: 15px; 
                background: #fff; 
                box-shadow: 0 4px 6px rgba(0,0,0,0.04);
            }
            #issue_table td { 
                border: none !important; 
                padding: 8px 0 !important; 
                display: flex; 
                align-items: center; 
                justify-content: space-between;
                text-align: right !important;
            }
            #issue_table td:before { 
                content: attr(data-label); 
                font-weight: 800; 
                flex-basis: 40%; 
                text-align: left; 
                font-size: 11px; 
                color: #888;
                text-transform: uppercase;
            }
            #issue_table td:first-child { 
                display: block;
                border-bottom: 1px dashed #eee !important; 
                padding-bottom: 15px !important;
                margin-bottom: 10px;
                text-align: left !important;
            }
            #issue_table td:first-child:before { display: none; }
            #issue_table td .product-image-container { margin-bottom: 10px; }
            #issue_table td .variant-entry-area { margin-top: 15px; }
        }
    </style> --}}
@endsection

@push('scripts')
    <script>
        const products = @json($products);
        const requestIdParam = @json($requestId ?? null);
        const orderIdParam = @json($orderId ?? null);
        let rowCount = 0;

        $(document).ready(function() {
            $('.select2').select2({ width: '100%', dropdownAutoWidth: true });

            const importSourceItems = (data, sourceType, sourceId) => {
                const items = data.items || [];
                Swal.close();
                $('#issue_items_body').empty();
                rowCount = 0;

                if (sourceType === 'order') {
                    $('#order_id_hidden').val(sourceId || '');
                    $('#product_request_id_hidden').val('');
                    if ($('#import_request_select').length) {
                        $('#import_request_select').val(null).trigger('change.select2');
                    }
                } else {
                    $('#product_request_id_hidden').val(sourceId || '');
                    $('#order_id_hidden').val('');
                    if ($('#import_order_select').length) {
                        $('#import_order_select').val(null).trigger('change.select2');
                    }
                }

                if (data.user_id) {
                    $('#outlet_select').val(data.user_id).trigger('change');
                }

                const grouped = {};
                items.forEach(item => {
                    if (!grouped[item.product_id]) grouped[item.product_id] = [];
                    grouped[item.product_id].push(item);
                });

                Object.keys(grouped).forEach(pid => {
                    addRow(grouped[pid]);
                });
            };

            const fetchSourceItems = (payload, title, sourceType, sourceId) => {
                Swal.fire({
                    title: title,
                    text: 'Bringing items into the issue form.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: "{{ route('admin.issues.get-request-items') }}",
                    method: "GET",
                    data: payload,
                    success: function(data) { importSourceItems(data, sourceType, sourceId); },
                    error: function() {
                        Swal.fire('Error', 'Failed to fetch request/order items.', 'error');
                    }
                });
            };

            $('#import_request_select').on('change', function() {
                const requestId = $(this).val();
                if (!requestId) return;
                fetchSourceItems({ request_id: requestId }, 'Importing Request...', 'request', requestId);
            });

            $('#import_order_select').on('change', function() {
                const orderId = $(this).val();
                if (!orderId) return;
                fetchSourceItems({ order_id: orderId }, 'Importing Order...', 'order', orderId);
            });

            // Initialize logic: order_id takes priority, then request_id, else empty row
            if (orderIdParam) {
                if ($('#import_order_select').length) {
                    $('#import_order_select').val(orderIdParam).trigger('change.select2');
                }
                fetchSourceItems({ order_id: orderIdParam }, 'Importing Order...', 'order', orderIdParam);
            } else if (requestIdParam) {
                if ($('#import_request_select').length) {
                    $('#import_request_select').val(requestIdParam).trigger('change.select2');
                }
                fetchSourceItems({ request_id: requestIdParam }, 'Importing Request...', 'request', requestIdParam);
            } else {
                addRow();
            }

            $('#add_row_btn').on('click', function() { addRow(); });
            
            $(document).on('click', '.remove_row', function() {
                const id = $(this).data('id');
                $(`#row-${id}`).fadeOut(200, function() {
                    $(this).remove();
                    updateGlobalSummary();
                });
            });

            $(document).on('change', '.product_selector', function() {
                const productId = $(this).val();
                const row = $(this).closest('tr');
                const product = products.find(p => p.id == productId);
                const variantArea = row.find('.variant-entry-area');
                const variantList = row.find('.variant-list');
                const imageTag = row.find('.product-thumb');
                const noImagePlaceholder = row.find('.no-image-placeholder');
                const priceDisplay = row.find('.unit-price-display');
                
                variantList.empty();
                priceDisplay.empty();
                
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
                        product.variants.forEach(v => {
                            const stock = v.inventory_stocks.find(s => s.outlet_id == 1)?.quantity || 0;
                            const colorName = v.color ? v.color.name : '';
                            const sizeName = v.size ? v.size.name : '';
                            const vName = v.name || `${colorName} ${sizeName}`.trim() || 'No Name';
                            
                            // Pricing hierarchy like the request page
                            const vPrice = parseFloat((v.outlet_price && v.outlet_price > 0) ? v.outlet_price : product.outlet_price) || 0;

                            priceDisplay.append(`
                                <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom border-light">
                                    <span class="text-muted small" style="font-size: 10px;">${vName}:</span>
                                    <span class="font-weight-bold text-primary small">${vPrice.toFixed(2)}</span>
                                </div>
                            `);

                            variantList.append(`
                                <div class="variant-item bg-white p-2 border rounded text-center shadow-sm" style="min-width: 100px;">
                                    <div class="small font-weight-bold text-dark mb-1">${vName}</div>
                                    <div class="text-muted small mb-1">Stock: <span class="v-stock">${stock}</span></div>
                                    <div class="text-primary small mb-2" style="font-size: 10px;">Price: ${vPrice.toFixed(2)}</div>
                                    <input type="number" class="form-control form-control-sm variant-qty-input text-center mx-auto" 
                                           style="width: 70px; height: 32px;"
                                           data-product-id="${product.id}" 
                                           data-variant-id="${v.id}" 
                                           data-variant-price="${vPrice}"
                                           data-max="${stock}" 
                                           min="0" max="${stock}" value="0">
                                </div>
                            `);
                        });
                        variantArea.fadeIn();
                    } else {
                        const stock = product.inventory_stocks.find(s => s.outlet_id == 1)?.quantity || 0;
                        const pPrice = parseFloat(product.outlet_price) || 0;

                        priceDisplay.html(`
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small" style="font-size: 10px;">Price:</span>
                                <span class="font-weight-bold text-primary">${pPrice.toFixed(2)}</span>
                            </div>
                        `);

                        variantList.append(`
                            <div class="variant-item bg-white p-2 border rounded text-center shadow-sm" style="min-width: 120px;">
                                <div class="small font-weight-bold text-dark mb-1">Standard</div>
                                <div class="text-muted small mb-1">Stock: <span class="v-stock">${stock}</span></div>
                                <div class="text-primary small mb-2" style="font-size: 10px;">Price: ${pPrice.toFixed(2)}</div>
                                <input type="number" class="form-control form-control-sm variant-qty-input text-center mx-auto" 
                                       style="width: 70px; height: 32px;"
                                       data-product-id="${product.id}" 
                                       data-variant-id="" 
                                       data-variant-price="${pPrice}"
                                       data-max="${stock}" 
                                       min="0" max="${stock}" value="0">
                            </div>
                        `);
                        variantArea.fadeIn();
                    }
                } else {
                    variantArea.fadeOut();
                    imageTag.hide();
                    noImagePlaceholder.show();
                    priceDisplay.text('0.00');
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
                
                const row = $(this).closest('.issue-row');
                calculateRowTotals(row);
            });

            $('#issue_form').on('submit', function() {
                reindexFormInputs();
                return true;
            });
        });

        function addRow(preDataItems = null) {
            $('#empty_state').hide();
            
            let options = '<option value=""></option>';
            products.forEach(p => {
                const selected = preDataItems && preDataItems[0].product_id == p.id ? 'selected' : '';
                options += `<option value="${p.id}" ${selected}>${p.name}</option>`;
            });

            let html = `
                <tr id="row-${rowCount}" class="issue-row border-bottom">
                    <td class="p-4" style="vertical-align: top;" data-label="Product">
                        <div class="d-flex align-items-start mb-3">
                            <div class="product-image-container mr-3" style="width: 60px; height: 60px; border: 1px solid #e4e6fc; border-radius: 6px; overflow: hidden; background: #fbfbfb; flex-shrink: 0;">
                                <img src="" class="product-thumb w-100 h-100" style="object-fit: cover; display: none;">
                                <div class="no-image-placeholder h-100 d-flex align-items-center justify-content-center text-muted small">
                                    <i class="fas fa-image"></i>
                                </div>
                            </div>
                            <div class="form-group mb-0 flex-grow-1">
                                <select class="form-control select2 product_selector" data-placeholder="Choose Product">
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
                    <td class="text-right align-middle px-4" style="vertical-align: top;" data-label="Price">
                        <div class="unit-price-display font-weight-bold text-dark">
                            <!-- Price breakdown will be shown here -->
                        </div>
                    </td>
                    <td class="text-center align-middle px-4" data-label="Total Qty">
                        <div class="row-qty-text font-weight-bold h6 mb-0">0</div>
                    </td>
                    <td class="text-right align-middle px-4" data-label="Subtotal">
                        <div class="row-total-text font-weight-bold h6 mb-0 text-primary">0.00</div>
                    </td>
                    <td class="text-center align-middle pr-4" data-label="Action">
                        <button type="button" class="btn btn-light btn-sm text-danger remove_row" data-id="${rowCount}">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#issue_items_body').append(html);
            const newRow = $(`#row-${rowCount}`);
            newRow.find('.select2').select2({ width: '100%', dropdownAutoWidth: true });
            
            // If we have pre-data (from import), set the product select and populate variant inputs
            if (preDataItems) {
                const product = products.find(p => p.id == preDataItems[0].product_id);
                if (product) {
                    // Ensure select2 value is set and trigger change so variant inputs are rendered
                    const $select = newRow.find('.product_selector');
                    $select.val(product.id).trigger('change');

                    // Give the change handler a moment to render variant inputs, then fill quantities
                    setTimeout(() => {
                        preDataItems.forEach(item => {
                            const vid = item.variant_id || '';
                            const vInput = newRow.find(`.variant-qty-input[data-variant-id="${vid}"]`);
                            if (vInput.length) {
                                vInput.data('variant-price', item.unit_price);
                                vInput.val(item.requested_qty).trigger('input');
                            }
                        });
                    }, 50);
                }
            }

            rowCount++;
            updateGlobalSummary();
        }

        function calculateRowTotals(row) {
            let totalQty = 0;
            let totalAmount = 0;

            row.find('.variant-qty-input').each(function() {
                const qty = parseInt($(this).val()) || 0;
                const price = parseFloat($(this).data('variant-price')) || 0;
                totalQty += qty;
                totalAmount += (qty * price);
            });
            
            row.find('.row-qty-text').text(totalQty);
            row.find('.row-total-text').text(totalAmount.toFixed(2));
            
            updateGlobalSummary();
        }

        function updateGlobalSummary() {
            let grandTotalQty = 0;
            let productTypes = 0;
            let hasInvalid = false;
            let hasPositiveQty = false;

            $('.issue-row').each(function() {
                const qty = parseInt($(this).find('.row-qty-text').text()) || 0;
                if (qty > 0) {
                    grandTotalQty += qty;
                    productTypes++;
                    hasPositiveQty = true;
                }

                $(this).find('.variant-qty-input').each(function() {
                    const val = parseInt($(this).val()) || 0;
                    const max = parseInt($(this).data('max')) || 0;
                    if (val > max) hasInvalid = true;
                });
            });

            $('#summary_total_items').text(productTypes);
            $('#summary_total_qty').text(grandTotalQty);
            
            $('#confirm_btn').prop('disabled', hasInvalid || !hasPositiveQty);
            
            if ($('.issue-row').length === 0) $('#empty_state').show();
            else $('#empty_state').hide();
        }

        function reindexFormInputs() {
            $('#hidden-inputs-container').empty();
            let index = 0;
            
            $('.variant-qty-input').each(function() {
                const qty = parseInt($(this).val()) || 0;
                if (qty > 0) {
                    const pid = $(this).data('product-id');
                    const vid = $(this).data('variant-id');
                    
                    $('#hidden-inputs-container').append(`
                        <input type="hidden" name="items[${index}][product_id]" value="${pid}">
                        <input type="hidden" name="items[${index}][variant_id]" value="${vid || ''}">
                        <input type="hidden" name="items[${index}][quantity]" value="${qty}">
                    `);
                    index++;
                }
            });
        }
    </script>
@endpush
