@extends('backend.layouts.master')

@section('title', 'PR Status & Pending Report')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-clipboard-list text-primary mr-2"></i> PR Received, Pending & Items Report</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Reports</div>
            <div class="breadcrumb-item">PR Status</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header">
                <h4>Purchase Requisition Metrics & Status</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.purchase-reports.pr-status') }}" method="GET" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label>Start Date:</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label>End Date:</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary mr-1"><i class="fas fa-search"></i> Filter PRs</button>
                            <a href="{{ route('admin.purchase-reports.pr-status') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </div>
                </form>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center border">
                            <small class="text-muted font-weight-bold">TOTAL PRs GENERATED</small>
                            <h3 class="text-primary mb-0">{{ $prData['total_pr_count'] }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center border border-success">
                            <small class="text-muted font-weight-bold">APPROVED / COMPLETED PRs</small>
                            <h3 class="text-success mb-0">{{ $prData['approved_pr_count'] }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center border">
                            <small class="text-muted font-weight-bold">PENDING PRs</small>
                            <h3 class="text-warning mb-0">{{ $prData['pending_pr_count'] }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center border border-danger">
                            <small class="text-muted font-weight-bold">ITEMS PENDING IN PR</small>
                            <h3 class="text-danger mb-0">{{ $prData['pending_items_count'] }}</h3>
                        </div>
                    </div>
                </div>

                <div class="section-title">Pending Purchase Requisitions Detail</div>
                <div class="table-responsive">
                    {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100']) !!}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}
@endpush
