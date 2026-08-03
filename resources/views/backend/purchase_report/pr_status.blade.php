@extends('backend.layouts.master')

@section('title', 'PR Status & Pending Items Report')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-clipboard-list text-primary mr-2"></i> PR Received, Pending & Items Report (Client Req 2.28 - 2.30)</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Reports</div>
            <div class="breadcrumb-item">PR Status</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header">
                <h4>Requisition Status Overview</h4>
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
                            <button type="submit" class="btn btn-primary mr-1"><i class="fas fa-filter"></i> Filter</button>
                            <a href="{{ route('admin.purchase-reports.pr-status') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </div>
                </form>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center border">
                            <small class="text-muted font-weight-bold">TOTAL PRs RECEIVED</small>
                            <h3 class="text-dark mb-0">{{ $prData['total_pr_count'] }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center border">
                            <small class="text-muted font-weight-bold">APPROVED PRs</small>
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
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr class="bg-light">
                                <th>PR No</th>
                                <th>Request Date</th>
                                <th>Requested By</th>
                                <th>Department</th>
                                <th class="text-center">Pending Line Items</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prData['pending_prs'] as $pr)
                                <tr>
                                    <td><code>{{ $pr->request_no ?? ('PR-'.$pr->id) }}</code></td>
                                    <td>{{ $pr->request_date ? $pr->request_date->format('d M Y') : 'N/A' }}</td>
                                    <td>{{ $pr->user?->name }}</td>
                                    <td>{{ $pr->department?->name }}</td>
                                    <td class="text-center font-weight-bold text-danger">{{ $pr->items->count() }}</td>
                                    <td><span class="badge badge-warning">{{ ucfirst($pr->status) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No pending purchase requisitions found. All PRs are processed!</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
