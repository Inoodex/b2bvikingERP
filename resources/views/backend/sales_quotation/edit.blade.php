@extends('backend.layouts.master')

@section('title', 'Edit Sales Quotation - ' . $salesQuotation->quotation_no)

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header border-0 shadow-sm mb-4" style="background: #ffffff; border-radius: 16px; padding: 20px 24px;">
            <div class="d-flex align-items-center flex-wrap w-100">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                    <div class="mr-3 p-3 rounded-circle text-white shadow-sm" style="background: linear-gradient(135deg, #0a0e1a 0%, #1e293b 100%); border: 1px solid rgba(205, 160, 90, 0.3);">
                        <i class="fas fa-edit text-warning" style="font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 font-weight-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">Edit Sales Quotation</h4>
                        <p class="text-muted mb-0 small">Update pricing items and commercial terms for #{{ $salesQuotation->quotation_no }}</p>
                    </div>
                </div>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <a href="{{ route('admin.sales-quotations.show', $salesQuotation->id) }}" class="btn btn-outline-secondary px-4 py-2 font-weight-bold mr-2" style="border-radius: 10px;">
                        <i class="fas fa-eye mr-1"></i> View Details
                    </a>
                    <a href="{{ route('admin.sales-quotations.index') }}" class="btn btn-outline-secondary px-4 py-2 font-weight-bold" style="border-radius: 10px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="section-body">
            <form action="{{ route('admin.sales-quotations.update', $salesQuotation->id) }}" method="POST" id="quotationForm">
                @csrf
                @method('PUT')
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
                                        <label class="font-weight-bold text-dark">Quotation No</label>
                                        <input type="text" class="form-control font-weight-bold text-primary bg-light" value="{{ $salesQuotation->quotation_no }}" readonly style="border-radius: 8px;">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Customer <span class="text-danger">*</span></label>
                                        <select name="customer_id" class="form-control select2" required style="border-radius: 8px;">
                                            <option value="">-- Select Customer --</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ $salesQuotation->customer_id == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }} ({{ $customer->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Currency</label>
                                        <select name="currency_id" id="currencySelect" class="form-control" style="border-radius: 8px;">
                                            <option value="" {{ !$salesQuotation->currency_id ? 'selected' : '' }}>Base Currency (DKK kr.)</option>
                                            @foreach($currencies as $curr)
                                                <option value="{{ $curr->id }}" data-rate="{{ $curr->exchange_rate }}" data-symbol="{{ $curr->symbol }}" {{ $salesQuotation->currency_id == $curr->id ? 'selected' : '' }}>
                                                    {{ $curr->code }} ({{ $curr->symbol }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Exchange Rate (to DKK Base)</label>
                                        <input type="number" step="0.000001" name="exchange_rate" id="exchangeRateInput" class="form-control" value="{{ $salesQuotation->exchange_rate }}" style="border-radius: 8px;">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Valid Until <span class="text-danger">*</span></label>
                                        <input type="date" name="valid_until" class="form-control" value="{{ $salesQuotation->valid_until ? $salesQuotation->valid_until->format('Y-m-d') : date('Y-m-d') }}" required style="border-radius: 8px;">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Tax / VAT Rule</label>
                                        <select name="tax_id" id="taxSelect" class="form-control" style="border-radius: 8px;">
                                            <option value="" data-rate="0">No Tax / Tax Exempt (0%)</option>
                                            @foreach($taxes as $tax)
                                                <option value="{{ $tax->id }}" data-rate="{{ $tax->rate }}" {{ $salesQuotation->tax_id == $tax->id ? 'selected' : '' }}>
                                                    {{ $tax->name }} ({{ $tax->rate }}%)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Incoterm (Shipping/Trade Terms)</label>
                                        <select name="incoterm" class="form-control" style="border-radius: 8px;">
                                            @foreach(['EXW', 'FOB', 'CIF', 'DDP', 'CFR', 'FCA'] as $term)
                                                <option value="{{ $term }}" {{ $salesQuotation->incoterm == $term ? 'selected' : '' }}>{{ $term }} - {{ $term === 'EXW' ? 'Ex Works' : ($term === 'FOB' ? 'Free on Board' : ($term === 'DDP' ? 'Delivered Duty Paid' : $term)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Line Items Table --}}
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; background: #ffffff;">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-list-ol mr-2 text-primary"></i> Quoted Products / Line Items</h6>
                                <button type="button" class="btn btn-primary btn-sm font-weight-bold px-3" id="addItemRow" style="border-radius: 8px;">
                                    <i class="fas fa-plus mr-1"></i> Add Product Item
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="itemsTable">
                                        <thead class="bg-light" style="font-size: 0.8rem; text-transform: uppercase; color: #64748b;">
                                            <tr>
                                                <th style="width: 45%;">Product <span class="text-danger">*</span></th>
                                                <th style="width: 20%;">Qty <span class="text-danger">*</span></th>
                                                <th style="width: 20%;">Unit Price <span class="text-danger">*</span></th>
                                                <th style="width: 10%;" class="text-right">Total</th>
                                                <th style="width: 5%;" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemRows">
                                            @foreach($salesQuotation->items as $index => $item)
                                                <tr class="item-row">
                                                    <td>
                                                        <select name="items[{{ $index }}][product_id]" class="form-control product-select" required style="border-radius: 8px;">
                                                            <option value="">-- Select Product --</option>
                                                            @foreach($products as $prod)
                                                                <option value="{{ $prod->id }}" data-price="{{ $prod->price ?? 0 }}" {{ $item->product_id == $prod->id ? 'selected' : '' }}>
                                                                    {{ $prod->name }} ({{ number_format($prod->price, 2) }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="1" min="1" name="items[{{ $index }}][qty]" class="form-control qty-input" value="{{ $item->qty }}" required style="border-radius: 8px;">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0" name="items[{{ $index }}][unit_price]" class="form-control price-input" value="{{ $item->unit_price }}" required style="border-radius: 8px;">
                                                    </td>
                                                    <td class="text-right font-weight-bold text-dark align-middle row-total">
                                                        kr. {{ number_format($item->qty * $item->unit_price, 2) }}
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-outline-danger btn-sm remove-row" style="border-radius: 6px;"><i class="fas fa-times"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Commercial Terms & Summary --}}
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; background: #ffffff;">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-calculator mr-2 text-primary"></i> Commercial Summary</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted font-weight-semibold">Subtotal:</span>
                                    <span class="font-weight-bold text-dark" id="displaySubtotal">kr. {{ number_format($salesQuotation->subtotal_amount, 2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted font-weight-semibold">Tax Amount:</span>
                                    <span class="font-weight-bold text-dark" id="displayTax">kr. {{ number_format($salesQuotation->tax_amount, 2) }}</span>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark">Discount Amount</label>
                                    <input type="number" step="0.01" min="0" name="discount_amount" id="discountInput" class="form-control" value="{{ $salesQuotation->discount_amount }}" style="border-radius: 8px;">
                                </div>

                                <hr style="border-top: 1px dashed #cbd5e1;">

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="font-weight-bold text-dark" style="font-size: 1.1rem;">Grand Total:</span>
                                    <span class="font-weight-bold text-primary" id="displayGrandTotal" style="font-size: 1.35rem;">
                                        kr. {{ number_format($salesQuotation->total_amount, 2) }}
                                    </span>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="font-weight-bold text-dark">Notes & Commercial Conditions</label>
                                    <textarea name="notes" class="form-control" rows="4" style="border-radius: 8px;">{{ $salesQuotation->notes }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-warning btn-block font-weight-bold text-dark py-3 shadow-sm" style="border-radius: 12px;">
                                    <i class="fas fa-save mr-2"></i> Update Sales Quotation
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    @push('scripts')
    <script>
        $(document).ready(function () {
            let itemIndex = {{ count($salesQuotation->items) }};

            // Add row
            $('#addItemRow').on('click', function () {
                let rowHtml = `
                    <tr class="item-row">
                        <td>
                            <select name="items[${itemIndex}][product_id]" class="form-control product-select" required style="border-radius: 8px;">
                                <option value="">-- Select Product --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" data-price="{{ $prod->price ?? 0 }}">{{ $prod->name }} ({{ number_format($prod->price, 2) }})</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" step="1" min="1" name="items[${itemIndex}][qty]" class="form-control qty-input" value="1" required style="border-radius: 8px;">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="items[${itemIndex}][unit_price]" class="form-control price-input" value="0.00" required style="border-radius: 8px;">
                        </td>
                        <td class="text-right font-weight-bold text-dark align-middle row-total">kr. 0.00</td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-row" style="border-radius: 6px;"><i class="fas fa-times"></i></button>
                        </td>
                    </tr>
                `;
                $('#itemRows').append(rowHtml);
                itemIndex++;
                calculateTotals();
            });

            // Remove row
            $(document).on('click', '.remove-row', function () {
                if ($('.item-row').length > 1) {
                    $(this).closest('tr').remove();
                    calculateTotals();
                } else {
                    toastr.warning('Quotation must contain at least 1 product item.');
                }
            });

            // Product Select Change
            $(document).on('change', '.product-select', function () {
                let price = $(this).find(':selected').data('price') || 0;
                $(this).closest('tr').find('.price-input').val(parseFloat(price).toFixed(2));
                calculateTotals();
            });

            // Input listener
            $(document).on('input', '.qty-input, .price-input, #discountInput', function () {
                calculateTotals();
            });

            $('#taxSelect, #currencySelect').on('change', function () {
                calculateTotals();
            });

            function calculateTotals() {
                let subtotal = 0;

                $('.item-row').each(function () {
                    let qty = parseFloat($(this).find('.qty-input').val()) || 0;
                    let price = parseFloat($(this).find('.price-input').val()) || 0;
                    let lineTotal = qty * price;
                    subtotal += lineTotal;
                    $(this).find('.row-total').text('kr. ' + lineTotal.toFixed(2));
                });

                let taxRate = parseFloat($('#taxSelect').find(':selected').data('rate')) || 0;
                let taxAmount = (subtotal * taxRate) / 100;
                let discountAmount = parseFloat($('#discountInput').val()) || 0;
                let grandTotal = Math.max(0, subtotal + taxAmount - discountAmount);

                $('#displaySubtotal').text('kr. ' + subtotal.toFixed(2));
                $('#displayTax').text('kr. ' + taxAmount.toFixed(2));
                $('#displayGrandTotal').text('kr. ' + grandTotal.toFixed(2));
            }

            calculateTotals();
        });
    </script>
    @endpush
@endsection
