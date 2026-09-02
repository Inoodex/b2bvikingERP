@extends('backend.layouts.master')
@section('title', 'Balance Sheet Statement')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-building text-primary mr-2"></i> Balance Sheet Statement</h1>
            <p class="text-muted mb-0 small">IFRS Statement of Financial Position (Assets = Liabilities + Owner's Equity)</p>
        </div>
        <div class="section-header-breadcrumb">
            <button onclick="window.print()" class="btn btn-outline-secondary font-weight-bold mr-2"><i class="fas fa-print mr-1"></i> Print</button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-light border font-weight-bold"><i class="fas fa-arrow-left mr-1"></i> Dashboard</a>
        </div>
    </div>

    <div class="section-body">
        <!-- Date Filter Card -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: #ffffff;">
            <div class="card-body p-4">
                <form action="{{ route('admin.reports.balance-sheet') }}" method="GET" class="row align-items-end">
                    <div class="col-md-8 form-group mb-md-0">
                        <label class="font-weight-bold text-dark mb-1"><i class="fas fa-calendar-day text-primary mr-1"></i> Statement As Of Date</label>
                        <input type="date" name="as_of_date" class="form-control form-control-lg border" value="{{ $asOfDate }}" style="border-radius: 8px;">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary btn-lg font-weight-bold btn-block shadow-sm" style="border-radius: 8px; height: 48px;">
                            <i class="fas fa-filter mr-2"></i> Generate Balance Sheet
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <!-- Assets Column -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-coins text-primary mr-2"></i> Total Assets (1000s)</h5>
                        <span class="badge badge-primary font-weight-bold px-3 py-1">kr. {{ number_format($totalAssets, 2) }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-dark">
                                    <tr>
                                        <th>Asset Account Code & Name</th>
                                        <th class="text-right">Balance Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($assetsData as $row)
                                        <tr>
                                            <td>
                                                <span class="badge badge-light border font-weight-bold text-dark mr-2">{{ $row['code'] }}</span>
                                                <strong class="text-dark">{{ $row['name'] }}</strong>
                                            </td>
                                            <td class="text-right font-weight-bold text-primary">kr. {{ number_format($row['amount'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">No asset balances found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td>TOTAL ASSETS:</td>
                                        <td class="text-right text-primary" style="font-size: 16px;">kr. {{ number_format($totalAssets, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liabilities & Equity Column -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-scale-balanced text-warning mr-2"></i> Liabilities & Owner's Equity</h5>
                        <span class="badge badge-dark font-weight-bold px-3 py-1">kr. {{ number_format($totalLiabilities + $totalEquity, 2) }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th colspan="2" class="text-dark font-weight-bold text-uppercase small">Liabilities (2000s)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($liabilitiesData as $row)
                                        <tr>
                                            <td>
                                                <span class="badge badge-light border font-weight-bold text-dark mr-2">{{ $row['code'] }}</span>
                                                <strong class="text-dark">{{ $row['name'] }}</strong>
                                            </td>
                                            <td class="text-right font-weight-bold text-warning">kr. {{ number_format($row['amount'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-2">No liabilities recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <thead class="bg-light">
                                    <tr>
                                        <th colspan="2" class="text-dark font-weight-bold text-uppercase small">Equity (3000s)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($equityData as $row)
                                        <tr>
                                            <td>
                                                <span class="badge badge-light border font-weight-bold text-dark mr-2">{{ $row['code'] }}</span>
                                                <strong class="text-dark">{{ $row['name'] }}</strong>
                                            </td>
                                            <td class="text-right font-weight-bold text-info">kr. {{ number_format($row['amount'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-2">No equity accounts recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td>TOTAL LIABILITIES & EQUITY:</td>
                                        <td class="text-right text-dark" style="font-size: 16px;">kr. {{ number_format($totalLiabilities + $totalEquity, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Balance Verification Badge -->
            <div class="col-12">
                @php
                    $isBalanced = abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01;
                @endphp
                <div class="card border-0 shadow-sm" style="border-radius: 14px; background: {{ $isBalanced ? '#e6fffa' : '#fff5f5' }}; border-left: 6px solid {{ $isBalanced ? '#38ef7d' : '#ff4b2b' }};">
                    <div class="card-body py-3 text-center">
                        <h5 class="mb-0 font-weight-bold {{ $isBalanced ? 'text-success' : 'text-danger' }}">
                            {{ $isBalanced ? '🟢 Balance Sheet Balanced: Assets (kr. '.number_format($totalAssets, 2).') = Liabilities & Equity (kr. '.number_format($totalLiabilities + $totalEquity, 2).')' : '⚠️ Balance Variance Notice' }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
