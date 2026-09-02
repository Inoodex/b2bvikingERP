@extends('backend.layouts.master')
@section('title', 'General Ledger Report')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>General Ledger Report</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">General Ledger</div>
        </div>
    </div>

    <div class="section-body">
        <!-- Filter Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('admin.reports.general-ledger') }}" method="GET" class="row align-items-end">
                    <div class="col-md-4 form-group mb-md-0">
                        <label class="font-weight-bold">Filter Account Head</label>
                        <select name="account_id" class="form-control select2">
                            <option value="">-- All Accounts --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ $selectedAccountId == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->account_code }} — {{ $acc->account_name }} ({{ ucfirst($acc->account_type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-md-0">
                        <label class="font-weight-bold">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-3 form-group mb-md-0">
                        <label class="font-weight-bold">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary font-weight-bold btn-block"><i class="fas fa-filter mr-1"></i> Filter Ledger</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Yajra DataTable Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-book text-primary mr-2"></i> Yajra Server-Side Journal Ledger</h4>
                <div>
                    <span class="badge badge-success font-weight-bold mr-2">Total Debit: kr. {{ number_format($totalDebit, 2) }}</span>
                    <span class="badge badge-info font-weight-bold">Total Credit: kr. {{ number_format($totalCredit, 2) }}</span>
                </div>
            </div>
            <div class="card-body table-responsive">
                {{ $dataTable->table(['class' => 'table table-striped table-bordered align-middle']) }}
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush
