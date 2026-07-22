@extends('backend.layouts.master')

@section('title', 'Currency Settings')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <div class="d-flex align-items-center flex-wrap w-100">
                <h1 class="mb-2 mb-sm-0 d-flex align-items-center" style="font-size: 1.25rem;">
                    <i class="fas fa-money-bill-wave mr-2" style="color: #2563eb;"></i>
                    Currency Settings
                </h1>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        </div>
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.settings.index') }}">Settings</a>
                        </div>
                        <div class="breadcrumb-item active">Currency</div>
                    </div>
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
                                    <i class="fas fa-coins"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold text-dark">System Default Currency</h5>
                                    <small class="text-muted">Set the default currency for your store</small>
                                </div>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body p-3 p-sm-4">
                            <form action="{{ route('admin.settings.currency.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-12 col-md-8">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                                <i class="fas fa-globe" style="color: #2563eb; width: 18px;"></i>
                                                <span class="ml-1">Select System Currency</span>
                                            </label>
                                            <select name="system_currency_select" id="system_currency_select" class="form-control select2" 
                                                    style="height: 44px; font-size: 0.95rem; border-radius: 10px; border: 2px solid #e2e8f0; width: 100%;">
                                                @foreach (config('settings.currency_list') as $currency)
                                                    <option value="{{ $currency['code'] }}" 
                                                            data-icon="{{ $currency['symbol'] }}" 
                                                            {{ old('currency_name', $setting->currency_name ?? 'USD') == $currency['code'] ? 'selected' : '' }}>
                                                        {{ $currency['name'] }} ({{ $currency['code'] }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="currency_name" id="currency_name" value="{{ old('currency_name', $setting->currency_name ?? 'USD') }}">
                                            <input type="hidden" name="currency_icon" id="currency_icon" value="{{ old('currency_icon', $setting->currency_icon ?? '$') }}">
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                <i class="fas fa-info-circle" style="color: #2563eb;"></i>
                                                <span class="ml-1">Select the currency you want to use as system default</span>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group text-center">
                                            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                                <i class="fas fa-dollar-sign" style="color: #2563eb; width: 18px;"></i>
                                                <span class="ml-1">Currency Icon</span>
                                            </label>
                                            <div class="currency-icon-box mt-2" id="system_icon_display" 
                                                 style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 3px dashed #2563eb; border-radius: 16px; padding: 20px; text-align: center; font-size: 4rem; font-weight: 700; color: #2563eb; min-height: 100px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                                {{ old('currency_icon', $setting->currency_icon ?? '$') }}
                                            </div>
                                            <small class="text-muted" style="font-size: 0.75rem;">Preview of selected currency symbol</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-blue border-0 rounded-lg mt-3" style="border-radius: 12px; padding: 1rem 1.2rem; background: linear-gradient(135deg, #eff6ff, #dbeafe);">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle fa-2x mr-3" style="color: #2563eb;"></i>
                                        <div>
                                            <strong class="d-block" style="color: #1d4ed8;">Information</strong>
                                            <span class="text-muted" style="font-size: 0.9rem;">This currency will be used as the default currency for pricing and reports.</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit Buttons --}}
                                <div class="row mt-4 pt-3 border-top">
                                    <div class="col-12">
                                        <div class="d-flex flex-column flex-sm-row justify-content-sm-end gap-2 gap-sm-0">
                                            <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary px-4 order-2 order-sm-1" 
                                               style="border-radius: 10px; min-height: 44px; width: 100%; width: auto;">
                                                <i class="fas fa-arrow-left mr-1"></i> Back
                                            </a>
                                            <button type="submit" class="btn px-5 shadow-sm order-1 order-sm-2" 
                                                    style="background: #2563eb; color: #ffffff; border: none; border-radius: 10px; min-height: 44px; width: 100%; width: auto;">
                                                <i class="fas fa-save mr-2"></i> Save Currency Settings
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
       CURRENCY SETTINGS - NIL (BLUE) THEME
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

    ::placeholder {
        color: #adb5bd !important;
        font-size: 0.9rem;
        opacity: 0.7;
    }

    /* Currency Icon Box */
    .currency-icon-box {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 3px dashed #2563eb;
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        font-size: 4rem;
        font-weight: 700;
        color: #2563eb;
        min-height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .currency-icon-box:hover {
        border-color: #1d4ed8;
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        transform: scale(1.02);
        box-shadow: 0 4px 20px rgba(37, 99, 235, 0.15);
    }

    /* Select2 */
    .select2-container--default .select2-selection--single {
        border-radius: 10px !important;
        border: 2px solid #e2e8f0 !important;
        height: 44px !important;
        padding: 0 12px;
        transition: all 0.3s ease;
        background: #fafbfc !important;
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        font-size: 0.95rem;
        color: #495057;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
    }

    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
    }

    .select2-dropdown {
        border-radius: 10px !important;
        border-color: #e2e8f0 !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: #2563eb !important;
    }

    /* Alert */
    .alert {
        border-radius: 12px !important;
        border: none !important;
        padding: 1rem 1.2rem !important;
    }

    .alert-blue {
        background: linear-gradient(135deg, #eff6ff, #dbeafe) !important;
        color: #1d4ed8 !important;
    }

    .alert-blue .text-muted {
        color: #1e40af !important;
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
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    .btn:active {
        transform: scale(0.97);
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
        .form-control {
            height: 40px !important;
            font-size: 0.85rem !important;
        }
        .btn {
            font-size: 0.8rem !important;
            min-height: 38px !important;
            padding: 0 1rem !important;
            width: 100% !important;
        }
        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
        }
        .section-header .breadcrumb {
            font-size: 0.7rem !important;
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
        .border-top {
            margin-top: 15px !important;
            padding-top: 15px !important;
        }
        .row .col-12.col-md-4 {
            margin-top: 15px !important;
        }
        .currency-icon-box {
            font-size: 3rem !important;
            min-height: 80px !important;
            padding: 15px !important;
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
        .alert {
            padding: 0.8rem 1rem !important;
        }
        .alert .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        .alert .fa-2x {
            margin-bottom: 8px !important;
            font-size: 1.8rem !important;
        }
        .alert .text-muted {
            font-size: 0.8rem !important;
        }

        /* Mobile Button Fix */
        .d-flex.flex-column.flex-sm-row {
            gap: 10px !important;
        }
        .d-flex.flex-column.flex-sm-row .btn {
            width: 100% !important;
            justify-content: center !important;
        }
        .d-flex.flex-column.flex-sm-row .order-1,
        .d-flex.flex-column.flex-sm-row .order-2 {
            order: unset !important;
        }
        .gap-2 {
            gap: 10px !important;
        }

        .section-header .ml-auto .section-header-breadcrumb {
            width: 100% !important;
        }
        .section-header .ml-auto .btn {
            width: 100% !important;
            margin-left: 0 !important;
        }
        .text-right {
            text-align: center !important;
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
        .currency-icon-box {
            font-size: 3.5rem !important;
            min-height: 90px !important;
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
        .row .col-12.col-md-4 {
            margin-top: 10px !important;
        }
        .d-flex.flex-column.flex-sm-row .btn {
            width: auto !important;
        }
        .section-header .ml-auto {
            flex-wrap: wrap !important;
            gap: 5px !important;
        }
        .card-header .d-flex {
            flex-wrap: wrap !important;
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
            font-size: 0.75rem !important;
        }
        .text-right {
            text-align: center !important;
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
        .currency-icon-box {
            font-size: 3.8rem !important;
            min-height: 95px !important;
        }
        .select2-container--default .select2-selection--single {
            height: 44px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
        }
        .d-flex.flex-column.flex-sm-row .btn {
            width: auto !important;
        }
    }

    @media (min-width: 992px) {
        .card-body {
            padding: 30px !important;
        }
        .form-control {
            height: 44px !important;
        }
        .currency-icon-box {
            font-size: 4rem !important;
            min-height: 100px !important;
        }
        .select2-container--default .select2-selection--single {
            height: 44px !important;
        }
        .d-flex.flex-column.flex-sm-row .btn {
            width: auto !important;
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

    /* Text muted with icons */
    .text-muted i {
        color: #2563eb !important;
    }

    /* Border top */
    .border-top {
        border-top: 2px solid #e2e8f0 !important;
    }

    /* Focus glow effect */
    .form-control:focus {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    }

    /* Select2 focus */
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    }

    /* Alert icon color */
    .alert-blue .fa-info-circle {
        color: #2563eb !important;
    }

    /* Gap Utility */
    .gap-2 {
        gap: 0.5rem !important;
    }

    .gap-sm-0 {
        gap: 0 !important;
    }

    @media (max-width: 575.98px) {
        .gap-sm-0 {
            gap: 0.5rem !important;
        }
    }

    /* Currency Icon Box hover */
    .currency-icon-box:hover {
        border-color: #1d4ed8 !important;
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%) !important;
        transform: scale(1.02) !important;
        box-shadow: 0 4px 20px rgba(37, 99, 235, 0.15) !important;
    }
</style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#system_currency_select').on('change', function () {
                const code = $(this).val();
                const icon = $(this).find(':selected').data('icon');

                $('#currency_name').val(code);
                $('#currency_icon').val(icon);
                $('#system_icon_display').text(icon);
            });

            // Initialize Select2
            $('.select2').select2({
                width: '100%',
                placeholder: 'Select Currency',
                allowClear: true
            });
        });
    </script>
@endpush