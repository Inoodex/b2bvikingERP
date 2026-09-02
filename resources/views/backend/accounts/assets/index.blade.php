@extends('backend.layouts.master')
@section('title', 'Fixed Assets & Monthly Depreciation — Asset Management')

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-building text-primary mr-2"></i> Fixed Assets & Depreciation Engine</h1>
            <p class="text-muted mb-0 small">Asset Capitalization, Useful Life Schedules & Automated Monthly GL Depreciation</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.chart-of-accounts.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Fixed Assets</div>
        </div>
    </div>

    <!-- 3 Core Asset KPI Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #17a2b8 !important;">
                <div class="card-icon bg-info text-white">
                    <i class="fas fa-cubes fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total Acquisition Cost</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">
                        kr. {{ number_format($totalPurchaseValue, 2) }}
                    </div>
                    <small class="text-info font-weight-bold"><i class="fas fa-tag mr-1"></i> Original Purchase Value</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #dc3545 !important;">
                <div class="card-icon bg-danger text-white">
                    <i class="fas fa-chart-line fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total Accumulated Depreciation</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">
                        kr. {{ number_format($totalDepreciation, 2) }}
                    </div>
                    <small class="text-danger font-weight-bold"><i class="fas fa-arrow-down mr-1"></i> GL Head 1080</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #28a745 !important;">
                <div class="card-icon bg-success text-white">
                    <i class="fas fa-balance-scale fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Net Carrying Book Value</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">
                        kr. {{ number_format($totalBookValue, 2) }}
                    </div>
                    <small class="text-success font-weight-bold"><i class="fas fa-shield-alt mr-1"></i> Current Net Asset Value</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Container -->
    <div class="section-body">
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-list text-primary mr-2"></i> Fixed Assets Register</h5>
                <div class="mt-2 mt-md-0">
                    <button type="button" class="btn btn-outline-danger font-weight-bold mr-2" data-toggle="modal" data-target="#modalRunDepreciation">
                        <i class="fas fa-bolt mr-1"></i> ⚡ Run Monthly Depreciation
                    </button>
                    <button type="button" class="btn btn-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalCreateAsset">
                        <i class="fas fa-plus-circle mr-1"></i> Register New Asset
                    </button>
                </div>
            </div>
            <div class="card-body p-4 table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th style="width: 10%;">Asset Code</th>
                            <th style="width: 25%;">Asset Description</th>
                            <th style="width: 15%;">Category</th>
                            <th style="width: 12%;">Acquisition Date</th>
                            <th style="width: 12%;" class="text-right">Cost (DKK)</th>
                            <th style="width: 12%;" class="text-right">Depreciated</th>
                            <th style="width: 14%;" class="text-right font-weight-bold">Book Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $a)
                            <tr>
                                <td><span class="badge badge-dark font-monospace">{{ $a->asset_code }}</span></td>
                                <td>
                                    <strong>{{ $a->name }}</strong>
                                    <br><small class="text-muted"><i class="fas fa-clock mr-1"></i> Useful Life: {{ $a->useful_life_years }} Years ({{ ucfirst(str_replace('_', ' ', $a->depreciation_method)) }})</small>
                                </td>
                                <td><span class="badge badge-light border">{{ $a->category }}</span></td>
                                <td>{{ $a->purchase_date ? $a->purchase_date->format('d M Y') : 'N/A' }}</td>
                                <td class="text-right font-weight-bold">kr. {{ number_format($a->purchase_value, 2) }}</td>
                                <td class="text-right text-danger">kr. {{ number_format($a->total_depreciation, 2) }}</td>
                                <td class="text-right font-weight-bold text-success h6 mb-0">kr. {{ number_format($a->current_book_value, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-building fa-2x mb-2 d-block text-muted"></i>
                                    No fixed assets registered yet. Click "Register New Asset" above to add plant, machinery, vehicles, or IT equipment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $assets->links() }}
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal: Register Asset -->
<div class="modal fade" id="modalCreateAsset" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <form action="{{ route('admin.assets.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold text-white"><i class="fas fa-plus-circle mr-2"></i> Register Fixed Asset</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Asset Name / Title <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Delivery Van (DK-8921), Forklift Toyota, Office Laptops" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-dark">Category <span class="text-danger">*</span></label>
                            <input type="text" name="category" class="form-control" placeholder="e.g. Vehicles, IT Equipment, Machinery" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-dark">Acquisition Date <span class="text-danger">*</span></label>
                            <input type="date" name="purchase_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-dark">Purchase Value (DKK) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="purchase_value" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-dark">Useful Life (Years) <span class="text-danger">*</span></label>
                            <input type="number" name="useful_life_years" class="form-control" min="1" max="50" value="5" required>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark">Depreciation Method</label>
                        <select name="depreciation_method" class="form-control select2">
                            <option value="straight_line">Straight-Line (Equal Monthly Amortization)</option>
                            <option value="reducing_balance">Reducing Balance Method</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3">
                    <button type="button" class="btn btn-light font-weight-bold border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold shadow-sm"><i class="fas fa-check mr-1"></i> Register Asset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Run Monthly Depreciation -->
<div class="modal fade" id="modalRunDepreciation" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <form action="{{ route('admin.assets.run-depreciation') }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-title font-weight-bold text-white"><i class="fas fa-bolt mr-2"></i> Run Monthly Depreciation Posting</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small">This will compute straight-line depreciation for all active fixed assets and automatically post a balanced GL Journal: <code>DR 5030 Depreciation Expense / CR 1080 Accumulated Depreciation</code>.</p>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark">Target Period (YYYY-MM) <span class="text-danger">*</span></label>
                        <input type="month" name="period" class="form-control" value="{{ now()->format('Y-m') }}" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3">
                    <button type="button" class="btn btn-light font-weight-bold border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger font-weight-bold shadow-sm"><i class="fas fa-check-double mr-1"></i> Execute GL Posting</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
