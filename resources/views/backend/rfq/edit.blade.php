@extends('backend.layouts.master')
@section('title', 'Edit RFQ - ' . $rfq->rfq_no)

@push('css')
<style>
    .vendor-picker-container {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 10px;
        padding: 16px;
        max-height: 220px;
        overflow-y: auto;
    }
    .vendor-checkbox-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 14px;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        display: flex;
        align-items: center;
        margin-bottom: 8px;
    }
    .vendor-checkbox-card:hover {
        border-color: #6777ef;
        background: #f0f3ff;
    }
    .vendor-checkbox-card.selected {
        border-color: #6777ef;
        background: #eef2ff;
        box-shadow: 0 2px 6px rgba(103,119,239,0.15);
    }
    .vendor-checkbox-card input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #6777ef;
        cursor: pointer;
    }
    .product-thumb-square {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        object-fit: cover;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
    }
    .table-edit-items th {
        background-color: #f8fafc !important;
        color: #34395e !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 14px 16px !important;
    }
    .table-edit-items td {
        padding: 12px 16px !important;
        vertical-align: middle !important;
    }
</style>
@endpush

@section('content')
    <section class="section">
        <!-- Native Stisla Page Header -->
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.rfqs.show', $rfq->id) }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Edit RFQ: {{ $rfq->rfq_no }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.rfqs.index') }}">Procurement</a></div>
                <div class="breadcrumb-item">Edit RFQ</div>
            </div>
        </div>

        <div class="section-body">
            <form action="{{ route('admin.rfqs.update', $rfq->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Top Primary Attributes & Interactive Vendor Selection Card -->
                <div class="card border shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 text-dark font-weight-bold">
                            <i class="fas fa-sliders-h text-primary mr-2"></i> RFQ Primary Information & Invited Vendors
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column: Primary Fields -->
                            <div class="col-lg-5 col-md-12 border-right pr-lg-4 mb-3 mb-lg-0">
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark"><i class="fas fa-hashtag text-muted mr-1"></i> RFQ Number *</label>
                                    <input type="text" name="rfq_no" class="form-control font-weight-bold bg-light" value="{{ $rfq->rfq_no }}" readonly required>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold text-dark"><i class="fas fa-calendar-alt text-warning mr-1"></i> Due Date / Deadline *</label>
                                    <input type="date" name="due_date" class="form-control font-weight-bold" value="{{ $rfq->due_date ? \Carbon\Carbon::parse($rfq->due_date)->format('Y-m-d') : \Carbon\Carbon::now()->addDays(7)->format('Y-m-d') }}" required>
                                </div>

                                <div class="alert alert-light border mb-0">
                                    <small class="text-muted d-block"><i class="fas fa-info-circle text-info mr-1"></i> Select invited vendors on the right side. All selected vendors will receive RFQ updates.</small>
                                </div>
                            </div>

                            <!-- Right Column: Interactive Vendor Checkbox Picker -->
                            <div class="col-lg-7 col-md-12 pl-lg-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-store text-info mr-1"></i> Invited Suppliers / Vendors *
                                    </label>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-link p-0 mr-2" id="select_all_vendors">Select All</button>
                                        <button type="button" class="btn btn-sm btn-link text-muted p-0" id="deselect_all_vendors">Deselect All</button>
                                    </div>
                                </div>

                                @php
                                    $selectedVendorIds = $rfq->vendors->pluck('vendor_id')->toArray();
                                @endphp

                                <div class="vendor-picker-container">
                                    <div class="row">
                                        @foreach($vendors as $vendor)
                                            @php
                                                $isSelected = in_array($vendor->id, $selectedVendorIds);
                                            @endphp
                                            <div class="col-md-6">
                                                <label class="vendor-checkbox-card {{ $isSelected ? 'selected' : '' }}" for="vendor_chk_{{ $vendor->id }}">
                                                    <input type="checkbox" name="vendors[]" id="vendor_chk_{{ $vendor->id }}" value="{{ $vendor->id }}" class="mr-2 vendor-checkbox" {{ $isSelected ? 'checked' : '' }}>
                                                    <div class="text-truncate">
                                                        <span class="d-block font-weight-bold text-dark small text-truncate" title="{{ $vendor->shop_name }}">{{ $vendor->shop_name }}</span>
                                                        <small class="text-muted d-block text-truncate" style="font-size: 10px;">{{ $vendor->email ?? 'No Email' }}</small>
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Line Items Section Card -->
                <div class="card border shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 text-dark font-weight-bold">
                            <i class="fas fa-boxes text-primary mr-2"></i> RFQ Requested Line Items & Quantities
                        </h5>
                        <button type="button" class="btn btn-sm btn-success px-3 font-weight-bold" id="add_item_btn">
                            <i class="fas fa-plus mr-1"></i> Add Line Item
                        </button>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive p-3">
                            <table class="table table-hover table-striped table-edit-items mb-0 w-100" id="items_table">
                                <thead>
                                    <tr>
                                        <th style="width: 45%;">Product Description *</th>
                                        <th style="width: 30%;">Variant (Specification)</th>
                                        <th style="width: 15%; text-align: center;">Requested Qty *</th>
                                        <th style="width: 10%; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="items_body">
                                    @foreach($rfq->items as $index => $rfqItem)
                                        @php
                                            $selectedProduct = $products->where('id', $rfqItem->product_id)->first();
                                            $productVariants = $selectedProduct ? $selectedProduct->variants : collect();
                                        @endphp
                                        <tr class="item-row">
                                            <td>
                                                <select name="items[{{ $index }}][product_id]" class="form-control select2 product-select" required>
                                                    <option value="">-- Select Product --</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" 
                                                                data-variants="{{ json_encode($product->variants) }}"
                                                                {{ $product->id == $rfqItem->product_id ? 'selected' : '' }}>
                                                            {{ $product->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="items[{{ $index }}][variant_id]" class="form-control select2 variant-select">
                                                    <option value="">No Variant (Default)</option>
                                                    @foreach($productVariants as $var)
                                                        <option value="{{ $var->id }}" {{ $rfqItem->variant_id == $var->id ? 'selected' : '' }}>
                                                            {{ $var->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <input type="number" name="items[{{ $index }}][qty]" class="form-control text-center font-weight-bold" value="{{ $rfqItem->qty }}" min="0.01" step="any" required>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm remove-row" title="Remove Item"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sticky Footer Form Actions Bar -->
                <div class="card border shadow-sm">
                    <div class="card-body py-3 d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.rfqs.show', $rfq->id) }}" class="btn btn-secondary px-4 font-weight-bold">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success btn-lg px-5 font-weight-bold">
                            <i class="fas fa-save mr-2"></i> Save RFQ Changes
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let rowIndex = {{ $rfq->items->count() }};

        // Vendor checkbox card highlight
        $(document).on('change', '.vendor-checkbox', function() {
            if ($(this).is(':checked')) {
                $(this).closest('.vendor-checkbox-card').addClass('selected');
            } else {
                $(this).closest('.vendor-checkbox-card').removeClass('selected');
            }
        });

        // Select / Deselect All Vendors
        $('#select_all_vendors').on('click', function() {
            $('.vendor-checkbox').prop('checked', true).trigger('change');
        });
        $('#deselect_all_vendors').on('click', function() {
            $('.vendor-checkbox').prop('checked', false).trigger('change');
        });

        // Function to update variant dropdown when product changes
        function updateVariantDropdown(productSelectEl) {
            let selectedOption = productSelectEl.find('option:selected');
            let variantsData = selectedOption.data('variants');
            let variantSelect = productSelectEl.closest('tr').find('.variant-select');

            variantSelect.empty();
            variantSelect.append('<option value="">No Variant (Default)</option>');

            if (variantsData && Array.isArray(variantsData) && variantsData.length > 0) {
                variantsData.forEach(function(v) {
                    variantSelect.append(`<option value="${v.id}">${v.name}</option>`);
                });
            }
            variantSelect.trigger('change.select2');
        }

        // Trigger variant load on product change
        $(document).on('change', '.product-select', function() {
            updateVariantDropdown($(this));
        });

        // Add new item row
        $('#add_item_btn').on('click', function() {
            let productOptions = '<option value="">-- Select Product --</option>';
            @foreach($products as $product)
                productOptions += `<option value="{{ $product->id }}" data-variants='{{ json_encode($product->variants) }}'>{{ addslashes($product->name) }}</option>`;
            @endforeach

            let newRow = `
                <tr class="item-row">
                    <td>
                        <select name="items[${rowIndex}][product_id]" class="form-control select2 product-select" required>
                            ${productOptions}
                        </select>
                    </td>
                    <td>
                        <select name="items[${rowIndex}][variant_id]" class="form-control select2 variant-select">
                            <option value="">No Variant (Default)</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <input type="number" name="items[${rowIndex}][qty]" class="form-control text-center font-weight-bold" value="1.00" min="0.01" step="any" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-row" title="Remove Item"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;

            $('#items_body').append(newRow);
            $('.select2').select2();
            rowIndex++;
        });

        // Remove row
        $(document).on('click', '.remove-row', function() {
            if ($('#items_body tr').length > 1) {
                $(this).closest('tr').remove();
            } else {
                toastr.warning('RFQ must contain at least one line item.');
            }
        });
    });
</script>
@endpush
