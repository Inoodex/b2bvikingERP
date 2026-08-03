@extends('backend.layouts.master')

@section('title', 'Supplier Ledger & Accounts Payable Summary')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-book text-primary mr-2"></i> Supplier Ledger & Accounts Payable (AP)</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Accounts Payable</div>
            <div class="breadcrumb-item">Supplier Ledger</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-hero card-primary">
                    <div class="card-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="card-description">Total Outstanding Payables</div>
                    <div class="card-header">
                        <div class="hashtag">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($agingSummary->sum('total_due'), 2) }}</div>
                        <div class="card-title">Across {{ $vendors->count() }} Suppliers</div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Accounts Payable Quick Actions</h4>
                        <div>
                            <a href="{{ route('admin.vendor-ledger.aging') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-clock mr-1"></i> View AP Aging Report
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Select a supplier below to inspect their running Statement of Account, billed invoices, debit note adjustments, and payment history.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4>Supplier Directory & Payables Balance</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Supplier Name</th>
                                        <th>Phone / Email</th>
                                        <th class="text-right">Current Outstanding</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vendors as $vendor)
                                        @php
                                            $vAging = $agingSummary->firstWhere('vendor_name', $vendor->name);
                                            $due = $vAging['total_due'] ?? 0.00;
                                        @endphp
                                        <tr>
                                            <td><code>{{ $vendor->code ?? 'V-'.$vendor->id }}</code></td>
                                            <td><strong>{{ $vendor->name }}</strong></td>
                                            <td>{{ $vendor->phone }}<br><small class="text-muted">{{ $vendor->email }}</small></td>
                                            <td class="text-right font-weight-bold text-{{ $due > 0 ? 'danger' : 'success' }}">
                                                {{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($due, 2) }}
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.vendor-ledger.show', $vendor->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-file-alt mr-1"></i> Statement
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
