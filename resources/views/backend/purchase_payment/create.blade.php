@extends('backend.layouts.master')

@section('title', 'Record Payment Voucher')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.purchase-payments.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Record Procurement Payment Voucher</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.purchase-orders.index') }}">Procurement</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.purchase-payments.index') }}">Payments</a></div>
            <div class="breadcrumb-item">Record Voucher</div>
        </div>
    </div>

    <div class="section-body">
        <form action="{{ route('admin.purchase-payments.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($vendorBill)
                <input type="hidden" name="vendor_bill_id" value="{{ $vendorBill->id }}">
            @endif

            <div class="row">
                <div class="col-md-7">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-money-bill-wave text-primary mr-2"></i> Payment Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Purchase Order <span class="text-danger">*</span></label>
                                <select name="purchase_id" class="form-control select2" required id="poSelect">
                                    <option value="">-- Select PO --</option>
                                    @foreach($purchases as $p)
                                        <option value="{{ $p->id }}" 
                                                data-vendor="{{ $p->vendor_id }}" 
                                                data-due="{{ $p->due_amount }}"
                                                data-currency="{{ $p->currency_id }}"
                                                data-rate="{{ $p->exchange_rate_used ?? 1.0 }}"
                                                {{ ($purchase && $purchase->id == $p->id) ? 'selected' : '' }}>
                                            {{ $p->po_no }} (Vendor: {{ $p->vendor?->name }} | Due: {{ $p->currency?->symbol ?? 'kr.' }}{{ number_format($p->due_amount, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Vendor <span class="text-danger">*</span></label>
                                <select name="vendor_id" class="form-control select2" required id="vendorSelect">
                                    <option value="">-- Select Vendor --</option>
                                    @foreach($vendors as $v)
                                        <option value="{{ $v->id }}" {{ ($purchase && $purchase->vendor_id == $v->id) ? 'selected' : '' }}>
                                            {{ $v->name }} ({{ $v->code ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Payment Date <span class="text-danger">*</span></label>
                                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Payment Method <span class="text-danger">*</span></label>
                                        <select name="payment_method" class="form-control" required id="paymentMethodSelect">
                                            <option value="bank_transfer" selected>Bank Transfer</option>
                                            <option value="cash">Cash</option>
                                            <option value="cheque">Cheque</option>
                                            <option value="lc_settlement">LC Margin Settlement</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Payment Amount <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="amount" id="paymentAmountInput" class="form-control" value="{{ $vendorBill ? $vendorBill->due_amount : ($purchase ? $purchase->due_amount : '') }}" required placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Payment Currency <span class="text-danger">*</span></label>
                                        <select name="currency_id" id="currencySelect" class="form-control">
                                            @foreach($currencies as $c)
                                                <option value="{{ $c->id }}"
                                                        data-code="{{ $c->code }}"
                                                        data-symbol="{{ $c->symbol }}"
                                                        data-rate="{{ $c->exchange_rate ?? 1.0 }}"
                                                        data-is-base="{{ $c->is_base ? '1' : '0' }}"
                                                        {{ ($purchase && $purchase->currency_id == $c->id) || (!$purchase && $c->is_base) ? 'selected' : '' }}>
                                                    {{ $c->code }} ({{ $c->symbol }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Exchange Rate <small class="text-muted">(1 = ? DKK)</small> <span class="text-danger">*</span></label>
                                        <input type="number" step="0.000001" min="0.000001" name="exchange_rate" id="exchangeRateInput" class="form-control"
                                               value="{{ $purchase && $purchase->exchange_rate_used ? $purchase->exchange_rate_used : 1.0 }}" required placeholder="1.0000">
                                    </div>
                                </div>
                            </div>

                            <!-- Live Multi-Currency Conversion Card -->
                            <div class="card mb-3 border-left border-primary shadow-sm" id="liveConverterBox" style="background: #f8fafc; border-left: 4px solid #6777ef !important;">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted text-uppercase font-weight-bold" style="font-size: 11px;">
                                                <i class="fas fa-calculator mr-1 text-primary"></i> Real-time Currency Conversion (DKK Base)
                                            </small>
                                            <div class="mt-1" id="conversionFormulaText">
                                                <span class="font-weight-bold text-dark" id="displayForeignAmount">0.00</span>
                                                <span class="text-muted mx-1">×</span>
                                                <span class="font-weight-bold text-dark" id="displayRate">1.0000</span>
                                                <span class="text-muted mx-1">=</span>
                                                <span class="text-success font-weight-bold h6 mb-0 ml-1" id="displayBaseAmount">kr. 0.00</span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-primary px-2 py-1 font-weight-normal" id="rateStatusBadge">Base Currency</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="bankDetailsRow">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bank Name / Account:</label>
                                        <input type="text" name="bank_name" class="form-control" placeholder="e.g. Danske Bank / Nordea">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Transaction ID / Cheque No:</label>
                                        <input type="text" name="transaction_id" class="form-control" placeholder="Ref/TRX No">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Upload Payment Receipt / Deposit Slip (Optional):</label>
                                <input type="file" name="receipt" class="form-control-file">
                                <small class="form-text text-muted">Allowed formats: PDF, JPG, PNG (Max: 5MB)</small>
                            </div>

                            <div class="form-group">
                                <label>Notes / Payment Description:</label>
                                <textarea name="note" class="form-control" rows="3" placeholder="Enter bank remittance notes..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block btn-lg">
                                <i class="fas fa-paper-plane mr-2"></i> Submit & Issue Payment Voucher
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    @if($vendorBill)
                    <div class="card card-info">
                        <div class="card-header">
                            <h4><i class="fas fa-file-invoice text-info mr-2"></i> Target Bill Information</h4>
                        </div>
                        <div class="card-body">
                            @php
                                $billRate = $vendorBill->purchase?->exchange_rate_used ?? $vendorBill->currency?->exchange_rate ?? $vendorBill->exchange_rate ?? 1;
                                $baseCurrencySymbol = $settings->currency_icon ?? 'kr.';
                                $isForeignBill = $vendorBill->currency_id && ($vendorBill->currency?->code !== 'DKK');
                            @endphp
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Bill No:</strong> <code>{{ $vendorBill->bill_no }}</code>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Grand Total:</strong>
                                    <div class="text-right">
                                        <div>{{ $vendorBill->currency?->symbol ?? ($settings->currency_icon ?? 'kr.') }}{{ number_format($vendorBill->grand_total, 2) }}</div>
                                        @if($isForeignBill && $billRate > 0)
                                            <div class="text-muted small font-weight-bold" style="font-size: 11px;">(≈ {{ $baseCurrencySymbol }}{{ number_format($vendorBill->grand_total * $billRate, 2) }} Base)</div>
                                        @endif
                                    </div>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Already Paid:</strong> {{ $vendorBill->currency?->symbol ?? ($settings->currency_icon ?? 'kr.') }}{{ number_format($vendorBill->paid_amount, 2) }}
                                </li>
                                <li class="list-group-item d-flex justify-content-between text-danger font-weight-bold align-items-center">
                                    <strong>Current Due:</strong>
                                    <div class="text-right">
                                        <div>{{ $vendorBill->currency?->symbol ?? ($settings->currency_icon ?? 'kr.') }}{{ number_format($vendorBill->due_amount, 2) }}</div>
                                        @if($isForeignBill && $billRate > 0)
                                            <div class="text-muted small font-weight-bold" style="font-size: 11px;">(≈ {{ $baseCurrencySymbol }}{{ number_format($vendorBill->due_amount * $billRate, 2) }} Base)</div>
                                        @endif
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @elseif($purchase)
                    <div class="card card-info">
                        <div class="card-header">
                            <h4><i class="fas fa-shopping-cart text-info mr-2"></i> Target PO Information</h4>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>PO No:</strong> <code>{{ $purchase->po_no }}</code>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Total Amount:</strong> {{ $purchase->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($purchase->total_amount, 2) }}
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Paid Amount:</strong> {{ $purchase->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($purchase->paid_amount, 2) }}
                                </li>
                                <li class="list-group-item d-flex justify-content-between text-danger font-weight-bold">
                                    <strong>Remaining Due:</strong> {{ $purchase->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($purchase->due_amount, 2) }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function calculateLiveConversion() {
        var amount = parseFloat($('#paymentAmountInput').val()) || 0;
        var rate = parseFloat($('#exchangeRateInput').val()) || 1;
        var selectedOpt = $('#currencySelect option:selected');
        var symbol = selectedOpt.data('symbol') || '';
        var code = selectedOpt.data('code') || 'DKK';
        var isBase = selectedOpt.data('is-base') == 1 || code === 'DKK';

        var baseTotal = (amount * rate).toFixed(2);

        $('#displayForeignAmount').text(symbol + ' ' + amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ' + code);
        $('#displayRate').text(rate.toFixed(4));
        $('#displayBaseAmount').text('kr. ' + Number(baseTotal).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        if (isBase && rate === 1) {
            $('#rateStatusBadge').removeClass('badge-warning').addClass('badge-primary').text('DKK Base Currency');
        } else {
            $('#rateStatusBadge').removeClass('badge-primary').addClass('badge-warning text-dark').text('Multi-Currency Active');
        }
    }

    $('#paymentAmountInput, #exchangeRateInput').on('input change', function() {
        calculateLiveConversion();
    });

    $('#currencySelect').on('change', function() {
        var selectedOpt = $(this).find('option:selected');
        var defaultRate = parseFloat(selectedOpt.data('rate')) || 1.0;
        $('#exchangeRateInput').val(defaultRate);
        calculateLiveConversion();
    });

    $('#poSelect').on('change', function() {
        var opt = $(this).find('option:selected');
        var vendorId = opt.data('vendor');
        var due = opt.data('due');
        var currencyId = opt.data('currency');
        var rate = opt.data('rate');

        if (vendorId) {
            $('#vendorSelect').val(vendorId).trigger('change');
        }
        if (due !== undefined && due !== '') {
            $('#paymentAmountInput').val(due);
        }
        if (currencyId) {
            $('#currencySelect').val(currencyId).trigger('change');
        }
        if (rate) {
            $('#exchangeRateInput').val(rate);
        }
        calculateLiveConversion();
    });

    calculateLiveConversion();
});
</script>
@endpush
