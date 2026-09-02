@extends('backend.layouts.master')
@section('title', 'Edit Account Head: ' . $chartOfAccount->account_code)

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-edit text-primary mr-2"></i> Edit Account Head</h1>
            <p class="text-muted mb-0 small">Update Chart of Accounts Classification & Properties</p>
        </div>
        <div class="section-header-breadcrumb">
            <a href="{{ route('admin.chart-of-accounts.index') }}" class="btn btn-secondary font-weight-bold shadow-sm" style="border-radius: 6px;">
                <i class="fas fa-arrow-left mr-1"></i> Back to Chart of Accounts
            </a>
        </div>
    </div>

    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="font-weight-bold text-dark mb-0">Account Details: <span class="text-primary">{{ $chartOfAccount->account_code }} - {{ $chartOfAccount->account_name }}</span></h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.chart-of-accounts.update', $chartOfAccount->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">Account Code <span class="text-danger">*</span></label>
                                    <input type="text" name="account_code" class="form-control form-control-lg @error('account_code') is-invalid @enderror" value="{{ old('account_code', $chartOfAccount->account_code) }}" required>
                                    @error('account_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Standard 4-digit code (e.g., 1010, 2010)</small>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">Account Name <span class="text-danger">*</span></label>
                                    <input type="text" name="account_name" class="form-control form-control-lg @error('account_name') is-invalid @enderror" value="{{ old('account_name', $chartOfAccount->account_name) }}" required>
                                    @error('account_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">Account Classification <span class="text-danger">*</span></label>
                                    <select name="account_type" class="form-control form-control-lg select2 @error('account_type') is-invalid @enderror" required>
                                        <option value="asset" {{ old('account_type', $chartOfAccount->account_type) == 'asset' ? 'selected' : '' }}>Asset (1000s)</option>
                                        <option value="liability" {{ old('account_type', $chartOfAccount->account_type) == 'liability' ? 'selected' : '' }}>Liability (2000s)</option>
                                        <option value="equity" {{ old('account_type', $chartOfAccount->account_type) == 'equity' ? 'selected' : '' }}>Equity (3000s)</option>
                                        <option value="revenue" {{ old('account_type', $chartOfAccount->account_type) == 'revenue' ? 'selected' : '' }}>Revenue (4000s)</option>
                                        <option value="expense" {{ old('account_type', $chartOfAccount->account_type) == 'expense' ? 'selected' : '' }}>Expense (5000s)</option>
                                    </select>
                                    @error('account_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">Normal Balance <span class="text-danger">*</span></label>
                                    <select name="normal_balance" class="form-control form-control-lg @error('normal_balance') is-invalid @enderror" required>
                                        <option value="debit" {{ old('normal_balance', $chartOfAccount->normal_balance) == 'debit' ? 'selected' : '' }}>Debit (Normal for Assets & Expenses)</option>
                                        <option value="credit" {{ old('normal_balance', $chartOfAccount->normal_balance) == 'credit' ? 'selected' : '' }}>Credit (Normal for Liabilities, Equity & Revenue)</option>
                                    </select>
                                    @error('normal_balance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Parent Group Account</label>
                                <select name="parent_id" class="form-control form-control-lg select2">
                                    <option value="">— None (Top-Level Account) —</option>
                                    @foreach($groupAccounts as $group)
                                        <option value="{{ $group->id }}" {{ old('parent_id', $chartOfAccount->parent_id) == $group->id ? 'selected' : '' }}>
                                            {{ $group->account_code }} - {{ $group->account_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="is_active" class="custom-control-input" id="isActive" value="1" {{ old('is_active', $chartOfAccount->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold" for="isActive">Account is Active for Postings</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('admin.chart-of-accounts.index') }}" class="btn btn-light btn-lg mr-2 font-weight-bold" style="border-radius: 8px;">Cancel</a>
                                <button type="submit" class="btn btn-primary btn-lg font-weight-bold px-4 shadow-sm" style="border-radius: 8px;">
                                    <i class="fas fa-save mr-1"></i> Save Changes
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
