@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Product</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Edit Product</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.products.index', request()->query()) }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="return_url" value="{{ route('admin.products.index', request()->query()) }}">
                                

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Name</label>
                                        <input type="text" class="form-control" name="name" value="{{ $product->name }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>item number </label>
                                        <input type="text" class="form-control" name="product_number" value="{{ $product->product_number }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Category</label>
                                        <select class="form-control main-category select2" name="category_id">
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                                <option {{ $category->id == $product->category_id ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Sub Category</label>
                                        <select class="form-control sub-category select2" name="sub_category_id">
                                            <option value="">Select Sub Category</option>
                                            @foreach ($subCategories ?? [] as $subCategory)
                                                <option {{ $subCategory->id == $product->sub_category_id ? 'selected' : '' }} value="{{ $subCategory->id }}">{{ $subCategory->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Child Category</label>
                                        <select class="form-control child-category select2" name="child_category_id">
                                            <option value="">Select Child Category</option>
                                            @foreach ($childCategories ?? [] as $childCategory)
                                                <option {{ $childCategory->id == $product->child_category_id ? 'selected' : '' }} value="{{ $childCategory->id }}">{{ $childCategory->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Brand</label>
                                        <select class="form-control select2" name="brand_id">
                                            <option value="">Select Brand</option>
                                            @foreach ($brands as $brand)
                                                <option {{ $brand->id == $product->brand_id ? 'selected' : '' }} value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Unit</label>
                                        <select class="form-control select2" name="unit_id">
                                            <option value="">Select Unit</option>
                                            @foreach ($units as $unit)
                                                <option {{ $unit->id == $product->unit_id ? 'selected' : '' }} value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if(isset($vendors) && $vendors->count() > 0)
                                    <div class="form-group col-md-4">
                                        <label>Primary / Preferred Supplier <small class="text-muted">(Optional)</small></label>
                                        <select class="form-control select2" name="vendor_id">
                                            <option value="">Select Preferred Supplier</option>
                                            @foreach ($vendors as $vendor)
                                                <option {{ $vendor->id == $product->vendor_id ? 'selected' : '' }} value="{{ $vendor->id }}">{{ $vendor->shop_name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Used for automatic replenishment & draft PO generation.</small>
                                    </div>
                                    @endif
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label>Purchase Price</label>
                                        <input type="number" class="form-control" name="purchase_price" step="any"
                                            value="{{ $product->purchase_price }}">
                                    </div>
                                    
                                     <div class="form-group col-md-3">
                                        <label>Whole Sale Price</label>
                                        <input type="number" class="form-control" name="outlet_price" step="any"
                                            value="{{ $product->outlet_price ?? 0 }}">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Outlet/Customer Price</label>
                                        <input type="number" class="form-control" name="price" step="any"
                                            value="{{ $product->price }}">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Shelf / Storage Note <small class="text-muted">(Optional)</small></label>
                                        <input type="text" class="form-control" name="self_number" placeholder="e.g. Rack-A / General Note"
                                            value="{{ $product->self_number }}">
                                        <small class="form-text text-muted">General reference. Dynamic bins are managed in WMS.</small>
                                    </div>
                                </div>

                                <div class="row">
                                     <div class="form-group col-md-4">
                                         <label>Raw Material Cost</label>
                                         <input type="number" class="form-control" name="raw_material_cost" step="any"
                                             value="{{ old('raw_material_cost', $product->raw_material_cost ?? 0) }}">
                                     </div>
                                     <div class="form-group col-md-4">
                                         <label>Transport Cost</label>
                                         <input type="number" class="form-control" name="transport_cost" step="any"
                                             value="{{ old('transport_cost', $product->transport_cost ?? 0) }}">
                                     </div>
                                    <div class="form-group col-md-4">
                                        <label>Tax</label>
                                        <input type="number" class="form-control" name="tax" step="any"
                                            value="{{ old('tax', $product->tax ?? 0) }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label>Discount Type</label>
                                        <select class="form-control" name="discount_type">
                                            <option value="">No Discount</option>
                                            <option value="percent" {{ old('discount_type', $product->discount_type) === 'percent' ? 'selected' : '' }}>Percent (%)</option>
                                            <option value="flat" {{ old('discount_type', $product->discount_type) === 'flat' ? 'selected' : '' }}>Flat</option>
                                        </select>
                                        <small class="text-muted">Product-specific discount.</small>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Discount Value</label>
                                        <input type="number" class="form-control" name="discount" step="any"
                                            value="{{ old('discount', $product->discount ?? 0) }}">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Product VAT Type</label>
                                        <select class="form-control" name="vat_type">
                                            <option value="">Use Dynamic Default</option>
                                            <option value="percent" {{ old('vat_type', $product->vat_type) === 'percent' ? 'selected' : '' }}>Percent (%)</option>
                                            <option value="flat" {{ old('vat_type', $product->vat_type) === 'flat' ? 'selected' : '' }}>Flat</option>
                                        </select>
                                        <small class="text-muted">Keep empty to use global VAT.</small>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Product VAT Value</label>
                                        <input type="number" class="form-control" name="vat_value" step="any"
                                            value="{{ old('vat_value', $product->vat_value ?? 0) }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Status</label>
                                        <select class="form-control" name="status">
                                            <option {{ $product->status == 1 ? 'selected' : '' }} value="1">Active</option>
                                            <option {{ $product->status == 0 ? 'selected' : '' }} value="0">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4" id="product-stock-group">
                                        <label>Current Stock</label>
                                        <input type="text" class="form-control" name="current_stock" value="{{ $product->inventory_stock }}">
                                    </div>
                                     <div class="form-group col-md-4">
                                        <label>Minimum Order Quantity</label>
                                        <input type="number" class="form-control" name="minimum_order_qty" value="{{ old('minimum_order_qty', $product->minimum_order_qty ?? 1) }}">
                                        <small class="text-muted">Minimum quantity that can be ordered.</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Occasion Type</label>
                                        <select class="form-control select2" name="product_type_id">
                                            <option value="">Select Option</option>
                                            @foreach ($productTypes as $type)
                                                <option {{ $product->product_type_id == $type->id ? 'selected' : '' }} value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Product Type (Legacy)</label>
                                        <select class="form-control select2" name="product_type">
                                            <option value="">Select Option</option>
                                            <option {{ $product->product_type == 'new_arrival' ? 'selected' : '' }} value="new_arrival">New Arrival</option>
                                            <option {{ $product->product_type == 'upcoming' ? 'selected' : '' }} value="upcoming">Upcoming</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Custom Label</label>
                                        <input type="text" class="form-control" name="custom_label" 
                                            placeholder="e.g. Best Seller, Hot, New" value="{{ $product->custom_label }}">
                                        <small class="form-text text-muted">e.g. Best Seller, Hot, New, Sale</small>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Barcode (Optional)</label>
                                        <input type="text" class="form-control" name="barcode" id="barcode_input" placeholder="Scan or enter barcode" value="{{ $product->barcode }}">
                                        <small class="form-text text-muted">Compatible with barcode scanners.</small>
                                    </div>
                                </div>



                                <div class="form-group">
                                    <label>Long Description</label>
                                    <textarea name="long_description" class="summernote">{{ $product->long_description }}</textarea>
                                </div>

                                <div class="card border">
                                    <div class="card-header">
                                        <h4>Product Variants</h4>
                                        <div class="card-header-action">
                                            <button type="button" class="btn btn-success" id="add-variant"><i class="fas fa-plus"></i> Add Variant</button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered table-responsive">
                                            <thead>
                                                <tr>
                                                    <th width="20%" class="variant-color-col">Color</th>
                                                    <th width="20%" class="variant-size-col">Size</th>
                                                    <th width="15%">Current Stock</th>
                                                    <th width="15%">Whole Sale Price</th>
                                                    <th width="15%">Outlet/Customer Price</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="variant-list">
                                                @foreach ($product->variants as $index => $variant)
                                                    <tr id="variant-row-{{ $index }}">
                                                        <td class="variant-color-cell">
                                                            <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                                                            <select name="variants[{{ $index }}][color_id]" class="form-control">
                                                                <option value="">Select Color</option>
                                                                @foreach($colors as $color)
                                                                    <option value="{{ $color->id }}" {{ $variant->color_id == $color->id ? 'selected' : '' }}>{{ $color->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="variant-size-cell">
                                                            <select name="variants[{{ $index }}][size_id]" class="form-control">
                                                                <option value="">Select Size</option>
                                                                @foreach($sizes as $size)
                                                                    <option value="{{ $size->id }}" {{ $variant->size_id == $size->id ? 'selected' : '' }}>{{ $size->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control" name="variants[{{ $index }}][current_stock]" value="{{ $variant->inventory_stock }}">
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" class="form-control" name="variants[{{ $index }}][outlet_price]" value="{{ $variant->outlet_price ?? 0 }}" placeholder="0.00">
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" class="form-control" name="variants[{{ $index }}][price]" value="{{ $variant->price ?? 0 }}" placeholder="0.00">
                                                        </td>
                                                        <td><button type="button" class="btn btn-danger remove-variant" data-id="{{ $index }}"><i class="fas fa-trash"></i></button></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6 text-center">
                                    <label>Preview</label><br>
                                    <img src="{{ asset('storage/' . $product->thumb_image) }}" width="150px" alt="">
                                </div>
                                <div class="form-group col-md-6 center">
                                    <label>Thumbnail Image</label>
                                    <div id="image-preview" class="image-preview">
                                        <label for="image-upload" id="image-label">Choose File</label>
                                        <input type="file" name="image" id="image-upload" />
                                    </div>
                                </div>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary px-4">Update Product</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stock Movement, Inflows, Velocity & B2B Rules Card (Collapsible) --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0" style="border-radius: 14px; overflow: hidden; border: 1px solid #e2e8f0 !important;">
                        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between"
                             role="button"
                             data-toggle="collapse"
                             data-target="#collapseStockMovement"
                             aria-expanded="false"
                             aria-controls="collapseStockMovement"
                             id="headingStockMovement"
                             style="cursor: pointer; user-select: none; transition: all 0.2s ease;">
                            <div class="d-flex align-items-center">
                                <div class="mr-3" style="width: 42px; height: 42px; border-radius: 10px; background: rgba(103, 119, 239, 0.1); display: flex; align-items: center; justify-content: center; color: #6777ef; font-size: 19px;">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div>
                                    <h4 class="font-weight-bold text-dark mb-0" style="font-size: 16px; line-height: 1.3;">
                                        Stock Movement, Inflow History & Consumption Velocity
                                    </h4>
                                    <small class="text-muted" style="font-size: 12px;">Click to expand/minimize movement ledger, purchase history and customer rules</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center" style="gap: 10px;">
                                <span class="badge badge-light border text-dark font-weight-bold px-3 py-2" style="font-size: 12px; border-radius: 20px;">
                                    <i class="fas fa-boxes text-info mr-1"></i> Stock: <strong>{{ number_format($currentStock ?? 0) }}</strong>
                                </span>
                                @if(isset($velocity['classification']))
                                    <span class="badge {{ $velocity['classification']['badge'] }} px-3 py-2 font-weight-bold d-none d-md-inline-block" style="font-size: 11px; border-radius: 20px;">
                                        <i class="{{ $velocity['classification']['icon'] }} mr-1"></i> {{ $velocity['classification']['label'] }}
                                    </span>
                                @endif
                                <div class="stock-collapse-icon-btn d-flex align-items-center justify-content-center ml-1" 
                                     title="Expand / Minimize"
                                     style="width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; transition: all 0.25s ease;">
                                    <i class="fas fa-chevron-down" id="stock-collapse-chevron" style="font-size: 13px; transition: transform 0.3s ease;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="collapse" id="collapseStockMovement" aria-labelledby="headingStockMovement">
                            <div class="card-body p-4" id="stock-movement-content-container" style="background: #fafbfe !important;">
                                @include('backend.product.partials.stock_movement_modal')
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                #headingStockMovement:hover {
                    background-color: #f8fafc !important;
                }
                #headingStockMovement:hover .stock-collapse-icon-btn {
                    background-color: #e2e8f0 !important;
                    color: #1e293b !important;
                    border-color: #94a3b8 !important;
                }
                #headingStockMovement[aria-expanded="true"] #stock-collapse-chevron {
                    transform: rotate(180deg);
                }
            </style>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $.uploadPreview({
                input_field: "#image-upload",
                preview_box: "#image-preview",
                label_field: "#image-label",
                label_default: "Choose File",
                label_selected: "Change File",
                no_label: false,
                success_callback: null
            });

            // Get sub categories
            $('body').on('change', '.main-category', function(e) {
                let id = $(this).val();
                $.ajax({
                    method: 'GET',
                    url: "{{ route('admin.get-subCategories') }}",
                    data: {
                        id: id
                    },
                    success: function(data) {
                        $('.sub-category').html('<option value="">Select Sub Category</option>')
                        $('.child-category').html('<option value="">Select Child Category</option>')
                        $.each(data, function(i, item) {
                            $('.sub-category').append(`<option value="${item.id}">${item.name}</option>`)
                        })
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                })
            })

            // Get child categories
            $('body').on('change', '.sub-category', function(e) {
                let id = $(this).val();
                $.ajax({
                    method: 'GET',
                    url: "{{ route('admin.get-child-categories') }}",
                    data: {
                        id: id
                    },
                    success: function(data) {
                        $('.child-category').html('<option value="">Select Child Category</option>')
                        $.each(data, function(i, item) {
                            $('.child-category').append(`<option value="${item.id}">${item.name}</option>`)
                        })
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                })
            })

            // Variant Logic
            let variantCount = {{ count($product->variants) }};
            $('#add-variant').on('click', function(){
                let colorOptions = '<option value="">Select Color</option>';
                @foreach($colors as $color)
                    colorOptions += '<option value="{{ $color->id }}">{{ $color->name }}</option>';
                @endforeach

                let sizeOptions = '<option value="">Select Size</option>';
                @foreach($sizes as $size)
                    sizeOptions += '<option value="{{ $size->id }}">{{ $size->name }}</option>';
                @endforeach

                let html = `
                    <tr id="variant-row-${variantCount}">
                        <td class="variant-color-cell">
                            <select name="variants[${variantCount}][color_id]" class="form-control">
                                ${colorOptions}
                            </select>
                        </td>
                        <td class="variant-size-cell">
                            <select name="variants[${variantCount}][size_id]" class="form-control">
                                ${sizeOptions}
                            </select>
                        </td>
                        <td>
                             <input type="text" class="form-control" name="variants[${variantCount}][current_stock]" value="0">
                        </td>
                        <td>
                             <input type="number" step="0.01" class="form-control" name="variants[${variantCount}][outlet_price]" value="0" placeholder="0.00">
                        </td>
                        <td>
                            <input type="number" step="0.01" class="form-control" name="variants[${variantCount}][price]" value="0" placeholder="0.00">
                        </td>
                        <td><button type="button" class="btn btn-danger remove-variant" data-id="${variantCount}"><i class="fas fa-trash"></i></button></td>
                    </tr>
                `;
                $('#variant-list').append(html);
                variantCount++;
            });

            $(document).on('click', '.remove-variant', function(){
                let id = $(this).data('id');
                $('#variant-row-'+id).remove();
            });

            // Prevent form submit on barcode scan Enter
            $('#barcode_input').on('keypress', function(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    return false;
                }
            });

            // Toggle Custom Month / Date range box in Stock Movement Section
            $('body').on('click', '#btn-toggle-custom-filter', function() {
                $('#velocity-custom-filter-box').slideToggle(200);
            });

            // Handle Velocity Preset Radio Click
            $('body').on('change', 'input[name="velocity_preset"]', function() {
                let preset = $(this).val();
                if (preset === 'custom') {
                    $('#velocity-custom-filter-box').slideDown(200);
                    return;
                }
                $('#velocity-custom-filter-box').slideUp(200);
                recalculateVelocityMetrics({ preset: preset });
            });

            // Handle Custom Month/Year or Date Range Apply
            $('body').on('click', '#btn-apply-custom-velocity', function() {
                let month = $('#vel_filter_month').val();
                let year = $('#vel_filter_year').val();
                let start_date = $('#vel_filter_start').val();
                let end_date = $('#vel_filter_end').val();

                recalculateVelocityMetrics({
                    preset: 'custom',
                    month: month,
                    year: year,
                    start_date: start_date,
                    end_date: end_date
                });
            });

            function recalculateVelocityMetrics(params) {
                let productId = window.currentStockMovementProductId || {{ $product->id }};
                if (!productId) return;

                let url = "{{ route('admin.products.velocity-metrics', ':id') }}".replace(':id', productId);
                
                // Show loading state in velocity badge
                $('#velocity-label-text').text('Calculating...');
                $('#velocity-progress-bar').css('opacity', '0.5');

                $.ajax({
                    url: url,
                    method: 'GET',
                    data: params,
                    success: function(res) {
                        if (res.status === 'success' && res.metrics) {
                            let m = res.metrics;
                            // Update KPIs
                            $('#kpi-period-inflow').text(Number(m.inflow_qty).toLocaleString());
                            $('#kpi-period-sold').text(Number(m.sold_qty).toLocaleString());
                            $('#kpi-period-inflow-sub').text('In ' + m.period_label);

                            // Update Badge
                            let badgeHtml = `
                                <span class="badge ${m.classification.badge} px-3 py-1 font-weight-bold" style="font-size: 12px; border-radius: 20px;">
                                    <i class="${m.classification.icon} mr-1"></i>
                                    <span id="velocity-label-text">${m.classification.label}</span>: 
                                    <span id="velocity-rate-text">${m.sell_through_rate}%</span> Sold
                                </span>
                            `;
                            $('#velocity-badge-container').html(badgeHtml);

                            // Update Progress Bar & Advice
                            $('#progress-rate-label').text(m.sell_through_rate + '%');
                            $('#velocity-advice-text').text(m.classification.advice);
                            $('#velocity-progress-bar')
                                .css('width', m.sell_through_rate + '%')
                                .css('background-color', m.classification.bg_color)
                                .css('opacity', '1')
                                .attr('aria-valuenow', m.sell_through_rate);
                        }
                    },
                    error: function() {
                        $('#velocity-label-text').text('Error');
                        $('#velocity-progress-bar').css('opacity', '1');
                    }
                });
            }

            // Collapsible Stock Movement Card Toggle Icon State
            $('#collapseStockMovement').on('show.bs.collapse', function () {
                $('#headingStockMovement').attr('aria-expanded', 'true');
                $('#stock-collapse-chevron').css('transform', 'rotate(180deg)');
            });
            $('#collapseStockMovement').on('shown.bs.collapse', function () {
                $('.b2b-select2').select2({ width: '100%' });
            });
            $('#collapseStockMovement').on('hide.bs.collapse', function () {
                $('#headingStockMovement').attr('aria-expanded', 'false');
                $('#stock-collapse-chevron').css('transform', 'rotate(0deg)');
            });

            // Handle Target Scope switching (Company / Outlet / Phone) in B2B Rule tab
            $('body').on('click', '.b2b-scope-pill', function() {
                let target = $(this).data('target');
                $('.b2b-scope-pill').removeClass('active');
                $(this).addClass('active');

                $('.b2b-scope-container').hide();
                $('#scope-box-' + target).fadeIn(150, function() {
                    $('#scope-box-' + target + ' .b2b-select2').select2({
                        width: '100%'
                    });
                });

                // Clear unselected inputs so only active scope is submitted
                if (target === 'company') {
                    $('#b2b_outlet_id').val('').trigger('change');
                    $('#b2b_user_id').val('').trigger('change');
                    $('#b2b_phone').val('');
                } else if (target === 'outlet') {
                    $('#b2b_company_id').val('').trigger('change');
                    $('#b2b_user_id').val('').trigger('change');
                    $('#b2b_phone').val('');
                } else if (target === 'phone') {
                    $('#b2b_company_id').val('').trigger('change');
                    $('#b2b_outlet_id').val('').trigger('change');
                }
            });

            // Auto-fill buyer phone when selecting a registered user
            $('body').on('change', '#b2b_user_id', function() {
                let phone = $(this).find(':selected').data('phone');
                if (phone) {
                    $('#b2b_phone').val(phone);
                }
            });

            // Initialize select2 on tab show
            $('body').on('shown.bs.tab', 'a[data-toggle="tab"], a[role="tab"]', function(e) {
                if ($(e.target).attr('href') === '#tab-visibility' || $(e.target).attr('id') === 'visibility-tab') {
                    $('.b2b-select2').select2({ width: '100%' });
                }
            });

            // Remove lingering focus outlines on button clicks
            $('body').on('click', '#velocity-preset-group .btn, #b2b-scope-pill-group .btn', function() {
                $(this).blur();
            });

            // Save B2B Visibility Rule via AJAX (Module 7)
            $('body').on('click', '#btn-save-b2b-visibility', function(e) {
                e.preventDefault();
                let productId = window.currentStockMovementProductId || {{ $product->id }};
                if (!productId) return;

                let form = $('#form-add-b2b-visibility');
                let data = form.serialize();

                let url = "{{ route('admin.products.b2b-visibility.store', ':id') }}".replace(':id', productId);
                let btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: data,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save B2B Rule');
                        if (res.status === 'success') {
                            if (window.toastr) toastr.success(res.message);
                            let reloadUrl = "{{ route('admin.products.stock-movement', ':id') }}".replace(':id', productId);
                            $.get(reloadUrl, function(html) {
                                $('#stock-movement-content-container').html(html);
                                $('#visibility-tab').tab('show');
                                $('.b2b-select2').select2({ width: '100%' });
                            }).fail(function() {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save B2B Rule');
                        let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error saving visibility rule';
                        if (window.toastr) toastr.error(msg);
                    }
                });
            });

            // Delete B2B Visibility Rule via AJAX (Module 7)
            $('body').on('click', '.btn-delete-b2b-rule', function(e) {
                e.preventDefault();
                let btn = $(this);
                let ruleId = btn.data('id');
                if (!ruleId) return;

                let executeDelete = function() {
                    let url = "{{ route('admin.products.b2b-visibility.destroy', ':id') }}".replace(':id', ruleId);
                    btn.prop('disabled', true);

                    $.ajax({
                        url: url,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                if (window.toastr) toastr.success(res.message);
                                $(`#b2b-rule-row-${ruleId}`).fadeOut(300, function() {
                                    $(this).remove();
                                    let remainingRows = $('#table-b2b-visibilities tbody tr[id^="b2b-rule-row-"]').length;
                                    $('#b2b-rules-count-badge').text(remainingRows + ' Rules');
                                    if (remainingRows === 0) {
                                        $('#table-b2b-visibilities tbody').html(`
                                            <tr id="no-b2b-rules-row">
                                                <td colspan="4" class="text-center py-4 bg-white">
                                                    <div class="py-2">
                                                        <div class="mb-2" style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-shield-alt text-muted" style="font-size: 18px;"></i>
                                                        </div>
                                                        <div class="font-weight-bold text-dark" style="font-size: 13px;">Standard Inventory Rules Active</div>
                                                        <div class="text-muted small mt-1" style="max-width: 400px; margin: 0 auto; font-size: 11px;">
                                                            No customer overrides set for this item. All buyers and outlets see actual warehouse stock.
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        `);
                                    }
                                });
                            }
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false);
                            let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error deleting rule';
                            if (window.toastr) toastr.error(msg);
                        }
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: "Remove B2B Stock Rule?",
                        text: "This customer/outlet override will be removed. The entity will revert to seeing actual warehouse stock.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#ef4444",
                        cancelButtonColor: "#64748b",
                        confirmButtonText: "<i class='fas fa-trash-alt mr-1'></i> Yes, remove it!",
                        cancelButtonText: "Cancel",
                        reverseButtons: true,
                        focusConfirm: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            executeDelete();
                        }
                    });
                } else if (confirm('Are you sure you want to remove this B2B stock rule?')) {
                    executeDelete();
                }
            });
        });
    </script>
@endpush
