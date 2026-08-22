@extends('backend.layouts.master')

@section('title', 'Create Vendor')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <div class="d-flex align-items-center flex-wrap w-100">
                <h1 class="mb-2 mb-sm-0 d-flex align-items-center" style="font-size: 1.25rem;">
                    <i class="fas fa-store mr-2" style="color: #2563eb;"></i>
                    Create Vendor
                </h1>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        </div>
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.vendor.index') }}">Vendors</a>
                        </div>
                        <div class="breadcrumb-item active">Create</div>
                    </div>
                    <a href="{{ route('admin.vendor.index') }}" class="btn btn-primary btn-sm ml-2 shadow-sm">
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
                                <div class="mr-3 p-2 rounded-circle text-white d-none d-sm-flex" style="background: #2563eb;">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold text-dark">Add New Vendor</h5>
                                </div>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body p-3 p-sm-4">
                            <form action="{{ route('admin.vendor.store') }}" method="POST" id="vendorForm">
                                @csrf
                                
                                {{-- Basic Information --}}
                                <div class="section-title mb-3 mb-sm-4">
                                    <span class="font-weight-bold" style="color: #2563eb; font-size: 1rem;">
                                        <i class="fas fa-user-circle mr-2"></i>Basic Information
                                    </span>
                                    <hr class="mt-1">
                                </div>

                                <div class="row">
                                    {{-- Company Name --}}
                                    <div class="form-group col-12 col-md-6">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                            <i class="fas fa-building" style="color: #2563eb; width: 18px;"></i>
                                            <span class="ml-1">Company Name</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('shop_name') is-invalid @enderror" 
                                               style="height: 44px; font-size: 0.95rem; border-radius: 10px; border: 2px solid #e2e8f0;"
                                               name="shop_name" id="shop_name" value="{{ old('shop_name') }}"
                                               placeholder="Enter company name">
                                        @error('shop_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Phone --}}
                                    <div class="form-group col-12 col-md-6">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                            <i class="fas fa-phone" style="color: #2563eb; width: 18px;"></i>
                                            <span class="ml-1">Phone</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                               style="height: 44px; font-size: 0.95rem; border-radius: 10px; border: 2px solid #e2e8f0;"
                                               name="phone" id="phone" value="{{ old('phone') }}"
                                               placeholder="Enter phone number">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Email --}}
                                    <div class="form-group col-12 col-md-6">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                            <i class="fas fa-envelope" style="color: #2563eb; width: 18px;"></i>
                                            <span class="ml-1">Email</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               style="height: 44px; font-size: 0.95rem; border-radius: 10px; border: 2px solid #e2e8f0;"
                                               name="email" id="email" value="{{ old('email') }}"
                                               placeholder="Enter email address">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Address --}}
                                    <div class="form-group col-12 col-md-6">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                            <i class="fas fa-map-marker-alt" style="color: #2563eb; width: 18px;"></i>
                                            <span class="ml-1">Address</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                               style="height: 44px; font-size: 0.95rem; border-radius: 10px; border: 2px solid #e2e8f0;"
                                               name="address" id="address" value="{{ old('address') }}"
                                               placeholder="Enter address">
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Location & Currency --}}
                                <div class="section-title mb-3 mb-sm-4 mt-3 mt-sm-4">
                                    <span class="font-weight-bold" style="color: #2563eb; font-size: 1rem;">
                                        <i class="fas fa-globe-americas mr-2"></i>Location & Currency
                                    </span>
                                    <hr class="mt-1">
                                </div>

                                <div class="row">
                                    {{-- Country --}}
                                    <div class="form-group col-12 col-md-6">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                            <i class="fas fa-globe" style="color: #2563eb; width: 18px;"></i>
                                            <span class="ml-1">Country</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select name="country" id="country" class="form-control select2 @error('country') is-invalid @enderror" 
                                                style="height: 44px; font-size: 0.95rem; border-radius: 10px; border: 2px solid #e2e8f0;">
                                            <option value="">Select Country</option>
                                            @foreach (config('settings.country_list') as $country)
                                                <option value="{{ $country }}" {{ old('country') == $country ? 'selected' : '' }}>
                                                    {{ $country }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('country')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Currency --}}
                                    <div class="form-group col-12 col-md-6">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                            <i class="fas fa-money-bill-wave" style="color: #2563eb; width: 18px;"></i>
                                            <span class="ml-1">Currency</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select name="currency_id" id="currency_select" class="form-control select2 @error('currency_id') is-invalid @enderror"
                                                style="height: 44px; font-size: 0.95rem; border-radius: 10px; border: 2px solid #e2e8f0;">
                                            <option value="">Select Currency</option>
                                            @foreach ($currencies as $currency)
                                                <option value="{{ $currency->id }}" 
                                                    data-icon="{{ $currency->symbol }}" 
                                                    data-rate="{{ $currency->exchange_rate }}"
                                                    {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                                                    {{ $currency->name }} ({{ $currency->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('currency_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Status & Additional Details --}}
                                <div class="section-title mb-3 mb-sm-4 mt-3 mt-sm-4">
                                    <span class="font-weight-bold" style="color: #2563eb; font-size: 1rem;">
                                        <i class="fas fa-cog mr-2"></i>Status & Additional Details
                                    </span>
                                    <hr class="mt-1">
                                </div>

                                <div class="row">
                                    {{-- Status --}}
                                    <div class="form-group col-12 col-md-6">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                            <i class="fas fa-toggle-on" style="color: #2563eb; width: 18px;"></i>
                                            <span class="ml-1">Status</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror"
                                                style="height: 44px; font-size: 0.95rem; border-radius: 10px; border: 2px solid #e2e8f0;">
                                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Description --}}
                                    <div class="form-group col-12 col-md-6">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                            <i class="fas fa-align-left" style="color: #2563eb; width: 18px;"></i>
                                            <span class="ml-1">Description</span>
                                        </label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  style="height: 44px; min-height: 44px; font-size: 0.95rem; border-radius: 10px; border: 2px solid #e2e8f0; resize: vertical; padding: 0.5rem 1rem;"
                                                  name="description" id="description" rows="2"
                                                  placeholder="Enter vendor description (optional)">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Submit Buttons --}}
                                <div class="row mt-4 pt-3 border-top">
                                    <div class="col-12">
                                        <div class="d-flex flex-column flex-sm-row justify-content-sm-end" style="gap: 12px;">
                                            <button type="reset" class="btn btn-secondary px-4 order-2 order-sm-1" 
                                                    style="border-radius: 10px; min-height: 44px; font-weight: 600; transition: all 0.3s ease; color: #ffffff; background: #6c757d; border: none;" id="resetBtn">
                                                <i class="fas fa-undo mr-1"></i> Reset
                                            </button>
                                            <button type="submit" class="btn btn-primary px-5 shadow-sm order-1 order-sm-2" 
                                                    style="background: #2563eb; border: none; border-radius: 10px; min-height: 44px; font-weight: 600; transition: all 0.3s ease;" id="submitBtn">
                                                <i class="fas fa-save mr-2"></i> Create Vendor
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
@endsection

@push('styles')
<style>
    /* =============================================
       VENDOR CREATE - PREMIUM DESIGN
       ============================================= */

    .form-group label {
        font-size: 0.85rem !important;
        margin-bottom: 0.5rem !important;
        letter-spacing: 0.3px;
    }

    .form-control {
        border-radius: 10px !important;
        border: 2px solid #e2e8f0;
        padding: 0.7rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        background: #fafbfc;
        height: 44px !important;
        width: 100% !important;
    }

    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
        background: #ffffff;
    }

    .form-control.is-invalid:focus {
        border-color: #e74a3b;
        box-shadow: 0 0 0 0.2rem rgba(231, 74, 59, 0.15);
    }

    .form-control:hover {
        border-color: #2563eb;
        background: #ffffff;
    }

    .form-control:focus {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    }

    textarea.form-control {
        height: auto !important;
        min-height: 100px;
        resize: vertical;
    }

    .section-title span {
        font-size: 1rem !important;
        letter-spacing: 0.5px;
    }

    .section-title hr {
        border-top: 2px solid #e2e8f0;
        opacity: 0.5;
        margin-top: 0.3rem;
    }

    .currency-icon-box {
        background: linear-gradient(135deg, #f8f9fc 0%, #eef2ff 100%);
        border: 2px dashed #2563eb;
        border-radius: 12px;
        padding: 15px;
        text-align: center;
        font-size: 2.5rem;
        font-weight: 700;
        color: #2563eb;
        min-height: 65px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .currency-icon-box:hover {
        border-color: #1d4ed8;
        background: linear-gradient(135deg, #eef2ff 0%, #dbe6ff 100%);
        transform: scale(1.02);
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.1);
    }

    .select2-container--default .select2-selection--single {
        border-radius: 10px !important;
        border: 2px solid #e2e8f0 !important;
        height: 44px !important;
        padding: 4px 12px;
        transition: all 0.3s ease;
        background: #fafbfc !important;
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px !important;
        color: #495057;
        font-size: 0.95rem;
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

    .input-group {
        width: 100% !important;
    }

    .input-group .input-group-text {
        border-radius: 10px 0 0 10px !important;
        border: 2px solid #e2e8f0;
        border-right: none;
        background: #f8f9fc;
        padding: 0 1.2rem;
        height: 44px;
        display: flex;
        align-items: center;
    }

    .input-group .form-control {
        border-radius: 0 10px 10px 0 !important;
    }

    .btn {
        border-radius: 10px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
        min-height: 44px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 1.5rem !important;
        font-size: 0.9rem !important;
        letter-spacing: 0.3px;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
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
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.35) !important;
    }

    .btn-secondary {
        background: #6c757d !important;
        border: none !important;
        color: #ffffff !important;
    }

    .btn-secondary:hover {
        background: #5a6268 !important;
        box-shadow: 0 8px 25px rgba(108, 117, 125, 0.35) !important;
        transform: translateY(-3px);
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
        .form-group {
            margin-bottom: 1.25rem !important;
        }
        .currency-icon-box {
            font-size: 1.8rem !important;
            min-height: 55px !important;
            padding: 12px !important;
        }
        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
        }
        .section-header .btn {
            width: auto !important;
        }
        .form-control {
            height: 40px !important;
            font-size: 0.85rem !important;
        }
        .select2-container--default .select2-selection--single {
            height: 40px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            font-size: 0.85rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }
        .section-title span {
            font-size: 0.9rem !important;
        }
        .btn {
            font-size: 0.8rem !important;
            min-height: 38px !important;
            padding: 0 1rem !important;
            width: 100% !important;
        }
        .input-group .form-control {
            font-size: 0.85rem !important;
            height: 40px !important;
        }
        .input-group .input-group-text {
            padding: 0 0.8rem !important;
            font-size: 0.85rem !important;
            height: 40px !important;
        }
        .card-header .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        .section-header .breadcrumb {
            font-size: 0.7rem !important;
        }
        .row .col-12 {
            padding: 0 5px !important;
        }
        .border-top {
            margin-top: 15px !important;
            padding-top: 15px !important;
        }
        .section-title hr {
            margin-top: 0.3rem !important;
        }
        textarea.form-control {
            min-height: 80px !important;
        }

        /* Mobile Button Fix */
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

        .section-header .ml-auto {
            flex-wrap: wrap !important;
            gap: 8px !important;
        }
        .section-header .ml-auto .section-header-breadcrumb {
            width: 100% !important;
        }
        .section-header .ml-auto .btn {
            width: 100% !important;
            margin-left: 0 !important;
        }
    }

    @media (min-width: 576px) and (max-width: 767.98px) {
        .card-body {
            padding: 20px !important;
        }
        .currency-icon-box {
            font-size: 2rem !important;
            min-height: 60px !important;
        }
        .form-control {
            height: 42px !important;
        }
        .select2-container--default .select2-selection--single {
            height: 42px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
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
        .input-group .input-group-text {
            height: 42px !important;
        }
        .input-group .form-control {
            height: 42px !important;
        }
        .d-flex.flex-column.flex-sm-row .btn {
            width: auto !important;
        }
        .d-flex.flex-column.flex-sm-row {
            gap: 12px !important;
        }
    }

    @media (max-width: 767.98px) {
        .card-header .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
        }
        .section-header .breadcrumb {
            font-size: 0.75rem !important;
        }
        .section-header .btn {
            width: auto !important;
        }
    }

    @media (min-width: 768px) and (max-width: 991.98px) {
        .card-body {
            padding: 25px !important;
        }
        .form-control {
            height: 44px !important;
        }
        .select2-container--default .select2-selection--single {
            height: 44px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 32px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
        }
        .input-group .input-group-text {
            height: 44px !important;
        }
        .input-group .form-control {
            height: 44px !important;
        }
        .d-flex.flex-column.flex-sm-row .btn {
            width: auto !important;
        }
        .d-flex.flex-column.flex-sm-row {
            gap: 16px !important;
        }
    }

    @media (min-width: 992px) {
        .card-body {
            padding: 30px !important;
        }
        .form-control {
            height: 44px !important;
        }
        .select2-container--default .select2-selection--single {
            height: 44px !important;
        }
        .input-group .input-group-text {
            height: 44px !important;
        }
        .input-group .form-control {
            height: 44px !important;
        }
        .d-flex.flex-column.flex-sm-row .btn {
            width: auto !important;
        }
        .d-flex.flex-column.flex-sm-row {
            gap: 16px !important;
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
        font-size: 0.8rem;
        font-weight: 500;
        margin-top: 0.3rem;
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
        font-size: 1.1rem;
    }

    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    }

    .text-danger {
        margin-left: 2px !important;
    }

    .form-group label i {
        width: 18px !important;
        text-align: center !important;
    }

    /* Mobile Spacing Fix */
    @media (max-width: 575.98px) {
        .form-group {
            padding: 0 8px !important;
        }
        .section-header .ml-auto {
            padding: 0 5px !important;
        }
        .card-body {
            padding: 12px !important;
        }
        .section-title {
            padding: 0 8px !important;
        }
    }

    /* Border Top */
    .border-top {
        border-top: 2px solid #e2e8f0 !important;
    }

    /* Button hover effects */
    .btn-secondary:hover {
        background: #5a6268 !important;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(108, 117, 125, 0.35) !important;
    }

    .btn-primary:hover {
        background: #1d4ed8 !important;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.35) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Currency selection handler
        $('#currency_select').on('change', function() {
            let icon = $(this).find(':selected').data('icon');
            $('#currency_icon_display').text(icon || '—');
        });

        // Initialize Select2
        $('.select2').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%',
            dropdownAutoWidth: true
        });

        // Reset button - FIXED
        $('#resetBtn').on('click', function(e) {
            e.preventDefault();
            
            // Reset all form inputs using native reset
            const form = document.getElementById('vendorForm');
            form.reset();
            
            // Reset Select2 elements properly
            $('.select2').each(function() {
                $(this).val('').trigger('change');
            });
            
            $('#currency_icon_display').text('—');
            
            // Remove validation states
            $('.is-invalid').removeClass('is-invalid');
            $('.is-valid').removeClass('is-valid');
            $('#vendorForm').removeClass('was-validated');
            
            // Reset border colors
            $('.form-control').css('border-color', '#e2e8f0');
        });

        // Form validation
        $('#vendorForm').on('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('was-validated');
            } else {
                // Add loading state to submit button
                let btn = $('#submitBtn');
                btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Creating...');
                btn.prop('disabled', true);
            }
        });
    });
</script>
@endpush