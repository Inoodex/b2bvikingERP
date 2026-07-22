@extends('backend.layouts.master')

@section('title', 'Vendor Payment History')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Accounts - Vendor Payment History</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-md-6 col-lg-3">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Payments</h4>
                            </div>
                            <div class="card-body">{{ number_format($summary['count']) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success">
                            <i class="fas fa-money-check-alt"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Paid</h4>
                            </div>
                            <div class="card-body">{!! formatConverted($summary['total_amount']) !!}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h4>Vendor Payment Ledger</h4>
                    <div class="card-header-action">
                        <a href="{{ route('admin.accounts.vendor-payments.pdf', request()->except('page')) }}" class="btn btn-primary btn-sm mr-2">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </a>
                        <a href="{{ route('admin.accounts.vendor-payments.pdf.view', request()->except('page')) }}" class="btn btn-outline-secondary btn-sm mr-2" target="_blank">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('admin.accounts.vendor-payments.record-payment') }}" class="btn btn-dark btn-sm px-2 py-1">
                            <i class="fas fa-plus-circle"></i> Pay Vendor Invoice
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4" id="vendor-payment-filter-form">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Vendor</label>
                                <select name="vendor_id" class="form-control">
                                    <option value="">All Vendors</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ (string) request('vendor_id') === (string) $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->shop_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>From</label>
                                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label>To</label>
                                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label>Method</label>
                                <select name="method" class="form-control">
                                    <option value="">All Methods</option>
                                    <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank" {{ request('method') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="mobile_banking" {{ request('method') === 'mobile_banking' ? 'selected' : '' }}>Mobile Pay</option>
                                    <option value="cheque" {{ request('method') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Search</label>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Invoice, vendor, transaction">
                            </div>
                            <div class="col-12 mt-3 d-flex justify-content-end align-items-center">
                                <small class="text-muted mr-3">Filters apply automatically.</small>
                                <a href="{{ route('admin.accounts.vendor-payments.index') }}" class="btn btn-light">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice No</th>
                                    <th>Vendor</th>
                                    <th>Method</th>
                                    <th>Trans ID</th>
                                    <th>Receipts</th>
                                    <th>Amount</th>
                                    <th>Note</th>
                                    <th class="text-center">PDF</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->created_at->format('d M, Y h:i A') }}</td>
                                        <td>{{ $payment->purchase->invoice_no ?? 'N/A' }}</td>
                                        <td>{{ $payment->purchase->vendor->shop_name ?? 'N/A' }}</td>
                                        <td><span class="badge badge-info">{{ strtoupper($payment->payment_method) }}</span></td>
                                        <td>{{ $payment->transaction_id ?: 'N/A' }}</td>
                                        <td>
                                            @if($payment->receipts->count() > 0)
                                                @foreach($payment->receipts as $receipt)
                                                    <div class="mb-1">
                                                        <a href="{{ route('admin.accounts.vendor-payments.receipts.download', $receipt->id) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-download mr-1"></i>
                                                        </a>
                                                        <a href="{{ route('admin.accounts.vendor-payments.receipts.destroy', $receipt->id) }}" class="btn btn-sm btn-outline-danger delete-item">
                                                            <i class="fas fa-trash mr-1"></i>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="font-weight-bold text-success">{!! formatConverted($payment->amount) !!}</td>
                                        <td>{{ $payment->note ?: 'N/A' }}</td>
                                        <td class="text-center text-nowrap">
                                            <a href="{{ route('admin.accounts.vendor-payments.single.pdf', $payment->id) }}" class="btn btn-sm btn-warning" title="Download PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <a href="{{ route('admin.accounts.vendor-payments.single.view', $payment->id) }}" class="btn btn-sm btn-outline-info ml-1" title="View" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            @if($payment->purchase)
                                                <a href="{{ route('admin.purchases.show', $payment->purchase->id) }}" class="btn btn-sm btn-primary" title="Purchase Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">No vendor payments found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const $form = $('#vendor-payment-filter-form');
        const $search = $form.find('input[name="search"]');
        let searchTimer = null;

        $form.find('select, input[type="date"]').on('change', function() {
            $form.trigger('submit');
        });

        $search.on('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                $form.trigger('submit');
            }, 450);
        });

        $search.on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimer);
                $form.trigger('submit');
            }
        });
    });
</script>
@endpush
