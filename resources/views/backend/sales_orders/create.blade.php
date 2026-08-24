@extends('backend.layouts.master')

@section('title', 'Create Sales Order (SO)')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header border-0 shadow-sm mb-4" style="background: #ffffff; border-radius: 16px; padding: 20px 24px;">
            <div class="d-flex align-items-center flex-wrap w-100">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                    <div class="mr-3 p-3 rounded-circle text-white shadow-sm" style="background: linear-gradient(135deg, #0a0e1a 0%, #1e293b 100%); border: 1px solid rgba(205, 160, 90, 0.3);">
                        <i class="fas fa-plus text-warning" style="font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 font-weight-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">Create Sales Order (SO)</h4>
                        <p class="text-muted mb-0 small">Create official sales order with real-time customer credit limit evaluation</p>
                    </div>
                </div>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <a href="{{ route('admin.sales-orders.index') }}" class="btn btn-outline-secondary px-4 py-2 font-weight-bold" style="border-radius: 10px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="section-body">
            <form action="{{ route('admin.sales-orders.store') }}" method="POST" id="salesOrderForm">
                @csrf
                <div class="row">
                    {{-- Left Form Column --}}
                    <div class="col-lg-8">
                        <div class="card card-primary border-0 shadow-sm mb-4" style="border-radius: 16px;">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-user-tag mr-2 text-primary"></i> Customer & Shipping Details</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Customer Account <span class="text-danger">*</span></label>
                                        <select name="user_id" id="customerSelect" class="form-control" required style="border-radius: 8px;">
                                            <option value="">-- Select Customer --</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" data-segment="{{ $customer->customer_segment }}" data-credit="{{ $customer->credit_limit }}">
                                                    {{ $customer->name }} ({{ $customer->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Shipping Method</label>
                                        <input type="text" name="shipping_method" class="form-control" placeholder="e.g. Standard Freight / Express" value="Standard Freight" style="border-radius: 8px;">
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold text-dark">Billing / Delivery Address</label>
                                    <textarea name="billing_address" class="form-control" rows="2" placeholder="Enter delivery address" style="border-radius: 8px;"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Line Items Table --}}
                        <div class="card card-primary border-0 shadow-sm mb-4" style="border-radius: 16px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-boxes mr-2 text-primary"></i> Order Line Items</h6>
                            </div>
                            <div class="card-body p-3 border-bottom bg-light">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold text-dark" style="font-size: 0.8rem;">Select Product to Add</label>
                                            <select class="form-control select2" id="product_selector">
                                                <option value="">-- Choose Product --</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="itemsTable">
                                        <thead class="bg-light" style="font-size: 0.8rem; text-transform: uppercase; color: #64748b;">
                                            <tr>
                                                <th style="width: 35%;" class="pl-4">Product Details <span class="text-danger">*</span></th>
                                                <th style="width: 10%;">Stock</th>
                                                <th style="width: 10%;">Quantity</th>
                                                <th style="width: 20%;">Unit Price (kr.)</th>
                                                <th style="width: 15%;" class="text-right pr-4">Subtotal</th>
                                                <th style="width: 10%;" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsTableBody">
                                            <!-- Dynamic Rows -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column (Financial Summary & Credit Widget) --}}
                    <div class="col-lg-4">
                        {{-- Credit Exposure Widget --}}
                        <div class="card card-warning border-0 shadow-sm mb-4" id="creditWidget" style="border-radius: 16px;">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-shield-alt mr-2 text-warning"></i> Customer Credit Exposure</h6>
                            </div>
                            <div class="card-body p-4">
                                <div id="creditWidgetContent" class="text-center text-muted">
                                    <i class="fas fa-info-circle mr-1"></i> Select a customer to evaluate real-time credit limit exposure.
                                </div>
                            </div>
                        </div>

                        {{-- Financial Calculation Box --}}
                        <div class="card card-primary border-0 shadow-sm mb-4" style="border-radius: 16px;">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-calculator mr-2 text-primary"></i> Financial Summary</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark">Applicable Tax</label>
                                    <select name="tax_id" id="taxSelect" class="form-control" style="border-radius: 8px;">
                                        <option value="" data-type="percent" data-value="0">No Tax (0%)</option>
                                        @foreach($taxes as $tax)
                                            <option value="{{ $tax->id }}" data-type="{{ $tax->type }}" data-value="{{ $tax->value }}">
                                                {{ $tax->name }} ({{ $tax->type === 'flat' ? 'kr. ' . $tax->value : $tax->value . '%' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark">Overall Discount (kr.)</label>
                                    <input type="number" step="0.01" min="0" name="discount_amount" id="discountInput" class="form-control" value="0.00" style="border-radius: 8px;">
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted font-weight-bold">Subtotal:</span>
                                    <span class="font-weight-bold text-dark" id="summarySubtotal">kr. 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted font-weight-bold">Tax:</span>
                                    <span class="font-weight-bold text-dark" id="summaryTax">kr. 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                    <span class="text-muted font-weight-bold">Discount:</span>
                                    <span class="font-weight-bold text-danger" id="summaryDiscount">- kr. 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-dark font-weight-bold">Order Total:</h6>
                                    <h4 class="mb-0 text-primary font-weight-bold" id="summaryGrandTotal">kr. 0.00</h4>
                                </div>

                                <button type="submit" class="btn btn-success btn-block mt-4 py-3 font-weight-bold shadow-sm" style="border-radius: 10px; font-size: 1.05rem;">
                                    <i class="fas fa-check-circle mr-2"></i> Save & Process Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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

    @push('scripts')
    <script>
        let rowIndex = 1;

        function calculateTotals() {
            let subtotal = 0;
            $('.item-row').each(function() {
                let qty = parseFloat($(this).find('.qty-input').val()) || 0;
                let price = parseFloat($(this).find('.price-input').val()) || 0;
                let lineTotal = qty * price;
                $(this).find('.line-subtotal').text('kr. ' + lineTotal.toFixed(2));
                subtotal += lineTotal;
            });

            $('#summarySubtotal').text('kr. ' + subtotal.toFixed(2));

            let selectedTax = $('#taxSelect option:selected');
            let taxType = selectedTax.data('type');
            let taxVal = parseFloat(selectedTax.data('value')) || 0;
            let taxAmount = taxType === 'percent' ? subtotal * (taxVal / 100) : taxVal;

            $('#summaryTax').text('kr. ' + taxAmount.toFixed(2));

            let discount = parseFloat($('#discountInput').val()) || 0;
            $('#summaryDiscount').text('- kr. ' + discount.toFixed(2));

            let grandTotal = Math.max(0, subtotal + taxAmount - discount);
            $('#summaryGrandTotal').text('kr. ' + grandTotal.toFixed(2));

            evaluateCreditExposure(grandTotal);
        }

        function evaluateCreditExposure(orderTotal) {
            let customerId = $('#customerSelect').val();
            if (!customerId) {
                $('#creditWidgetContent').html('<div class="text-muted"><i class="fas fa-info-circle mr-1"></i> Select a customer to evaluate real-time credit limit exposure.</div>');
                return;
            }

            $.ajax({
                url: "{{ route('admin.sales-orders.check-credit') }}",
                method: 'GET',
                data: {
                    user_id: customerId,
                    order_amount: orderTotal
                },
                success: function(res) {
                    let badge = res.is_exceeded ? '<span class="badge badge-danger px-3 py-1">CREDIT HOLD WARNING</span>' : '<span class="badge badge-success px-3 py-1">CREDIT APPROVED</span>';
                    let html = `
                        <div class="mb-3">${badge}</div>
                        <div class="d-flex justify-content-between mb-1"><small class="text-muted font-weight-bold">Credit Limit:</small> <small class="font-weight-bold">kr. ${parseFloat(res.credit_limit).toFixed(2)}</small></div>
                        <div class="d-flex justify-content-between mb-1"><small class="text-muted font-weight-bold">Unpaid Dues:</small> <small class="font-weight-bold text-danger">kr. ${parseFloat(res.current_dues).toFixed(2)}</small></div>
                        <div class="d-flex justify-content-between mb-1"><small class="text-muted font-weight-bold">New Order:</small> <small class="font-weight-bold text-primary">kr. ${parseFloat(res.new_order_total).toFixed(2)}</small></div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between"><small class="font-weight-bold">Total Exposure:</small> <small class="font-weight-bold ${res.is_exceeded ? 'text-danger' : 'text-success'}">kr. ${parseFloat(res.total_exposure).toFixed(2)}</small></div>
                        <p class="small text-muted mb-0 mt-2">${res.reason}</p>
                    `;
                    $('#creditWidgetContent').html(html);
                }
            });
        }

        $(document).ready(function() {
            $('#customerSelect').on('change', function() {
                calculateTotals();
            });

            // Auto-fetch variants when product is selected
            $('#product_selector').on('change', function() {
                let productId = $(this).val();
                if (!productId) return;
                let productName = $(this).find('option:selected').text();
                let productPrice = parseFloat($(this).find('option:selected').data('price')) || 0;

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
                                $('#modal_product_name').val(productName);
                                let tbody = '';
                                variants.forEach(v => {
                                    tbody += `<tr>
                                        <td>${v.name}</td>
                                        <td>${v.qty || 0}</td>
                                        <td><input type="number" class="form-control form-control-sm variant_qty_input" data-variant-id="${v.id}" data-variant-name="${v.name}" data-price="${v.price || productPrice}" data-stock="${v.qty || 0}" step="1" min="0"></td>
                                    </tr>`;
                                });
                                $('#modal_variants_body').html(tbody);
                                $('#bulkVariantModal').modal('show');
                            } else {
                                // Add single row without variant
                                appendItemRow(productId, productName, null, '', 1, productPrice, response.product.qty || 0);
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
                let productName = $('#modal_product_name').val();
                let added = false;
                
                $('.variant_qty_input').each(function() {
                    let qty = parseFloat($(this).val());
                    if (qty > 0) {
                        let variantId = $(this).data('variant-id');
                        let variantName = $(this).data('variant-name');
                        let price = $(this).data('price');
                        let stock = $(this).data('stock') || 0;
                        appendItemRow(productId, productName, variantId, variantName, qty, price, stock);
                        added = true;
                    }
                });

                if (added) {
                    $('#bulkVariantModal').modal('hide');
                } else {
                    toastr.warning('Please enter quantity for at least one variant.');
                }
            });

            function appendItemRow(productId, productName, variantId, variantName, qty, price, stock = 0) {
                let variantDisplay = variantName ? variantName : 'Standard';
                
                let newRow = `
                    <tr class="item-row">
                        <td class="pl-4">
                            <strong class="text-dark d-block" style="font-size: 0.9rem;">${productName}</strong>
                            <small class="badge badge-secondary mt-1">${variantDisplay}</small>
                            <input type="hidden" name="items[${rowIndex}][product_id]" value="${productId}" class="product-id-input">
                            ${variantId ? `<input type="hidden" name="items[${rowIndex}][variant_id]" value="${variantId}">` : ''}
                        </td>
                        <td>
                            <span class="badge badge-info px-2 py-1" style="font-size: 0.85rem;">${stock}</span>
                        </td>
                        <td>
                            <input type="number" step="1" min="1" name="items[${rowIndex}][qty]" class="form-control qty-input" value="${qty}" required style="border-radius: 8px;">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="items[${rowIndex}][unit_price]" class="form-control price-input" value="${parseFloat(price).toFixed(2)}" required style="border-radius: 8px;" data-default-price="${price}">
                        </td>
                        <td class="text-right pr-4 font-weight-bold text-dark line-subtotal">kr. 0.00</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-link text-danger remove-row-btn"><i class="fas fa-times"></i></button>
                        </td>
                    </tr>
                `;
                $('#itemsTableBody').append(newRow);
                let row = $('#itemsTableBody .item-row').last();
                rowIndex++;
                
                // Resolve customer specific price if customer is selected
                let customerId = $('#customerSelect').val();
                if (customerId) {
                    $.ajax({
                        url: "{{ route('admin.pricelists.resolve-price') }}",
                        method: 'GET',
                        data: { product_id: productId, customer_id: customerId },
                        success: function(res) {
                            row.find('.price-input').val(parseFloat(res.price).toFixed(2));
                            calculateTotals();
                        }
                    });
                }
                
                calculateTotals();
            }

            $(document).on('input', '.qty-input, .price-input, #discountInput', function() {
                calculateTotals();
            });

            $('#taxSelect').on('change', function() {
                calculateTotals();
            });

            $(document).on('click', '.remove-row-btn', function() {
                $(this).closest('.item-row').remove();
                calculateTotals();
            });
        });
    </script>
    @endpush
@endsection
