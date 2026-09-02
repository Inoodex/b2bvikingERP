@extends('backend.layouts.master')
@section('title', 'Create Chart of Account Head — General Ledger')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-plus-circle text-primary mr-2"></i> Create Chart of Account Head</h1>
            <p class="text-muted mb-0 small">Define a new General Ledger account head within the 5-Tier GAAP/IFRS hierarchy</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.chart-of-accounts.index') }}">Chart of Accounts</a></div>
            <div class="breadcrumb-item active">Create Head</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-sitemap text-primary mr-2"></i> Account Head Definition</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.chart-of-accounts.store') }}" method="POST">
                            @csrf
                            
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark">Account Code <span class="text-danger">*</span></label>
                                <input type="text" name="account_code" class="form-control @error('account_code') is-invalid @enderror" 
                                    placeholder="e.g. 1060 (Asset), 2040 (Liability), 5030 (Expense)" value="{{ old('account_code') }}" required style="border-radius: 6px;">
                                @error('account_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Standard 4-digit code (1000s: Asset, 2000s: Liability, 3000s: Equity, 4000s: Revenue, 5000s: Expense).</small>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark">Account Head Name <span class="text-danger">*</span></label>
                                <input type="text" name="account_name" class="form-control @error('account_name') is-invalid @enderror" 
                                    placeholder="e.g. Petty Cash Vault, Rent Expense, Marketing" value="{{ old('account_name') }}" required style="border-radius: 6px;">
                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark">Classification / Type <span class="text-danger">*</span></label>
                                    <select name="account_type" class="form-control select2" required>
                                        <option value="asset" {{ old('account_type') == 'asset' ? 'selected' : '' }}>Asset (1000s)</option>
                                        <option value="liability" {{ old('account_type') == 'liability' ? 'selected' : '' }}>Liability (2000s)</option>
                                        <option value="equity" {{ old('account_type') == 'equity' ? 'selected' : '' }}>Equity (3000s)</option>
                                        <option value="revenue" {{ old('account_type') == 'revenue' ? 'selected' : '' }}>Revenue (4000s)</option>
                                        <option value="expense" {{ old('account_type') == 'expense' ? 'selected' : '' }}>Expense (5000s)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark">Normal Balance <span class="text-danger">*</span></label>
                                    <select name="normal_balance" class="form-control select2" required>
                                        <option value="debit" {{ old('normal_balance') == 'debit' ? 'selected' : '' }}>Debit (Normal for Assets & Expenses)</option>
                                        <option value="credit" {{ old('normal_balance') == 'credit' ? 'selected' : '' }}>Credit (Normal for Liabilities, Equity & Revenue)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark">Parent Group Account (Optional)</label>
                                <select name="parent_id" class="form-control select2">
                                    <option value="">-- None (Top Level Head) --</option>
                                    @foreach($groupAccounts as $g)
                                        <option value="{{ $g->id }}" {{ old('parent_id') == $g->id ? 'selected' : '' }}>
                                            {{ $g->account_code }} — {{ $g->account_name }} ({{ ucfirst($g->account_type) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="custom-control custom-checkbox mb-4">
                                <input type="checkbox" name="is_group" value="1" class="custom-control-input" id="chk_is_group" {{ old('is_group') ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold text-dark" for="chk_is_group">Is Parent Group Account (Container)?</label>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.chart-of-accounts.index') }}" class="btn btn-light font-weight-bold border px-4">
                                    <i class="fas fa-arrow-left mr-1"></i> Back to Chart of Accounts
                                </a>
                                <button type="submit" class="btn btn-primary font-weight-bold px-4 shadow-sm" style="border-radius: 6px;">
                                    <i class="fas fa-check-circle mr-1"></i> Save Account Head
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
