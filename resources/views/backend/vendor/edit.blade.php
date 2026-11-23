@extends('backend.layouts.master')

@section('title', 'Update Vendor')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <div class="d-flex align-items-center flex-wrap w-100">
                <h1 class="mb-2 mb-sm-0 d-flex align-items-center" style="font-size: 1.25rem;">
                    <i class="fas fa-store mr-2" style="color: #2563eb;"></i>
                    Update Vendor Profile
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
                        <div class="breadcrumb-item active">Update</div>
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
                        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 p-2 rounded-circle text-white d-none d-sm-flex" style="background: #2563eb;">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold text-dark">Edit Vendor: {{ $vendor->shop_name }}</h5>
                                    <small class="text-muted">Vendor ID: #{{ $vendor->code ?? $vendor->id }}</small>
                                </div>
                            </div>
                            <span class="badge badge-{{ $vendor->status ? 'success' : 'secondary' }} px-3 py-2 font-weight-bold">
                                {{ $vendor->status ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body p-3 p-sm-4">
                            <form action="{{ route('admin.vendor.update', $vendor->id) }}" method="POST" id="vendorEditForm">
                                @csrf
                                @method('PUT')
                                
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
                                               name="shop_name" id="shop_name" value="{{ old('shop_name', $vendor->shop_name) }}"
                                               placeholder="Enter company name" required>
                                        @error('shop_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Phone --}}
                                    <div class="form-group col-12 col-md-6">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                            <i class="fas fa-phone" style="color: #2563eb; width: 18px;"></i>
                                            <span class="ml-1">Phone</span>
                                        </label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                               style="height: 44px; font-size: 0.95rem; border-radius: 10px; border: 2px solid #e2e8f0;"
                                               name="phone" id="phone" value="{{ old('phone', $vendor->phone) }}"
                                               placeholder="Enter phone number (optional)">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Email --}}
                                    <div class="form-group col-12 col-md-6">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                            <i class="fas fa-envelope" style="color: #2563eb; width: 18px;"></i>
                                            <span class="ml-1">Email</span>
                                        </label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               style="height: 44px; font-size: 0.95rem; border-radius: 10px; border: 2px solid #e2e8f0;"
                                               name="email" id="email" value="{{ old('email', $vendor->email) }}"
                                               placeholder="Enter email address (optional)">
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
                                               name="address" id="address" value="{{ old('address', $vendor->address) }}"
                                               placeholder="Enter address" required>
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
                                                <option value="{{ $country }}" {{ old('country', $vendor->country) == $country ? 'selected' : '' }}>
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
                                                    {{ old('currency_id', $vendor->currency_id) == $currency->id ? 'selected' : '' }}>
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
                                            <option value="1" {{ old('status', (string)$vendor->status) === '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status', (string)$vendor->status) === '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Description / Notes --}}
                                    <div class="form-group col-12 col-md-6">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                            <i class="fas fa-align-left" style="color: #2563eb; width: 18px;"></i>
                                            <span class="ml-1">Description / Notes</span>
                                        </label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  style="height: 44px; min-height: 44px; font-size: 0.95rem; border-radius: 10px; border: 2px solid #e2e8f0; resize: vertical; padding: 0.5rem 1rem;"
                                                  name="description" id="description" rows="2"
                                                  placeholder="Enter vendor description or notes (optional)">{{ old('description', $vendor->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Submit Buttons --}}
                                <div class="row mt-4 pt-3 border-top">
                                    <div class="col-12">
                                        <div class="d-flex flex-column flex-sm-row justify-content-sm-end" style="gap: 12px;">
                                            <a href="{{ route('admin.vendor.index') }}" class="btn btn-secondary px-4 order-2 order-sm-1"
                                               style="border-radius: 10px; min-height: 44px; font-weight: 600; color: #ffffff; background: #6c757d; border: none;">
                                                <i class="fas fa-times mr-1"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary px-5 shadow-sm order-1 order-sm-2" 
                                                    style="background: #2563eb; border: none; border-radius: 10px; min-height: 44px; font-weight: 600; transition: all 0.3s ease;" id="submitBtn">
                                                <i class="fas fa-save mr-2"></i> Update Vendor
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
        border-color: #E11835;
        box-shadow: 0 0 0 0.2rem rgba(225, 24, 53, 0.15);
    }

    .form-control:hover {
        border-color: #2563eb;
        background: #ffffff;
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
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
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

    .card {
        border-radius: 16px !important;
        overflow: hidden !important;
    }

    .border-top {
        border-top: 2px solid #e2e8f0 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: 'Select an option',
            width: '100%',
            dropdownAutoWidth: true
        });

        // Form submission loading state
        $('#vendorEditForm').on('submit', function() {
            let btn = $('#submitBtn');
            btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Saving Changes...');
            btn.prop('disabled', true);
        });
    });
</script>
@endpush
