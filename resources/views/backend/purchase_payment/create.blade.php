@extends('backend.layouts.master')

@section('title', 'Record Payment Voucher')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.purchase-payments.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Record Purchase Payment Voucher</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.purchase-payments.index') }}">Payments</a></div>
            <div class="breadcrumb-item">Record Payment</div>
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
                                        <option value="{{ $p->id }}" data-vendor="{{ $p->vendor_id }}" data-due="{{ $p->due_amount }}" {{ ($purchase && $purchase->id == $p->id) ? 'selected' : '' }}>
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
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Payment Amount <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="amount" class="form-control" value="{{ $vendorBill ? $vendorBill->due_amount : ($purchase ? $purchase->due_amount : '') }}" required placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Currency:</label>
                                        <select name="currency_id" class="form-control">
                                            @foreach($currencies as $c)
                                                <option value="{{ $c->id }}" {{ ($purchase && $purchase->currency_id == $c->id) ? 'selected' : '' }}>
                                                    {{ $c->code }} ({{ $c->symbol }})
                                                </option>
                                            @endforeach
                                        </select>
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
