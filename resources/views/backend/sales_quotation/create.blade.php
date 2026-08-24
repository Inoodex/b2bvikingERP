@extends('backend.layouts.master')

@section('title', 'Create Sales Quotation')

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
                        <h4 class="mb-1 font-weight-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">Create New Sales Quotation</h4>
                        <p class="text-muted mb-0 small">Issue a formal B2B pricing quote for customer approval</p>
                    </div>
                </div>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <a href="{{ route('admin.sales-quotations.index') }}" class="btn btn-outline-secondary px-4 py-2 font-weight-bold" style="border-radius: 10px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="section-body">
            <form action="{{ route('admin.sales-quotations.store') }}" method="POST" id="quotationForm">
                @csrf
                <div class="row">
                    {{-- Main Info --}}
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; background: #ffffff;">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-info-circle mr-2 text-primary"></i> Customer & Quotation Details</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Quotation No (Auto-Generated)</label>
                                        <input type="text" class="form-control font-weight-bold text-primary bg-light" value="{{ $nextQuotationNo }}" readonly style="border-radius: 8px;">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Customer <span class="text-danger">*</span></label>
                                        <select name="customer_id" class="form-control select2" required style="border-radius: 8px;">
                                            <option value="">-- Select Customer --</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Currency</label>
                                        <select name="currency_id" id="currencySelect" class="form-control" style="border-radius: 8px;">
                                            <option value="">Base Currency (DKK kr.)</option>
                                            @foreach($currencies as $curr)
                                                <option value="{{ $curr->id }}" data-rate="{{ $curr->exchange_rate }}" data-symbol="{{ $curr->symbol }}">
                                                    {{ $curr->code }} ({{ $curr->symbol }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Exchange Rate (to DKK Base)</label>
                                        <input type="number" step="0.000001" name="exchange_rate" id="exchangeRateInput" class="form-control" value="1.000000" style="border-radius: 8px;">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Valid Until <span class="text-danger">*</span></label>
                                        <input type="date" name="valid_until" class="form-control" value="{{ date('Y-m-d', strtotime('+15 days')) }}" required style="border-radius: 8px;">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Tax / VAT Rule</label>
                                        <select name="tax_id" id="taxSelect" class="form-control" style="border-radius: 8px;">
                                            <option value="" data-type="none" data-value="0">No Tax (0%)</option>
                                            @foreach($taxes as $tax)
                                                <option value="{{ $tax->id }}" data-type="{{ $tax->type }}" data-value="{{ $tax->value }}">
                                                    {{ $tax->name }} ({{ $tax->type === 'percent' ? $tax->value . '%' : '$' . $tax->value }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Incoterms (Commercial Delivery Terms)</label>
                                        <select name="incoterm" class="form-control" style="border-radius: 8px;">
                                            <option value="EXW">EXW - Ex Works</option>
                                            <option value="FOB">FOB - Free on Board</option>
                                            <option value="CIF">CIF - Cost, Insurance & Freight</option>
                                            <option value="DDP">DDP - Delivered Duty Paid</option>
                                            <option value="CFR">CFR - Cost and Freight</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Item Grid --}}
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; background: #ffffff;">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-boxes mr-2 text-primary"></i> Quotation Items Grid</h6>
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
                                    <table class="table align-middle mb-0" id="itemsTable">
                                        <thead class="bg-light" style="font-size: 0.8rem; text-transform: uppercase; color: #64748b;">
                                            <tr>
                                                <th style="width: 35%;" class="pl-4">Product Details</th>
                                                <th style="width: 10%;">Stock</th>
                                                <th style="width: 15%;">Qty</th>
                                                <th style="width: 20%;">Unit Price</th>
                                                <th style="width: 15%;" class="text-right pr-4">Subtotal</th>
                                                <th style="width: 5%;"></th>
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

                    {{-- Summary Side Panel --}}
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; background: #ffffff;">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-calculator mr-2 text-primary"></i> Calculation Summary</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted font-weight-semibold">Items Subtotal:</span>
                                    <span class="font-weight-bold text-dark" id="summarySubtotal">kr. 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted font-weight-semibold">Tax / VAT Amount:</span>
                                    <span class="font-weight-bold text-dark" id="summaryTax">kr. 0.00</span>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark small mb-1">Discount Amount</label>
                                    <input type="number" step="0.01" min="0" name="discount_amount" id="discountInput" class="form-control" value="0.00" style="border-radius: 8px;">
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="font-weight-bold text-dark" style="font-size: 1.1rem;">Grand Total:</span>
                                    <span class="font-weight-bold text-primary" style="font-size: 1.3rem;" id="summaryGrandTotal">kr. 0.00</span>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="font-weight-bold text-dark">Notes / Terms & Conditions</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Enter quote terms..." style="border-radius: 10px;"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block btn-lg shadow-sm font-weight-bold" style="border-radius: 12px; background: #2563eb; border: none;">
                                    <i class="fas fa-save mr-1"></i> Save Sales Quotation
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
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

        let taxAmount = 0;
        if (taxType === 'percent') {
            taxAmount = subtotal * (taxVal / 100);
        } else if (taxType === 'flat') {
            taxAmount = taxVal;
        }

        $('#summaryTax').text('kr. ' + taxAmount.toFixed(2));

        let discount = parseFloat($('#discountInput').val()) || 0;
        let grandTotal = Math.max(0, subtotal + taxAmount - discount);

        $('#summaryGrandTotal').text('kr. ' + grandTotal.toFixed(2));
    }

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
                <td class="text-right pr-4 font-weight-bold text-dark line-subtotal">
                    kr. 0.00
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-link text-danger remove-row-btn" style="outline: none;"><i class="fas fa-times"></i></button>
                </td>
            </tr>
        `;
        $('#itemsTableBody').append(newRow);
        let row = $('#itemsTableBody .item-row').last();
        rowIndex++;
        
        // Resolve customer specific price if customer is selected
        let customerId = $('select[name="customer_id"]').val();
        if (customerId) {
            $.ajax({
                url: "{{ route('admin.pricelists.resolve-price') }}",
                method: 'GET',
                data: {
                    product_id: productId,
                    customer_id: customerId
                },
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

    $('#currencySelect').on('change', function() {
        let rate = $(this).find('option:selected').data('rate') || 1.0;
        $('#exchangeRateInput').val(rate);
    });
});
</script>
@endpush
