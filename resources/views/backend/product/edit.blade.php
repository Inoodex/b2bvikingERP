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
        });
    </script>
@endpush
