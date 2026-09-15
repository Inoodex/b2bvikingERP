@extends('backend.layouts.master')
@section('title', 'General Ledger Report')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-book text-primary mr-2"></i> General Ledger Report</h1>
            <p class="text-muted mb-0 small">Chronological accounting journal line transactions</p>
        </div>
        <div class="section-header-breadcrumb d-flex align-items-center">
            @if(!empty($latestPdf))
                <a href="{{ $latestPdf['url'] }}" id="btn-download-pdf" class="btn btn-success font-weight-bold mr-2 shadow-sm" title="{{ $latestPdf['filename'] }}">
                    <i class="fas fa-file-download mr-1"></i> Download PDF (Ready: {{ $latestPdf['time'] }})
                </a>
            @else
                <a href="#" id="btn-download-pdf" class="btn btn-success font-weight-bold mr-2 shadow-sm" style="display: none;">
                    <i class="fas fa-file-download mr-1"></i> Download PDF (Ready)
                </a>
            @endif

            <button type="button" id="btn-generate-pdf" class="btn btn-danger font-weight-bold mr-3 shadow-sm" data-url="{{ route('admin.reports.general-ledger.pdf') }}" data-type="general_ledger">
                <i class="fas fa-file-pdf mr-1"></i> Generate Fresh PDF
            </button>

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
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary font-weight-bold flex-grow-1 mr-1"><i class="fas fa-filter mr-1"></i> Filter</button>
                            <a href="{{ route('admin.reports.general-ledger') }}" class="btn btn-secondary" title="Reset"><i class="fas fa-redo"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Yajra DataTable Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-book text-primary mr-2"></i> General Ledger Transactions</h4>
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
    @include('backend.reports.financial.partials.async_pdf_js')
@endpush
