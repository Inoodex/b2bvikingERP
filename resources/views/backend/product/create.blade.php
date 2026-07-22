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
                            <h4>Create Product</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.products.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf


                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Name</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>item number</label>
                                        <input type="text" class="form-control" name="product_number"
                                            value="{{ old('product_number') }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Category</label>
                                        <select class="form-control main-category select2" name="category_id">
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Sub Category</label>
                                        <select class="form-control sub-category select2" name="sub_category_id">
                                            <option value="">Select Sub Category</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Child Category</label>
                                        <select class="form-control child-category select2" name="child_category_id">
                                            <option value="">Select Child Category</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Brand</label>
                                        <select class="form-control select2" name="brand_id">
                                            <option value="">Select Brand</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Unit</label>
                                        <select class="form-control select2" name="unit_id">
                                            <option value="">Select Unit</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if(isset($vendors) && $vendors->count() > 0)
                                    <div class="form-group col-md-4">
                                        <label>Vendor</label>
                                        <select class="form-control select2" name="vendor_id">
                                            <option value="">Select Vendor</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->shop_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                </div>

                               <div class="row">
                                    <div class="form-group col-md-3">
                                         <label>Purchase Price</label>
                                         <input type="number" class="form-control" name="purchase_price" step="any"
                                             value="{{ old('purchase_price') }}">
                                     </div>
                                     
                                     <div class="form-group col-md-3">
                                        <label>Whole Sale Price @auth
                                            
                                        @endauth</label>
                                        <input type="number" class="form-control" name="outlet_price" step="any"
                                            value="{{ old('outlet_price') }}">
                                    </div>
                                    <div class="form-group col-md-3">
                                         <label>Outlet/Customer price</label>
                                         <input type="number" class="form-control" name="price" step="any"
                                             value="{{ old('price') }}">
                                     </div>
                                    <div class="form-group col-md-3">
                                        <label>Shelve Number</label>
                                        <input type="text" class="form-control" name="self_number"
                                            value="{{ old('self_number') }}">
                                    </div>
                                </div>
                                
                                <div class="row">
                                     <div class="form-group col-md-4">
                                         <label>Raw Material Cost</label>
                                         <input type="number" class="form-control" name="raw_material_cost" step="any"
                                             value="{{ old('raw_material_cost', 0) }}">
                                     </div>
                                     <div class="form-group col-md-4">
                                         <label>Transport Cost</label>
                                         <input type="number" class="form-control" name="transport_cost" step="any"
                                             value="{{ old('transport_cost', 0) }}">
                                     </div>
                                    <div class="form-group col-md-4">
                                        <label>Tax</label>
                                        <input type="number" class="form-control" name="tax" step="any"
                                            value="{{ old('tax', 0) }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label>Discount Type</label>
                                        <select class="form-control" name="discount_type">
                                            <option value="">No Discount</option>
                                            <option value="percent" {{ old('discount_type') === 'percent' ? 'selected' : '' }}>Percent (%)</option>
                                            <option value="flat" {{ old('discount_type') === 'flat' ? 'selected' : '' }}>Flat</option>
                                        </select>
                                        <small class="text-muted">Product-specific discount.</small>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Discount Value</label>
                                        <input type="number" class="form-control" name="discount" step="any"
                                            value="{{ old('discount', 0) }}">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Product VAT Type</label>
                                        <select class="form-control" name="vat_type">
                                            <option value="">Use Dynamic Default</option>
                                            <option value="percent" {{ old('vat_type') === 'percent' ? 'selected' : '' }}>Percent (%)</option>
                                            <option value="flat" {{ old('vat_type') === 'flat' ? 'selected' : '' }}>Flat</option>
                                        </select>
                                        <small class="text-muted">Keep empty to use global VAT.</small>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Product VAT Value</label>
                                        <input type="number" class="form-control" name="vat_value" step="any"
                                            value="{{ old('vat_value', 0) }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Status</label>
                                        <select class="form-control" name="status">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4" id="product-stock-group">
                                        <label>Opening Stock</label>
                                        <input type="number" class="form-control" name="qty" value="{{ old('qty', 0) }}">
                                        <small class="text-muted">Only used if no variants are added.</small>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Minimum Order Quantity</label>
                                        <input type="number" class="form-control" name="minimum_order_qty" value="{{ old('minimum_order_qty', 1) }}">
                                        <small class="text-muted">Minimum quantity that can be ordered.</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Occasion Type</label>
                                        <select class="form-control select2" name="product_type_id">
                                            <option value="">Select Option</option>
                                            @foreach ($productTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Product Type (Legacy)</label>
                                        <select class="form-control select2" name="product_type">
                                            <option value="">Select Option</option>
                                            <option value="new_arrival">New Arrival</option>
                                            <option value="upcoming">Upcoming</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Custom Label</label>
                                        <input type="text" class="form-control" name="custom_label" 
                                            placeholder="e.g. Best Seller, Hot, New" value="{{ old('custom_label') }}">
                                        <small class="form-text text-muted">e.g. Best Seller, Hot, New, Sale</small>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Barcode (Optional)</label>
                                        <input type="text" class="form-control" name="barcode" id="barcode_input"
                                            placeholder="Scan or enter barcode" value="{{ old('barcode') }}">
                                        <small class="form-text text-muted">Compatible with barcode scanners.</small>
                                    </div>
                                </div>



                                <div class="form-group">
                                    <label>Long Description</label>
                                    <textarea name="long_description" class="summernote">{{ old('long_description') }}</textarea>
                                </div>

                                <div class="card border">
                                    <div class="card-header">
                                        <h4>Product Variants</h4>
                                        <div class="card-header-action">
                                            <button type="button" class="btn btn-success" id="add-variant"><i
                                                    class="fas fa-plus"></i> Add Variant</button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered table-responsive">
                                            <thead>
                                                <tr>
                                                        <th width="20%" class="variant-color-col">Color</th>
                                                        <th width="20%" class="variant-size-col">Size</th>
                                                        <th width="15%">Opening Stock</th>
                                                        <th width="15%">Whole Sale Price</th>
                                                        <th width="15%">Outlet/Customer Price</th>
                                                        <th>Action</th>
                                                    </tr>
                                            </thead>
                                            <tbody id="variant-list">
                                                <!-- Dynamic Rows -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Thumbnail Image</label>
                                    <div id="image-preview" class="image-preview">
                                        <label for="image-upload" id="image-label">Choose File</label>
                                        <input type="file" name="image" id="image-upload" />
                                    </div>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary px-4">Create Product</button>
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
                        $('.child-category').html(
                            '<option value="">Select Child Category</option>')
                        $.each(data, function(i, item) {
                            $('.sub-category').append(
                                `<option value="${item.id}">${item.name}</option>`)
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
                        $('.child-category').html(
                            '<option value="">Select Child Category</option>')
                        $.each(data, function(i, item) {
                            $('.child-category').append(
                                `<option value="${item.id}">${item.name}</option>`)
                        })
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                })
            })

            // Variant Logic
            let variantCount = 0;
            $('#add-variant').on('click', function() {
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
                            <input type="number" name="variants[${variantCount}][qty]" class="form-control" value="0">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="variants[${variantCount}][outlet_price]" class="form-control" value="0" placeholder="0.00">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="variants[${variantCount}][price]" class="form-control" value="0" placeholder="0.00">
                        </td>
                        <td><button type="button" class="btn btn-danger remove-variant" data-id="${variantCount}"><i class="fas fa-trash"></i></button></td>
                    </tr>
                `;
                $('#variant-list').append(html);
                variantCount++;
            });

            $(document).on('click', '.remove-variant', function() {
                let id = $(this).data('id');
                $('#variant-row-' + id).remove();
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
