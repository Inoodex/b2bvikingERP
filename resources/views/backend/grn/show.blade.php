@extends('backend.layouts.master')

@section('title', 'GRN Details: ' . $grn->grn_no)

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.goods-receipts.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Goods Receipt Note: <code>{{ $grn->grn_no }}</code></h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.goods-receipts.index') }}">GRN</a></div>
            <div class="breadcrumb-item">GRN Details</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4><i class="fas fa-file-invoice text-primary mr-2"></i> GRN Overview</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>QC Status:</strong> {!! $grn->qc_status_badge !!}
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>PO Reference:</strong>
                                <a href="{{ route('admin.purchase-orders.show', $grn->purchase_id) }}" target="_blank">
                                    {{ $grn->purchase?->po_no ?? 'PO #'.$grn->purchase_id }}
                                </a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Supplier:</strong> {{ $grn->purchase?->vendor?->name }}
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Destination Outlet:</strong> {{ $grn->outlet?->name }}
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Received By:</strong> {{ $grn->receivedBy?->name }}
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Receiving Date:</strong> {{ $grn->created_at ? $grn->created_at->format('d M Y, h:i A') : 'N/A' }}
                            </li>
                        </ul>

                        <div class="mt-3">
                            <a href="{{ route('admin.goods-receipts.pdf', $grn->id) }}" target="_blank" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-file-pdf mr-1"></i> Stream / Print Official GRN PDF
                            </a>
                            
                            @if($grn->purchase)
                                <a href="{{ route('admin.landed-cost.show', $grn->purchase_id) }}" class="btn btn-outline-info btn-block mt-2">
                                    <i class="fas fa-calculator mr-1"></i> View Landed Cost Matrix
                                </a>
                            @endif

                            @if(in_array($grn->qc_status, ['partial', 'failed']))
                                <a href="{{ route('admin.vendor-returns.create', ['grn_id' => $grn->id]) }}" class="btn btn-warning btn-block mt-2">
                                    <i class="fas fa-undo mr-1"></i> Process Vendor Return (Debit Note)
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-success">
                    <div class="card-header">
                        <h4><i class="fas fa-list-check text-success mr-2"></i> Quality Control & Accepted Items</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product Name</th>
                                        <th>Variant</th>
                                        <th class="text-right text-success">Accepted Qty</th>
                                        <th class="text-right text-danger">Rejected Qty</th>
                                        <th>Rejection Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($grn->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $item->product?->name }}</strong></td>
                                            <td>{{ $item->variant?->name ?? '-' }}</td>
                                            <td class="text-right font-weight-bold text-success">{{ number_format($item->accepted_qty, 2) }}</td>
                                            <td class="text-right font-weight-bold text-danger">{{ number_format($item->rejected_qty, 2) }}</td>
                                            <td>{{ $item->rejection_reason ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle mr-1"></i> Stock quantities for accepted items have been added to <strong>{{ $grn->outlet?->name }}</strong> inventory stock ledger.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
