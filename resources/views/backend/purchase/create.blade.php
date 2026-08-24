@extends('backend.layouts.master')

@section('title', 'Create Order Receive Invoice')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <div class="d-flex align-items-center flex-wrap w-100">
                <h1 class="mb-2 mb-sm-0 d-flex align-items-center" style="font-size: 1.1rem;">
                    <i class="fas fa-file-invoice mr-2" style="color: #2563eb;"></i>
                    Create Order Receive Invoice
                </h1>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        </div>
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.purchases.index') }}">Order Receive</a>
                        </div>
                        <div class="breadcrumb-item active">Create</div>
                    </div>
                    <button class="btn btn-primary btn-sm ml-2 shadow-sm" type="button" data-toggle="modal" data-target="#importModal" style="background: #2563eb; border: none; border-radius: 10px; min-height: 36px; font-size: 0.8rem;">
                        <i class="fas fa-file-import mr-1"></i>
                        <span class="d-none d-sm-inline">Import from Order Place</span>
                        <span class="d-sm-none">Import</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
                        {{-- Card Header --}}
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 p-2 rounded-circle text-white d-none d-sm-flex" style="background: #2563eb;">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 1rem;">New Order Receive</h5>
                                    <small class="text-muted" style="font-size: 0.8rem;">Create a new purchase invoice</small>
                                </div>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body p-3 p-sm-4">
                            <form action="{{ route('admin.purchases.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                
                                {{-- Section 1: General Information --}}
                                <div class="section-title mb-3">
                                    <span class="font-weight-bold" style="color: #2563eb; font-size: 0.95rem;">
                                        <i class="fas fa-info-circle mr-2"></i>General Information
                                    </span>
                                    <hr style="border-top: 2px solid #e2e8f0; opacity: 0.5; margin-top: 0.3rem;">
                                </div>

                                <div class="row">
                                    <div class="form-group col-12 col-md-4">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.8rem;">
                                            Vendor <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control select2 @error('vendor_id') is-invalid @enderror" 
                                                name="vendor_id" id="vendor_select" required
                                                style="height: 44px; font-size: 0.9rem; border-radius: 10px; border: 2px solid #e2e8f0; width: 100%;">
                                            <option value="">Select Vendor</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->shop_name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted" style="font-size: 0.7rem;">System Rate: <strong id="current_rate_display">1.00</strong></small>
                                    </div>

                                    <div class="form-group col-12 col-md-4">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.8rem;">Shipping Method</label>
                                        <select class="form-control @error('shipping_method') is-invalid @enderror" 
                                                name="shipping_method" id="shipping_method_select"
                                                style="height: 44px; font-size: 0.9rem; border-radius: 10px; border: 2px solid #e2e8f0; width: 100%;">
                                            <option value="">Select Shipping</option>
                                            <option value="Air">Air</option>
                                            <option value="Train">Train</option>
                                            <option value="Ship">Ship</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-12 col-md-4">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.8rem;">
                                            Purchase Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                               name="date" value="{{ date('Y-m-d') }}" required
                                               style="height: 44px; font-size: 0.9rem; border-radius: 10px; border: 2px solid #e2e8f0; width: 100%;">
                                    </div>

                                    <div class="form-group col-12 col-md-6">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.8rem;">Reference / Note</label>
                                        <input type="text" class="form-control @error('note') is-invalid @enderror" 
                                               name="note" placeholder="Optional reference..."
                                               style="height: 44px; font-size: 0.9rem; border-radius: 10px; border: 2px solid #e2e8f0; width: 100%;">
                                    </div>

                                    <div class="form-group col-12 col-md-6">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.8rem;">Invoice Attachment <span class="text-muted" style="font-size: 0.7rem;">(PDF, Excel, Image)</span></label>
                                        <input type="file" class="form-control @error('invoice_attachment') is-invalid @enderror" 
                                               name="invoice_attachment"
                                               style="height: 44px; font-size: 0.9rem; border-radius: 10px; border: 2px solid #e2e8f0; width: 100%; padding: 0.3rem 0.7rem;">
                                    </div>

                                    <div class="form-group col-12 col-md-4">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.8rem;">Pricing Rule (Multiplier)</label>
                                        <select class="form-control select2 @error('pricing_rule_id') is-invalid @enderror" 
                                                name="pricing_rule_id" id="pricing_rule_select"
                                                style="height: 44px; font-size: 0.9rem; border-radius: 10px; border: 2px solid #e2e8f0; width: 100%;">
                                            <option value="">Manual Prices</option>
                                            @if(isset($pricingRules))
                                                @foreach($pricingRules as $rule)
                                                    <option value="{{ $rule->id }}" {{ (isset($defaultPricingRuleId) && $defaultPricingRuleId == $rule->id) ? 'selected' : '' }}>
                                                        {{ $rule->name }} (Sale ×{{ $rule->sale_multiplier }}, Outlet ×{{ $rule->outlet_multiplier }})
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <small class="text-muted" style="font-size: 0.7rem;">When selected, Sale/Outlet price auto-calculates from Local Unit Cost.</small>
                                    </div>

                                    <input type="hidden" name="booking_id" id="booking_id_hidden">
                                </div>

                                {{-- Section 2: Invoice Items --}}
                                <div class="section-title mb-3 mt-3">
                                    <span class="font-weight-bold" style="color: #2563eb; font-size: 0.95rem;">
                                        <i class="fas fa-list-ul mr-2"></i>Invoice Items
                                    </span>
                                    <hr style="border-top: 2px solid #e2e8f0; opacity: 0.5; margin-top: 0.3rem;">
                                </div>

                                <div class="row align-items-end mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold" style="font-size: 0.8rem;">Select Product to Add</label>
                                            <select class="form-control select2" id="product_selector">
                                                <option value="">-- Choose Product --</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                    <table class="table table-bordered table-sm" id="product_table" style="min-width: 800px; font-size: 0.8rem;">
                                        <thead class="bg-light text-center">
                                            <tr>
                                                <th style="width: 4%; font-size: 0.65rem; padding: 0.4rem 0.3rem;">Image</th>
                                                <th style="width: 17%; font-size: 0.65rem; padding: 0.4rem 0.3rem;">Product Details</th>
                                                <th style="width: 5%; font-size: 0.65rem; padding: 0.4rem 0.3rem;">Stock</th>
                                                <th style="width: 8%; font-size: 0.65rem; padding: 0.4rem 0.3rem;" id="vendor_unit_cost_header">Cost (Vendor)</th>
                                                <th style="width: 6%; font-size: 0.65rem; padding: 0.4rem 0.3rem;">Qty</th>
                                                <th style="width: 10%; font-size: 0.65rem; padding: 0.4rem 0.3rem;" id="vendor_subtotal_header">Total (Vendor)</th>
                                                <th style="width: 8%; font-size: 0.65rem; padding: 0.4rem 0.3rem;">Raw Cost</th>
                                                <th style="width: 8%; font-size: 0.65rem; padding: 0.4rem 0.3rem;">Tax</th>
                                                <th style="width: 8%; font-size: 0.65rem; padding: 0.4rem 0.3rem;">Transport</th>
                                                <th style="width: 9%; font-size: 0.65rem; padding: 0.4rem 0.3rem;">Local Unit Cost</th>
                                                <th style="width: 7%; font-size: 0.65rem; padding: 0.4rem 0.3rem;">Sale Price</th>
                                                <th style="width: 7%; font-size: 0.65rem; padding: 0.4rem 0.3rem;">Outlet Price</th>
                                                <th style="width: 3%; font-size: 0.65rem; padding: 0.4rem 0.3rem;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Items will be appended here -->
                                        </tbody>
                                        <tfoot>
                                        </tfoot>
                                    </table>
                                </div>

                                {{-- Section 3: Payment Summary --}}
                                <div class="section-title mb-3 mt-3">
                                    <span class="font-weight-bold" style="color: #2563eb; font-size: 0.95rem;">
                                        <i class="fas fa-calculator mr-2"></i>Payment Summary
                                    </span>
                                    <hr style="border-top: 2px solid #e2e8f0; opacity: 0.5; margin-top: 0.3rem;">
                                </div>

                                <div class="row justify-content-end">
                                    <div class="col-12 col-md-6 col-lg-5">
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-6 col-form-label text-right font-weight-bold" style="font-size: 0.85rem;">Product Total (Vendor):</label>
                                            <div class="col-sm-6">
                                                <div class="form-control-plaintext font-weight-bold" style="color: #2563eb; font-size: 0.9rem;" id="vendor_grand_total">0.00</div>
                                            </div>
                                        </div>
                                        <hr style="border-top: 1px solid #e2e8f0;">
                                        <div class="form-group row">
                                            <label class="col-sm-6 col-form-label text-right h5 mb-0 font-weight-bold" style="font-size: 0.95rem;">Grand Total (System):</label>
                                            <div class="col-sm-6">
                                                <div class="h4 mb-0 font-weight-bold" style="color: #2563eb; font-size: 1.2rem;" id="grand_total_display">{{ $settings->currency_icon }}0.00</div>
                                                <input type="hidden" name="total_amount" id="total_amount_hidden">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit Buttons --}}
                                <div class="row mt-3 pt-3 border-top">
                                    <div class="col-12">
                                        <div class="d-flex flex-column flex-sm-row justify-content-sm-end" style="gap: 15px;">
                                            <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary px-4 order-2 order-sm-1" 
                                               style="border-radius: 10px; min-height: 40px; font-size: 0.85rem; width: 100%; width: auto;">
                                                <i class="fas fa-times mr-1"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn px-5 shadow-sm order-1 order-sm-2" 
                                                    style="background: #2563eb; border: none; border-radius: 10px; min-height: 40px; font-size: 0.85rem; width: 100%; width: auto; color: #ffffff;">
                                                <i class="fas fa-check-circle mr-2"></i> Confirm & Save
                                            </button>
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

    {{-- Import Modal --}}
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="border-bottom: 2px solid #f0f0f0; padding: 1rem 1.5rem;">
                    <h5 class="modal-title" id="importModalLabel" style="font-size: 1rem;">
                        <i class="fas fa-file-import mr-2" style="color: #2563eb;"></i>
                        Import from Order Place
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">Select Pending Order Place</label>
                        <select class="form-control select2" id="booking_select" style="width: 100%; height: 44px; font-size: 0.9rem; border-radius: 10px; border: 2px solid #e2e8f0;">
                            <option value="">Manual / None</option>
                            @foreach ($bookings as $booking)
                                <option value="{{ $booking->id }}">#{{ $booking->booking_no }} | {{ $booking->vendor->shop_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted mt-2 d-block" style="font-size: 0.75rem;">Selecting a booking will auto-import items, vendor, and shipping method.</small>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 2px solid #f0f0f0; padding: 1rem 1.5rem;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 10px; min-height: 40px; font-size: 0.85rem;">
                        <i class="fas fa-times mr-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
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

@push('styles')
<style>
    /* =============================================
       CREATE ORDER RECEIVE - FIXED DESIGN
       ============================================= */

    .border-dashed {
        border-style: dashed !important;
        border-color: #d1d3e2 !important;
    }

    .border-dashed:hover {
        background: #f8f9fc !important;
        border-color: #2563eb !important;
        color: #2563eb !important;
    }

    .form-group label {
        font-size: 0.8rem !important;
        margin-bottom: 0.4rem !important;
        letter-spacing: 0.3px;
    }

    .form-control {
        border-radius: 10px !important;
        border: 2px solid #e2e8f0;
        padding: 0.5rem 0.8rem;
        transition: all 0.3s ease;
        font-size: 0.9rem !important;
        background: #fafbfc;
        height: 44px !important;
        width: 100% !important;
    }

    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
        background: #ffffff;
    }

    .form-control-sm {
        height: 34px !important;
        font-size: 0.8rem !important;
        padding: 0.2rem 0.4rem !important;
    }

    .form-control-plaintext {
        font-size: 0.9rem !important;
        font-weight: 700;
        padding: 0.2rem 0;
    }

    .section-title span {
        font-size: 0.95rem !important;
        letter-spacing: 0.5px;
    }

    .section-title hr {
        border-top: 2px solid #e2e8f0;
        opacity: 0.5;
        margin-top: 0.3rem;
    }

    .table th {
        font-size: 0.65rem !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: #f8f9fc !important;
        color: #1a1a2e !important;
        font-weight: 700 !important;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 0.4rem 0.3rem !important;
    }

    .table td {
        vertical-align: middle !important;
        font-size: 0.8rem !important;
        padding: 0.4rem 0.3rem !important;
    }

    .btn {
        border-radius: 10px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
        min-height: 40px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 1.2rem !important;
        font-size: 0.85rem !important;
    }

    .btn-sm {
        min-height: 36px !important;
        font-size: 0.8rem !important;
        padding: 0 0.8rem !important;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    .btn:active {
        transform: scale(0.97);
    }

    .btn-primary {
        background: #2563eb !important;
        border: none !important;
        color: #ffffff !important;
    }

    .btn-primary:hover {
        background: #1d4ed8 !important;
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35) !important;
    }

    .btn-outline-primary {
        color: #2563eb !important;
        border-color: #2563eb !important;
        background: transparent !important;
    }

    .btn-outline-primary:hover {
        background: #2563eb !important;
        color: #fff !important;
    }

    .btn-outline-secondary {
        color: #858796 !important;
        border-color: #858796 !important;
        background: transparent !important;
    }

    .btn-outline-secondary:hover {
        background: #858796 !important;
        color: #fff !important;
        border-color: #858796 !important;
    }

    .btn-outline-danger {
        color: #e74a3b !important;
        border-color: #e74a3b !important;
        background: transparent !important;
    }

    .btn-outline-danger:hover {
        background: #e74a3b !important;
        color: #fff !important;
    }

    .card {
        border-radius: 16px !important;
        overflow: hidden !important;
    }

    .card-header:first-child {
        border-radius: 16px 16px 0 0 !important;
    }

    .card-footer:last-child {
        border-radius: 0 0 16px 16px !important;
    }

    .modal-content {
        border-radius: 16px !important;
        border: none !important;
    }

    .modal-header {
        border-bottom: 2px solid #f0f0f0 !important;
    }

    .modal-footer {
        border-top: 2px solid #f0f0f0 !important;
    }

    /* Select2 */
    .select2-container--default .select2-selection--single {
        border-radius: 10px !important;
        border: 2px solid #e2e8f0 !important;
        height: 44px !important;
        padding: 0 12px;
        background: #fafbfc !important;
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        font-size: 0.9rem;
        color: #495057;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
    }

    .select2-dropdown {
        border-radius: 10px !important;
        border-color: #e2e8f0 !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: #2563eb !important;
    }

    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    }

    /* =============================================
       RESPONSIVE BREAKPOINTS
       ============================================= */

    @media (max-width: 575.98px) {
        .section-header {
            padding: 12px 15px !important;
        }
        .section-header h1 {
            font-size: 1rem !important;
        }
        .card-body {
            padding: 15px !important;
        }
        .card-header {
            padding: 10px 15px !important;
        }
        .card-header h5 {
            font-size: 0.95rem !important;
        }
        .form-group {
            margin-bottom: 1rem !important;
        }
        .form-control {
            height: 40px !important;
            font-size: 0.85rem !important;
        }
        .form-control-sm {
            height: 30px !important;
            font-size: 0.75rem !important;
        }
        .btn {
            font-size: 0.8rem !important;
            min-height: 38px !important;
            padding: 0 0.8rem !important;
            width: 100% !important;
        }
        .btn-sm {
            min-height: 34px !important;
            font-size: 0.75rem !important;
            padding: 0 0.6rem !important;
        }
        .table td {
            font-size: 0.7rem !important;
            padding: 0.3rem !important;
        }
        .table th {
            font-size: 0.55rem !important;
            padding: 0.3rem !important;
        }
        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
        }
        .section-header .btn {
            width: 100% !important;
            margin-left: 0 !important;
        }
        .section-header .breadcrumb {
            font-size: 0.65rem !important;
            width: 100% !important;
        }
        .card-header .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        .card-header .card-header-action {
            margin-top: 10px !important;
            width: 100% !important;
        }
        .card-header .card-header-action .btn {
            width: 100% !important;
        }
        .card-header .ml-auto {
            margin-left: 0 !important;
            margin-top: 8px !important;
            width: 100% !important;
        }
        .text-right {
            text-align: center !important;
        }
        .text-right .btn {
            width: 100% !important;
            margin-bottom: 8px !important;
        }
        .select2-container--default .select2-selection--single {
            height: 40px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 34px !important;
            font-size: 0.85rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }
        .modal-dialog {
            margin: 10px !important;
        }
        .modal-content {
            border-radius: 12px !important;
        }
        .modal-header {
            padding: 0.8rem 1rem !important;
        }
        .modal-footer {
            padding: 0.8rem 1rem !important;
        }
        .modal-body {
            padding: 0.8rem 1rem !important;
        }
        .modal-title {
            font-size: 0.9rem !important;
        }

        /* Mobile Button Fix - Increased gap */
        .d-flex.flex-column.flex-sm-row {
            gap: 12px !important;
        }
        .d-flex.flex-column.flex-sm-row .btn {
            width: 100% !important;
            justify-content: center !important;
        }
        .d-flex.flex-column.flex-sm-row .order-1,
        .d-flex.flex-column.flex-sm-row .order-2 {
            order: unset !important;
        }

        .section-header .ml-auto .section-header-breadcrumb {
            width: 100% !important;
        }
        .section-header .ml-auto .btn {
            width: 100% !important;
            margin-left: 0 !important;
        }

        .table-responsive {
            margin: 0 -10px !important;
            padding: 0 10px !important;
            width: calc(100% + 20px) !important;
        }

        .row .col-12 {
            padding: 0 5px !important;
        }
    }

    @media (min-width: 576px) and (max-width: 767.98px) {
        .card-body {
            padding: 20px !important;
        }
        .form-control {
            height: 42px !important;
        }
        .btn {
            min-height: 40px !important;
            font-size: 0.85rem !important;
        }
        .section-header .ml-auto {
            flex-wrap: wrap !important;
            gap: 5px !important;
        }
        .card-header .d-flex {
            flex-wrap: wrap !important;
        }
        .d-flex.flex-column.flex-sm-row .btn {
            width: auto !important;
        }
        .d-flex.flex-column.flex-sm-row {
            gap: 12px !important;
        }
        .select2-container--default .select2-selection--single {
            height: 42px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
        }
    }

    @media (max-width: 767.98px) {
        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
        }
        .card-header .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        .card-header .ml-auto {
            margin-left: 0 !important;
            margin-top: 8px !important;
            width: 100% !important;
        }
        .section-header .breadcrumb {
            font-size: 0.7rem !important;
        }
    }

    @media (min-width: 768px) and (max-width: 991.98px) {
        .card-body {
            padding: 25px !important;
        }
        .form-control {
            height: 44px !important;
        }
        .btn {
            min-height: 44px !important;
        }
        .d-flex.flex-column.flex-sm-row .btn {
            width: auto !important;
        }
        .d-flex.flex-column.flex-sm-row {
            gap: 15px !important;
        }
        .select2-container--default .select2-selection--single {
            height: 44px !important;
        }
    }

    @media (min-width: 992px) {
        .card-body {
            padding: 30px !important;
        }
        .form-control {
            height: 44px !important;
        }
        .d-flex.flex-column.flex-sm-row .btn {
            width: auto !important;
        }
        .d-flex.flex-column.flex-sm-row {
            gap: 15px !important;
        }
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeInUp 0.4s ease-out;
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #2563eb;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #1d4ed8;
    }

    /* Validation */
    .invalid-feedback {
        font-size: 0.75rem !important;
        font-weight: 500;
        margin-top: 0.2rem;
    }

    .was-validated .form-control:valid,
    .form-control.is-valid {
        border-color: #1cc88a !important;
    }

    .was-validated .form-control:invalid,
    .form-control.is-invalid {
        border-color: #e74a3b !important;
    }

    .text-danger {
        font-weight: 700 !important;
        font-size: 1rem;
    }

    .form-group label i {
        width: 18px !important;
        text-align: center !important;
    }

    /* Border Top */
    .border-top {
        border-top: 2px solid #e2e8f0 !important;
    }

    /* Focus glow effect */
    .form-control:focus {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    }

    /* Modal backdrop */
    .modal-backdrop {
        background: rgba(26, 26, 46, 0.6) !important;
    }

    /* Add Row Button */
    .btn-block {
        border-radius: 0 0 16px 16px !important;
    }

    .btn-block:hover {
        background: #f8f9fc !important;
    }
</style>
@endpush

@push('scripts')
<script>
    const products = @json($products);
    const pricingRules = @json($pricingRules ?? []);
    const selectedIds = @json($selectedIds ?? []);
    let selectedPricingRuleId = {{ $defaultPricingRuleId ?? 'null' }};
    let rowCount = 0;
    let currentVendorRate = 1;
    let currentVendorIcon = '{{ $settings->currency_icon }}';
    let currentVendorName = '{{ $settings->currency_name }}';
    const systemIcon = '{{ $settings->currency_icon }}';

    $(document).ready(function() {
        if (selectedIds && selectedIds.length > 0) {
            $('#product_table tbody').empty();
            rowCount = 0;
            
            selectedIds.forEach((productId, index) => {
                let product = products.find(p => p.id == productId);
                if (product) {
                    appendItemRow(product, null, '', 1);
                }
            });
            
            setTimeout(() => {
                toastr.success(selectedIds.length + ' product(s) loaded from Low Stock Alert.');
            }, 500);
        }

        $('#pricing_rule_select').on('change', function() {
            const val = $(this).val();
            selectedPricingRuleId = val ? parseInt(val) : null;
            applyPricingToAllRows();
        });
        
        $('#importModal').on('shown.bs.modal', function () {
            $('#booking_select').select2({
                dropdownParent: $('#importModal'),
                width: '100%'
            });
        });
        
        $('#booking_select').on('change', function() {
            let bookingId = $(this).val();
            $('#product_table tbody').empty();
            rowCount = 0;
            
            if(bookingId) {
                $('#booking_id_hidden').val(bookingId); 
                $.ajax({
                    url: "{{ route('admin.purchases.get-booking-details') }}",
                    method: 'GET',
                    data: { id: bookingId },
                    success: function(bookings) {
                        if(bookings.length > 0 && bookings[0].vendor_id) {
                            $('#vendor_select').val(bookings[0].vendor_id).trigger('change');
                        }
                        if(bookings.length > 0 && bookings[0].shipping_method) {
                            $('#shipping_method_select').val(bookings[0].shipping_method);
                        }
                        bookings.forEach(booking => {
                            addBookingRow(booking);
                        });
                        toastr.success(bookings.length + ' item(s) imported from Order Place.', 'Loaded');
                        $('#importModal').modal('hide');
                    }
                });
                $('#booking_id_hidden').val('');
            }
        });

        $('#vendor_select').on('change', function() {
            let vendorId = $(this).val();
            if (vendorId) {
                $.ajax({
                    url: "{{ route('admin.vendor.get-details') }}",
                    method: 'GET',
                    data: { id: vendorId },
                    success: function(data) {
                        currentVendorRate = data.currency_rate;
                        currentVendorIcon = data.currency_icon;
                        currentVendorName = data.currency_name;
                        updateCurrencyMetadata();
                        recalculateAllRows();
                    }
                });
            } else {
                currentVendorRate = 1;
                currentVendorIcon = '{{ $settings->currency_icon }}';
                currentVendorName = '{{ $settings->currency_name }}';
                updateCurrencyMetadata();
                recalculateAllRows();
            }
        });

        // $('#add_row_btn').on('click', function() { addRow(); });
        
        $(document).on('click', '.remove_row', function() {
            $(this).closest('tr').remove();
            calculateGrandTotal();
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
                                    <td><input type="number" class="form-control form-control-sm variant_qty_input" data-variant-id="${v.id}" data-variant-name="${v.name}" data-stock="${v.qty || 0}" step="0.01" min="0"></td>
                                </tr>`;
                            });
                            $('#modal_variants_body').html(tbody);
                            $('#bulkVariantModal').modal('show');
                        } else {
                            // Add single row without variant
                            appendItemRow(product, null, '', 1, response.product.qty || 0);
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
                    let stock = $(this).data('stock') || 0;
                    appendItemRow(product, variantId, variantName, qty, stock);
                    added = true;
                }
            });

            if (added) {
                $('#bulkVariantModal').modal('hide');
            } else {
                toastr.warning('Please enter quantity for at least one variant.');
            }
        });

        $(document).on('input', '.qty, .unit_cost', function() {
            let row = $(this).closest('tr');
            if ($(this).hasClass('unit_cost')) {
                let vendorCost = parseFloat($(this).val()) || 0;
                row.find('.raw_material_cost').val((vendorCost * currentVendorRate).toFixed(2));
                if (vendorCost === 0) {
                    let existingTax = parseFloat(row.find('.tax_cost').val()) || 0;
                    let existingTransport = parseFloat(row.find('.transport_cost').val()) || 0;
                    if (existingTax < 0.01) row.find('.tax_cost').val('0.00');
                    if (existingTransport < 0.01) row.find('.transport_cost').val('0.00');
                }
            }
            calculateRowTotal(row);
            calculateLocalUnitCost(row);
            applyPricingToRow(row);
        });

        $(document).on('input', '.raw_material_cost, .tax_cost, .transport_cost', function() {
            let row = $(this).closest('tr');
            calculateLocalUnitCost(row);
            calculateRowTotal(row);
            applyPricingToRow(row);
        });
    });

    function updateCurrencyMetadata() {
        $('#vendor_unit_cost_header').text('Unit Cost (' + currentVendorName + ')');
        $('#vendor_subtotal_header').text('Total (' + currentVendorName + ')');
        $('#current_rate_display').text(currentVendorRate);
    }

    function addBookingRow(booking) {
        let product = booking.product;
        let variantHtml = '';
        let variantInput = '';
        let hasVariants = false;
        
        if(booking.variant_info) {
            variantInput = JSON.stringify(booking.variant_info);
            let variants = booking.variant_info['variant'] ? {[booking.variant_info['variant']]: booking.qty} : booking.variant_info;
            variantHtml += '<div class="mt-1 bg-light rounded p-1" style="font-size: 10px;">';
            for (const [key, qty] of Object.entries(variants)) {
                hasVariants = true;
                let cleanKey = key.replace(/Color:\s*/gi, '').replace(/Size:\s*/gi, '').replace(/\s*-\s*/g, ' ').trim();
                variantHtml += `
                    <div class="d-flex justify-content-between align-items-center mb-1 last:mb-0">
                        <span class="text-dark font-weight-500" style="font-size: 10px;">${cleanKey}</span>
                        <input type="number" class="form-control form-control-sm variant-qty-input p-0 text-center" 
                               data-key="${cleanKey}" value="${qty}" min="0" 
                               style="height: 18px; width: 45px; font-size: 10px; border: 1px solid #ced4da;">
                    </div>`;
            }
            variantHtml += '</div>';
        }
        if(!hasVariants) { variantHtml = ''; variantInput = ''; }

        let imageHtml = product.thumb_image 
            ? `<img src="{{ asset('storage') }}/${product.thumb_image}" class="rounded" style="width: 35px; height: 35px; object-fit: cover;">`
            : `<div class="bg-light rounded d-flex align-items-center justify-content-center text-muted small" style="width: 35px; height: 35px;"><i class="fas fa-box"></i></div>`;

        let productInfo = `<strong style="font-size: 0.8rem;">${product.name}</strong>`;
        if(product.product_number) productInfo += `<br><small class="text-muted" style="font-size: 0.65rem;">Item #: ${product.product_number}</small>`;

        if(booking.vendor && booking.vendor.currency_rate) {
            currentVendorRate = booking.vendor.currency_rate;
            currentVendorIcon = booking.vendor.currency_icon;
            currentVendorName = booking.vendor.currency_name;
            updateCurrencyMetadata();
        }
        
        let html = `
            <tr class="main-row">
                <td class="align-middle text-center">${imageHtml}</td>
                <td class="align-middle product_select_col">
                    <select class="form-control form-control-sm product_select select2" name="items[${rowCount}][product_id]" required>
                        <option value="${product.id}" selected>${product.name}</option>
                        ${products.map(p => p.id != product.id ? `<option value="${p.id}">${p.name}</option>` : '').join('')}
                    </select>
                    <div class="product-info mt-1" style="font-size: 11px; line-height: 1.3; color: #666;">${productInfo}</div>
                    <div class="variant-container">${variantHtml}</div>
                    <input type="hidden" class="variant_info_hidden" name="items[${rowCount}][variant_info]" value='${variantInput}'>
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm unit_cost text-center" name="items[${rowCount}][unit_cost]" step="any" value="${booking.unit_price}" required style="font-size: 0.8rem; height: 32px;">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm qty text-center" name="items[${rowCount}][qty]" value="${booking.qty}" min="1" required style="font-weight: bold; font-size: 0.8rem; height: 32px;">
                </td>
                <td class="align-middle text-right">
                    <input type="text" class="form-control-plaintext form-control-sm subtotal mb-0 text-dark text-right font-weight-bold pr-2" readonly value="0.00" style="font-size: 0.8rem;">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm raw_material_cost text-center" name="items[${rowCount}][raw_material_cost]" value="${product.raw_material_cost || 0}" step="any" style="font-size: 0.8rem; height: 32px;">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm tax_cost text-center" name="items[${rowCount}][tax_cost]" value="${product.tax || 0}" step="any" style="font-size: 0.8rem; height: 32px;">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm transport_cost text-center" name="items[${rowCount}][transport_cost]" value="${product.transport_cost || 0}" step="any" style="font-size: 0.8rem; height: 32px;">
                </td>
                <td class="align-middle text-center">
                    <div class="form-control-plaintext form-control-sm local_unit_cost mb-0 text-primary text-center font-weight-bold pr-2" style="font-size: 0.8rem;">0.00</div>
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm sale_price text-center" name="items[${rowCount}][sale_price]" value="${product.price || 0}" step="any" style="font-size: 0.8rem; height: 32px;">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm outlet_price text-center" name="items[${rowCount}][outlet_price]" value="${product.outlet_price || 0}" step="any" style="font-size: 0.8rem; height: 32px;">
                </td>
                <td class="align-middle text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove_row" style="padding: 1px 5px; font-size: 0.7rem; min-height: 28px;"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        $('#product_table tbody').append(html);
        $('.select2').select2({ width: '100%' });
        let newRow = $('#product_table tbody tr').last();
        calculateLocalUnitCost(newRow);
        calculateRowTotal(newRow);
        applyPricingToRow(newRow);
        rowCount++;
    }

    function appendItemRow(product, variantId, variantName, qty, stock = 0) {
        let imageHtml = product.thumb_image 
            ? `<img src="{{ asset('storage') }}/${product.thumb_image}" class="rounded" style="width: 35px; height: 35px; object-fit: cover;">`
            : `<div class="bg-light rounded d-flex align-items-center justify-content-center text-muted small" style="width: 35px; height: 35px;"><i class="fas fa-box"></i></div>`;

        let productInfo = `<strong style="font-size: 0.8rem;">${product.name}</strong>`;
        if(product.product_number) productInfo += `<br><small class="text-muted" style="font-size: 0.65rem;">Item #: ${product.product_number}</small>`;
        if(product.category && product.category.name) productInfo += `<br><small class="text-muted" style="font-size: 0.65rem;">Category: ${product.category.name}</small>`;
        if(variantName) productInfo += `<br><span class="badge badge-info mt-1">${variantName}</span>`;

        let html = `
            <tr class="main-row">
                <td class="align-middle text-center">${imageHtml}</td>
                <td class="align-middle product_select_col">
                    <input type="hidden" name="items[${rowCount}][product_id]" value="${product.id}">
                    ${variantId ? `<input type="hidden" name="items[${rowCount}][variant_id]" value="${variantId}">` : ''}
                    <div class="product-info mt-1" style="font-size: 11px; line-height: 1.4; color: #666;">${productInfo}</div>
                </td>
                <td class="align-middle text-center">
                    <span class="badge badge-info px-2 py-1" style="font-size: 0.75rem;">${stock}</span>
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm unit_cost text-center" name="items[${rowCount}][unit_cost]" value="${product.purchase_price || 0}" step="any" required placeholder="0.00" style="font-size: 0.8rem; height: 32px;">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm qty text-center" name="items[${rowCount}][qty]" value="${qty}" min="1" required style="font-weight: bold; font-size: 0.8rem; height: 32px;">
                </td>
                <td class="align-middle text-right">
                    <input type="text" class="form-control-plaintext form-control-sm subtotal mb-0 text-dark text-right font-weight-bold pr-2" readonly value="0.00" style="font-size: 0.8rem;">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm raw_material_cost text-center" name="items[${rowCount}][raw_material_cost]" value="${product.raw_material_cost || 0}" step="any" style="font-size: 0.8rem; height: 32px;">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm tax_cost text-center" name="items[${rowCount}][tax_cost]" value="${product.tax || 0}" step="any" style="font-size: 0.8rem; height: 32px;">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm transport_cost text-center" name="items[${rowCount}][transport_cost]" value="${product.transport_cost || 0}" step="any" style="font-size: 0.8rem; height: 32px;">
                </td>
                <td class="align-middle text-center">
                    <div class="form-control-plaintext form-control-sm local_unit_cost mb-0 text-primary text-center font-weight-bold pr-2" style="font-size: 0.8rem;">0.00</div>
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm sale_price text-center" name="items[${rowCount}][sale_price]" value="${product.price || 0}" step="any" style="font-size: 0.8rem; height: 32px;">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm outlet_price text-center" name="items[${rowCount}][outlet_price]" value="${product.outlet_price || 0}" step="any" style="font-size: 0.8rem; height: 32px;">
                </td>
                <td class="align-middle text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove_row" style="padding: 1px 5px; font-size: 0.7rem; min-height: 28px;"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        $('#product_table tbody').append(html);
        let newRow = $('#product_table tbody tr').last();
        calculateLocalUnitCost(newRow);
        calculateRowTotal(newRow);
        applyPricingToRow(newRow);
        rowCount++;
    }

    function getSelectedPricingRule() {
        if (!selectedPricingRuleId) return null;
        return pricingRules.find(r => parseInt(r.id) === parseInt(selectedPricingRuleId)) || null;
    }

    function getLocalUnitCostNumber(row) {
        let rawMaterial = parseFloat(row.find('.raw_material_cost').val()) || 0;
        let tax = parseFloat(row.find('.tax_cost').val()) || 0;
        let transport = parseFloat(row.find('.transport_cost').val()) || 0;
        return rawMaterial + tax + transport;
    }

    function applyPricingToRow(row) {
        const rule = getSelectedPricingRule();
        if (!rule) return;
        const unitCost = getLocalUnitCostNumber(row);
        const sale = unitCost * (parseFloat(rule.sale_multiplier) || 0);
        const outlet = unitCost * (parseFloat(rule.outlet_multiplier) || 0);
        row.find('.sale_price').val(sale.toFixed(2));
        row.find('.outlet_price').val(outlet.toFixed(2));
    }

    function applyPricingToAllRows() {
        $('#product_table tbody tr').each(function() {
            applyPricingToRow($(this));
        });
    }

    function calculateLocalUnitCost(row) {
        let rawMaterial = parseFloat(row.find('.raw_material_cost').val()) || 0;
        let tax = parseFloat(row.find('.tax_cost').val()) || 0;
        let transport = parseFloat(row.find('.transport_cost').val()) || 0;
        let totalLocalCost = rawMaterial + tax + transport;
        row.find('.local_unit_cost').text(systemIcon + totalLocalCost.toFixed(2));
    }

    function calculateRowTotal(row) {
        let qty = parseFloat(row.find('.qty').val()) || 0;
        let vendorCost = parseFloat(row.find('.unit_cost').val()) || 0;
        let vendorTotal = qty * vendorCost;
        row.find('.subtotal').val(vendorTotal.toFixed(2));
        calculateLocalUnitCost(row);
        calculateGrandTotal();
    }

    function recalculateAllRows() {
        $('#product_table tbody tr').each(function() { calculateRowTotal($(this)); });
    }

    function calculateGrandTotal() {
        let vendorTotal = 0;
        let systemTotal = 0;
        
        $('#product_table tbody tr').each(function() {
            let row = $(this);
            let qty = parseFloat(row.find('.qty').val()) || 0;
            let vendorCost = parseFloat(row.find('.unit_cost').val()) || 0;
            let raw = parseFloat(row.find('.raw_material_cost').val()) || 0;
            let tax = parseFloat(row.find('.tax_cost').val()) || 0;
            let transport = parseFloat(row.find('.transport_cost').val()) || 0;
            let localUnitCost = raw + tax + transport;
            vendorTotal += qty * vendorCost;
            systemTotal += localUnitCost * qty;
        });

        $('#vendor_grand_total').text(currentVendorIcon + vendorTotal.toFixed(2));
        $('#grand_total_display').text(systemIcon + systemTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#total_amount_hidden').val(systemTotal.toFixed(2)); 
    }
</script>
@endpush