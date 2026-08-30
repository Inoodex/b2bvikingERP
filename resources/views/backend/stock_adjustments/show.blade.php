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
                            <form id="approve_adjustment_form" action="{{ route('admin.stock-adjustments.approve', $stockAdjustment->id) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="button" class="btn btn-success btn-block font-weight-bold shadow-sm py-2" id="btn_approve_adjustment">
                                    <i class="fas fa-check-circle mr-1"></i> Approve & Apply Adjustment
                                </button>
                            </form>
                            <form id="cancel_adjustment_form" action="{{ route('admin.stock-adjustments.cancel', $stockAdjustment->id) }}" method="POST">
                                @csrf
                                <button type="button" class="btn btn-outline-danger btn-block font-weight-bold py-2" id="btn_cancel_adjustment">
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

@push('scripts')
<script>
    $(document).ready(function() {
        $('#btn_approve_adjustment').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: "Approve this Stock Adjustment?",
                text: "This will update warehouse inventory balances according to the adjusted quantities.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#47c363",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, Approve & Apply!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#approve_adjustment_form').submit();
                }
            });
        });

        $('#btn_cancel_adjustment').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: "Cancel Adjustment?",
                text: "Are you sure you want to cancel this draft stock adjustment?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#fc544b",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, Cancel Adjustment!",
                cancelButtonText: "Keep Draft"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#cancel_adjustment_form').submit();
                }
            });
        });
    });
</script>
@endpush
