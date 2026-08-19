@extends('backend.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.stock-transfers.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Stock Transfer #{{ $stockTransfer->transfer_no }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.stock-transfers.index') }}">Stock Transfers</a></div>
            <div class="breadcrumb-item">Details</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-route mr-2 text-primary"></i> Route & Logistics</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 p-3 bg-light rounded text-center border">
                            <div class="font-weight-bold text-muted small text-uppercase">Transfer Route</div>
                            <h6 class="text-primary font-weight-bold mb-1 mt-2">
                                <i class="fas fa-warehouse mr-1"></i> {{ $stockTransfer->fromOutlet ? ($stockTransfer->fromOutlet->outlet_name ?? $stockTransfer->fromOutlet->name) : 'Central Warehouse' }}
                            </h6>
                            <i class="fas fa-arrow-down my-1 text-muted"></i>
                            <h6 class="text-success font-weight-bold mb-0">
                                <i class="fas fa-store mr-1"></i> {{ $stockTransfer->toOutlet ? ($stockTransfer->toOutlet->outlet_name ?? $stockTransfer->toOutlet->name) : 'Outlet #' . $stockTransfer->to_outlet_id }}
                            </h6>
                        </div>

                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th class="text-muted" style="width: 140px;">Status:</th>
                                <td>
                                    @if($stockTransfer->status === 'received')
                                        <span class="badge badge-success"><i class="fas fa-check-double mr-1"></i> Received (Stock In)</span>
                                    @elseif($stockTransfer->status === 'dispatched')
                                        <span class="badge badge-primary"><i class="fas fa-truck mr-1"></i> Dispatched (In Transit)</span>
                                    @elseif($stockTransfer->status === 'cancelled')
                                        <span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i> Cancelled</span>
                                    @else
                                        <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i> Draft (Pending Dispatch)</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Transfer Date:</th>
                                <td>{{ optional($stockTransfer->transfer_date)->format('d M, Y') ?: optional($stockTransfer->created_at)->format('d M, Y') }}</td>
                            </tr>
                            @if($stockTransfer->dispatched_at)
                            <tr>
                                <th class="text-muted">Dispatched At:</th>
                                <td>{{ optional($stockTransfer->dispatched_at)->format('d M, Y h:i A') }}</td>
                            </tr>
                            @endif
                            @if($stockTransfer->received_at)
                            <tr>
                                <th class="text-muted">Received At:</th>
                                <td>{{ optional($stockTransfer->received_at)->format('d M, Y h:i A') }}</td>
                            </tr>
                            @endif
                            @if($stockTransfer->vehicle_no)
                            <tr>
                                <th class="text-muted">Vehicle No:</th>
                                <td><strong>{{ $stockTransfer->vehicle_no }}</strong></td>
                            </tr>
                            @endif
                            @if($stockTransfer->driver_name)
                            <tr>
                                <th class="text-muted">Driver:</th>
                                <td>{{ $stockTransfer->driver_name }} ({{ $stockTransfer->driver_phone ?: 'No phone' }})</td>
                            </tr>
                            @endif
                            <tr>
                                <th class="text-muted">Initiated By:</th>
                                <td>{{ $stockTransfer->requestedByUser->name ?? 'System' }}</td>
                            </tr>
                        </table>

                        @if($stockTransfer->note)
                            <div class="alert alert-light border mt-3 mb-0 small">
                                <strong>Notes:</strong> {{ $stockTransfer->note }}
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-light border-top py-3">
                        @if($stockTransfer->status === 'draft')
                            <form action="{{ route('admin.stock-transfers.dispatch', $stockTransfer->id) }}" method="POST" class="mb-2" onsubmit="return confirm('Dispatch this transfer? This will deduct the items from the source warehouse inventory.');">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow-sm py-2">
                                    <i class="fas fa-truck mr-1"></i> Dispatch Transfer (Deduct Source Stock)
                                </button>
                            </form>
                            <form action="{{ route('admin.stock-transfers.cancel', $stockTransfer->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this transfer?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-block font-weight-bold py-2">
                                    <i class="fas fa-times-circle mr-1"></i> Cancel Transfer
                                </button>
                            </form>
                        @elseif($stockTransfer->status === 'dispatched')
                            <a href="{{ route('admin.stock-transfers.receive-form', $stockTransfer->id) }}" class="btn btn-success btn-block font-weight-bold shadow-sm py-2 mb-2">
                                <i class="fas fa-box-check mr-1"></i> Receive & Verify Stock at Destination
                            </a>
                            <a href="{{ route('admin.stock-transfers.pdf', $stockTransfer->id) }}" target="_blank" class="btn btn-danger btn-block font-weight-bold py-2">
                                <i class="fas fa-file-pdf mr-1"></i> Download Gate Pass / Challan PDF
                            </a>
                        @elseif($stockTransfer->status === 'received')
                            <a href="{{ route('admin.stock-transfers.pdf', $stockTransfer->id) }}" target="_blank" class="btn btn-danger btn-block font-weight-bold py-2">
                                <i class="fas fa-file-pdf mr-1"></i> Download Transfer Challan PDF
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-boxes mr-2 text-primary"></i> Transferred Items ({{ $stockTransfer->items->count() }})</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th class="text-center">Dispatched Qty</th>
                                        <th class="text-center">Received Qty</th>
                                        <th>Item Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stockTransfer->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->product->name ?? 'Product #' . $item->product_id }}</strong>
                                                @if($item->variant)
                                                    <br><small class="text-muted">{{ $item->variant->color->name ?? '' }} {{ $item->variant->size->name ?? '' }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-primary font-weight-bold">{{ number_format((float)$item->qty, 2) }} {{ $item->product->unit->name ?? 'pcs' }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($stockTransfer->status === 'received')
                                                    <span class="badge badge-success font-weight-bold">{{ number_format((float)$item->received_qty, 2) }} {{ $item->product->unit->name ?? 'pcs' }}</span>
                                                @else
                                                    <span class="text-muted small">Pending Receipt</span>
                                                @endif
                                            </td>
                                            <td class="small text-muted">{{ $item->item_note ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
