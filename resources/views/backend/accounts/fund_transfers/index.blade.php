@extends('backend.layouts.master')
@section('title', 'Fund Transfers — Inter-Account Contra Vouchers')

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-random text-primary mr-2"></i> Inter-Account Fund Transfers</h1>
            <p class="text-muted mb-0 small">Contra Vouchers, Bank-to-Bank / Bank-to-Cash Transfers & Instant Balancing</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.chart-of-accounts.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Fund Transfers</div>
        </div>
    </div>

    <!-- KPI Metric Card -->
    <div class="row mb-4">
        <div class="col-lg-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #17a2b8 !important;">
                <div class="card-icon bg-info text-white">
                    <i class="fas fa-exchange-alt fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total Transferred Volume</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">
                        kr. {{ number_format($totalTransferred, 2) }}
                    </div>
                    <small class="text-info font-weight-bold"><i class="fas fa-shield-alt mr-1"></i> Balanced Contra Journals</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="section-body">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-history text-primary mr-2"></i> Transfer History</h5>
                <button type="button" class="btn btn-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalCreateTransfer">
                    <i class="fas fa-plus-circle mr-1"></i> New Fund Transfer
                </button>
            </div>
            <div class="card-body p-4 table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 15%;">Transfer Date</th>
                            <th style="width: 25%;">From Account (Source)</th>
                            <th style="width: 25%;">To Account (Destination)</th>
                            <th style="width: 15%;" class="text-right">Transferred Amount</th>
                            <th style="width: 15%;" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $idx => $tr)
                            <tr>
                                <td>{{ $transfers->firstItem() + $idx }}</td>
                                <td>{{ $tr->transfer_date ? $tr->transfer_date->format('d M Y') : 'N/A' }}</td>
                                <td>
                                    <strong>{{ $tr->fromAccount?->account_name }}</strong>
                                    <br><small class="text-muted">{{ $tr->fromAccount?->bank_name }}</small>
                                </td>
                                <td>
                                    <strong>{{ $tr->toAccount?->account_name }}</strong>
                                    <br><small class="text-muted">{{ $tr->toAccount?->bank_name }}</small>
                                </td>
                                <td class="text-right font-weight-bold text-primary">kr. {{ number_format($tr->amount, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Posted GL</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-exchange-alt fa-2x mb-2 d-block text-muted"></i>
                                    No fund transfers recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $transfers->links() }}
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal: New Fund Transfer -->
<div class="modal fade" id="modalCreateTransfer" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <form action="{{ route('admin.fund-transfers.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold text-white"><i class="fas fa-random mr-2"></i> Inter-Account Transfer (Contra)</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Transfer Date <span class="text-danger">*</span></label>
                        <input type="date" name="transfer_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Source Account (Debit/Deduct From) <span class="text-danger">*</span></label>
                        <select name="from_account_id" class="form-control select2" required>
                            <option value="">-- Select Source Bank/Vault --</option>
                            @foreach($bankAccounts as $b)
                                <option value="{{ $b->id }}">{{ $b->bank_name }} — {{ $b->account_name }} (Avail: kr. {{ number_format($b->current_balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Destination Account (Credit/Deposit To) <span class="text-danger">*</span></label>
                        <select name="to_account_id" class="form-control select2" required>
                            <option value="">-- Select Destination Bank/Vault --</option>
                            @foreach($bankAccounts as $b)
                                <option value="{{ $b->id }}">{{ $b->bank_name }} — {{ $b->account_name }} (Avail: kr. {{ number_format($b->current_balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark">Transfer Amount (DKK) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control font-weight-bold text-dark" placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3">
                    <button type="button" class="btn btn-light font-weight-bold border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold shadow-sm"><i class="fas fa-check mr-1"></i> Execute Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
