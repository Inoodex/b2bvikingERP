@extends('backend.layouts.master')
@section('title', 'Chart of Accounts (COA) — Enterprise Ledger')

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-sitemap text-primary mr-2"></i> Chart of Accounts (COA)</h1>
            <p class="text-muted mb-0 small">SAP / IFRS 5-Tier General Ledger Master Data & Hierarchy</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.chart-of-accounts.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Chart of Accounts</div>
        </div>
    </div>

    <!-- 4 Core Financial KPI Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #28a745 !important;">
                <div class="card-icon bg-success text-white">
                    <i class="fas fa-coins fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total Asset Heads</h4></div>
                    <div class="card-body font-weight-bold text-dark h5 mb-0">
                        kr. {{ number_format($totalAssets, 2) }}
                    </div>
                    <small class="text-success font-weight-bold"><i class="fas fa-arrow-up mr-1"></i> Class 1000s</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #dc3545 !important;">
                <div class="card-icon bg-danger text-white">
                    <i class="fas fa-hand-holding-usd fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total Liabilities</h4></div>
                    <div class="card-body font-weight-bold text-dark h5 mb-0">
                        kr. {{ number_format($totalLiabilities, 2) }}
                    </div>
                    <small class="text-danger font-weight-bold"><i class="fas fa-arrow-down mr-1"></i> Class 2000s</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #17a2b8 !important;">
                <div class="card-icon bg-info text-white">
                    <i class="fas fa-balance-scale fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total Equity</h4></div>
                    <div class="card-body font-weight-bold text-dark h5 mb-0">
                        kr. {{ number_format($totalEquity, 2) }}
                    </div>
                    <small class="text-info font-weight-bold"><i class="fas fa-shield-alt mr-1"></i> Class 3000s</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #6777ef !important;">
                <div class="card-icon bg-primary text-white">
                    <i class="fas fa-layer-group fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Active Accounts</h4></div>
                    <div class="card-body font-weight-bold text-dark h5 mb-0">
                        {{ $activeAccountsCount }} Heads
                    </div>
                    <small class="text-primary font-weight-bold"><i class="fas fa-check-double mr-1"></i> Dual-Entry Active</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container with Tabs -->
    <div class="section-body">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 flex-wrap">
                <!-- Nav Tabs -->
                <ul class="nav nav-pills" id="coaTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold py-2 px-3" id="tree-tab" data-toggle="pill" href="#tree-view" role="tab" aria-selected="true" style="border-radius: 8px;">
                            <i class="fas fa-network-wired mr-1"></i> Interactive Tree View (SAP Hierarchy)
                        </a>
                    </li>
                    <li class="nav-item ml-2">
                        <a class="nav-link font-weight-bold py-2 px-3" id="table-tab" data-toggle="pill" href="#table-view" role="tab" aria-selected="false" style="border-radius: 8px;">
                            <i class="fas fa-table mr-1"></i> Master Data Table
                        </a>
                    </li>
                </ul>

                <!-- Action Button -->
                <div class="card-header-action mt-2 mt-md-0">
                    <button type="button" class="btn btn-primary font-weight-bold shadow-sm px-3 py-2" data-toggle="modal" data-target="#modalCreateAccount" style="border-radius: 8px;">
                        <i class="fas fa-plus-circle mr-1"></i> Add Account Head
                    </button>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="coaTabContent">
                    <!-- Tab 1: Interactive Hierarchical Tree View -->
                    <div class="tab-pane fade show active" id="tree-view" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold text-dark mb-0">General Ledger Hierarchy & Running Balances</h6>
                            <div>
                                <button type="button" class="btn btn-sm btn-light border font-weight-bold mr-1" id="btn-expand-all"><i class="fas fa-expand-alt mr-1"></i> Expand All</button>
                                <button type="button" class="btn btn-sm btn-light border font-weight-bold" id="btn-collapse-all"><i class="fas fa-compress-alt mr-1"></i> Collapse All</button>
                            </div>
                        </div>

                        <div class="coa-tree-container p-3 border rounded bg-light" style="font-family: inherit;">
                            @php
                                $classes = [
                                    'asset' => ['name' => '1000 - Assets', 'badge' => 'badge-success', 'icon' => 'fa-coins'],
                                    'liability' => ['name' => '2000 - Liabilities', 'badge' => 'badge-danger', 'icon' => 'fa-hand-holding-usd'],
                                    'equity' => ['name' => '3000 - Equity', 'badge' => 'badge-info', 'icon' => 'fa-balance-scale'],
                                    'revenue' => ['name' => '4000 - Revenue & Income', 'badge' => 'badge-primary', 'icon' => 'fa-chart-line'],
                                    'expense' => ['name' => '5000 - Operating & Direct Expenses', 'badge' => 'badge-warning text-dark', 'icon' => 'fa-receipt'],
                                ];
                            @endphp

                            @foreach($classes as $typeKey => $meta)
                                @php
                                    $groupNodes = $treeAccounts->where('account_type', $typeKey);
                                @endphp
                                <div class="coa-class-group mb-3 border rounded bg-white shadow-sm overflow-hidden">
                                    <div class="coa-class-header p-3 bg-light border-bottom d-flex justify-content-between align-items-center cursor-pointer toggle-tree-node" data-target="#class-{{ $typeKey }}">
                                        <div class="font-weight-bold text-dark">
                                            <i class="fas {{ $meta['icon'] }} text-primary mr-2"></i> {{ $meta['name'] }}
                                            <span class="badge {{ $meta['badge'] }} ml-2">{{ $groupNodes->count() }} Sub-Heads</span>
                                        </div>
                                        <div>
                                            <i class="fas fa-chevron-down text-muted transition-icon"></i>
                                        </div>
                                    </div>

                                    <div class="coa-class-body p-3 collapse show" id="class-{{ $typeKey }}">
                                        @forelse($groupNodes as $head)
                                            <div class="coa-node-item py-2 px-2 border-bottom d-flex justify-content-between align-items-center hover-bg">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge badge-dark font-monospace px-2 py-1 mr-2">{{ $head->account_code }}</span>
                                                    @if($head->is_group)
                                                        <i class="fas fa-folder-open text-warning mr-2"></i>
                                                        <strong class="text-dark">{{ $head->account_name }}</strong>
                                                    @else
                                                        <i class="fas fa-file-invoice text-info mr-2 ml-3"></i>
                                                        <span class="text-dark">{{ $head->account_name }}</span>
                                                    @endif

                                                    @if($head->isSystemProtected())
                                                        <span class="badge badge-warning text-dark font-weight-bold ml-2" style="font-size: 10px;" title="Core System Account"><i class="fas fa-lock"></i> Core</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <span class="mr-3 font-weight-bold {{ $head->balance >= 0 ? 'text-success' : 'text-danger' }}">
                                                        kr. {{ number_format($head->balance, 2) }}
                                                    </span>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('admin.chart-of-accounts.edit', $head->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Head"><i class="fas fa-edit"></i></a>
                                                    </div>
                                                </div>
                                            </div>

                                            @if($head->children->count() > 0)
                                                <div class="ml-4 pl-3 border-left">
                                                    @foreach($head->children as $child)
                                                        <div class="coa-node-item py-2 px-2 border-bottom d-flex justify-content-between align-items-center hover-bg">
                                                            <div class="d-flex align-items-center">
                                                                <span class="badge badge-secondary font-monospace px-2 py-1 mr-2">{{ $child->account_code }}</span>
                                                                <i class="fas fa-level-down-alt text-muted mr-2"></i>
                                                                <span class="text-dark">{{ $child->account_name }}</span>
                                                                @if($child->isSystemProtected())
                                                                    <span class="badge badge-warning text-dark font-weight-bold ml-2" style="font-size: 10px;"><i class="fas fa-lock"></i> Core</span>
                                                                @endif
                                                            </div>
                                                            <div class="d-flex align-items-center">
                                                                <span class="mr-3 font-weight-bold {{ $child->balance >= 0 ? 'text-success' : 'text-danger' }}">
                                                                    kr. {{ number_format($child->balance, 2) }}
                                                                </span>
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="{{ route('admin.chart-of-accounts.edit', $child->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Head"><i class="fas fa-edit"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @empty
                                            <div class="text-muted small p-2">No account heads registered in this classification.</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Tab 2: Standard Server-Side Master Data Table -->
                    <div class="tab-pane fade" id="table-view" role="tabpanel">
                        <div class="table-responsive">
                            {{ $dataTable->table(['class' => 'table table-striped table-bordered align-middle']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal: Create New Account -->
<div class="modal fade" id="modalCreateAccount" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <form action="{{ route('admin.chart-of-accounts.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i> Create Chart of Account Head</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Account Code <span class="text-danger">*</span></label>
                        <input type="text" name="account_code" class="form-control" placeholder="e.g. 1060 or 5040" required style="border-radius: 6px;">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Account Head Name <span class="text-danger">*</span></label>
                        <input type="text" name="account_name" class="form-control" placeholder="e.g. Petty Cash Vault" required style="border-radius: 6px;">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6 form-group mb-0">
                            <label class="font-weight-bold text-dark">Classification <span class="text-danger">*</span></label>
                            <select name="account_type" class="form-control select2" required>
                                <option value="asset">Asset (1000s)</option>
                                <option value="liability">Liability (2000s)</option>
                                <option value="equity">Equity (3000s)</option>
                                <option value="revenue">Revenue (4000s)</option>
                                <option value="expense">Expense (5000s)</option>
                            </select>
                        </div>
                        <div class="col-6 form-group mb-0">
                            <label class="font-weight-bold text-dark">Normal Balance <span class="text-danger">*</span></label>
                            <select name="normal_balance" class="form-control select2" required>
                                <option value="debit">Debit</option>
                                <option value="credit">Credit</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Parent Group Account</label>
                        <select name="parent_id" class="form-control select2">
                            <option value="">-- None (Top Level Group) --</option>
                            @foreach($groupAccounts as $g)
                                <option value="{{ $g->id }}">{{ $g->account_code }} — {{ $g->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="custom-control custom-checkbox mt-2">
                        <input type="checkbox" name="is_group" value="1" class="custom-control-input" id="chk_is_group">
                        <label class="custom-control-label font-weight-bold text-dark" for="chk_is_group">Is Parent Group Account?</label>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4"><i class="fas fa-check mr-1"></i> Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        $(document).ready(function() {
            // Toggle Tree Node on Click
            $('.toggle-tree-node').on('click', function() {
                const target = $($(this).data('target'));
                target.collapse('toggle');
                $(this).find('.transition-icon').toggleClass('fa-chevron-down fa-chevron-right');
            });

            // Expand All
            $('#btn-expand-all').on('click', function() {
                $('.coa-class-body').collapse('show');
                $('.transition-icon').removeClass('fa-chevron-right').addClass('fa-chevron-down');
            });

            // Collapse All
            $('#btn-collapse-all').on('click', function() {
                $('.coa-class-body').collapse('hide');
                $('.transition-icon').removeClass('fa-chevron-down').addClass('fa-chevron-right');
            });
        });
    </script>
@endpush
