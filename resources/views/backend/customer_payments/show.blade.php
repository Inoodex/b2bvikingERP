@extends('backend.layouts.master')

@section('title', 'Payment Receipt #' . $customerPayment->payment_no)

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <div>
            <h1>💳 Payment Receipt Voucher #{{ $customerPayment->payment_no }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.customer-payments.index') }}">Payments</a></div>
                <div class="breadcrumb-item">Receipt #{{ $customerPayment->payment_no }}</div>
            </div>
        </div>
        <div>
            <a href="{{ route('admin.customer-payments.pdf', $customerPayment->id) }}" target="_blank" class="btn btn-danger font-weight-bold shadow-sm mr-2" style="border-radius: 6px;">
                <i class="fas fa-file-pdf mr-1"></i> Print / Download PDF
            </a>
            <a href="{{ route('admin.customer-payments.index') }}" class="btn btn-secondary font-weight-bold shadow-sm" style="border-radius: 6px;">
                <i class="fas fa-arrow-left mr-1"></i> Back to Registry
            </a>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-md-8">
                <div class="card card-primary shadow-sm">
                    <div class="card-header d-flex justify-content-between">
                        <h4>Receipt Summary</h4>
                        <span class="badge badge-success px-3 py-2 font-weight-bold" style="border-radius: 6px;">
                            <i class="fas fa-check-circle mr-1"></i> POSTED & GL SYNCED
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4 p-3 bg-light rounded">
                            <div class="col-md-6">
                                <span class="text-muted small text-uppercase font-weight-bold d-block">Received From Customer</span>
                                <h5 class="mb-1 text-primary">{{ $customerPayment->user ? ($customerPayment->user->outlet_name ?: $customerPayment->user->name) : 'Guest / Cash' }}</h5>
                                <p class="mb-0 text-muted small">{{ $customerPayment->user ? $customerPayment->user->email : '' }}</p>
                            </div>
                            <div class="col-md-6 text-md-right">
                                <span class="text-muted small text-uppercase font-weight-bold d-block">Total Amount Received</span>
                                <h2 class="mb-0 text-success font-weight-bold">kr. {{ number_format((float)$customerPayment->amount, 2) }}</h2>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th style="width: 35%;" class="bg-light">Payment Receipt No</th>
                                        <td class="font-weight-bold text-dark">{{ $customerPayment->payment_no }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Payment Date</th>
                                        <td>{{ $customerPayment->payment_date ? $customerPayment->payment_date->format('d F, Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Payment Method</th>
                                        <td>
                                            <span class="badge badge-primary px-3 py-1 font-weight-bold">
                                                {{ strtoupper(str_replace('_', ' ', $customerPayment->payment_method)) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Reference / Cheque / Txn No</th>
                                        <td>{{ $customerPayment->reference_no ?: 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Linked Sales Invoice</th>
                                        <td>
                                            @if($customerPayment->invoice)
                                                <a href="{{ route('admin.sales-invoices.show', $customerPayment->sales_invoice_id) }}" class="font-weight-bold text-primary">
                                                    {{ $customerPayment->invoice->invoice_no }}
                                                </a>
                                                (Remaining Due: kr. {{ number_format((float)$customerPayment->invoice->due_amount, 2) }})
                                            @else
                                                <span class="text-muted">N/A (Customer Advance / Deposit)</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Deposit Account (General Ledger)</th>
                                        <td>{{ $customerPayment->account ? $customerPayment->account->account_code . ' - ' . $customerPayment->account->account_name : 'Default Cash/Bank Account' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Created By</th>
                                        <td>{{ $customerPayment->creator ? $customerPayment->creator->name : 'System' }}</td>
                                    </tr>
                                    @if($customerPayment->notes)
                                        <tr>
                                            <th class="bg-light">Internal Notes</th>
                                            <td>{{ $customerPayment->notes }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-dark shadow-sm">
                    <div class="card-header">
                        <h4>General Ledger Journal Posting</h4>
                    </div>
                    <div class="card-body">
                        @if($customerPayment->journalEntry)
                            <div class="alert alert-success p-2 small mb-3">
                                <i class="fas fa-link mr-1"></i> Journal Entry <strong>{{ $customerPayment->journalEntry->entry_no }}</strong>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0 small">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Account</th>
                                            <th class="text-right">Dr (kr)</th>
                                            <th class="text-right">Cr (kr)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customerPayment->journalEntry->lines as $line)
                                            <tr>
                                                <td>{{ $line->account ? $line->account->account_name : 'Account' }}</td>
                                                <td class="text-right font-weight-bold text-success">{{ $line->debit > 0 ? number_format((float)$line->debit, 2) : '-' }}</td>
                                                <td class="text-right font-weight-bold text-primary">{{ $line->credit > 0 ? number_format((float)$line->credit, 2) : '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <span class="text-muted small">GL Journal Entry Auto-Posted</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
