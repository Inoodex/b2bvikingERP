@extends('backend.layouts.master')
@section('title', 'Fiscal Years & Period Governance — Enterprise Ledger')

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-calendar-alt text-primary mr-2"></i> Fiscal Years & Period Governance</h1>
            <p class="text-muted mb-0 small">SAP / IFRS Posting Period Lock & Audit Trail Management</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.chart-of-accounts.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Fiscal Years</div>
        </div>
    </div>

    <!-- Active Fiscal Period Status Cards -->
    @php
        $activeFy = $fiscalYears->where('is_closed', false)->first();
        $daysRemaining = 0;
        $progressPct = 0;
        if ($activeFy && $activeFy->start_date && $activeFy->end_date) {
            $totalDays = $activeFy->start_date->diffInDays($activeFy->end_date) ?: 365;
            $passedDays = $activeFy->start_date->diffInDays(now());
            $daysRemaining = max(0, now()->diffInDays($activeFy->end_date, false));
            $progressPct = min(100, max(0, round(($passedDays / $totalDays) * 100)));
        }
    @endphp

    <div class="row mb-4">
        <div class="col-lg-8 col-md-12 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge badge-success px-3 py-1 font-weight-bold mb-2"><i class="fas fa-dot-circle mr-1"></i> Currently Open Fiscal Period</span>
                            <h4 class="font-weight-bold text-dark mb-0">{{ $activeFy ? $activeFy->name : 'No Active Fiscal Year Defined' }}</h4>
                            <p class="text-muted small mb-0">
                                @if($activeFy)
                                    Active Span: {{ $activeFy->start_date->format('d M, Y') }} — {{ $activeFy->end_date->format('d M, Y') }}
                                @else
                                    Please define and open a fiscal year for operational postings.
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <h3 class="font-weight-bold text-primary mb-0">{{ $daysRemaining }}</h3>
                            <small class="text-muted font-weight-bold text-uppercase">Days Remaining</small>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between small text-muted font-weight-bold mb-1">
                            <span>Period Elapsed: {{ $progressPct }}%</span>
                            <span>{{ $daysRemaining > 0 ? 'Posting Allowed' : 'Period Due for Closing' }}</span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $progressPct }}%;" aria-valuenow="{{ $progressPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 mb-3">
            <div class="card shadow-sm border-0 h-100 bg-primary text-white" style="border-radius: 12px;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="font-weight-bold text-white mb-2"><i class="fas fa-shield-alt mr-2"></i> Hard Period Lock</h5>
                        <p class="small text-white-50 mb-0">
                            Closing a fiscal period immediately blocks all users and automated services from posting backdated invoices, vouchers, or payments into that period.
                        </p>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-light text-primary font-weight-bold btn-block shadow-sm py-2" data-toggle="modal" data-target="#modalCreateFiscalYear" style="border-radius: 8px;">
                            <i class="fas fa-plus-circle mr-1"></i> Create New Fiscal Year
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Master Fiscal Years Table -->
    <div class="section-body">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-history text-primary mr-2"></i> Fiscal Periods Registry & Governance</h5>
            </div>
            <div class="card-body p-4 table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th style="width: 25%;">Fiscal Period Name</th>
                            <th style="width: 15%;">Start Date</th>
                            <th style="width: 15%;">End Date</th>
                            <th class="text-center" style="width: 20%;">Governance Status</th>
                            <th class="text-center" style="width: 25%;">Period Lock Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fiscalYears as $fy)
                            <tr>
                                <td>
                                    <strong class="text-dark">{{ $fy->name }}</strong>
                                    @if(!$fy->is_closed)
                                        <span class="badge badge-success ml-2" style="font-size: 10px;">CURRENT</span>
                                    @endif
                                </td>
                                <td>{{ $fy->start_date ? $fy->start_date->format('d M, Y') : 'N/A' }}</td>
                                <td>{{ $fy->end_date ? $fy->end_date->format('d M, Y') : 'N/A' }}</td>
                                <td class="text-center">
                                    @if($fy->is_closed)
                                        <span class="badge badge-danger font-weight-bold px-3 py-2" style="border-radius: 6px;">
                                            <i class="fas fa-lock mr-1"></i> CLOSED & LOCKED
                                        </span>
                                        @if($fy->closed_at)
                                            <br><small class="text-muted">Locked: {{ $fy->closed_at->format('d M, Y H:i') }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-success font-weight-bold px-3 py-2" style="border-radius: 6px;">
                                            <i class="fas fa-lock-open mr-1"></i> OPEN FOR POSTING
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.fiscal-years.toggle-close', $fy->id) }}" method="POST" class="d-inline lock-form">
                                        @csrf
                                        @if($fy->is_closed)
                                            <button type="submit" class="btn btn-warning btn-sm font-weight-bold px-3 py-1 shadow-sm" style="border-radius: 6px;" title="Reopen Period for Corrections">
                                                <i class="fas fa-unlock-alt mr-1"></i> Reopen Period
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-danger btn-sm font-weight-bold px-3 py-1 shadow-sm" style="border-radius: 6px;" title="Hard Close & Lock Period">
                                                <i class="fas fa-lock mr-1"></i> Close & Lock Period
                                            </button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No fiscal years defined. Click "Create New Fiscal Year" to initialize.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal: Create Fiscal Year -->
<div class="modal fade" id="modalCreateFiscalYear" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <form action="{{ route('admin.fiscal-years.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-calendar-plus mr-2"></i> Create Fiscal Year</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Fiscal Period Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. FY 2026-2027" required style="border-radius: 6px;">
                    </div>
                    <div class="row">
                        <div class="col-6 form-group mb-3">
                            <label class="font-weight-bold text-dark">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required style="border-radius: 6px;">
                        </div>
                        <div class="col-6 form-group mb-3">
                            <label class="font-weight-bold text-dark">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required style="border-radius: 6px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4"><i class="fas fa-check mr-1"></i> Create Period</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
