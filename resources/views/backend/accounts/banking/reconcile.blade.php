@extends('backend.layouts.master')
@section('title', 'Bank Reconciliation — Match Statement with GL')

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-sync text-primary mr-2"></i> Bank Reconciliation</h1>
            <p class="text-muted mb-0 small">Audit and reconcile Bank Statement against General Ledger double-entry lines</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.bank-accounts.index') }}">Bank Accounts</a></div>
            <div class="breadcrumb-item active">Reconcile</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <!-- Left Column: Filter & Reconciliation Form -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-search text-primary mr-2"></i> Select Bank Account</h5>
                        <form method="GET" action="{{ route('admin.bank-reconciliation.index') }}" class="form-inline">
                            <select name="bank_account_id" class="form-control select2 mr-2" onchange="this.form.submit()">
                                @foreach($bankAccounts as $bank)
                                    <option value="{{ $bank->id }}" {{ $selectedBank && $selectedBank->id == $bank->id ? 'selected' : '' }}>
                                        {{ $bank->bank_name }} - {{ $bank->account_name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="card-body p-4">
                        @if($selectedBank)
                            <form action="{{ route('admin.bank-reconciliation.reconcile') }}" method="POST">
                                @csrf
                                <input type="hidden" name="bank_account_id" value="{{ $selectedBank->id }}">

                                <div class="row mb-4">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Statement Date <span class="text-danger">*</span></label>
                                        <input type="date" name="statement_date" class="form-control" value="{{ now()->toDateString() }}" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Ending Statement Balance (DKK) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="statement_balance" id="stmt_balance" class="form-control font-weight-bold text-dark" value="{{ $glBalance }}" required>
                                    </div>
                                </div>

                                <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-list-check text-info mr-2"></i> Unreconciled Transactions</h6>
                                
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered table-striped table-hover align-middle mb-0">
                                        <thead class="bg-light text-dark">
                                            <tr>
                                                <th style="width: 5%;" class="text-center">
                                                    <input type="checkbox" id="select-all-tx">
                                                </th>
                                                <th>Date</th>
                                                <th>Ref / Type</th>
                                                <th>Direction</th>
                                                <th class="text-right">Amount (DKK)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($unreconciledTransactions as $tx)
                                                <tr>
                                                    <td class="text-center">
                                                        <input type="checkbox" name="transaction_ids[]" value="{{ $tx->id }}" class="tx-checkbox">
                                                    </td>
                                                    <td>{{ $tx->transaction_date ? $tx->transaction_date->format('d M Y') : 'N/A' }}</td>
                                                    <td>{{ $tx->reference_type ?? 'Manual Entry' }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $tx->type == 'in' ? 'success' : 'danger' }}">
                                                            {{ strtoupper($tx->type) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-right font-weight-bold">{{ number_format($tx->amount, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-success py-4">
                                                        <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                                                        All recorded bank transactions are currently reconciled!
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <button type="submit" class="btn btn-success btn-lg font-weight-bold shadow-sm px-4">
                                    <i class="fas fa-check-double mr-1"></i> Save Reconciliation
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Live Audit Box & History -->
            <div class="col-lg-4">
                @if($selectedBank)
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-balance-scale text-primary mr-2"></i> Account Audit Summary</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="border rounded p-3 bg-light mb-3">
                                <small class="text-muted text-uppercase font-weight-bold d-block">GL Ledger Balance</small>
                                <h4 class="font-weight-bold text-primary mb-0">kr. {{ number_format($glBalance, 2) }}</h4>
                                <small class="text-muted d-block mt-1">General Ledger Book Value</small>
                            </div>
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted text-uppercase font-weight-bold d-block">Unreconciled Items</small>
                                <h5 class="font-weight-bold text-dark mb-0">{{ $unreconciledTransactions->count() }} Transactions</h5>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-history text-muted mr-2"></i> Recent Reconciliation History</h6>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-group list-group-flush">
                            @forelse($reconciliations as $rec)
                                <li class="list-group-item px-0 py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="font-weight-bold text-dark">{{ $rec->bankAccount?->account_name }}</small>
                                        <span class="badge badge-{{ $rec->status == 'reconciled' ? 'success' : 'warning' }} small font-weight-bold">
                                            {{ ucfirst($rec->status) }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span>Stmt: kr. {{ number_format($rec->statement_balance, 2) }}</span>
                                        <span>{{ $rec->statement_date ? $rec->statement_date->format('d M Y') : '' }}</span>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item px-0 text-muted small text-center">No reconciliation logs found.</li>
                            @endforelse
                        </ul>
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
    $('#select-all-tx').on('change', function() {
        $('.tx-checkbox').prop('checked', $(this).prop('checked'));
    });
});
</script>
@endpush
