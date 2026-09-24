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
            @if(isset($cartItems) && $cartItems->count() > 0)
                <div class="alert alert-primary d-flex align-items-center mb-4 shadow-sm" style="border-radius: 12px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af;">
                    <div class="mr-3" style="font-size: 1.5rem;">
                        <i class="fas fa-file-invoice-dollar text-primary"></i>
                    </div>
                    <div>
                        <strong class="d-block font-weight-bold" style="font-size: 0.95rem;">Pre-filled from Sales Quotation Cart!</strong>
                        <span class="small" style="color: #3b82f6;">{{ $cartItems->count() }} item(s) have been pre-loaded from your visual catalog selection. Review quantities, select a customer, and save to finalize the quote.</span>
                    </div>
                </div>
            @endif
            <form action="{{ route('admin.sales-quotations.store') }}" method="POST" id="quotationForm">
                @csrf
                @if(isset($cartItems) && $cartItems->count() > 0)
                    <input type="hidden" name="from_cart" value="1">
                @endif
                {{-- Row 1: Customer Details (col-lg-8) + Calculation Summary (col-lg-4) --}}
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; background: #ffffff;">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-info-circle mr-2 text-primary"></i> Customer & Quotation Details</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Quotation No (Auto-Generated)</label>
                                        <input type="text" class="form-control font-weight-bold text-primary bg-light" value="{{ $nextQuotationNo }}" readonly style="border-radius: 8px; font-size: 1rem;">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Quotation Date <span class="text-danger">*</span></label>
                                        <input type="date" name="quotation_date" class="form-control" value="{{ date('Y-m-d') }}" required style="border-radius: 8px;">
                                    </div>
                                </div>

                                {{-- Segmented Quotation Type Tabs --}}
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold text-dark d-flex align-items-center mb-2" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-layer-group text-primary mr-2"></i> QUOTATION TYPE <span class="text-danger ml-1">*</span>
                                    </label>
                                    <div class="nav nav-pills quotation-type-nav p-1 bg-light rounded d-inline-flex border" style="border-color: #e4e6fc !important; background-color: #f4f6f9 !important; gap: 4px;">
                                        <a href="javascript:void(0)" 
                                           class="nav-link quotation-type-btn active font-weight-bold px-3 py-2" 
                                           data-mode="registered" 
                                           id="tabModeRegistered" 
                                           style="border-radius: 6px; font-size: 13px; transition: all 0.2s ease;">
                                            <i class="fas fa-building mr-1"></i> Registered B2B Customer / Outlet
                                        </a>
                                        <a href="javascript:void(0)" 
                                           class="nav-link quotation-type-btn text-muted font-weight-bold px-3 py-2" 
                                           data-mode="prospect" 
                                           id="tabModeProspect" 
                                           style="border-radius: 6px; font-size: 13px; transition: all 0.2s ease;">
                                            <i class="fas fa-user-tag mr-1"></i> Walk-in Prospect / Catalog Inquiry
                                        </a>
                                    </div>
                                    <input type="hidden" name="customer_mode" id="customerModeInput" value="registered">
                                    <input type="hidden" name="customer_type" id="customerTypeInput" value="registered">
                                </div>

                                {{-- Registered Customer Selection Container --}}
                                <div class="row" id="registeredCustomerContainer">
                                    {{-- Regular Customer Selection --}}
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-user text-primary mr-1"></i> Customer (Regular B2B)
                                        </label>
                                        <select id="regularCustomerSelect" class="form-control select2" style="border-radius: 8px;">
                                            <option value="">-- Choose B2B Customer --</option>
                                            @foreach($regularCustomers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
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
                                                <option value="{{ $outlet->id }}">{{ $outlet->name }} ({{ $outlet->email }})</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted d-block mt-1">Select if selling directly to a retail outlet</small>
                                    </div>
                                </div>

                                {{-- Walk-in Prospect / Catalog Inquiry Container --}}
                                <div class="row d-none" id="prospectCustomerContainer">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-store text-primary mr-1"></i> Prospect / Business Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="prospect_name" id="prospectNameInput" class="form-control" placeholder="e.g. Nyhavn Souvenir Kiosk / Walk-in Buyer" style="border-radius: 8px;">
                                        <small class="text-muted d-block mt-1">Prints on the Buyer Lookbook & Quotation Header</small>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-phone-alt text-primary mr-1"></i> Phone Number
                                        </label>
                                        <input type="text" name="prospect_phone" id="prospectPhoneInput" class="form-control" placeholder="e.g. +45 12 34 56 78" style="border-radius: 8px;">
                                        <small class="text-muted d-block mt-1">Direct contact for catalog dispatch and commercial follow-up</small>
                                    </div>
                                </div>

                                {{-- Hidden input holding the actual selected customer_id submitted to backend --}}
                                <input type="hidden" name="customer_id" id="finalCustomerId" value="">

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
                                    <div class="col-md-6 form-group mb-0">
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
                                    <div class="col-md-6 form-group mb-0">
                                        <label class="font-weight-bold text-dark">Incoterms (Commercial Delivery Terms)</label>
                                        <select name="incoterm" class="form-control" style="border-radius: 8px;">
                                            <option value="EXW">EXW - Ex Works (Buyer arranges collection & freight)</option>
                                            <option value="FOB">FOB - Free on Board (Loaded onto vessel)</option>
                                            <option value="CIF">CIF - Cost, Insurance & Freight (Paid to destination port)</option>
                                            <option value="DDP">DDP - Delivered Duty Paid (Seller assumes all risks & customs clearance)</option>
                                            <option value="CFR">CFR - Cost and Freight (Port destination)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Summary Side Panel (col-lg-4) --}}
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
                                    <span class="font-weight-bold text-primary" style="font-size: 1.35rem;" id="summaryGrandTotal">kr. 0.00</span>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="font-weight-bold text-dark">Notes / Terms & Conditions</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Enter quote terms, dispatch instructions..." style="border-radius: 10px;"></textarea>
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
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-boxes mr-2 text-primary"></i> Quotation Items Grid</h6>
                                <span class="badge badge-light border text-muted px-2.5 py-1.5" style="font-size: 11px;">
                                    <i class="fas fa-layer-group text-primary mr-1"></i> Multi-Product & Variant Engine
                                </span>
                            </div>
                            <div class="card-body p-3 border-bottom bg-light sq-toolbar">
                                <div class="row align-items-end">
                                    {{-- 1: Single Product Search --}}
                                    <div class="col-lg-4 col-md-4 mb-2 mb-md-0">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold text-dark small mb-1 d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-search mr-1.5 text-primary"></i> Single Product Search</span>
                                                <small class="text-muted font-italic" id="filtered_hint">All Categories</small>
                                            </label>
                                            <select class="form-control select2" id="product_selector">
                                                <option value="">-- Choose Product to Add --</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" data-category-id="{{ $product->category_id }}" data-price="{{ $product->outlet_price ?: $product->price }}">{{ $product->name }} (SKU: {{ $product->product_number ?? $product->id }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- 2: Category Filter --}}
                                    <div class="col-lg-4 col-md-4 mb-2 mb-md-0">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold text-dark small mb-1 d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-filter mr-1.5 text-warning"></i> Quick 1-Click Category Load</span>
                                                <span class="badge badge-primary font-weight-bold" id="category_count_badge" style="font-size: 11px; display: none;"></span>
                                            </label>
                                            <select class="form-control select2" id="category_bulk_selector">
                                                <option value="">-- All Categories (No Filter) --</option>
                                                @foreach($categories as $cat)
                                                    @php $catProductCount = $products->where('category_id', $cat->id)->count(); @endphp
                                                    <option value="{{ $cat->id }}" data-count="{{ $catProductCount }}">{{ $cat->name }} ({{ $catProductCount }} items)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- 3: Add All Category Items Button & Reset --}}
                                    <div class="col-lg-4 col-md-4">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold text-dark small mb-1 d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-bolt mr-1.5 text-success"></i> Bulk Load Action</span>
                                                <small class="text-muted">Populate Table</small>
                                            </label>
                                            <div class="d-flex align-items-center" style="gap: 8px;">
                                                <button type="button" class="btn btn-primary font-weight-bold shadow-sm flex-grow-1 d-flex align-items-center justify-content-center" id="btn_bulk_add_category" title="Add all products of this category into table" style="background: #2563eb; border: none; height: 42px; border-radius: 8px; font-size: 13px; font-weight: 600; white-space: nowrap;">
                                                    <i class="fas fa-plus-circle mr-1.5"></i> Add All Category Items
                                                </button>
                                                <button type="button" class="btn btn-light border font-weight-semibold shadow-sm d-flex align-items-center justify-content-center" id="btn_reset_category_filter" title="Reset Category Filter" style="height: 42px; width: 42px; min-width: 42px; border-radius: 8px; color: #475569; background: #ffffff; border-color: #cbd5e1 !important; flex-shrink: 0; transition: all 0.2s ease;">
                                                    <i class="fas fa-redo-alt text-primary"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 w-100 table-hover" id="itemsTable">
                                        <thead class="bg-light" style="font-size: 0.8rem; text-transform: uppercase; color: #475569; letter-spacing: 0.5px;">
                                            <tr>
                                                <th style="width: 40%;" class="pl-4 py-3">Product Details</th>
                                                <th style="width: 12%;" class="py-3 text-center">Stock</th>
                                                <th style="width: 14%;" class="py-3 text-center">Qty</th>
                                                <th style="width: 16%;" class="py-3 text-right">Unit Price</th>
                                                <th style="width: 14%;" class="py-3 text-right pr-4">Subtotal</th>
                                                <th style="width: 4%;" class="py-3 text-center"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsTableBody">
                                            @if(isset($cartItems) && $cartItems->count() > 0)
                                                @foreach($cartItems as $index => $cItem)
                                                    @php
                                                        $p = $cItem->product;
                                                        $v = $cItem->variant;
                                                        $rIndex = $index + 1;
                                                        $pName = $p ? $p->name : 'Product';
                                                        $vName = $v ? ($v->name ?: (trim(implode(' - ', array_filter([optional($v->color)->name, optional($v->size)->name]))) ?: '#'.$v->id)) : 'Standard';
                                                        $price = $v && $v->price > 0 ? $v->price : ($p ? ($p->outlet_price ?: $p->price) : 0);
                                                        $stock = $v ? ($v->qty ?? 0) : ($p ? ($p->qty ?? 0) : 0);
                                                        $qty = $cItem->quantity > 0 ? (float)$cItem->quantity : 1;
                                                        $lineTotal = $qty * $price;
                                                    @endphp
                                                    <tr class="item-row">
                                                        <td class="pl-4">
                                                            <strong class="text-dark d-block" style="font-size: 0.9rem;">{{ $pName }}</strong>
                                                            <small class="badge badge-secondary mt-1">{{ $vName }}</small>
                                                            <input type="hidden" name="items[{{ $rIndex }}][product_id]" value="{{ $cItem->product_id }}" class="product-id-input">
                                                            @if($cItem->variant_id)
                                                                <input type="hidden" name="items[{{ $rIndex }}][variant_id]" value="{{ $cItem->variant_id }}">
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-info px-2 py-1" style="font-size: 0.85rem;">{{ $stock }}</span>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="1" min="1" name="items[{{ $rIndex }}][qty]" class="form-control qty-input" value="{{ $qty }}" required style="border-radius: 8px;">
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" name="items[{{ $rIndex }}][unit_price]" class="form-control price-input" value="{{ number_format($price, 2, '.', '') }}" required style="border-radius: 8px;" data-default-price="{{ $price }}">
                                                        </td>
                                                        <td class="text-right pr-4 font-weight-bold text-dark line-subtotal">
                                                            kr. {{ number_format($lineTotal, 2) }}
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-link text-danger remove-row-btn" style="outline: none;"><i class="fas fa-times"></i></button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                                <a href="{{ route('admin.sales-quotations.index') }}" class="btn btn-outline-secondary font-weight-semibold px-4 py-2" style="border-radius: 8px;">
                                    <i class="fas fa-arrow-left mr-1.5"></i> Back to Quotations
                                </a>
                                <button type="submit" class="btn btn-primary font-weight-bold px-5 py-2.5 shadow-sm" style="border-radius: 10px; background: #2563eb; border: none; font-size: 0.95rem;">
                                    <i class="fas fa-save mr-1.5"></i> Save Sales Quotation
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
    #btn_reset_category_filter:hover {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        border-color: #94a3b8 !important;
        transform: translateY(-1px);
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let rowIndex = {{ isset($cartItems) && $cartItems->count() > 0 ? $cartItems->count() + 1 : 1 }};

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

    // Initialize calculation on page load (for pre-filled cart items)
    calculateTotals();

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
        let customerId = $('#finalCustomerId').val();
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

    // --- Category Filter & Synchronized Single Product Selector ---
    const allCatalogProducts = @json($products ?? []);
    const originalProductOptions = [];
    $('#product_selector option').each(function() {
        if ($(this).val()) {
            originalProductOptions.push({
                id: $(this).val(),
                text: $(this).text(),
                price: $(this).data('price'),
                categoryId: $(this).data('category-id')
            });
        }
    });

    function filterProductSelectorByCategory(catId, catName) {
        $('#product_selector').empty().append('<option value="">-- Choose Product --</option>');
        
        let filtered = originalProductOptions;
        if (catId) {
            filtered = originalProductOptions.filter(p => p.categoryId == catId);
            $('#category_count_badge').text(`${catName}: ${filtered.length} items`).show();
            $('#filtered_hint').text(`Filtered: ${catName}`);
        } else {
            $('#category_count_badge').hide();
            $('#filtered_hint').text('All Categories');
        }

        filtered.forEach(p => {
            let opt = new Option(p.text, p.id, false, false);
            $(opt).attr('data-price', p.price);
            $(opt).attr('data-category-id', p.categoryId);
            $('#product_selector').append(opt);
        });

        $('#product_selector').trigger('change.select2');
    }

    $('#category_bulk_selector').on('change', function() {
        let catId = $(this).val();
        let catName = $(this).find('option:selected').text().replace(/\s*\(\d+\s*items\)/, '');
        filterProductSelectorByCategory(catId, catName);
    });

    $('#btn_reset_category_filter').on('click', function() {
        $('#category_bulk_selector').val('').trigger('change.select2');
        filterProductSelectorByCategory('', '');
        toastr.info('Category filter reset: Showing all products.');
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
            // Check if already in quotation table
            let alreadyExists = false;
            $('.product-id-input').each(function() {
                if ($(this).val() == product.id) {
                    alreadyExists = true;
                    return false;
                }
            });

            if (!alreadyExists) {
                let defaultPrice = parseFloat(product.outlet_price || product.price || 0);
                let stock = product.qty || 0;
                appendItemRow(product.id, product.name, null, '', 1, defaultPrice, stock);
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

    function resolvePricelistForItems(customerId) {
        if (!customerId) return;
        $('.item-row').each(function() {
            let row = $(this);
            let productId = row.find('.product-id-input').val();
            if (productId) {
                $.ajax({
                    url: "{{ route('admin.pricelists.resolve-price') }}",
                    method: 'GET',
                    data: {
                        product_id: productId,
                        customer_id: customerId
                    },
                    success: function(res) {
                        if (res && res.price !== undefined) {
                            row.find('.price-input').val(parseFloat(res.price).toFixed(2));
                            calculateTotals();
                        }
                    }
                });
            }
        });
    }

    // Customer Mode Tab Click Handler
    $('.quotation-type-btn').on('click', function(e) {
        e.preventDefault();
        $('.quotation-type-btn').removeClass('active').addClass('text-muted');
        $(this).addClass('active').removeClass('text-muted');

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

    // Mutual clear and sync between Regular Customer and Outlet User
    $('#regularCustomerSelect').on('change', function() {
        let val = $(this).val();
        if (val) {
            $('#outletUserSelect').val('').trigger('change.select2');
            $('#finalCustomerId').val(val);
            resolvePricelistForItems(val);
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
            resolvePricelistForItems(val);
        } else {
            if (!$('#regularCustomerSelect').val()) {
                $('#finalCustomerId').val('');
            }
        }
    });

    // Client-side form validation before submission
    $('#quotationForm').on('submit', function(e) {
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
});
</script>
@endpush
