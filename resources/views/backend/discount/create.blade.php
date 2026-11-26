@extends('backend.layouts.master')

@section('title', 'Create Discount Rule')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <div class="d-flex align-items-center flex-wrap w-100">
                <h1 class="mb-2 mb-sm-0 d-flex align-items-center">
                    <i class="fas fa-percent mr-2 text-primary"></i>
                    Create Discount Rule
                </h1>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        </div>
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.discounts.index') }}">Discount Rules</a>
                        </div>
                        <div class="breadcrumb-item active">Create</div>
                    </div>
                    <a href="{{ route('admin.discounts.index') }}" class="btn btn-primary btn-sm ml-2 shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i>
                        <span class="d-none d-sm-inline">Back to List</span>
                        <span class="d-sm-none">Back</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        {{-- Card Header --}}
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 p-2 bg-primary rounded-circle text-white d-none d-sm-flex">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold text-dark">Add Discount Rule</h5>
                                    <small class="text-muted">Create a standard or seasonal targeted discount for your store</small>
                                </div>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body p-4">
                            <form action="{{ route('admin.discounts.store') }}" method="post" id="discountForm">
                                @csrf

                                {{-- Basic Settings --}}
                                <div class="row">
                                    {{-- Rule Name --}}
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-tag text-primary mr-1"></i>
                                            Rule Name
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name') }}"
                                               placeholder="e.g. Winter Sale 15% OFF" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Type --}}
                                    <div class="form-group col-md-3">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-list-ul text-info mr-1"></i>
                                            Discount Type
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control form-control-lg @error('type') is-invalid @enderror" 
                                                id="discount_type" name="type" required>
                                            <option value="percent" {{ old('type', 'percent') == 'percent' ? 'selected' : '' }}>
                                                Percentage (%)
                                            </option>
                                            <option value="flat" {{ old('type') == 'flat' ? 'selected' : '' }}>
                                                Flat Amount (kr.)
                                            </option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Value --}}
                                    <div class="form-group col-md-3">
                                        <label class="font-weight-bold text-dark" id="discount_value_label">
                                            <i class="fas fa-calculator text-warning mr-1"></i>
                                            Value (%)
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" 
                                                   class="form-control form-control-lg @error('value') is-invalid @enderror" 
                                                   name="value" value="{{ old('value', 0) }}"
                                                   placeholder="0.00" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-light font-weight-bold" id="value-symbol">%</span>
                                            </div>
                                        </div>
                                        @error('value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    {{-- Set as Default --}}
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-star text-warning mr-1"></i>
                                            Set as Default Store Discount
                                        </label>
                                        <select class="form-control form-control-lg @error('is_default') is-invalid @enderror" 
                                                name="is_default">
                                            <option value="0" {{ old('is_default', '0') == '0' ? 'selected' : '' }}>
                                                No (Custom Rule)
                                            </option>
                                            <option value="1" {{ old('is_default') == '1' ? 'selected' : '' }}>
                                                Yes (Default for All Orders)
                                            </option>
                                        </select>
                                        @error('is_default')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Default rule applies whenever no product or seasonal discount matches.</small>
                                    </div>

                                    {{-- Status --}}
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-toggle-on text-success mr-1"></i>
                                            Status
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control form-control-lg @error('status') is-invalid @enderror" 
                                                name="status" required>
                                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>
                                                Active
                                            </option>
                                            <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>
                                                Inactive
                                            </option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Advanced / Seasonal Options Progressive Disclosure Toggle --}}
                                <div class="seasonal-toggle-card mt-3 mb-4 p-3 rounded" style="background: #f8fafc; border: 1.5px dashed #cbd5e1;">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                        <div class="d-flex align-items-center">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-switch-input" id="toggleSeasonal" 
                                                       name="is_seasonal" value="1" {{ old('is_seasonal') ? 'checked' : '' }}>
                                                <label class="custom-switch-indicator" for="toggleSeasonal"></label>
                                            </div>
                                            <label class="font-weight-bold text-dark mb-0 ml-2 cursor-pointer" for="toggleSeasonal" style="font-size: 0.95rem;">
                                                <i class="fas fa-snowflake text-info mr-1"></i> 
                                                ⚙️ Advanced / Seasonal Settings (Occasion Type, Date Range, Category & Customer Targeting)
                                            </label>
                                        </div>
                                        <span class="badge badge-light border text-muted px-2 py-1 mt-2 mt-sm-0" style="font-size: 11px;">
                                            Optional Target Rules
                                        </span>
                                    </div>

                                    {{-- Expandable Seasonal Settings Container --}}
                                    <div id="seasonalSettingsContainer" class="mt-3 pt-3 border-top" style="display: {{ old('is_seasonal') ? 'block' : 'none' }};">
                                        <div class="row">
                                            {{-- Occasion Type Dropdown (Exact match with product create/edit) --}}
                                            <div class="form-group col-md-4">
                                                <label class="font-weight-bold text-dark">
                                                    <i class="fas fa-calendar-alt text-primary mr-1"></i>
                                                    Occasion Type / Season
                                                </label>
                                                <select name="product_type_id" id="product_type_id" class="form-control select2" style="width: 100%;">
                                                    <option value="">-- All Occasions / Any --</option>
                                                    @foreach ($productTypes as $pt)
                                                        <option value="{{ $pt->id }}" {{ old('product_type_id') == $pt->id ? 'selected' : '' }}>
                                                            {{ $pt->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">Matches products tagged with this Occasion (e.g. Winter Item)</small>
                                            </div>

                                            {{-- Start Date --}}
                                            <div class="form-group col-md-4">
                                                <label class="font-weight-bold text-dark">
                                                    <i class="fas fa-hourglass-start text-success mr-1"></i>
                                                    Season Start Date
                                                </label>
                                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                                       name="start_date" id="start_date" value="{{ old('start_date') }}">
                                                @error('start_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">Leave blank to start immediately</small>
                                            </div>

                                            {{-- End Date --}}
                                            <div class="form-group col-md-4">
                                                <label class="font-weight-bold text-dark">
                                                    <i class="fas fa-hourglass-end text-danger mr-1"></i>
                                                    Season End Date (Expiry)
                                                </label>
                                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                                       name="end_date" id="end_date" value="{{ old('end_date') }}">
                                                @error('end_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">Auto-expires at 23:59 on this date</small>
                                            </div>
                                        </div>

                                        <div class="row">
                                            {{-- Target Categories --}}
                                            <div class="form-group col-md-6">
                                                <label class="font-weight-bold text-dark">
                                                    <i class="fas fa-boxes text-info mr-1"></i>
                                                    Category-wise Target (Product Categories)
                                                </label>
                                                <select name="category_ids[]" id="category_ids" class="form-control select2" multiple="multiple" style="width: 100%;">
                                                    @foreach ($categories as $cat)
                                                        <option value="{{ $cat->id }}" 
                                                            {{ (is_array(old('category_ids')) && in_array($cat->id, old('category_ids'))) ? 'selected' : '' }}>
                                                            {{ $cat->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle text-info"></i> 
                                                    Select categories. <strong>Leave empty to apply to ALL categories.</strong>
                                                </small>
                                            </div>

                                            {{-- Target Customers / Outlets --}}
                                            <div class="form-group col-md-6">
                                                <label class="font-weight-bold text-dark">
                                                    <i class="fas fa-users text-primary mr-1"></i>
                                                    Customer / Outlet Target
                                                </label>
                                                <select name="user_ids[]" id="user_ids" class="form-control select2" multiple="multiple" style="width: 100%;">
                                                    @foreach ($customers as $cust)
                                                        <option value="{{ $cust->id }}"
                                                            {{ (is_array(old('user_ids')) && in_array($cust->id, old('user_ids'))) ? 'selected' : '' }}>
                                                            {{ $cust->name }} {{ $cust->outlet_name ? '— [' . $cust->outlet_name . ']' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle text-info"></i> 
                                                    Select specific customer or outlet stores. <strong>Leave empty to apply to ALL customers.</strong>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit Buttons --}}
                                <div class="text-right mt-4 pt-3 border-top">
                                    <button type="reset" class="btn btn-outline-secondary px-4 mr-2 reset-arrow-button" id="btnReset">
                                        <i class="fas fa-undo mr-1"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary px-5 shadow-sm" id="btnSubmit">
                                        <i class="fas fa-save mr-2"></i> Create Discount Rule
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .form-group label {
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
        letter-spacing: 0.3px;
    }
    .form-control {
        border-radius: 10px !important;
        border: 2px solid #e2e8f0;
        padding: 0.7rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        background: #fafbfc;
    }
    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
        background: #ffffff;
    }
    .form-control-lg {
        height: 50px !important;
        font-size: 0.95rem !important;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .select2-container--default .select2-selection--multiple,
    .select2-container--default .select2-selection--single {
        border: 2px solid #e2e8f0 !important;
        border-radius: 10px !important;
        min-height: 48px !important;
        padding: 4px 8px;
        background: #fafbfc;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #2563eb !important;
        border: none !important;
        color: #ffffff !important;
        border-radius: 6px !important;
        padding: 4px 8px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #ffffff !important;
        margin-right: 6px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: 'Click to select options...',
            allowClear: true,
            width: '100%'
        });

        // Type change label handler
        function updateTypeDisplay() {
            let type = $('#discount_type').val();
            if (type === 'percent') {
                $('#discount_value_label').html('<i class="fas fa-calculator text-warning mr-1"></i> Value (%) <span class="text-danger">*</span>');
                $('#value-symbol').text('%');
            } else {
                $('#discount_value_label').html('<i class="fas fa-calculator text-warning mr-1"></i> Value (kr.) <span class="text-danger">*</span>');
                $('#value-symbol').text('kr.');
            }
        }
        $('#discount_type').on('change', updateTypeDisplay);
        updateTypeDisplay();

        // Advanced / Seasonal Toggle handler
        $('#toggleSeasonal').on('change', function() {
            if ($(this).is(':checked')) {
                $('#seasonalSettingsContainer').slideDown(250);
            } else {
                $('#seasonalSettingsContainer').slideUp(200);
            }
        });

        // Reset Handler
        $('#btnReset').on('click', function() {
            setTimeout(function() {
                $('#discount_type').trigger('change');
                $('.select2').val(null).trigger('change');
                if (!$('#toggleSeasonal').is(':checked')) {
                    $('#seasonalSettingsContainer').hide();
                }
            }, 50);
        });
    });
</script>
@endpush
