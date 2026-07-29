@extends('backend.layouts.master')
@section('title', 'LC Details - ' . $lc->lc_no)

@section('content')
    <section class="section">
        <!-- Seamless Header -->
        <div class="section-header d-block p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <div class="section-header-back mr-3">
                        <a href="{{ route('admin.letters-of-credit.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
                    </div>
                    <h1 class="mb-0 text-dark font-weight-bold" style="font-size: 22px;">LC Register: {{ $lc->lc_no }}</h1>
                </div>
                <div class="section-header-breadcrumb" style="position: relative; top: 0; right: 0;">
                    <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.letters-of-credit.index') }}">Procurement</a></div>
                    <div class="breadcrumb-item">LC Details</div>
                </div>
            </div>

            <div class="pt-3 border-top d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <span class="badge badge-success py-2 px-3 mr-2"><i class="fas fa-university mr-1"></i> {{ $lc->issuing_bank }}</span>
                    <span class="badge badge-info py-2 px-3"><i class="fas fa-flag mr-1"></i> Status: {{ strtoupper($lc->status) }}</span>
                </div>
                <div class="mt-2 mt-sm-0">
                    <button type="button" class="btn btn-primary btn-sm font-weight-bold" data-toggle="modal" data-target="#amendmentModal">
                        <i class="fas fa-edit mr-1"></i> Record LC Amendment
                    </button>
                </div>
            </div>
        </div>

        <div class="section-body">
            <!-- Meta KPI Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border shadow-sm text-center">
                        <div class="card-body">
                            <small class="text-muted font-weight-bold d-block">LC AMOUNT</small>
                            <h4 class="font-weight-bold text-primary mb-0">{{ $lc->currency ? $lc->currency->symbol : 'USD' }} {{ number_format($lc->amount, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-sm text-center">
                        <div class="card-body">
                            <small class="text-muted font-weight-bold d-block">BANK MARGIN</small>
                            <h4 class="font-weight-bold text-success mb-0">{{ $lc->margin_percent }}%</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-sm text-center">
                        <div class="card-body">
                            <small class="text-muted font-weight-bold d-block">TOTAL IMPORT EXPENSES</small>
                            <h4 class="font-weight-bold text-warning mb-0">kr. {{ number_format($lc->total_expenses, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-sm text-center">
                        <div class="card-body">
                            <small class="text-muted font-weight-bold d-block">EXPIRY DATE</small>
                            <h4 class="font-weight-bold text-danger mb-0">{{ $lc->expiry_date ? $lc->expiry_date->format('d M, Y') : 'N/A' }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Normalized 13 LC Expenses Breakdown -->
            <div class="card border shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-file-invoice text-primary mr-2"></i> Normalized Import Cost Elements & Landed Cost Breakdown</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive p-3">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Cost Element</th>
                                    <th>Allocation Type</th>
                                    <th class="text-right">Amount (DKK Base)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lc->expenses as $exp)
                                    <tr>
                                        <td class="font-weight-bold text-dark">{{ $exp->cost_element }}</td>
                                        <td><span class="badge badge-light border">Unit Cost Allocation</span></td>
                                        <td class="text-right font-weight-bold">kr. {{ number_format($exp->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No specific import expenses recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- LC Amendments History Log -->
            <div class="card border shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-history text-info mr-2"></i> LC Amendment History Log</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive p-3">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Amendment #</th>
                                    <th>Amended Date</th>
                                    <th>Change Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lc->amendments as $amend)
                                    <tr>
                                        <td class="font-weight-bold">{{ $amend->amendment_no }}</td>
                                        <td>{{ $amend->amended_date ? $amend->amended_date->format('d M, Y') : 'N/A' }}</td>
                                        <td>{{ $amend->change_details }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No amendments recorded for this Letter of Credit.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Amendment Modal -->
    <div class="modal fade" id="amendmentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.letters-of-credit.amendments.store', $lc->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold">Record LC Amendment</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-2">
                            <label class="font-weight-bold">Amendment # *</label>
                            <input type="text" name="amendment_no" class="form-control" required placeholder="e.g. AMD-001">
                        </div>
                        <div class="form-group mb-2">
                            <label class="font-weight-bold">Amended Date *</label>
                            <input type="date" name="amended_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Change Details *</label>
                            <textarea name="change_details" class="form-control" rows="3" required placeholder="e.g. Shipment date extended by 30 days and LC amount increased by $5,000."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary font-weight-bold">Save Amendment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
