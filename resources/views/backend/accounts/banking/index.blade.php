@extends('backend.layouts.master')
@section('title', 'Bank & Cash Accounts — Treasury Management')

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-university text-primary mr-2"></i> Bank & Treasury Accounts</h1>
            <p class="text-muted mb-0 small">Multi-Currency Bank Vaults, Live Liquidity, and GL Account Integrations</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.chart-of-accounts.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Bank Accounts</div>
        </div>
    </div>

    <!-- 2 Top KPI Cards -->
    <div class="row mb-4">
        <div class="col-lg-6 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #28a745 !important;">
                <div class="card-icon bg-success text-white">
                    <i class="fas fa-wallet fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total Liquid Bank Balance</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">
                        kr. {{ number_format($totalBankLiquidity, 2) }}
                    </div>
                    <small class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Across All Active Accounts</small>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #17a2b8 !important;">
                <div class="card-icon bg-info text-white">
                    <i class="fas fa-landmark fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Registered Bank Accounts</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">
                        {{ $activeBanksCount }} Active Vaults
                    </div>
                    <small class="text-info font-weight-bold"><i class="fas fa-exchange-alt mr-1"></i> Ready for Reconciliation</small>
                </div>
            </div>
        </div>
    </div>

    <div class="section-body">
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-list text-primary mr-2"></i> Corporate Bank Accounts</h5>
                <div>
                    <a href="{{ route('admin.bank-reconciliation.index') }}" class="btn btn-outline-info font-weight-bold mr-2">
                        <i class="fas fa-sync mr-1"></i> Bank Reconciliation
                    </a>
                    <button type="button" class="btn btn-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalCreateBankAccount">
                        <i class="fas fa-plus-circle mr-1"></i> Add Bank Account
                    </button>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    @forelse($bankAccounts as $bank)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border shadow-none h-100" style="border-radius: 10px;">
                                <div class="card-body p-4 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge badge-primary font-weight-bold">{{ $bank->bank_name }}</span>
                                            @if($bank->status)
                                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </div>
                                        <h5 class="font-weight-bold text-dark mb-1">{{ $bank->account_name }}</h5>
                                        <p class="text-muted small font-monospace mb-3"><i class="fas fa-credit-card mr-1"></i> A/C: {{ $bank->account_number }}</p>
                                        
                                        <div class="border rounded p-3 bg-light mb-3">
                                            <small class="text-muted text-uppercase font-weight-bold d-block">Live Balance</small>
                                            <h4 class="font-weight-bold text-success mb-0">kr. {{ number_format($bank->current_balance, 2) }}</h4>
                                            @if($bank->glAccount)
                                                <small class="text-muted d-block mt-1"><i class="fas fa-link text-info mr-1"></i> GL: {{ $bank->glAccount->account_code }} - {{ $bank->glAccount->account_name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                        <a href="{{ route('admin.bank-reconciliation.index', ['bank_account_id' => $bank->id]) }}" class="btn btn-sm btn-outline-primary font-weight-bold">
                                            <i class="fas fa-sync-alt mr-1"></i> Reconcile
                                        </a>
                                        <form action="{{ route('admin.bank-accounts.toggle-status', $bank->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light border text-muted">
                                                {{ $bank->status ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-university fa-3x text-muted mb-3 d-block"></i>
                            <h6 class="font-weight-bold text-dark">No Bank Accounts Created Yet</h6>
                            <p class="text-muted small">Add your primary company bank accounts (e.g. Danske Bank, Nordea, Jyske Bank) to track liquidity and automated reconciliations.</p>
                            <button type="button" class="btn btn-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalCreateBankAccount">
                                <i class="fas fa-plus-circle mr-1"></i> Add Bank Account
                            </button>
                        </div>
                    @endforelse
                </div>
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
                        <label class="font-weight-bold text-dark">Account Holder / Nickname <span class="text-danger">*</span></label>
                        <input type="text" name="account_name" class="form-control" placeholder="e.g. Main Operating Account" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Bank Institution Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" class="form-control" placeholder="e.g. Danske Bank, Nordea" required>
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
@endsection
