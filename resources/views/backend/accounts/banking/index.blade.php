@extends('backend.layouts.master')
@section('title', 'Bank & Cash Accounts — Treasury Management')

@push('styles')
<style>
    .bank-accounts-page {
        --bank-accent: #E11835;
        --bank-ink: #172033;
        --bank-muted: #748094;
        --bank-line: #e8edf4;
        padding-bottom: 24px;
    }

    .bank-page-header,
    .bank-section-panel,
    .bank-metric-card,
    .bank-account-card {
        background: #fff;
        border: 1px solid var(--bank-line);
        box-shadow: 0 8px 24px rgba(23, 32, 51, 0.045);
    }

    .bank-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        padding: 24px 28px;
        margin-bottom: 22px;
        border-radius: 18px;
        background: linear-gradient(120deg, #fff 0%, #fff 68%, #fff7f8 100%);
    }

    .bank-page-kicker {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 8px;
        color: var(--bank-accent);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1.15px;
        text-transform: uppercase;
    }

    .bank-page-title {
        margin: 0;
        color: var(--bank-ink);
        font-size: 25px;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .bank-page-description {
        margin: 7px 0 0;
        color: var(--bank-muted);
        font-size: 12px;
    }

    .bank-page-header .section-header-breadcrumb {
        flex: 0 0 auto;
        margin-bottom: 2px;
    }

    .bank-metric-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 16px;
        min-height: 126px;
        padding: 22px 24px;
        overflow: hidden;
        border-radius: 16px;
    }

    .bank-metric-card::after {
        position: absolute;
        right: -24px;
        bottom: -48px;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: currentColor;
        content: "";
        opacity: 0.035;
    }

    .bank-metric-icon {
        display: inline-flex;
        flex: 0 0 54px;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        border-radius: 15px;
        font-size: 20px;
    }

    .bank-metric-liquidity .bank-metric-icon {
        background: #eaf7ef;
        color: #15803d;
    }

    .bank-metric-accounts .bank-metric-icon {
        background: #fff0f2;
        color: var(--bank-accent);
    }

    .bank-metric-label {
        display: block;
        margin-bottom: 5px;
        color: var(--bank-muted);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.7px;
        text-transform: uppercase;
    }

    .bank-metric-value {
        margin: 0;
        color: var(--bank-ink);
        font-size: 23px;
        font-weight: 800;
        line-height: 1.2;
    }

    .bank-metric-note {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 7px;
        color: var(--bank-muted);
        font-size: 10px;
        font-weight: 600;
    }

    .bank-section-panel {
        overflow: hidden;
        border-radius: 18px;
    }

    .bank-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--bank-line);
    }

    .bank-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        color: var(--bank-ink);
        font-size: 16px;
        font-weight: 800;
    }

    .bank-section-title-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #fff0f2;
        color: var(--bank-accent);
    }

    .bank-count {
        padding: 4px 9px;
        border-radius: 20px;
        background: #f1f4f8;
        color: #667085;
        font-size: 11px;
        font-weight: 700;
    }

    .bank-section-actions {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .bank-section-actions .btn {
        min-height: 36px;
        padding: 8px 13px;
        border-radius: 9px;
        font-size: 11px;
        font-weight: 700;
    }

    .bank-accounts-page .bank-section-actions .btn:not(.btn-social):not(.btn-social-icon):not(.btn-link) {
        background: var(--bank-accent) !important;
        border-color: var(--bank-accent) !important;
        color: #fff !important;
        box-shadow: 0 5px 12px rgba(225, 24, 53, 0.16) !important;
    }

    .bank-accounts-page .bank-section-actions .btn.bank-reconcile-button:not(.btn-social):not(.btn-social-icon):not(.btn-link) {
        background: #fff !important;
        border: 1px solid #dfe5ed !important;
        color: #344054 !important;
    }

    .bank-grid {
        padding: 22px;
    }

    .bank-account-card {
        height: 100%;
        border-radius: 15px;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .bank-account-card:hover {
        transform: translateY(-3px);
        border-color: #f1b6c0;
        box-shadow: 0 14px 28px rgba(23, 32, 51, 0.09);
    }

    .bank-account-card .card-body {
        display: flex;
        flex-direction: column;
        padding: 20px;
    }

    .bank-account-topline {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }

    .bank-institution {
        max-width: 65%;
        overflow: hidden;
        padding: 6px 10px;
        border-radius: 7px;
        background: #f2f5f9;
        color: #475467;
        font-size: 10px;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .bank-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 9px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .bank-status-active {
        background: #eaf7ef;
        color: #16834b;
    }

    .bank-status-inactive {
        background: #f1f3f6;
        color: #667085;
    }

    .bank-account-name {
        margin: 0 0 7px;
        color: var(--bank-ink);
        font-size: 15px;
        font-weight: 800;
        line-height: 1.4;
    }

    .bank-account-number {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 17px;
        color: var(--bank-muted);
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .bank-balance-box {
        margin-bottom: 17px;
        padding: 14px 15px;
        border: 1px solid #e8edf4;
        border-radius: 11px;
        background: #f7f9fc;
    }

    .bank-balance-label {
        display: block;
        margin-bottom: 4px;
        color: #8a94a6;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 0.7px;
        text-transform: uppercase;
    }

    .bank-balance-value {
        margin: 0;
        color: #16834b;
        font-size: 20px;
        font-weight: 800;
        line-height: 1.25;
    }

    .bank-gl-reference {
        display: block;
        margin-top: 8px;
        color: var(--bank-muted);
        font-size: 10px;
        line-height: 1.45;
    }

    .bank-account-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-top: auto;
        padding-top: 14px;
        border-top: 1px solid #edf0f4;
    }

    .bank-account-utility-actions {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .bank-account-actions .btn {
        min-height: 31px;
        padding: 6px 9px;
        border-radius: 7px;
        font-size: 10px;
        font-weight: 700;
    }

    .bank-accounts-page .bank-account-actions .btn:not(.btn-social):not(.btn-social-icon):not(.btn-link) {
        background: #f1f5f9 !important;
        border-color: #e4e9f0 !important;
        color: #475467 !important;
    }

    .bank-accounts-page .bank-account-actions .btn.bank-reconcile-link:not(.btn-social):not(.btn-social-icon):not(.btn-link) {
        background: #fff0f2 !important;
        border-color: #f6c4cd !important;
        color: var(--bank-accent) !important;
    }

    .bank-accounts-page .bank-account-actions .btn.bank-toggle-button:not(.btn-social):not(.btn-social-icon):not(.btn-link) {
        border-color: #e2e7ee !important;
        background: #fff !important;
        color: #667085 !important;
    }

    .bank-accounts-page .bank-account-actions .btn.btn-edit-account:not(.btn-social):not(.btn-social-icon):not(.btn-link) {
        background: #f1f5f9 !important;
        border-color: #e4e9f0 !important;
        color: #475467 !important;
    }

    .bank-accounts-page .bank-account-actions .btn.delete-item:not(.btn-social):not(.btn-social-icon):not(.btn-link) {
        background: var(--bank-accent) !important;
        border-color: var(--bank-accent) !important;
    }

    .bank-accounts-page .bank-empty-state .btn.bank-add-button:not(.btn-social):not(.btn-social-icon):not(.btn-link) {
        background: var(--bank-accent) !important;
        border-color: var(--bank-accent) !important;
        color: #fff !important;
    }

    .bank-empty-state {
        padding: 54px 20px;
        text-align: center;
    }

    .bank-empty-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 70px;
        margin-bottom: 16px;
        border-radius: 22px;
        background: #fff0f2;
        color: var(--bank-accent);
        font-size: 27px;
    }

    .bank-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 16px 22px;
        border-top: 1px solid var(--bank-line);
        color: var(--bank-muted);
        font-size: 11px;
    }

    @media (max-width: 767.98px) {
        .bank-page-header {
            align-items: flex-start;
            flex-direction: column;
            padding: 20px;
        }

        .bank-page-title {
            font-size: 21px;
        }

        .bank-page-header .section-header-breadcrumb {
            display: none;
        }

        .bank-section-header {
            align-items: flex-start;
            flex-direction: column;
            padding: 17px;
        }

        .bank-section-actions {
            width: 100%;
        }

        .bank-section-actions .btn {
            flex: 1;
            padding-right: 8px;
            padding-left: 8px;
        }

        .bank-grid {
            padding: 15px;
        }

        .bank-pagination {
            align-items: flex-start;
            flex-direction: column;
            padding: 15px;
        }
    }
</style>
@endpush

@section('content')
<section class="section bank-accounts-page">
    <div class="bank-page-header">
        <div>
            <span class="bank-page-kicker"><i class="fas fa-chart-line"></i> Treasury management</span>
            <h1 class="bank-page-title">Bank & Treasury Accounts</h1>
            <p class="bank-page-description">Manage bank balances, linked ledger accounts, and reconciliation in one place.</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.chart-of-accounts.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Bank Accounts</div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6 col-md-6 col-12 mb-3">
            <div class="bank-metric-card bank-metric-liquidity">
                <span class="bank-metric-icon"><i class="fas fa-wallet"></i></span>
                <div>
                    <span class="bank-metric-label">Total liquid bank balance</span>
                    <p class="bank-metric-value">kr. {{ number_format($totalBankLiquidity, 2) }}</p>
                    <span class="bank-metric-note"><i class="fas fa-check-circle text-success"></i> Across all active accounts</span>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-12 mb-3">
            <div class="bank-metric-card bank-metric-accounts">
                <span class="bank-metric-icon"><i class="fas fa-landmark"></i></span>
                <div>
                    <span class="bank-metric-label">Registered bank accounts</span>
                    <p class="bank-metric-value">{{ $activeBanksCount }} <span class="font-weight-normal">active</span></p>
                    <span class="bank-metric-note"><i class="fas fa-sync-alt"></i> Ready for reconciliation</span>
                </div>
            </div>
        </div>
    </div>

    <div class="section-body">
        <div class="bank-section-panel mb-4">
            <div class="bank-section-header">
                <div>
                    <h2 class="bank-section-title">
                        <span class="bank-section-title-icon"><i class="fas fa-university"></i></span>
                        Corporate bank accounts
                        <span class="bank-count">{{ $bankAccounts->total() }}</span>
                    </h2>
                </div>
                <div class="bank-section-actions">
                    <a href="{{ route('admin.bank-reconciliation.index') }}" class="btn bank-reconcile-button">
                        <i class="fas fa-sync-alt mr-1"></i> Reconciliation
                    </a>
                    <button type="button" class="btn btn-primary bank-add-button" data-toggle="modal" data-target="#modalCreateBankAccount">
                        <i class="fas fa-plus-circle mr-1"></i> Add Bank Account
                    </button>
                </div>
            </div>
            <div class="bank-grid">
                <div class="row">
                    @forelse($bankAccounts as $bank)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <article class="bank-account-card">
                                <div class="card-body">
                                    <div>
                                        <div class="bank-account-topline">
                                            <span class="bank-institution">{{ $bank->bank_name }}</span>
                                            @if($bank->status)
                                                <span class="bank-status bank-status-active"><i class="fas fa-check-circle"></i> Active</span>
                                            @else
                                                <span class="bank-status bank-status-inactive"><i class="fas fa-pause-circle"></i> Inactive</span>
                                            @endif
                                        </div>
                                        <h3 class="bank-account-name">{{ $bank->account_name }}</h3>
                                        <div class="bank-account-number"><i class="fas fa-credit-card"></i> A/C: {{ $bank->account_number }}</div>
                                        
                                        <div class="bank-balance-box">
                                            <span class="bank-balance-label">Live balance</span>
                                            <p class="bank-balance-value">kr. {{ number_format($bank->current_balance, 2) }}</p>
                                            @if($bank->glAccount)
                                                <span class="bank-gl-reference"><i class="fas fa-link mr-1"></i> GL {{ $bank->glAccount->account_code }} · {{ $bank->glAccount->account_name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="bank-account-actions">
                                        <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                            <a href="{{ route('admin.bank-reconciliation.index', ['bank_account_id' => $bank->id]) }}" class="btn btn-sm bank-reconcile-link" title="Reconcile Statement">
                                                <i class="fas fa-sync-alt mr-1"></i> Reconcile
                                            </a>
                                            <form action="{{ route('admin.bank-accounts.toggle-status', $bank->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm bank-toggle-button" title="{{ $bank->status ? 'Deactivate' : 'Activate' }}">
                                                    {{ $bank->status ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                        <div class="bank-account-utility-actions" role="group" aria-label="Account actions">
                                            <button type="button" 
                                                    class="btn btn-sm btn-edit-account"
                                                    data-id="{{ $bank->id }}"
                                                    data-bank="{{ $bank->bank_name }}"
                                                    data-name="{{ $bank->account_name }}"
                                                    data-number="{{ $bank->account_number }}"
                                                    data-currency="{{ $bank->currency_id }}"
                                                    data-gl="{{ $bank->gl_account_id }}"
                                                    data-action="{{ route('admin.bank-accounts.update', $bank->id) }}"
                                                    title="Edit Bank Account">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="{{ route('admin.bank-accounts.destroy', $bank->id) }}" 
                                               class="btn btn-sm btn-danger delete-item" 
                                               title="Delete Bank Account">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="bank-empty-state">
                                <span class="bank-empty-icon"><i class="fas fa-university"></i></span>
                                <h3 class="bank-account-name">No bank accounts yet</h3>
                                <p class="text-muted small mb-3">Add a company bank account to track balances and reconcile transactions.</p>
                                <button type="button" class="btn btn-primary bank-add-button" data-toggle="modal" data-target="#modalCreateBankAccount">
                                    <i class="fas fa-plus-circle mr-1"></i> Add your first account
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($bankAccounts->total() > 0)
                    <div class="bank-pagination">
                        <div>
                            Showing <strong>{{ $bankAccounts->firstItem() ?? 0 }}</strong> to <strong>{{ $bankAccounts->lastItem() ?? 0 }}</strong> of <strong>{{ $bankAccounts->total() }}</strong> accounts
                            @if($bankAccounts->hasPages())
                                <span class="bank-count ml-1">Page {{ $bankAccounts->currentPage() }} of {{ $bankAccounts->lastPage() }}</span>
                            @endif
                        </div>
                        @if($bankAccounts->hasPages())
                            <div class="pagination-sm mb-0 ml-auto">
                                {{ $bankAccounts->links() }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Modal: Add Bank Account -->
<div class="modal fade" id="modalCreateBankAccount" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <form action="{{ route('admin.bank-accounts.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold text-white"><i class="fas fa-plus-circle mr-2"></i> Add Bank Account</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Bank Institution Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" class="form-control" placeholder="e.g. Danske Bank, Nordea" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Account Holder / Nickname <span class="text-danger">*</span></label>
                        <input type="text" name="account_name" class="form-control" placeholder="e.g. Main Operating Account" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Account / IBAN Number <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" class="form-control" placeholder="e.g. DK00 0000 0000 0000" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-dark">Linked GL Account Head</label>
                            <select name="gl_account_id" class="form-control select2">
                                <option value="">-- Select GL Head --</option>
                                @foreach($glAccounts as $gl)
                                    <option value="{{ $gl->id }}">{{ $gl->account_code }} — {{ $gl->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-dark">Opening Balance (DKK)</label>
                            <input type="number" step="0.01" name="opening_balance" class="form-control" placeholder="0.00" value="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3">
                    <button type="button" class="btn btn-light font-weight-bold border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold shadow-sm"><i class="fas fa-check mr-1"></i> Save Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Bank Account -->
<div class="modal fade" id="modalEditBankAccount" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <form id="formEditBankAccount" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold text-white"><i class="fas fa-edit mr-2"></i> Edit Bank Account</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Bank Institution Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" id="edit_bank_name" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Account Holder / Nickname <span class="text-danger">*</span></label>
                        <input type="text" name="account_name" id="edit_account_name" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Account / IBAN Number <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" id="edit_account_number" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-dark">Currency</label>
                            <select name="currency_id" id="edit_currency_id" class="form-control">
                                @foreach($currencies as $c)
                                    <option value="{{ $c->id }}">{{ $c->code }} ({{ $c->symbol }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-dark">Linked GL Account Head</label>
                            <select name="gl_account_id" id="edit_gl_account_id" class="form-control">
                                <option value="">-- Select GL Head --</option>
                                @foreach($glAccounts as $gl)
                                    <option value="{{ $gl->id }}">{{ $gl->account_code }} — {{ $gl->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3">
                    <button type="button" class="btn btn-light font-weight-bold border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold shadow-sm"><i class="fas fa-save mr-1"></i> Update Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.btn-edit-account').on('click', function() {
        var action = $(this).data('action');
        var bankName = $(this).data('bank');
        var accountName = $(this).data('name');
        var accountNumber = $(this).data('number');
        var currencyId = $(this).data('currency');
        var glId = $(this).data('gl');

        $('#formEditBankAccount').attr('action', action);
        $('#edit_bank_name').val(bankName);
        $('#edit_account_name').val(accountName);
        $('#edit_account_number').val(accountNumber);
        $('#edit_currency_id').val(currencyId);
        $('#edit_gl_account_id').val(glId);

        $('#modalEditBankAccount').modal('show');
    });
});
</script>
@endpush
