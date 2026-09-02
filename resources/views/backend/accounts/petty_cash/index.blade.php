@extends('backend.layouts.master')
@section('title', 'Petty Cash Register — Daily Cash Floats & Expenses')

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-coins text-warning mr-2"></i> Petty Cash Register</h1>
            <p class="text-muted mb-0 small">Daily Office Float, Minor Expense Vouchers & Instant GL Double-Entry</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.chart-of-accounts.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Petty Cash</div>
        </div>
    </div>

    <!-- 3 Live KPI Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #28a745 !important;">
                <div class="card-icon bg-success text-white">
                    <i class="fas fa-cash-register fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Current Float Balance</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">
                        kr. {{ number_format($currentFloat, 2) }}
                    </div>
                    <small class="text-success font-weight-bold"><i class="fas fa-vault mr-1"></i> Cash on Hand (1010)</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #dc3545 !important;">
                <div class="card-icon bg-danger text-white">
                    <i class="fas fa-receipt fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Today's Cash Outflow</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">
                        kr. {{ number_format($todayOut, 2) }}
                    </div>
                    <small class="text-danger font-weight-bold"><i class="fas fa-arrow-down mr-1"></i> Minor Expenses</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #17a2b8 !important;">
                <div class="card-icon bg-info text-white">
                    <i class="fas fa-history fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total Inflow Top-up</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">
                        kr. {{ number_format($totalIn, 2) }}
                    </div>
                    <small class="text-info font-weight-bold"><i class="fas fa-university mr-1"></i> Bank Replenishments</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section-body">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-list text-primary mr-2"></i> Petty Cash Voucher History</h5>
                <div>
                    <button type="button" class="btn btn-outline-success font-weight-bold mr-2" data-toggle="modal" data-target="#modalTopUpPettyCash">
                        <i class="fas fa-plus mr-1"></i> Replenish Float (In)
                    </button>
                    <button type="button" class="btn btn-danger font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalExpensePettyCash">
                        <i class="fas fa-minus-circle mr-1"></i> Record Expense (Out)
                    </button>
                </div>
            </div>
            <div class="card-body p-4 table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 15%;">Date & Time</th>
                            <th style="width: 10%;" class="text-center">Type</th>
                            <th style="width: 40%;">Purpose / Voucher Note</th>
                            <th style="width: 15%;" class="text-right">Amount (DKK)</th>
                            <th style="width: 15%;">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $idx => $t)
                            <tr>
                                <td>{{ $transactions->firstItem() + $idx }}</td>
                                <td>{{ $t->created_at ? $t->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                <td class="text-center">
                                    @if($t->type == 'in')
                                        <span class="badge badge-success px-2 py-1 font-weight-bold"><i class="fas fa-arrow-up mr-1"></i> FLOAT IN</span>
                                    @else
                                        <span class="badge badge-danger px-2 py-1 font-weight-bold"><i class="fas fa-arrow-down mr-1"></i> EXPENSE</span>
                                    @endif
                                </td>
                                <td><strong>{{ $t->purpose }}</strong></td>
                                <td class="text-right font-weight-bold {{ $t->type == 'in' ? 'text-success' : 'text-danger' }}">
                                    {{ $t->type == 'in' ? '+' : '-' }} kr. {{ number_format($t->amount, 2) }}
                                </td>
                                <td>{{ $t->creator?->name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-coins fa-2x mb-2 d-block text-muted"></i>
                                    No petty cash transactions recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal: Replenish Petty Cash (In) -->
<div class="modal fade" id="modalTopUpPettyCash" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <form action="{{ route('admin.petty-cash.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="in">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title font-weight-bold text-white"><i class="fas fa-plus-circle mr-2"></i> Replenish Petty Cash Float</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Top-up Amount (DKK) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="e.g. 5000.00" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark">Voucher Purpose / Note <span class="text-danger">*</span></label>
                        <input type="text" name="purpose" class="form-control" placeholder="e.g. Cash withdrawal from Danske Bank for weekly office float" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3">
                    <button type="button" class="btn btn-light font-weight-bold border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success font-weight-bold shadow-sm"><i class="fas fa-check mr-1"></i> Post Replenishment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Record Expense (Out) -->
<div class="modal fade" id="modalExpensePettyCash" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <form action="{{ route('admin.petty-cash.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="out">
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-title font-weight-bold text-white"><i class="fas fa-minus-circle mr-2"></i> Record Minor Cash Expense</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Expense Amount (DKK) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="e.g. 150.00" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Expense Account Head</label>
                        <select name="expense_acc" class="form-control select2">
                            <option value="5010">5010 — General & Office Expenses</option>
                            @foreach($expenseAccounts as $exp)
                                <option value="{{ $exp->account_code }}">{{ $exp->account_code }} — {{ $exp->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark">Purpose / Receipt Memo <span class="text-danger">*</span></label>
                        <input type="text" name="purpose" class="form-control" placeholder="e.g. Office tea, coffee & cleaning supplies" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3">
                    <button type="button" class="btn btn-light font-weight-bold border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger font-weight-bold shadow-sm"><i class="fas fa-check mr-1"></i> Post Expense Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
