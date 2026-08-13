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
                                <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold" id="addItemBtn" style="border-radius: 8px;">
                                    <i class="fas fa-plus mr-1"></i> Add Product
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0" id="itemsTable">
                                        <thead class="bg-light" style="font-size: 0.8rem; text-transform: uppercase; color: #64748b;">
                                            <tr>
                                                <th style="width: 40%;" class="pl-4">Product / Item</th>
                                                <th style="width: 20%;">Qty</th>
                                                <th style="width: 25%;">Unit Price</th>
                                                <th style="width: 15%;" class="text-right pr-4">Subtotal</th>
                                                <th style="width: 5%;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsTableBody">
                                            {{-- First Default Row --}}
                                            <tr class="item-row">
                                                <td class="pl-4">
                                                    <select name="items[0][product_id]" class="form-control product-select" required style="border-radius: 8px;">
                                                        <option value="">-- Select Product --</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                                                {{ $product->name }} (kr. {{ number_format($product->price, 2) }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" step="1" min="1" name="items[0][qty]" class="form-control qty-input" value="1" required style="border-radius: 8px;">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="items[0][unit_price]" class="form-control price-input" value="0.00" required style="border-radius: 8px;">
                                                </td>
                                                <td class="text-right pr-4 font-weight-bold text-dark line-subtotal">
                                                    kr. 0.00
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-link text-danger remove-row-btn" style="outline: none;"><i class="fas fa-times"></i></button>
                                                </td>
                                            </tr>
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

    $(document).on('change', '.product-select', function() {
        let price = parseFloat($(this).find('option:selected').data('price')) || 0;
        $(this).closest('.item-row').find('.price-input').val(price.toFixed(2));
        calculateTotals();
    });

    $(document).on('input', '.qty-input, .price-input, #discountInput', function() {
        calculateTotals();
    });

    $('#taxSelect').on('change', function() {
        calculateTotals();
    });

    $('#addItemBtn').on('click', function() {
        let newRow = `
            <tr class="item-row">
                <td class="pl-4">
                    <select name="items[${rowIndex}][product_id]" class="form-control product-select" required style="border-radius: 8px;">
                        <option value="">-- Select Product --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                {{ $product->name }} (kr. {{ number_format($product->price, 2) }})
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" step="1" min="1" name="items[${rowIndex}][qty]" class="form-control qty-input" value="1" required style="border-radius: 8px;">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="items[${rowIndex}][unit_price]" class="form-control price-input" value="0.00" required style="border-radius: 8px;">
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
        rowIndex++;
    });

    $(document).on('click', '.remove-row-btn', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('.item-row').remove();
            calculateTotals();
        }
    });

    $('#currencySelect').on('change', function() {
        let rate = $(this).find('option:selected').data('rate') || 1.0;
        $('#exchangeRateInput').val(rate);
    });
});
</script>
@endpush
