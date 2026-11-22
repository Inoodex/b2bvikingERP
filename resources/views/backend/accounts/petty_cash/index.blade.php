@extends('backend.layouts.master')
@section('title', 'Petty Cash Register — Daily Cash Floats & Expenses')

@push('styles')
<style>
    .petty-cash-page {
        --cash-accent: #E11835;
        --cash-ink: #172033;
        --cash-muted: #748094;
        --cash-line: #e8edf4;
        padding-bottom: 24px;
    }

    .petty-cash-header,
    .petty-summary-card,
    .petty-history-panel {
        background: #fff;
        border: 1px solid var(--cash-line);
        box-shadow: 0 8px 24px rgba(23, 32, 51, 0.045);
    }

    .petty-cash-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        padding: 24px 28px;
        margin-bottom: 22px;
        border-radius: 18px;
        background: linear-gradient(120deg, #fff 0%, #fff 68%, #fff7f8 100%);
    }

    .petty-kicker {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 8px;
        color: var(--cash-accent);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1.1px;
        text-transform: uppercase;
    }

    .petty-page-title {
        margin: 0;
        color: var(--cash-ink);
        font-size: 25px;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .petty-page-description {
        margin: 7px 0 0;
        color: var(--cash-muted);
        font-size: 12px;
    }

    .petty-cash-header .section-header-breadcrumb {
        display: inline-flex !important;
        align-items: center;
        flex-wrap: nowrap;
        gap: 0;
        max-width: 100%;
        flex: 0 0 auto;
        padding: 8px 12px;
        margin-bottom: 2px;
        border: 1px solid var(--cash-line);
        border-radius: 999px;
        background: #fff;
        white-space: nowrap;
    }

    .petty-cash-header .breadcrumb-item {
        display: inline-flex;
        align-items: center;
        font-size: 11px;
    }

    .petty-cash-header .breadcrumb-item + .breadcrumb-item::before {
        padding: 0 8px;
        color: #a0a9b8;
    }

    .petty-summary-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 16px;
        min-height: 128px;
        padding: 21px 22px;
        overflow: hidden;
        border-radius: 16px;
    }

    .petty-summary-icon {
        display: inline-flex;
        flex: 0 0 52px;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 15px;
        font-size: 19px;
    }

    .petty-summary-balance .petty-summary-icon {
        background: #eaf7ef;
        color: #15803d;
    }

    .petty-summary-out .petty-summary-icon {
        background: #fff0f2;
        color: var(--cash-accent);
    }

    .petty-summary-in .petty-summary-icon {
        background: #eef4ff;
        color: #2563eb;
    }

    .petty-summary-label {
        display: block;
        margin-bottom: 5px;
        color: var(--cash-muted);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.65px;
        text-transform: uppercase;
    }

    .petty-summary-value {
        display: block;
        color: var(--cash-ink);
        font-size: 21px;
        font-weight: 800;
        line-height: 1.25;
    }

    .petty-summary-value.is-negative {
        color: var(--cash-accent);
    }

    .petty-summary-note {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 7px;
        color: var(--cash-muted);
        font-size: 10px;
        font-weight: 600;
    }

    .petty-history-panel {
        overflow: hidden;
        border-radius: 18px;
    }

    .petty-history-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 19px 23px;
        border-bottom: 1px solid var(--cash-line);
    }

    .petty-history-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        color: var(--cash-ink);
        font-size: 16px;
        font-weight: 800;
    }

    .petty-history-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #fff0f2;
        color: var(--cash-accent);
    }

    .petty-history-count {
        padding: 4px 9px;
        border-radius: 20px;
        background: #f1f4f8;
        color: #667085;
        font-size: 10px;
        font-weight: 700;
    }

    .petty-actions {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .petty-actions .btn {
        min-height: 36px;
        padding: 8px 13px;
        border-radius: 9px;
        font-size: 11px;
        font-weight: 700;
    }

    .petty-cash-page .petty-actions .btn:not(.btn-social):not(.btn-social-icon):not(.btn-link) {
        background: #fff !important;
        border: 1px solid #dfe5ed !important;
        color: #344054 !important;
        box-shadow: none !important;
    }

    .petty-cash-page .petty-actions .btn.petty-expense-button:not(.btn-social):not(.btn-social-icon):not(.btn-link) {
        background: var(--cash-accent) !important;
        border-color: var(--cash-accent) !important;
        color: #fff !important;
        box-shadow: 0 5px 12px rgba(225, 24, 53, 0.16) !important;
    }

    .petty-table-wrap {
        padding: 0 21px 18px;
    }

    .petty-table {
        min-width: 760px;
        margin-bottom: 0;
        border: 0;
    }

    .petty-table thead th {
        padding: 13px 14px;
        border: 0;
        border-bottom: 1px solid #2563EB;
        background: #2563EB !important;
        background-image: none !important;
        color: #fff !important;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 0.65px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .petty-table tbody td {
        padding: 13px 14px;
        border: 0;
        border-bottom: 1px solid #edf0f4;
        color: #475467;
        font-size: 11px;
        vertical-align: middle;
    }

    .petty-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .petty-table tbody tr:hover {
        background: #fbfcfe;
    }

    .petty-row-number {
        color: #98a2b3 !important;
        font-weight: 700;
    }

    .petty-date {
        color: #667085 !important;
        white-space: nowrap;
    }

    .petty-voucher-type {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        border-radius: 20px;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 0.25px;
        white-space: nowrap;
    }

    .petty-voucher-in {
        background: #eaf7ef;
        color: #16834b;
    }

    .petty-voucher-out {
        background: #fff0f2;
        color: var(--cash-accent);
    }

    .petty-purpose {
        color: var(--cash-ink) !important;
        font-weight: 700;
    }

    .petty-amount {
        font-weight: 800;
        white-space: nowrap;
    }

    .petty-amount-in {
        color: #16834b !important;
    }

    .petty-amount-out {
        color: var(--cash-accent) !important;
    }

    .petty-recorder {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        white-space: nowrap;
    }

    .petty-recorder-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        background: #f1f4f8;
        color: #667085;
        font-size: 10px;
    }

    .petty-empty-state {
        padding: 42px 18px !important;
        text-align: center;
    }

    .petty-empty-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        margin-bottom: 12px;
        border-radius: 18px;
        background: #fff0f2;
        color: var(--cash-accent);
        font-size: 22px;
    }

    .petty-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 15px 22px;
        border-top: 1px solid var(--cash-line);
        color: var(--cash-muted);
        font-size: 11px;
    }

    .petty-cash-modal {
        overflow: hidden;
        border-radius: 16px;
    }

    @media (max-width: 767.98px) {
        .petty-cash-header {
            align-items: flex-start;
            flex-direction: column;
            padding: 20px;
        }

        .petty-page-title {
            font-size: 21px;
        }

        .petty-cash-header .section-header-breadcrumb {
            display: none;
        }

        .petty-history-header {
            align-items: flex-start;
            flex-direction: column;
            padding: 17px;
        }

        .petty-actions {
            width: 100%;
        }

        .petty-actions .btn {
            flex: 1;
            padding-right: 8px;
            padding-left: 8px;
        }

        .petty-table-wrap {
            padding: 0 12px 12px;
        }

        .petty-pagination {
            align-items: flex-start;
            flex-direction: column;
            padding: 14px;
        }
    }
</style>
@endpush

@section('content')
<section class="section petty-cash-page">
    <div class="petty-cash-header">
        <div>
            <span class="petty-kicker"><i class="fas fa-wallet"></i> Cash management</span>
            <h1 class="petty-page-title">Petty Cash Register</h1>
            <p class="petty-page-description">Daily office float, expense vouchers, and instant GL posting.</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.chart-of-accounts.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Petty Cash</div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="petty-summary-card petty-summary-balance">
                <span class="petty-summary-icon"><i class="fas fa-wallet"></i></span>
                <div>
                    <span class="petty-summary-label">Current float balance</span>
                    <strong class="petty-summary-value {{ $currentFloat < 0 ? 'is-negative' : '' }}">kr. {{ number_format($currentFloat, 2) }}</strong>
                    <span class="petty-summary-note"><i class="fas fa-vault"></i> Cash on hand (1010)</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="petty-summary-card petty-summary-out">
                <span class="petty-summary-icon"><i class="fas fa-arrow-down"></i></span>
                <div>
                    <span class="petty-summary-label">Today's cash outflow</span>
                    <strong class="petty-summary-value">kr. {{ number_format($todayOut, 2) }}</strong>
                    <span class="petty-summary-note"><i class="fas fa-receipt"></i> Minor expenses</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="petty-summary-card petty-summary-in">
                <span class="petty-summary-icon"><i class="fas fa-university"></i></span>
                <div>
                    <span class="petty-summary-label">Total float replenishment</span>
                    <strong class="petty-summary-value">kr. {{ number_format($totalIn, 2) }}</strong>
                    <span class="petty-summary-note"><i class="fas fa-arrow-up"></i> Funds added to float</span>
                </div>
            </div>
        </div>
    </div>

    <div class="section-body">
        <div class="petty-history-panel">
            <div class="petty-history-header">
                <h2 class="petty-history-title">
                    <span class="petty-history-icon"><i class="fas fa-list-alt"></i></span>
                    Voucher history
                    <span class="petty-history-count">{{ $transactions->total() }} records</span>
                </h2>
                <div class="petty-actions">
                    <button type="button" class="btn petty-topup-button" data-toggle="modal" data-target="#modalTopUpPettyCash">
                        <i class="fas fa-plus mr-1"></i> Replenish float
                    </button>
                    <button type="button" class="btn petty-expense-button" data-toggle="modal" data-target="#modalExpensePettyCash">
                        <i class="fas fa-minus-circle mr-1"></i> Record expense
                    </button>
                </div>
            </div>
            <div class="table-responsive petty-table-wrap">
                <table class="table petty-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 17%;">Date & time</th>
                            <th style="width: 14%;" class="text-center">Type</th>
                            <th style="width: 34%;">Purpose / voucher note</th>
                            <th style="width: 16%;" class="text-right">Amount (DKK)</th>
                            <th style="width: 14%;">Recorded by</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $idx => $t)
                            <tr>
                                <td class="petty-row-number">{{ $transactions->firstItem() + $idx }}</td>
                                <td class="petty-date"><i class="far fa-clock mr-1"></i> {{ $t->created_at ? $t->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                <td class="text-center">
                                    @if($t->type == 'in')
                                        <span class="petty-voucher-type petty-voucher-in"><i class="fas fa-arrow-up"></i> Float in</span>
                                    @else
                                        <span class="petty-voucher-type petty-voucher-out"><i class="fas fa-arrow-down"></i> Expense</span>
                                    @endif
                                </td>
                                <td class="petty-purpose">{{ $t->purpose }}</td>
                                <td class="text-right petty-amount {{ $t->type == 'in' ? 'petty-amount-in' : 'petty-amount-out' }}">
                                    {{ $t->type == 'in' ? '+' : '-' }} kr. {{ number_format($t->amount, 2) }}
                                </td>
                                <td><span class="petty-recorder"><span class="petty-recorder-icon"><i class="fas fa-user"></i></span>{{ $t->creator?->name ?? 'System' }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="petty-empty-state">
                                    <span class="petty-empty-icon"><i class="fas fa-wallet"></i></span>
                                    <h3 class="font-weight-bold text-dark mb-1">No vouchers yet</h3>
                                    <p class="text-muted small mb-0">Replenishments and expenses will appear here once recorded.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="petty-pagination">
                <span>Showing {{ $transactions->firstItem() ?? 0 }}–{{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} vouchers</span>
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</section>

<!-- Modal: Replenish Petty Cash (In) -->
<div class="modal fade" id="modalTopUpPettyCash" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content petty-cash-modal border-0 shadow">
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
        <div class="modal-content petty-cash-modal border-0 shadow">
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
