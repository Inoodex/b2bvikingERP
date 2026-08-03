@extends('backend.layouts.master')

@section('title', 'Items Purchased & PO Issued Report')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-file-signature text-primary mr-2"></i> Items Purchased & PO Issued List (Client Req 2.31 & 2.32)</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Reports</div>
            <div class="breadcrumb-item">PO Issued</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header">
                <h4>Issued Purchase Orders Registry</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.purchase-reports.po-status') }}" method="GET" class="mb-4">
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
                            <button type="submit" class="btn btn-primary mr-1"><i class="fas fa-search"></i> Filter POs</button>
                            <a href="{{ route('admin.purchase-reports.po-status') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr class="bg-light">
                                <th>PO Number</th>
                                <th>PO Date</th>
                                <th>Supplier</th>
                                <th>Type</th>
                                <th class="text-right">Total Amount</th>
                                <th>Milestone Status</th>
                                <th>Payment Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($poList as $po)
                                <tr>
                                    <td><code>{{ $po->po_no }}</code></td>
                                    <td>{{ $po->date ? $po->date->format('d M Y') : 'N/A' }}</td>
                                    <td><strong>{{ $po->vendor?->name }}</strong></td>
                                    <td><span class="badge badge-{{ $po->purchase_type == 'foreign' ? 'info' : 'secondary' }}">{{ ucfirst($po->purchase_type ?? 'local') }}</span></td>
                                    <td class="text-right font-weight-bold">${{ number_format($po->total_amount, 2) }}</td>
                                    <td><span class="badge badge-primary">{{ ucfirst(str_replace('_', ' ', $po->milestone_status ?? 'issued')) }}</span></td>
                                    <td><span class="badge badge-{{ $po->payment_status == 'paid' ? 'success' : ($po->payment_status == 'partial' ? 'warning' : 'danger') }}">{{ ucfirst($po->payment_status ?? 'unpaid') }}</span></td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-eye"></i> View PO
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No purchase orders found.</td>
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
