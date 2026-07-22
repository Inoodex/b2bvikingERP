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
                                    <small class="text-muted">Create a new discount or promo rule for your store</small>
                                </div>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body p-4">
                            <form action="{{ route('admin.discounts.store') }}" method="post">
                                @csrf
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
                                               placeholder="Enter discount rule name">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Type --}}
                                    <div class="form-group col-md-3">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-list-ul text-info mr-1"></i>
                                            Type
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control form-control-lg @error('type') is-invalid @enderror" 
                                                id="discount_type" name="type" required>
                                            <option value="percent" {{ old('type', 'percent') == 'percent' ? 'selected' : '' }}>
                                                Percent
                                            </option>
                                            <option value="flat" {{ old('type') == 'flat' ? 'selected' : '' }}>
                                                Flat
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
                                                <span class="input-group-text bg-light" id="value-symbol">%</span>
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
                                            Set as Default
                                        </label>
                                        <select class="form-control form-control-lg @error('is_default') is-invalid @enderror" 
                                                name="is_default">
                                            <option value="0" {{ old('is_default', '0') == '0' ? 'selected' : '' }}>
                                                No
                                            </option>
                                            <option value="1" {{ old('is_default') == '1' ? 'selected' : '' }}>
                                                Yes
                                            </option>
                                        </select>
                                        @error('is_default')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Set this discount as the default rule</small>
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
                                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>
                                                Inactive
                                            </option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Active discount rules will be applied to orders</small>
                                    </div>
                                </div>

                                {{-- Submit Buttons --}}
                                <div class="text-right mt-4 pt-3 border-top">
                                    <button type="reset" class="btn btn-outline-secondary px-4 mr-2">
                                        <i class="fas fa-undo mr-1"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                        <i class="fas fa-save mr-2"></i> Create
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
    /* =============================================
       DISCOUNT RULES CREATE - CUSTOM STYLES
       ============================================= */

    .form-group label {
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
        letter-spacing: 0.3px;
    }

    .form-group .form-text {
        font-size: 0.8rem;
        margin-top: 0.25rem;
        color: #858796;
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
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        background: #ffffff;
    }

    .form-control-lg {
        height: 50px !important;
        font-size: 0.95rem !important;
    }

    .form-control.is-invalid:focus {
        border-color: #e74a3b;
        box-shadow: 0 0 0 0.2rem rgba(231, 74, 59, 0.15);
    }

    /* Input Group */
    .input-group .input-group-text {
        border-radius: 0 10px 10px 0 !important;
        border: 2px solid #e2e8f0;
        border-left: none;
        background: #f8f9fc;
        padding: 0 1.2rem;
        font-weight: 600;
        color: #4e73df;
    }

    .input-group .form-control {
        border-radius: 10px 0 0 10px !important;
    }

    .input-group .form-control:focus + .input-group-append .input-group-text {
        border-color: #4e73df;
    }

    /* Buttons */
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
        background: #4e73df !important;
        border: none !important;
        color: #ffffff !important;
    }

    .btn-primary:hover {
        background: #224abe !important;
        box-shadow: 0 8px 25px rgba(78, 115, 223, 0.35) !important;
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

    /* Card */
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

    /* Mobile Responsive */
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
        .text-right {
            text-align: center !important;
        }
        .text-right .btn {
            width: 100% !important;
            margin-bottom: 8px !important;
        }
        .text-right .btn:last-child {
            margin-bottom: 0 !important;
        }
        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
        }
        .section-header .btn {
            width: auto !important;
        }
        .form-control-lg {
            height: 44px !important;
            font-size: 0.85rem !important;
        }
        .btn {
            font-size: 0.8rem !important;
            min-height: 38px !important;
            padding: 0 1rem !important;
        }
        .input-group .form-control {
            font-size: 0.85rem !important;
            height: 44px !important;
        }
        .input-group .input-group-text {
            padding: 0 0.8rem !important;
            font-size: 0.85rem !important;
        }
        .card-header .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
    }

    @media (min-width: 576px) and (max-width: 767.98px) {
        .card-body {
            padding: 20px !important;
        }
        .form-control-lg {
            height: 46px !important;
        }
        .btn {
            min-height: 40px !important;
            padding: 0 1.2rem !important;
        }
    }

    @media (max-width: 767.98px) {
        .section-header .breadcrumb {
            font-size: 0.75rem !important;
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
        .card-header .ml-auto .btn {
            width: 100% !important;
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
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #4e73df;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #224abe;
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

    .form-control:hover {
        border-color: #4e73df;
        background: #ffffff;
    }

    .form-control:focus {
        box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.15) !important;
    }

    select.form-control {
        appearance: auto !important;
        -webkit-appearance: auto !important;
    }
</style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            function updateValueLabel() {
                const type = $('#discount_type').val();
                const label = type === 'percent' ? 'Value (%)' : 'Value (Flat)';
                const symbol = type === 'percent' ? '%' : '$';
                
                $('#discount_value_label').html(`
                    <i class="fas fa-calculator text-warning mr-1"></i>
                    ${label}
                    <span class="text-danger">*</span>
                `);
                $('#value-symbol').text(symbol);
            }

            $('#discount_type').on('change', updateValueLabel);
            updateValueLabel();

            // Reset button
            $('button[type="reset"]').on('click', function(e) {
                e.preventDefault();
                $('form')[0].reset();
                $('.is-invalid').removeClass('is-invalid');
                $('form').removeClass('was-validated');
                updateValueLabel();
            });

            // Form validation
            $('form').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).addClass('was-validated');
                }
            });
        });
    </script>
@endpush