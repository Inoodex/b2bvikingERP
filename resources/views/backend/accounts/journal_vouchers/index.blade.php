@extends('backend.layouts.master')
@section('title', 'Manual Journal Vouchers — General Ledger')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-journal-whills text-primary mr-2"></i> Manual Journal Vouchers</h1>
            <p class="text-muted mb-0 small">Accountant-posted General Ledger Journal Entries (MJV prefix)</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.chart-of-accounts.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Journal Vouchers</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-history text-primary mr-2"></i> Posted Voucher History</h5>
                <a href="{{ route('admin.journal-vouchers.create') }}" class="btn btn-primary font-weight-bold shadow-sm" style="border-radius:8px;">
                    <i class="fas fa-plus-circle mr-1"></i> New Journal Voucher
                </a>
            </div>
            <div class="card-body p-4 table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th>JV Number</th>
                            <th>Date</th>
                            <th>Narration</th>
                            <th class="text-right">Total Debit</th>
                            <th class="text-right">Total Credit</th>
                            <th class="text-center">Lines</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vouchers as $jv)
                            <tr>
                                <td><span class="badge badge-dark font-monospace font-weight-bold">{{ $jv->entry_no }}</span></td>
                                <td>{{ $jv->entry_date ? \Carbon\Carbon::parse($jv->entry_date)->format('d M, Y') : 'N/A' }}</td>
                                <td>{{ $jv->narration }}</td>
                                <td class="text-right font-weight-bold text-success">kr. {{ number_format($jv->lines->sum('debit'), 2) }}</td>
                                <td class="text-right font-weight-bold text-danger">kr. {{ number_format($jv->lines->sum('credit'), 2) }}</td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $jv->lines->count() }} lines</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-journal-whills fa-2x mb-2 d-block text-muted"></i>
                                    No manual journal vouchers posted yet.
                                    <a href="{{ route('admin.journal-vouchers.create') }}" class="d-block mt-2 btn btn-sm btn-primary">Create First JV</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">{{ $vouchers->links() }}</div>
            </div>
        </div>
    </div>
</section>
@endsection
