@extends('backend.layouts.master')

@section('title') Product Import @endsection

@push('css')
<style>
    /* .import-container {
        max-width: 800px;
        margin: 0 auto;
    } */
    .drop-zone {
        border: 2px dashed #ccc;
        border-radius: 10px;
        padding: 40px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    .drop-zone:hover {
        border-color: #6777ef;
        background-color: #f8f9ff;
    }
    .drop-zone.dragover {
        border-color: #6777ef;
        background-color: #f0f4ff;
    }
    .file-info {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    .format-table {
        margin-top: 30px;
    }
    .format-table th {
        background-color: #f1f1f1;
    }
    .alert-info {
        border-left: 4px solid #0dcaf0;
    }
</style>
@endpush

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Import Products from Excel</h4>
            </div>
            <div class="card-body">
                <div class="import-container">
                    
                    <!-- Upload Form -->
                    <div id="upload-section">
                        <form id="preview-form" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <label for="import_file" class="form-label">Select Excel/CSV File</label>
                                <input type="file" 
                                       name="import_file" 
                                       id="import_file_input" 
                                       class="form-control" 
                                       accept=".csv,.xlsx,.xls"
                                       required>
                                <div id="file-error" class="text-danger mt-1"></div>
                            </div>

                            <div class="mb-4 d-flex gap-2">
                                <button type="submit" id="preview-btn" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Preview Data And Submit
                                </button>
                                <a href="{{ route('admin.products.index') }}" class="btn btn-danger mr-2 ml-2">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                                <a href="{{ route('admin.products.sample.download', 'products_complete.csv') }}" download class="btn btn-info">
                                    <i class="fas fa-download"></i> Download Sample CSV
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Preview Section (Hidden by default) -->
                    <div id="preview-section" style="display: none;" class="mt-4">
                        <hr>
                        <h4><i class="fas fa-list"></i> Import Preview</h4>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Please review the data below. If everything looks correct, click "Confirm Import".
                        </div>
                        
                        <div class="table-responsive" style="max-height: 500px; border: 1px solid #ddd;">
                            <table class="table table-sm table-bordered table-striped mb-0" id="preview-table">
                                <thead class="sticky-top bg-white">
                                    <tr id="preview-headers"></tr>
                                </thead>
                                <tbody id="preview-body"></tbody>
                            </table>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <form id="import-form">
                                @csrf
                                <input type="hidden" name="temp_path" id="temp_path">
                                <input type="hidden" name="original_name" id="original_name">
                                <button type="submit" id="confirm-import-btn" class="btn btn-success btn-lg">
                                    <i class="fas fa-check-circle"></i> Confirm & Import
                                </button>
                                <button type="button" id="cancel-preview-btn" class="btn btn-danger btn-lg">
                                    <i class="fas fa-times-circle"></i> Cancel
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="loading-spinner" style="display: none;" class="text-center mt-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2" id="spinner-text">Processing...</p>
                    </div>

                    <!-- Instructions -->
                    <div class="alert alert-info mt-4">
                        <h5><i class="fas fa-info-circle"></i> Instructions:</h5>
                        <ul class="mb-0">
                            <li>Upload an Excel file (.xlsx, .xls, or .csv)</li>
                            <li>The first row should contain column headers</li>
                            <li>Category, Brand, Vendor, Unit can be matched by name or ID</li>
                            <li>For variants, use combined columns like variant_1_color_name, variant_1_size_name, variant_1_qty, variant_1_outlet_price, variant_1_price</li>
                            <li>Maximum file size: 199MB</li>
                        </ul>
                    </div>

                    <!-- Excel Format Table -->
                    <div class="format-table">
                        <h5>Excel Column Format:</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Column Name</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                        <th>Example</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>name</strong></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Product Name</td>
                                        <td>Samsung Galaxy S21</td>
                                    </tr>
                                    <tr>
                                        <td>product_number</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Product Number (use same number for multiple variants)</td>
                                        <td>PRD-001</td>
                                    </tr>
                                    <tr>
                                        <td>category_name / category_id</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Category</td>
                                        <td>Electronics / 1</td>
                                    </tr>
                                    <tr>
                                        <td>sub_category_name / sub_category_id</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Sub Category</td>
                                        <td>Mobile / 2</td>
                                    </tr>
                                    <tr>
                                        <td>child_category_name / child_category_id</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Child Category</td>
                                        <td>Smartphone / 3</td>
                                    </tr>
                                    <tr>
                                        <td>brand_name / brand_id</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Brand</td>
                                        <td>Samsung / 1</td>
                                    </tr>
                                    <tr>
                                        <td>vendor_name / vendor_id</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Vendor</td>
                                        <td>ABC Corp / 1</td>
                                    </tr>
                                    <tr>
                                        <td>unit_name / unit_id</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Unit</td>
                                        <td>Pcs / 1</td>
                                    </tr>
                                    <tr>
                                        <td>product_number</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Product Number</td>
                                        <td>PRD-001</td>
                                    </tr>
                                    <tr>
                                        <td>barcode</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Barcode</td>
                                        <td>123456789</td>
                                    </tr>
                                    <tr>
                                        <td>purchase_price</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Purchase Price</td>
                                        <td>1000</td>
                                    </tr>
                                    <tr>
                                        <td>price</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Sale Price</td>
                                        <td>1500</td>
                                    </tr>
                                    <tr>
                                        <td>outlet_price</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Outlet Price</td>
                                        <td>1400</td>
                                    </tr>
                                    <tr>
                                        <td>qty</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Quantity</td>
                                        <td>100</td>
                                    </tr>
                                    <tr>
                                        <td>long_description / description</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Description</td>
                                        <td>Product details...</td>
                                    </tr>
                                    <tr>
                                        <td>image</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Image URL</td>
                                        <td>https://example.com/img.jpg</td>
                                    </tr>
                                    <tr>
                                        <td>raw_material_cost</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Raw Material Cost</td>
                                        <td>500</td>
                                    </tr>
                                    <tr>
                                        <td>transport_cost</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Transport Cost</td>
                                        <td>100</td>
                                    </tr>
                                    <tr>
                                        <td>tax</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Tax</td>
                                        <td>50</td>
                                    </tr>
                                    <tr>
                                        <td>discount_type</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Product Discount Type (flat / percent)</td>
                                        <td>percent</td>
                                    </tr>
                                    <tr>
                                        <td>discount</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Product Discount Value</td>
                                        <td>10</td>
                                    </tr>
                                    <tr>
                                        <td>vat_type</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Product VAT Type (flat / percent), empty = dynamic default</td>
                                        <td>percent</td>
                                    </tr>
                                    <tr>
                                        <td>vat_value</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Product VAT Value</td>
                                        <td>7.5</td>
                                    </tr>
                                    <tr>
                                        <td>status</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Status (1=active, 0=inactive)</td>
                                        <td>1</td>
                                    </tr>
                                    <!-- Variant Columns -->
                                    <tr class="table-primary">
                                        <td colspan="4"><strong>Variant Columns (Optional)</strong></td>
                                    </tr>
                                    <tr>
                                        <td>variant_1_color_name</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Variant Color (1st combo)</td>
                                        <td>Red</td>
                                    </tr>
                                    <tr>
                                        <td>variant_1_size_name</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Variant Size (1st combo)</td>
                                        <td>Large</td>
                                    </tr>
                                    <tr>
                                        <td>variant_1_qty</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Variant Quantity (1st combo)</td>
                                        <td>50</td>
                                    </tr>
                                    <tr>
                                        <td>variant_1_outlet_price</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Variant Whole Sale Price (1st combo)</td>
                                        <td>1600</td>
                                    </tr>
                                    <tr>
                                        <td>variant_1_price</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Variant Outlet/Customer Price (1st combo)</td>
                                        <td>1700</td>
                                    </tr>
                                    <tr>
                                        <td>variant_2_..., variant_3_...</td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Use next index for more variant combinations (color can stay blank to reuse previous color)</td>
                                        <td>variant_2_color_name, variant_2_size_name...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Preview Form Submission
        $('#preview-form').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            $('#file-error').text('');
            $('#preview-btn').prop('disabled', true);
            $('#loading-spinner').show();
            $('#spinner-text').text('Reading file and preparing preview...');

            $.ajax({
                url: "{{ route('admin.products.import.preview') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#loading-spinner').hide();
                        $('#upload-section').hide();
                        $('#preview-section').show();
                        
                        $('#temp_path').val(response.temp_path);
                        $('#original_name').val(response.original_name);
                        
                        // Find image column index
                        const imageColIndex = response.preview.headers.findIndex(h => {
                            const header = h.toLowerCase().trim();
                            return header === 'image' || header === 'img' || header === 'image_url';
                        });
                        
                        // Populate headers
                        let headHtml = '';
                        response.preview.headers.forEach(header => {
                            headHtml += `<th class="text-nowrap">${header}</th>`;
                        });
                        $('#preview-headers').html(headHtml);
                        
                        // Populate data
                        let bodyHtml = '';
                        response.preview.data.forEach(row => {
                            bodyHtml += '<tr>';
                            row.forEach((cell, index) => {
                                let content = cell || '';
                                if (index === imageColIndex && content && (content.toString().startsWith('http') || content.toString().startsWith('data:'))) {
                                    bodyHtml += `<td class="text-nowrap text-center">
                                        <img src="${content}" alt="Preview" style="height: 50px; width: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                                    </td>`;
                                } else {
                                    bodyHtml += `<td class="text-nowrap">${content}</td>`;
                                }
                            });
                            bodyHtml += '</tr>';
                        });
                        $('#preview-body').html(bodyHtml);
                        
                        // Initialize DataTable
                        if ($.fn.DataTable.isDataTable('#preview-table')) {
                            $('#preview-table').DataTable().destroy();
                        }
                        
                        $('#preview-table').DataTable({
                            pageLength: 25,
                            scrollX: true,
                            autoWidth: false,
                            order: []
                        });
                    }
                },
                error: function(xhr) {
                    $('#loading-spinner').hide();
                    $('#preview-btn').prop('disabled', false);
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        $('#file-error').text(xhr.responseJSON.message);
                    } else {
                        $('#file-error').text('An error occurred while reading the file.');
                    }
                }
            });
        });

        // Cancel Preview
        $('#cancel-preview-btn').on('click', function() {
            $('#preview-section').hide();
            $('#upload-section').show();
            $('#preview-btn').prop('disabled', false);
            $('#import_file_input').val('');
            if ($.fn.DataTable.isDataTable('#preview-table')) {
                $('#preview-table').DataTable().destroy();
            }
        });

        // Confirm Import
        $('#import-form').on('submit', function(e) {
            e.preventDefault();
            
            $('#confirm-import-btn').prop('disabled', true);
            $('#cancel-preview-btn').hide();
            $('#loading-spinner').show();
            $('#spinner-text').text('Importing products from original file, please wait...');

            $.ajax({
                url: "{{ route('admin.products.import.store') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    }
                },
                error: function(xhr) {
                    $('#loading-spinner').hide();
                    $('#confirm-import-btn').prop('disabled', false);
                    $('#cancel-preview-btn').show();
                    alert(xhr.responseJSON.message || 'Import failed.');
                }
            });
        });

        // File input validation
        $('#import_file_input').on('change', function() {
            if (this.files[0] && this.files[0].size > 10 * 1024 * 1024) {
                $('#file-error').text('File size must be less than 10MB');
                this.value = '';
            } else {
                $('#file-error').text('');
            }
        });
    });
</script>
@endpush
