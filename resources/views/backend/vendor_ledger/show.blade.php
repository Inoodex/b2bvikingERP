@extends('backend.layouts.master')

@section('title', 'Statement of Account: ' . $statement['vendor']->name)

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.vendor-ledger.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Supplier Statement of Account</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.vendor-ledger.index') }}">Supplier Ledger</a></div>
            <div class="breadcrumb-item">Statement</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-user-tag text-primary mr-2"></i> {{ $statement['vendor']->name }}</h4>
                <a href="{{ route('admin.vendor-ledger.pdf', ['vendor_id' => $statement['vendor']->id, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-danger btn-sm" target="_blank">
                    <i class="fas fa-file-pdf mr-1"></i> Print PDF Statement
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.vendor-ledger.show', $statement['vendor']->id) }}" method="GET" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label>From Date:</label>
                            <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                        </div>
                        <div class="col-md-4">
                            <label>To Date:</label>
                            <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary mr-1"><i class="fas fa-filter"></i> Filter</button>
                            <a href="{{ route('admin.vendor-ledger.show', $statement['vendor']->id) }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </div>
                </form>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center border">
                            <small class="text-muted font-weight-bold">TOTAL BILLED</small>
                            <h4 class="text-dark mb-0">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($statement['total_billed'], 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center border">
                            <small class="text-muted font-weight-bold">TOTAL PAID</small>
                            <h4 class="text-success mb-0">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($statement['total_paid'], 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center border">
                            <small class="text-muted font-weight-bold">DEBIT NOTES / CLAIMS</small>
                            <h4 class="text-warning mb-0">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($statement['total_debit_notes'], 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center border border-danger">
                            <small class="text-muted font-weight-bold">CURRENT OUTSTANDING</small>
                            <h4 class="text-danger mb-0">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($statement['outstanding_balance'], 2) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr class="bg-light">
                                <th>Date</th>
                                <th>Type</th>
                                <th>Reference</th>
                                <th>PO Ref</th>
                                <th class="text-right">Billed (Debit)</th>
                                <th class="text-right">Paid / Claim (Credit)</th>
                                <th class="text-right">Running Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($statement['transactions'] as $tx)
                                <tr>
                                    <td>{{ $tx['date'] }}</td>
                                    <td>
                                        @if($tx['type'] == 'Bill')
                                            <span class="badge badge-primary">Bill</span>
                                        @elseif($tx['type'] == 'Payment')
                                            <span class="badge badge-success">Payment</span>
                                        @else
                                            <span class="badge badge-warning">Debit Note</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $tx['reference'] }}</code></td>
                                    <td>{{ $tx['po_no'] }}</td>
                                    <td class="text-right">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($tx['debit'], 2) }}</td>
                                    <td class="text-right text-success">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($tx['credit'], 2) }}</td>
                                    <td class="text-right font-weight-bold text-{{ $tx['running_balance'] > 0 ? 'danger' : 'dark' }}">
                                        {{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($tx['running_balance'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No transactions found for this supplier within selected date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
