@extends('backend.layouts.master')
@section('title', 'Trial Balance Report')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Trial Balance Report</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Trial Balance</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('admin.reports.trial-balance') }}" method="GET" class="row align-items-end">
                    <div class="col-md-4 form-group mb-md-0">
                        <label class="font-weight-bold">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-4 form-group mb-md-0">
                        <label class="font-weight-bold">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary font-weight-bold btn-block"><i class="fas fa-filter mr-1"></i> Generate Trial Balance</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-balance-scale text-primary mr-2"></i> Trial Balance Statement</h4>
                <span class="badge {{ abs($totalDebitSum - $totalCreditSum) < 0.01 ? 'badge-success' : 'badge-danger' }} font-weight-bold px-3 py-2">
                    {{ abs($totalDebitSum - $totalCreditSum) < 0.01 ? '🟢 Balanced (Debits = Credits)' : '🔴 Imbalanced Warning' }}
                </span>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th width="15%">Account Code</th>
                            <th>Account Name</th>
                            <th width="15%">Account Type</th>
                            <th width="20%" class="text-right">Debit Balance (DR)</th>
                            <th width="20%" class="text-right">Credit Balance (CR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData as $row)
                            <tr>
                                <td>
                                    <span class="badge badge-light border text-dark font-weight-bold">{{ $row['account_code'] }}</span>
                                </td>
                                <td><strong class="text-dark">{{ $row['account_name'] }}</strong></td>
                                <td><span class="badge badge-secondary text-uppercase">{{ $row['account_type'] }}</span></td>
                                <td class="text-right font-weight-bold text-success">
                                    {{ $row['debit'] > 0 ? 'kr. '.number_format($row['debit'], 2) : '—' }}
                                </td>
                                <td class="text-right font-weight-bold text-info">
                                    {{ $row['credit'] > 0 ? 'kr. '.number_format($row['credit'], 2) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No transactions found for Trial Balance.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light font-weight-bold" style="font-size: 16px;">
                        <tr>
                            <td colspan="3" class="text-right">Total Balanced Summary:</td>
                            <td class="text-right text-success">kr. {{ number_format($totalDebitSum, 2) }}</td>
                            <td class="text-right text-info">kr. {{ number_format($totalCreditSum, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
