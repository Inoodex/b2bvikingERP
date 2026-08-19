@extends('backend.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.stock-adjustments.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Stock Adjustment #{{ $stockAdjustment->adjustment_no }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.stock-adjustments.index') }}">Stock Adjustments</a></div>
            <div class="breadcrumb-item">Details</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-info-circle mr-2 text-primary"></i> Summary</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th class="text-muted" style="width: 140px;">Status:</th>
                                <td>
                                    @if($stockAdjustment->status === 'approved')
                                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Approved</span>
                                    @elseif($stockAdjustment->status === 'cancelled')
                                        <span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i> Cancelled</span>
                                    @else
                                        <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i> Draft</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Type:</th>
                                <td>
                                    @if($stockAdjustment->adjustment_type === 'increase')
                                        <span class="badge badge-success">Stock Increase (+)</span>
                                    @elseif($stockAdjustment->adjustment_type === 'decrease')
                                        <span class="badge badge-danger">Stock Decrease (-)</span>
                                    @else
                                        <span class="badge badge-info">Reconciliation</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Reason:</th>
                                <td><strong class="text-dark">{{ ucfirst(str_replace('_', ' ', $stockAdjustment->reason_code)) }}</strong></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Warehouse / Outlet:</th>
                                <td>{{ $stockAdjustment->outlet ? ($stockAdjustment->outlet->outlet_name ?? $stockAdjustment->outlet->name) : 'Central Warehouse' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Requested By:</th>
                                <td>{{ $stockAdjustment->requestedByUser->name ?? 'System' }}</td>
                            </tr>
                            @if($stockAdjustment->approved_by)
                            <tr>
                                <th class="text-muted">Approved By:</th>
                                <td>{{ $stockAdjustment->approvedByUser->name ?? 'Admin' }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th class="text-muted">Created Date:</th>
                                <td>{{ optional($stockAdjustment->created_at)->format('d M, Y h:i A') }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Total Cost Impact:</th>
                                <td><h5 class="text-primary font-weight-bold mb-0">{{ number_format((float)$stockAdjustment->total_adjusted_cost, 2) }}</h5></td>
                            </tr>
                        </table>

                        @if($stockAdjustment->note)
                            <div class="alert alert-light border mt-3 mb-0 small">
                                <strong>Notes:</strong> {{ $stockAdjustment->note }}
                            </div>
                        @endif
                    </div>

                    @if($stockAdjustment->status === 'draft')
                        <div class="card-footer bg-light border-top py-3">
                            <form action="{{ route('admin.stock-adjustments.approve', $stockAdjustment->id) }}" method="POST" class="mb-2" onsubmit="return confirm('Are you sure you want to approve this adjustment? This will update warehouse inventory balances.');">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block font-weight-bold shadow-sm py-2">
                                    <i class="fas fa-check-circle mr-1"></i> Approve & Apply Adjustment
                                </button>
                            </form>
                            <form action="{{ route('admin.stock-adjustments.cancel', $stockAdjustment->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this adjustment?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-block font-weight-bold py-2">
                                    <i class="fas fa-times-circle mr-1"></i> Cancel Adjustment
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-list mr-2 text-primary"></i> Adjusted Items ({{ $stockAdjustment->items->count() }})</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th class="text-center">System Qty</th>
                                        <th class="text-center">Adjusted Qty</th>
                                        <th class="text-right">Unit Cost</th>
                                        <th class="text-right">Total Cost</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stockAdjustment->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->product->name ?? 'Product #' . $item->product_id }}</strong>
                                                @if($item->variant)
                                                    <br><small class="text-muted">{{ $item->variant->color->name ?? '' }} {{ $item->variant->size->name ?? '' }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ number_format((float)$item->system_qty, 2) }}</td>
                                            <td class="text-center">
                                                @if($stockAdjustment->adjustment_type === 'decrease')
                                                    <span class="badge badge-danger">-{{ number_format((float)$item->adjusted_qty, 2) }}</span>
                                                @else
                                                    <span class="badge badge-success">+{{ number_format((float)$item->adjusted_qty, 2) }}</span>
                                                @endif
                                            </td>
                                            <td class="text-right">{{ number_format((float)$item->unit_cost, 2) }}</td>
                                            <td class="text-right font-weight-bold">{{ number_format((float)$item->total_cost, 2) }}</td>
                                            <td class="small text-muted">{{ $item->item_note ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="5" class="text-right">Grand Total:</td>
                                        <td class="text-right text-primary font-weight-bold">{{ number_format((float)$stockAdjustment->total_adjusted_cost, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
