@extends('backend.layouts.master')

@section('title', 'Payment Voucher: ' . ($payment->payment_no ?? ('PAY-' . $payment->id)))

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.purchase-payments.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Payment Voucher: <code>{{ $payment->payment_no ?? ('PAY-'.$payment->id) }}</code></h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.purchase-payments.index') }}">Payments</a></div>
            <div class="breadcrumb-item">Voucher Details</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header d-flex justify-content-between">
                        <h4><i class="fas fa-receipt text-primary mr-2"></i> Voucher Overview</h4>
                        <a href="{{ route('admin.purchase-payments.pdf', $payment->id) }}" class="btn btn-danger btn-sm" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i> Print PDF Slip
                        </a>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Voucher No:</strong> <code>{{ $payment->payment_no ?? ('PAY-'.$payment->id) }}</code>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Payment Date:</strong> {{ $payment->payment_date ? $payment->payment_date->format('d M Y') : $payment->created_at->format('d M Y') }}
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Supplier:</strong> {{ $payment->vendor?->name }}
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>PO Reference:</strong>
                                <a href="{{ route('admin.purchase-orders.show', $payment->purchase_id) }}" target="_blank">
                                    {{ $payment->purchase?->po_no }}
                                </a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                            </li>
                            @if($payment->bank_name)
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Bank Name:</strong> {{ $payment->bank_name }}
                            </li>
                            @endif
                            @if($payment->transaction_id)
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Transaction ID / Ref:</strong> <code>{{ $payment->transaction_id }}</code>
                            </li>
                            @endif
                            <li class="list-group-item d-flex justify-content-between bg-light">
                                <strong class="h6 mb-0">Paid Amount:</strong>
                                <strong class="h5 mb-0 text-success">{{ $payment->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($payment->amount, 2) }}</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4><i class="fas fa-paperclip text-primary mr-2"></i> Receipts & Documents</h4>
                    </div>
                    <div class="card-body">
                        @if($payment->receipts && $payment->receipts->count() > 0)
                            <div class="list-group">
                                @foreach($payment->receipts as $r)
                                    <a href="{{ asset('storage/' . $r->file_path) }}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-file-pdf text-danger mr-2"></i>
                                            <strong>{{ $r->original_name ?? 'Payment_Receipt.pdf' }}</strong>
                                        </div>
                                        <span class="badge badge-primary badge-pill"><i class="fas fa-download"></i> View</span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted text-center my-4"><i class="fas fa-info-circle mr-1"></i> No receipt attachment uploaded for this voucher.</p>
                        @endif

                        @if($payment->note)
                        <div class="section-title mt-4">Payment Remarks</div>
                        <p class="bg-light p-3 rounded text-dark">{{ $payment->note }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
