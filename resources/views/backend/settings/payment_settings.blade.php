@extends('backend.layouts.master')

@section('title', 'Payment Settings')

@push('css')
<style>
    .payment-settings-card {
        border-radius: 12px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        border: 1px solid #edf2f7;
        background: #ffffff;
    }
    .payment-nav-pills .nav-link {
        border-radius: 8px;
        color: #4a5568;
        font-weight: 600;
        font-size: 0.95rem;
        padding: 14px 20px;
        margin-bottom: 8px;
        border: 1px solid #edf2f7;
        background-color: #ffffff;
        transition: all 0.2s ease-in-out;
    }
    .payment-nav-pills .nav-link:hover {
        background-color: #f7fafc;
        color: #2b6cb0;
    }
    .payment-nav-pills .nav-link.active {
        background-color: #5c6bc0 !important;
        color: #ffffff !important;
        border-color: #5c6bc0 !important;
        box-shadow: 0 4px 12px rgba(92, 107, 192, 0.35);
    }
    .form-control-custom {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 10px 14px;
        font-size: 0.92rem;
        color: #2d3748;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .form-control-custom:focus {
        border-color: #5c6bc0;
        box-shadow: 0 0 0 3px rgba(92, 107, 192, 0.15);
    }
    .form-label-custom {
        font-size: 0.88rem;
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 6px;
    }
    .btn-update-payment {
        background-color: #5c6bc0 !important;
        border-color: #5c6bc0 !important;
        color: #ffffff !important;
        font-weight: 600;
        font-size: 0.92rem;
        padding: 10px 30px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(92, 107, 192, 0.35);
        transition: all 0.2s ease-in-out;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        outline: none !important;
    }
    .btn-update-payment:hover,
    .btn-update-payment:focus,
    .btn-update-payment:active {
        background-color: #4a57a8 !important;
        border-color: #4a57a8 !important;
        color: #ffffff !important;
        box-shadow: 0 6px 16px rgba(74, 87, 168, 0.45) !important;
        transform: translateY(-1px);
        outline: none !important;
    }
    .select2-container--default .select2-selection--single {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0 !important;
        height: 42px !important;
        padding: 6px 12px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        color: #2d3748 !important;
        font-size: 0.92rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
</style>
@endpush

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Settings</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></div>
            <div class="breadcrumb-item">Payment Settings</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card payment-settings-card">
            <div class="card-body p-4 p-md-5">
                <div class="row">
                    <!-- Left Vertical Nav Tabs -->
                    <div class="col-12 col-md-3 mb-4 mb-md-0">
                        @php
                            $activeTab = request()->query('tab', 'paypal');
                        @endphp
                        <div class="nav flex-column nav-pills payment-nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <a class="nav-link {{ $activeTab === 'paypal' ? 'active' : '' }}" id="v-pills-paypal-tab" data-toggle="pill" href="#v-pills-paypal" role="tab" aria-controls="v-pills-paypal" aria-selected="{{ $activeTab === 'paypal' ? 'true' : 'false' }}">
                                Paypal
                            </a>
                            {{-- Payoneer (Commented out for future use) --}}
                            {{--
                            <a class="nav-link {{ $activeTab === 'payoneer' ? 'active' : '' }}" id="v-pills-payoneer-tab" data-toggle="pill" href="#v-pills-payoneer" role="tab" aria-controls="v-pills-payoneer" aria-selected="{{ $activeTab === 'payoneer' ? 'true' : 'false' }}">
                                Payoneer
                            </a>
                            --}}

                            {{-- Mobile Pay (Commented out for future use) --}}
                            {{--
                            <a class="nav-link {{ $activeTab === 'mobile_pay' ? 'active' : '' }}" id="v-pills-mobilepay-tab" data-toggle="pill" href="#v-pills-mobilepay" role="tab" aria-controls="v-pills-mobilepay" aria-selected="{{ $activeTab === 'mobile_pay' ? 'true' : 'false' }}">
                                Mobile Pay
                            </a>
                            --}}
                            <a class="nav-link {{ $activeTab === 'cod' ? 'active' : '' }}" id="v-pills-cod-tab" data-toggle="pill" href="#v-pills-cod" role="tab" aria-controls="v-pills-cod" aria-selected="{{ $activeTab === 'cod' ? 'true' : 'false' }}">
                                COD
                            </a>
                        </div>
                    </div>

                    <!-- Right Form Content Panes -->
                    <div class="col-12 col-md-9 pl-md-4">
                        <div class="tab-content" id="v-pills-tabContent">
                            
                            <!-- TAB 1: PAYPAL -->
                            <div class="tab-pane fade {{ $activeTab === 'paypal' ? 'show active' : '' }}" id="v-pills-paypal" role="tabpanel" aria-labelledby="v-pills-paypal-tab">
                                <form action="{{ route('admin.payment-settings.paypal.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">Paypal Status</label>
                                            <select name="status" class="form-control form-control-custom">
                                                <option value="enable" {{ $paypal->status === 'enable' ? 'selected' : '' }}>Enable</option>
                                                <option value="disable" {{ $paypal->status === 'disable' ? 'selected' : '' }}>Disable</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">Account Mode</label>
                                            <select name="mode" class="form-control form-control-custom">
                                                <option value="sandbox" {{ $paypal->mode === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                                                <option value="live" {{ $paypal->mode === 'live' ? 'selected' : '' }}>Live</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">Country Name</label>
                                            <select name="country_name" id="paypal_country_select" class="form-control form-control-custom select2">
                                                <option value="">Select Country</option>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country }}" {{ old('country_name', $paypal->country_name ?? 'Denmark') === $country ? 'selected' : '' }}>
                                                        {{ $country }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">Currency Name</label>
                                            <select name="currency_name" id="paypal_currency_select" class="form-control form-control-custom select2">
                                                <option value="">Select Currency</option>
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->code }}" {{ (old('currency_name', $paypal->currency_name ?? 'DKK') === $currency->code || old('currency_name', $paypal->currency_name) === $currency->name) ? 'selected' : '' }}>
                                                        {{ $currency->code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label class="form-label-custom">Paypal Client Id</label>
                                            <input type="text" name="client_id" class="form-control form-control-custom" value="{{ old('client_id', $paypal->client_id) }}" placeholder="Enter PayPal Client ID">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label-custom">Paypal Secret Key</label>
                                            <input type="text" name="client_secret" class="form-control form-control-custom" value="{{ old('client_secret', $paypal->client_secret) }}" placeholder="Enter PayPal Secret Key">
                                        </div>

                                        <div class="col-md-6 mb-4">
                                            <label class="form-label-custom">Currency Rate (Per DKK)</label>
                                            <input type="number" step="0.0001" name="currency_rate" class="form-control form-control-custom" value="{{ old('currency_rate', $paypal->currency_rate ?? 1) }}" placeholder="1">
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-1 d-flex align-items-center flex-wrap" style="gap: 12px;">
                                        <button type="submit" class="btn btn-update-payment">
                                            <i class="fas fa-save mr-2"></i> Update
                                        </button>
                                        <button type="button" id="btn-test-paypal" class="btn btn-outline-info font-weight-bold" style="border-radius: 8px; height: 42px; padding: 0 20px;">
                                            <i class="fas fa-plug mr-1"></i> Test Connection
                                        </button>
                                        <span id="paypal-test-status" class="small"></span>
                                    </div>
                                </form>
                            </div>

                            {{-- TAB 2: PAYONEER (Commented out for future use) --}}
                            {{--
                            <div class="tab-pane fade {{ $activeTab === 'payoneer' ? 'show active' : '' }}" id="v-pills-payoneer" role="tabpanel" aria-labelledby="v-pills-payoneer-tab">
                                <form action="{{ route('admin.payment-settings.payoneer.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">Payoneer Status</label>
                                            <select name="status" class="form-control form-control-custom">
                                                <option value="enable" {{ $payoneer->status === 'enable' ? 'selected' : '' }}>Enable</option>
                                                <option value="disable" {{ $payoneer->status === 'disable' ? 'selected' : '' }}>Disable</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">Account Mode</label>
                                            <select name="mode" class="form-control form-control-custom">
                                                <option value="sandbox" {{ $payoneer->mode === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                                                <option value="live" {{ $payoneer->mode === 'live' ? 'selected' : '' }}>Live</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">Country Name</label>
                                            <input type="text" name="country_name" class="form-control form-control-custom" value="{{ old('country_name', $payoneer->country_name ?? 'United States') }}">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">Currency Name</label>
                                            <input type="text" name="currency_name" class="form-control form-control-custom" value="{{ old('currency_name', $payoneer->currency_name ?? 'USD') }}">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label class="form-label-custom">Payoneer Program ID / Client ID</label>
                                            <input type="text" name="client_id" class="form-control form-control-custom" value="{{ old('client_id', $payoneer->client_id) }}">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label-custom">Payoneer Secret Key</label>
                                            <input type="text" name="client_secret" class="form-control form-control-custom" value="{{ old('client_secret', $payoneer->client_secret) }}">
                                        </div>

                                        <div class="col-md-6 mb-4">
                                            <label class="form-label-custom">Currency Rate (Per DKK)</label>
                                            <input type="number" step="0.0001" name="currency_rate" class="form-control form-control-custom" value="{{ old('currency_rate', $payoneer->currency_rate ?? 1) }}">
                                        </div>
                                    </div>

                                    <div>
                                        <button type="submit" class="btn btn-update-payment">Update</button>
                                    </div>
                                </form>
                            </div>
                            --}}

                            {{-- TAB 3: MOBILE PAY (Commented out for future use) --}}
                            {{--
                            <div class="tab-pane fade {{ $activeTab === 'mobile_pay' ? 'show active' : '' }}" id="v-pills-mobilepay" role="tabpanel" aria-labelledby="v-pills-mobilepay-tab">
                                <form action="{{ route('admin.payment-settings.mobile-pay.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">Mobile Pay Status</label>
                                            <select name="status" class="form-control form-control-custom">
                                                <option value="enable" {{ $mobilePay->status === 'enable' ? 'selected' : '' }}>Enable</option>
                                                <option value="disable" {{ $mobilePay->status === 'disable' ? 'selected' : '' }}>Disable</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">Account Mode</label>
                                            <select name="mode" class="form-control form-control-custom">
                                                <option value="sandbox" {{ $mobilePay->mode === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                                                <option value="live" {{ $mobilePay->mode === 'live' ? 'selected' : '' }}>Live</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">Country Name</label>
                                            <input type="text" name="country_name" class="form-control form-control-custom" value="{{ old('country_name', $mobilePay->country_name ?? 'Denmark') }}">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">Currency Name</label>
                                            <input type="text" name="currency_name" class="form-control form-control-custom" value="{{ old('currency_name', $mobilePay->currency_name ?? 'DKK') }}">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label-custom">Merchant / Client ID</label>
                                            <input type="text" name="client_id" class="form-control form-control-custom" value="{{ old('client_id', $mobilePay->client_id) }}">
                                        </div>

                                        <div class="col-md-6 mb-4">
                                            <label class="form-label-custom">Currency Rate (Per DKK)</label>
                                            <input type="number" step="0.0001" name="currency_rate" class="form-control form-control-custom" value="{{ old('currency_rate', $mobilePay->currency_rate ?? 1) }}">
                                        </div>
                                    </div>

                                    <div>
                                        <button type="submit" class="btn btn-update-payment">Update</button>
                                    </div>
                                </form>
                            </div>
                            --}}

                            <!-- TAB 4: COD (Cash On Delivery) -->
                            <div class="tab-pane fade {{ $activeTab === 'cod' ? 'show active' : '' }}" id="v-pills-cod" role="tabpanel" aria-labelledby="v-pills-cod-tab">
                                <form action="{{ route('admin.payment-settings.cod.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">COD Status</label>
                                            <select name="status" class="form-control form-control-custom">
                                                <option value="enable" {{ $cod->status === 'enable' ? 'selected' : '' }}>Enable</option>
                                                <option value="disable" {{ $cod->status === 'disable' ? 'selected' : '' }}>Disable</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">Country Name</label>
                                            <select name="country_name" id="cod_country_select" class="form-control form-control-custom select2">
                                                <option value="">Select Country</option>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country }}" {{ old('country_name', $cod->country_name ?? 'Denmark') === $country ? 'selected' : '' }}>
                                                        {{ $country }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">Currency Name</label>
                                            <select name="currency_name" id="cod_currency_select" class="form-control form-control-custom select2">
                                                <option value="">Select Currency</option>
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->code }}" {{ (old('currency_name', $cod->currency_name ?? 'DKK') === $currency->code || old('currency_name', $cod->currency_name) === $currency->name) ? 'selected' : '' }}>
                                                        {{ $currency->code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label-custom">Currency Rate (Per DKK)</label>
                                            <input type="number" step="0.0001" name="currency_rate" class="form-control form-control-custom" value="{{ old('currency_rate', $cod->currency_rate ?? 1) }}">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label-custom">Cashier Deposit Account (COA)</label>
                                            <select name="deposit_account_id" id="cod_deposit_account_id" class="form-control form-control-custom select2">
                                                <option value="">-- Default (1010 Petty Cash / Cash in Hand) --</option>
                                                @foreach($cashAccounts as $acc)
                                                    <option value="{{ $acc->id }}" {{ (string)old('deposit_account_id', $cod->deposit_account_id) === (string)$acc->id ? 'selected' : '' }}>
                                                        {{ $acc->account_code }} - {{ $acc->account_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 mb-4">
                                            <label class="form-label-custom">Delivery Instructions</label>
                                            <textarea name="instructions" rows="3" class="form-control form-control-custom" placeholder="Payment instructions displayed to driver and customer">{{ old('instructions', $cod->instructions) }}</textarea>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-1">
                                        <button type="submit" class="btn btn-update-payment">
                                            <i class="fas fa-save mr-2"></i> Update
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
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
        if ($.fn.select2) {
            $('.select2').select2({
                width: '100%'
            });
        }

        const currencyCountryMap = @json($currencyCountryMap);
        const countryCurrencyMap = {};
        if (currencyCountryMap) {
            for (const [curr, country] of Object.entries(currencyCountryMap)) {
                countryCurrencyMap[country] = curr;
            }
        }

        let isSyncing = false;

        // Auto-sync country when currency changes in PayPal
        $('#paypal_currency_select').on('change', function() {
            if (isSyncing) return;
            const curr = $(this).val();
            if (currencyCountryMap && currencyCountryMap[curr]) {
                const targetCountry = currencyCountryMap[curr];
                if ($('#paypal_country_select').val() !== targetCountry) {
                    isSyncing = true;
                    $('#paypal_country_select').val(targetCountry).trigger('change');
                    isSyncing = false;
                }
            }
        });

        // Auto-sync currency when country changes in PayPal
        $('#paypal_country_select').on('change', function() {
            if (isSyncing) return;
            const country = $(this).val();
            if (countryCurrencyMap && countryCurrencyMap[country]) {
                const targetCurr = countryCurrencyMap[country];
                if ($('#paypal_currency_select').val() !== targetCurr) {
                    isSyncing = true;
                    $('#paypal_currency_select').val(targetCurr).trigger('change');
                    isSyncing = false;
                }
            }
        });

        // Auto-sync country when currency changes in COD
        $('#cod_currency_select').on('change', function() {
            if (isSyncing) return;
            const curr = $(this).val();
            if (currencyCountryMap && currencyCountryMap[curr]) {
                const targetCountry = currencyCountryMap[curr];
                if ($('#cod_country_select').val() !== targetCountry) {
                    isSyncing = true;
                    $('#cod_country_select').val(targetCountry).trigger('change');
                    isSyncing = false;
                }
            }
        });

        // Auto-sync currency when country changes in COD
        $('#cod_country_select').on('change', function() {
            if (isSyncing) return;
            const country = $(this).val();
            if (countryCurrencyMap && countryCurrencyMap[country]) {
                const targetCurr = countryCurrencyMap[country];
                if ($('#cod_currency_select').val() !== targetCurr) {
                    isSyncing = true;
                    $('#cod_currency_select').val(targetCurr).trigger('change');
                    isSyncing = false;
                }
            }
        });

        // Ensure Select2 elements inside inactive tabs recalculate full width upon tab switch
        $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
            if ($.fn.select2) {
                $('.select2').select2({
                    width: '100%'
                });
            }
        });

        // Test PayPal Connection via AJAX
        $('#btn-test-paypal').on('click', function() {
            const btn = $(this);
            const statusSpan = $('#paypal-test-status');
            const originalHtml = btn.html();

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Testing...');
            statusSpan.html('<span class="text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Connecting to PayPal REST API...</span>');

            const clientId = $('input[name="client_id"]').val();
            const clientSecret = $('input[name="client_secret"]').val();
            const mode = $('select[name="mode"]').val();

            $.ajax({
                url: '{{ route('admin.payment-settings.paypal.test') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    client_id: clientId,
                    client_secret: clientSecret,
                    mode: mode
                },
                success: function(response) {
                    btn.prop('disabled', false).html(originalHtml);
                    statusSpan.html('<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> ' + response.message + '</span>');
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalHtml);
                    const errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Connection failed. Please check credentials.';
                    statusSpan.html('<span class="text-danger font-weight-bold"><i class="fas fa-times-circle mr-1"></i> ' + errorMsg + '</span>');
                }
            });
        });
    });
</script>
@endpush
