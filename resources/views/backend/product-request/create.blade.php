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
                                                </div>
                                            </div>
                                            <div class="card-body p-3 border-bottom">
                                                <div class="row align-items-end">
                                                    <div class="col-md-8">
                                                        <div class="form-group mb-0">
                                                            <label class="font-weight-bold text-dark" style="font-size: 0.8rem;">Select Product to Request</label>
                                                            <select class="form-control select2" id="product_selector">
                                                                <option value="">-- Choose Product --</option>
                                                                @foreach($products as $product)
                                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0" id="items-table">
                                                        <thead class="bg-whitesmoke text-uppercase small font-weight-bold">
                                                            <tr>
                                                                <th width="35%">Product Details</th>
                                                                 <th width="10%">Stock</th>
                                                                 <th width="12%" class="text-right">Buying Price</th>
                                                                 <th width="13%" class="text-center">Selling Price</th>
                                                                 <th width="10%" class="text-center">Qty</th>
                                                                 <th width="15%" class="text-right">Total Price</th>
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
    <!-- Bulk Variant Modal -->
    <div class="modal fade" id="bulkVariantModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="border-bottom: 2px solid #f0f0f0; padding: 1rem 1.5rem;">
                    <h5 class="modal-title" style="font-size: 1rem;">
                        <i class="fas fa-list mr-2" style="color: #2563eb;"></i> Select Variants
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <input type="hidden" id="modal_product_id">
                    <input type="hidden" id="modal_product_name">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" style="font-size: 0.8rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Variant Name</th>
                                    <th>Current Stock</th>
                                    <th width="150">Quantity to Add</th>
                                </tr>
                            </thead>
                            <tbody id="modal_variants_body">
                                <!-- Variants will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 2px solid #f0f0f0; padding: 1rem 1.5rem;">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" style="border-radius: 10px; min-height: 40px; font-size: 0.85rem;">Close</button>
                    <button type="button" class="btn btn-primary shadow-sm" id="btn_add_selected_variants" style="background: #2563eb; border: none; border-radius: 10px; min-height: 40px; font-size: 0.85rem;">
                        <i class="fas fa-check-circle mr-1"></i> Add Selected
                    </button>
                </div>
            </div>
        </div>
    </div>
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
                    let product = products.find(p => p.id == id);
                    if (product) appendItemRow(product, null, '', 1, product.outlet_price, product.price);
                });
            }

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
                                updateGlobalSummary();
                                toastr.info('All items and basket cleared');
                            }
                        });
                    }
                });
            });

            // Auto-fetch variants when product is selected
            $('#product_selector').on('change', function() {
                let productId = $(this).val();
                if (!productId) return;
                let product = products.find(p => p.id == productId);

                // Reset selector
                $(this).val('').trigger('change.select2');

                // Fetch variants
                $.ajax({
                    url: `/admin/products/${productId}/variants`,
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'success') {
                            let variants = response.variants;
                            if (variants.length > 0) {
                                // Open Modal
                                $('#modal_product_id').val(productId);
                                $('#modal_product_name').val(product.name);
                                let tbody = '';
                                variants.forEach(v => {
                                    tbody += `<tr>
                                        <td>${v.name}</td>
                                        <td>${v.qty || 0}</td>
                                        <td><input type="number" class="form-control form-control-sm variant_qty_input" data-variant-id="${v.id}" data-variant-name="${v.name}" data-price="${v.price || product.price}" data-outlet-price="${v.outlet_price || product.outlet_price}" data-stock="${v.qty || 0}" step="0.01" min="0"></td>
                                    </tr>`;
                                });
                                $('#modal_variants_body').html(tbody);
                                $('#bulkVariantModal').modal('show');
                            } else {
                                // Add single row without variant
                                appendItemRow(product, null, '', 1, product.outlet_price, product.price, response.product.qty || 0);
                            }
                        }
                    },
                    error: function() {
                        toastr.error('Failed to fetch product variants.');
                    }
                });
            });

            $('#btn_add_selected_variants').click(function() {
                let productId = $('#modal_product_id').val();
                let product = products.find(p => p.id == productId);
                let added = false;
                
                $('.variant_qty_input').each(function() {
                    let qty = parseFloat($(this).val());
                    if (qty > 0) {
                        let variantId = $(this).data('variant-id');
                        let variantName = $(this).data('variant-name');
                        let price = $(this).data('price');
                        let outletPrice = $(this).data('outlet-price');
                        let stock = $(this).data('stock') || 0;
                        appendItemRow(product, variantId, variantName, qty, outletPrice, price, stock);
                        added = true;
                    }
                });

                if (added) {
                    $('#bulkVariantModal').modal('hide');
                } else {
                    toastr.warning('Please enter quantity for at least one variant.');
                }
            });

            function appendItemRow(product, variantId, variantName, qty, outletPrice, sellPrice, stock = 0) {
                let variantDisplay = variantName ? variantName : 'Standard';
                let imageHtml = product.thumb_image 
                    ? `<img src="/storage/${product.thumb_image}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">`
                    : `<div class="bg-light rounded d-flex align-items-center justify-content-center text-muted small" style="width: 40px; height: 40px;"><i class="fas fa-box"></i></div>`;
                
                let buyPrice = parseFloat(outletPrice) || 0;
                let sPrice = parseFloat(sellPrice) || 0;

                let html = `
                    <tr class="product-row border-bottom" id="row-${rowCounter}">
                        <td class="p-3" style="vertical-align: middle;">
                            <div class="d-flex align-items-center">
                                <div class="mr-3">${imageHtml}</div>
                                <div>
                                    <strong class="text-dark d-block" style="font-size: 0.9rem;">${product.name}</strong>
                                    <small class="badge badge-secondary mt-1">${variantDisplay}</small>
                                    <input type="hidden" name="items[${rowCounter}][product_id]" value="${product.id}">
                                    ${variantId ? `<input type="hidden" name="items[${rowCounter}][variant_id]" value="${variantId}">` : ''}
                                </div>
                            </div>
                        </td>
                        <td class="text-center align-middle">
                            <span class="badge badge-info px-2 py-1" style="font-size: 0.85rem;">${stock}</span>
                        </td>
                        <td class="text-right align-middle font-weight-bold text-primary" style="font-size: 0.9rem;" data-val="${buyPrice}">
                            ${currencyIcon}${buyPrice.toFixed(2)}
                        </td>
                        <td class="text-center align-middle font-weight-bold text-success" style="font-size: 0.9rem;">
                            ${currencyIcon}${sPrice.toFixed(2)}
                        </td>
                        <td class="text-center align-middle">
                            <input type="number" class="form-control form-control-sm text-center variant-qty-input mx-auto" name="items[${rowCounter}][qty]" value="${qty}" min="1" style="width: 80px; font-weight: bold;">
                        </td>
                        <td class="text-right align-middle">
                            <div class="row-total-text font-weight-bold text-dark" style="font-size: 1rem;">${(buyPrice * qty).toFixed(2)}</div>
                        </td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-light btn-sm text-danger remove-row" data-id="${rowCounter}">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `;
                
                $('#items-container').append(html);
                rowCounter++;
                updateGlobalSummary();
            }

            $(document).on('click', '.remove-row', function() {
                const id = $(this).data('id');
                $(`#row-${id}`).fadeOut(200, function() {
                    $(this).remove();
                    updateGlobalSummary();
                });
            });

            $(document).on('input', '.variant-qty-input', function() {
                const val = parseFloat($(this).val()) || 0;
                if (val < 0) {
                    $(this).val(0);
                }
                const row = $(this).closest('.product-row');
                const buyPrice = parseFloat(row.find('td:eq(1)').data('val')) || 0;
                row.find('.row-total-text').text((val * buyPrice).toFixed(2));
                
                updateGlobalSummary();
            });

            function updateGlobalSummary() {
                let grandTotal = 0;
                let grandTotalQty = 0;
                let hasItems = false;

                $('.product-row').each(function() {
                    let qty = parseFloat($(this).find('.variant-qty-input').val()) || 0;
                    let total = parseFloat($(this).find('.row-total-text').text()) || 0;
                    
                    if (qty > 0) hasItems = true;
                    
                    grandTotalQty += qty;
                    grandTotal += total;
                });

                $('#total-items-count').text($('.product-row').length);
                $('#total-qty-display').text(grandTotalQty);
                $('#grand-total-display').text(grandTotal.toFixed(2));
                
                $('#submit-btn').prop('disabled', !hasItems);
            }
        });
    </script>
@endpush


