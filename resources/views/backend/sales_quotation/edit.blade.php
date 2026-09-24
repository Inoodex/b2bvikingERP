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
                {{-- Row 1: Customer Details (col-lg-8) + Commercial Summary (col-lg-4) --}}
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; background: #ffffff;">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-info-circle mr-2 text-primary"></i> Customer & Quotation Details</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Quotation No</label>
                                        <input type="text" class="form-control font-weight-bold text-primary bg-light" value="{{ $salesQuotation->quotation_no }}" readonly style="border-radius: 8px; font-size: 1rem;">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Quotation Date <span class="text-danger">*</span></label>
                                        <input type="date" name="quotation_date" class="form-control" value="{{ $salesQuotation->quotation_date ? $salesQuotation->quotation_date->format('Y-m-d') : date('Y-m-d') }}" required style="border-radius: 8px;">
                                    </div>
                                </div>

                                {{-- Segmented Quotation Type Tabs --}}
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold text-dark d-flex align-items-center mb-2" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-layer-group text-primary mr-2"></i> QUOTATION TYPE <span class="text-danger ml-1">*</span>
                                    </label>
                                    <div class="nav nav-pills quotation-type-nav p-1 bg-light rounded d-inline-flex border" style="border-color: #e4e6fc !important; background-color: #f4f6f9 !important; gap: 4px;">
                                        <a href="javascript:void(0)" 
                                           class="nav-link quotation-type-btn {{ !$isProspect ? 'active font-weight-bold' : 'text-muted' }} px-3 py-2" 
                                           data-mode="registered" 
                                           id="tabModeRegistered" 
                                           style="border-radius: 6px; font-size: 13px; transition: all 0.2s ease;">
                                            <i class="fas fa-building mr-1"></i> Registered B2B Customer / Outlet
                                        </a>
                                        <a href="javascript:void(0)" 
                                           class="nav-link quotation-type-btn {{ $isProspect ? 'active font-weight-bold' : 'text-muted' }} px-3 py-2" 
                                           data-mode="prospect" 
                                           id="tabModeProspect" 
                                           style="border-radius: 6px; font-size: 13px; transition: all 0.2s ease;">
                                            <i class="fas fa-user-tag mr-1"></i> Walk-in Prospect / Catalog Inquiry
                                        </a>
                                    </div>
                                    <input type="hidden" name="customer_mode" id="customerModeInput" value="{{ $isProspect ? 'prospect' : 'registered' }}">
                                    <input type="hidden" name="customer_type" id="customerTypeInput" value="{{ $isProspect ? 'prospect' : 'registered' }}">
                                </div>

                                {{-- Registered Customer Selection Container --}}
                                <div class="row {{ $isProspect ? 'd-none' : '' }}" id="registeredCustomerContainer">
                                    @php
                                        $selectedIsOutlet = isset($outlets) && $outlets->contains('id', $salesQuotation->customer_id);
                                    @endphp
                                    {{-- Regular Customer Selection --}}
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-user text-primary mr-1"></i> Customer (Regular B2B)
                                        </label>
                                        <select id="regularCustomerSelect" class="form-control select2" style="border-radius: 8px;">
                                            <option value="">-- Choose B2B Customer --</option>
                                            @foreach($regularCustomers as $customer)
                                                <option value="{{ $customer->id }}" {{ !$selectedIsOutlet && $salesQuotation->customer_id == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }} ({{ $customer->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted d-block mt-1">Select if selling to an external client/customer</small>
                                    </div>

                                    {{-- Outlet User Selection --}}
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-store text-warning mr-1"></i> Outlet User (Store / Branch)
                                        </label>
                                        <select id="outletUserSelect" class="form-control select2" style="border-radius: 8px;">
                                            <option value="">-- Choose Outlet / Store --</option>
                                            @foreach($outlets as $outlet)
                                                <option value="{{ $outlet->id }}" {{ $selectedIsOutlet && $salesQuotation->customer_id == $outlet->id ? 'selected' : '' }}>
                                                    {{ $outlet->name }} ({{ $outlet->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted d-block mt-1">Select if selling directly to a retail outlet</small>
                                    </div>
                                </div>

                                {{-- Walk-in Prospect / Catalog Inquiry Container --}}
                                <div class="row {{ !$isProspect ? 'd-none' : '' }}" id="prospectCustomerContainer">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-store text-primary mr-1"></i> Prospect / Business Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="prospect_name" id="prospectNameInput" class="form-control" value="{{ $isProspect ? $prospectName : '' }}" placeholder="e.g. Nyhavn Souvenir Kiosk / Walk-in Buyer" style="border-radius: 8px;">
                                        <small class="text-muted d-block mt-1">Prints on the Buyer Lookbook & Quotation Header</small>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-phone-alt text-primary mr-1"></i> Phone Number
                                        </label>
                                        <input type="text" name="prospect_phone" id="prospectPhoneInput" class="form-control" value="{{ $isProspect ? $prospectPhone : '' }}" placeholder="e.g. +45 12 34 56 78" style="border-radius: 8px;">
                                        <small class="text-muted d-block mt-1">Direct contact for catalog dispatch and commercial follow-up</small>
                                    </div>
                                </div>

                                {{-- Hidden input holding the actual selected customer_id submitted to backend --}}
                                <input type="hidden" name="customer_id" id="finalCustomerId" value="{{ $salesQuotation->customer_id }}">

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
                                    <div class="col-md-6 form-group mb-0">
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
                                    <div class="col-md-6 form-group mb-0">
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
                    </div>

                    {{-- Commercial Summary Side Panel (col-lg-4) --}}
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
                                    <label class="font-weight-bold text-dark small mb-1">Discount Amount</label>
                                    <input type="number" step="0.01" min="0" name="discount_amount" id="discountInput" class="form-control" value="{{ $salesQuotation->discount_amount }}" style="border-radius: 8px;">
                                </div>

                                <hr style="border-top: 1px dashed #cbd5e1;">

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="font-weight-bold text-dark" style="font-size: 1.1rem;">Grand Total:</span>
                                    <span class="font-weight-bold text-primary" id="displayGrandTotal" style="font-size: 1.35rem;">
                                        kr. {{ number_format($salesQuotation->total_amount, 2) }}
                                    </span>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="font-weight-bold text-dark">Terms, Notes & Dispatch Instructions</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Enter quote terms..." style="border-radius: 8px;">{{ $cleanNotes ?? $salesQuotation->clean_notes ?? $salesQuotation->notes }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 2: ONLY Quotation Items Grid is 100% Full Page Width --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; background: #ffffff;">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-list-ol mr-2 text-primary"></i> Quoted Products / Line Items</h6>
                                <button type="button" class="btn btn-primary btn-sm font-weight-bold px-3" id="addItemRow" style="border-radius: 8px; background: #2563eb; border: none;">
                                    <i class="fas fa-plus mr-1"></i> Add Product Item
                                </button>
                            </div>
                            <div class="card-body p-3 border-bottom bg-light sq-toolbar">
                                <div class="row align-items-end">
                                    <div class="col-md-7 mb-2 mb-md-0">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold text-dark small mb-1">
                                                <i class="fas fa-bolt mr-1.5 text-warning"></i> Quick 1-Click Category Load (e.g. Magnets, Mugs)
                                            </label>
                                            <select class="form-control select2" id="category_bulk_selector">
                                                <option value="">-- Choose Category to Load All Items --</option>
                                                @foreach($categories as $cat)
                                                    @php $catProductCount = $products->where('category_id', $cat->id)->count(); @endphp
                                                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $catProductCount }} items)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold text-dark small mb-1 d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-bolt mr-1.5 text-success"></i> Bulk Load Action</span>
                                                <small class="text-muted">Populate Table</small>
                                            </label>
                                            <div class="d-flex align-items-center" style="gap: 8px;">
                                                <button type="button" class="btn btn-primary font-weight-bold shadow-sm flex-grow-1 d-flex align-items-center justify-content-center" id="btn_bulk_add_category" title="Add all products of this category into table" style="background: #2563eb; border: none; height: 42px; border-radius: 8px; font-size: 13px; font-weight: 600; white-space: nowrap;">
                                                    <i class="fas fa-plus-circle mr-1.5"></i> Add All Category Items
                                                </button>
                                                <button type="button" class="btn btn-light border font-weight-semibold shadow-sm d-flex align-items-center justify-content-center" id="btn_reset_category_filter" title="Reset Category Selection" style="height: 42px; width: 42px; min-width: 42px; border-radius: 8px; color: #475569; background: #ffffff; border-color: #cbd5e1 !important; flex-shrink: 0; transition: all 0.2s ease;">
                                                    <i class="fas fa-redo-alt text-primary"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 w-100" id="itemsTable">
                                        <thead class="bg-light" style="font-size: 0.8rem; text-transform: uppercase; color: #475569; letter-spacing: 0.5px;">
                                            <tr>
                                                <th style="width: 42%;" class="pl-4 py-3">Product <span class="text-danger">*</span></th>
                                                <th style="width: 16%;" class="py-3 text-center">Qty <span class="text-danger">*</span></th>
                                                <th style="width: 20%;" class="py-3 text-right">Unit Price <span class="text-danger">*</span></th>
                                                <th style="width: 16%;" class="py-3 text-right">Total</th>
                                                <th style="width: 6%;" class="py-3 text-center pr-4">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemRows">
                                            @foreach($salesQuotation->items as $index => $item)
                                                <tr class="item-row">
                                                    <td class="pl-4">
                                                        <select name="items[{{ $index }}][product_id]" class="form-control product-select" required style="border-radius: 8px;">
                                                            <option value="">-- Select Product --</option>
                                                            @foreach($products as $prod)
                                                                <option value="{{ $prod->id }}" data-price="{{ $prod->price ?? 0 }}" {{ $item->product_id == $prod->id ? 'selected' : '' }}>
                                                                    {{ $prod->name }} ({{ number_format($prod->price, 2) }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" step="1" min="1" name="items[{{ $index }}][qty]" class="form-control qty-input text-center mx-auto" value="{{ $item->qty }}" required style="width: 100px; border-radius: 8px;">
                                                    </td>
                                                    <td class="text-right">
                                                        <input type="number" step="0.01" min="0" name="items[{{ $index }}][unit_price]" class="form-control price-input text-right ml-auto" value="{{ $item->unit_price }}" required style="width: 120px; border-radius: 8px;">
                                                    </td>
                                                    <td class="text-right font-weight-bold text-dark align-middle row-total">
                                                        kr. {{ number_format($item->qty * $item->unit_price, 2) }}
                                                    </td>
                                                    <td class="text-center align-middle pr-4">
                                                        <button type="button" class="btn btn-outline-danger btn-sm remove-row" style="border-radius: 6px;"><i class="fas fa-times"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                                <a href="{{ route('admin.sales-quotations.index') }}" class="btn btn-outline-secondary font-weight-semibold px-4 py-2" style="border-radius: 8px;">
                                    <i class="fas fa-arrow-left mr-1.5"></i> Back to Quotations
                                </a>
                                <button type="submit" class="btn btn-warning font-weight-bold text-dark px-5 py-2.5 shadow-sm" style="border-radius: 10px; font-size: 0.95rem;">
                                    <i class="fas fa-save mr-1.5"></i> Update Sales Quotation
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    @push('css')
    <style>
        .quotation-type-nav {
            background: #f1f5f9 !important;
            border: 1px solid #e2e8f0 !important;
            padding: 4px !important;
            border-radius: 8px !important;
        }
        .quotation-type-nav .quotation-type-btn {
            color: #475569 !important;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 8px 20px !important;
            border-radius: 6px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .quotation-type-nav .quotation-type-btn:hover:not(.active) {
            background: #e2e8f0 !important;
            color: #0f172a !important;
        }
        .quotation-type-nav .quotation-type-btn.active {
            background: #2563eb !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25) !important;
        }
        .sq-toolbar .select2-container {
            width: 100% !important;
        }
        .sq-toolbar .select2-container--default .select2-selection--single {
            height: 42px !important;
            border-radius: 8px !important;
            border: 1px solid #cbd5e1 !important;
            display: flex !important;
            align-items: center !important;
            background-color: #ffffff !important;
        }
        .sq-toolbar .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px !important;
            padding-left: 12px !important;
            color: #1e293b !important;
            font-weight: 500 !important;
        }
        .sq-toolbar .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 8px !important;
        }
    </style>
    @endpush

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

            // Mutual clear and sync between Regular Customer and Outlet User
            $('#regularCustomerSelect').on('change', function() {
                let val = $(this).val();
                if (val) {
                    $('#outletUserSelect').val('').trigger('change.select2');
                    $('#finalCustomerId').val(val);
                } else {
                    if (!$('#outletUserSelect').val()) {
                        $('#finalCustomerId').val('');
                    }
                }
            });

            $('#outletUserSelect').on('change', function() {
                let val = $(this).val();
                if (val) {
                    $('#regularCustomerSelect').val('').trigger('change.select2');
                    $('#finalCustomerId').val(val);
                } else {
                    if (!$('#regularCustomerSelect').val()) {
                        $('#finalCustomerId').val('');
                    }
                }
            });

            // Bulk Category Add Handler
            const allCatalogProducts = @json($products ?? []);

            $('#btn_reset_category_filter').on('click', function() {
                $('#category_bulk_selector').val('').trigger('change.select2');
                toastr.info('Category selection reset.');
            });

            $('#btn_bulk_add_category').on('click', function() {
                let catId = $('#category_bulk_selector').val();
                if (!catId) {
                    toastr.warning('Please choose a category first.');
                    return;
                }

                let catName = $('#category_bulk_selector option:selected').text().replace(/\s*\(\d+\s*items\)/, '');
                let matchingProducts = allCatalogProducts.filter(p => p.category_id == catId);

                if (matchingProducts.length === 0) {
                    toastr.info(`No active products found in category "${catName}".`);
                    return;
                }

                let addedCount = 0;
                matchingProducts.forEach(product => {
                    let alreadyExists = false;
                    $('.product-select').each(function() {
                        if ($(this).val() == product.id) {
                            alreadyExists = true;
                            return false;
                        }
                    });

                    if (!alreadyExists) {
                        let defaultPrice = parseFloat(product.outlet_price || product.price || 0).toFixed(2);
                        let rowHtml = `
                            <tr class="item-row">
                                <td>
                                    <select name="items[${itemIndex}][product_id]" class="form-control product-select" required style="border-radius: 8px;">
                                        <option value="">-- Select Product --</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}" data-price="{{ $prod->outlet_price ?: $prod->price }}" ${product.id == {{ $prod->id }} ? 'selected' : ''}>
                                                {{ $prod->name }} (${parseFloat({{ $prod->outlet_price ?: $prod->price }}).toFixed(2)})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="1" min="1" name="items[${itemIndex}][qty]" class="form-control qty-input" value="1" required style="border-radius: 8px;">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[${itemIndex}][unit_price]" class="form-control price-input" value="${defaultPrice}" required style="border-radius: 8px;">
                                </td>
                                <td class="text-right font-weight-bold text-dark align-middle row-total">kr. ${defaultPrice}</td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-row" style="border-radius: 6px;"><i class="fas fa-times"></i></button>
                                </td>
                            </tr>
                        `;
                        $('#itemRows').append(rowHtml);
                        itemIndex++;
                        addedCount++;
                    }
                });

                calculateTotals();

                if (addedCount > 0) {
                    toastr.success(`Added ${addedCount} product(s) from "${catName}" to Quotation!`);
                } else {
                    toastr.info(`All products from "${catName}" are already in the Quotation.`);
                }
            });

            // Customer Mode Tab Click Handler
            $('.quotation-type-btn').on('click', function(e) {
                e.preventDefault();
                $('.quotation-type-btn').removeClass('active font-weight-bold').addClass('text-muted');
                $(this).addClass('active font-weight-bold').removeClass('text-muted');

                let mode = $(this).data('mode');
                $('#customerModeInput').val(mode);
                $('#customerTypeInput').val(mode);

                if (mode === 'prospect') {
                    $('#registeredCustomerContainer').addClass('d-none');
                    $('#prospectCustomerContainer').removeClass('d-none');
                    $('#finalCustomerId').val('{{ $prospectUser->id }}');
                } else {
                    $('#prospectCustomerContainer').addClass('d-none');
                    $('#registeredCustomerContainer').removeClass('d-none');
                    let regVal = $('#regularCustomerSelect').val();
                    let outVal = $('#outletUserSelect').val();
                    $('#finalCustomerId').val(regVal || outVal || '');
                }
            });

            $('form').on('submit', function(e) {
                let mode = $('#customerModeInput').val();
                if (mode === 'prospect') {
                    let pName = $('#prospectNameInput').val().trim();
                    if (!pName) {
                        e.preventDefault();
                        toastr.error('Please enter a Buyer / Shop Name for the prospect.');
                        $('#prospectNameInput').focus();
                        return false;
                    }
                    $('#finalCustomerId').val('{{ $prospectUser->id }}');
                } else {
                    if (!$('#finalCustomerId').val()) {
                        e.preventDefault();
                        toastr.error('Please select either a Regular B2B Customer or an Outlet User.');
                        return false;
                    }
                }
            });

            calculateTotals();
        });
    </script>
    @endpush
@endsection
