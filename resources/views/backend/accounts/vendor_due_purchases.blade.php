@extends('backend.layouts.master')

@section('title', 'Vendor Due Purchases')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Accounts - Vendor Due Purchases</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-md-6 col-lg-3">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Invoices Due</h4>
                            </div>
                            <div class="card-body">{{ number_format($summary['count']) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Due</h4>
                            </div>
                            <div class="card-body">{!! formatConverted($summary['total_due']) !!}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h4>Outstanding Vendor Balances</h4>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4" id="vendor-due-filter-form">
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
                            <div class="col-md-3">
                                <label>Search</label>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Invoice or vendor">
                            </div>
                            <div class="col-md-2 d-flex align-items-end justify-content-end">
                                <a href="{{ route('admin.accounts.vendor-payments.due-purchases') }}" class="btn btn-light btn-block">Reset</a>
                            </div>
                            <div class="col-12 mt-3 text-right">
                                <small class="text-muted">Filters apply automatically.</small>
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
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Payment Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $purchase)
                                    @php
                                        $paymentClass = match($purchase->payment_status) {
                                            'paid' => 'badge-success',
                                            'partial' => 'badge-warning',
                                            default => 'badge-secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($purchase->date)->format('d M, Y') }}</td>
                                        <td>{{ $purchase->invoice_no }}</td>
                                        <td>
                                            <strong>{{ $purchase->vendor->shop_name ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">{{ $purchase->vendor->phone ?? 'N/A' }}</small>
                                        </td>
                                        <td>{!! formatConverted($purchase->total_amount) !!}</td>
                                        <td class="text-success font-weight-bold">{!! formatConverted($purchase->paid_amount) !!}</td>
                                        <td class="text-danger font-weight-bold">{!! formatConverted($purchase->due_amount) !!}</td>
                                        <td><span class="badge {{ $paymentClass }}">{{ ucfirst($purchase->payment_status ?? 'pending') }}</span></td>
                                        <td class="text-center text-nowrap">
                                            <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-sm btn-primary" title="Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.accounts.vendor-payments.record-payment', ['invoice_no' => $purchase->invoice_no]) }}" class="btn btn-sm btn-dark" title="Pay Now">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No due purchases found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $purchases->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const $form = $('#vendor-due-filter-form');
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
