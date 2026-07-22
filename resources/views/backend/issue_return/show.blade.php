@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.issue-returns.index') }}" class="btn btn-icon">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            <h1>Return Details - {{ $return->return_no }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.issue-returns.index') }}">Stock Returns</a></div>
                <div class="breadcrumb-item">{{ $return->return_no }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom pb-2">
                            <h4 class="text-primary font-weight-bold">Returned Items</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-md">
                                    <thead>
                                        <tr>
                                            <th data-width="40">#</th>
                                            <th>Product Details</th>
                                            <th class="text-center">Returned Qty</th>
                                            <th class="text-right">Unit Price</th>
                                            <th class="text-right">Total Price</th>
                                            <th class="text-center">Condition</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($return->items as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $item->product ? $item->product->name : 'Deleted Product' }}</strong>
                                                    @if($item->variant)
                                                        @php
                                                            $variantColorName = is_object($item->variant->color ?? null) ? ($item->variant->color->name ?? '') : ((string) ($item->variant->color ?? ''));
                                                            $variantSizeName = is_object($item->variant->size ?? null) ? ($item->variant->size->name ?? '') : ((string) ($item->variant->size ?? ''));
                                                        @endphp
                                                        <br>
                                                        <span class="text-muted small">
                                                            Variant: {{ $item->variant->name }} 
                                                            @if($variantColorName || $variantSizeName)
                                                                ({{ $variantColorName }}{{ $variantColorName && $variantSizeName ? ' / ' : '' }}{{ $variantSizeName }})
                                                            @endif
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $item->quantity }}</td>
                                                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                                <td class="text-right">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                                <td class="text-center">
                                                    @if ($item->condition === 'good')
                                                        <span class="badge badge-success">Good</span>
                                                    @else
                                                        <span class="badge badge-danger">Damaged</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom pb-2">
                            <h4 class="text-primary font-weight-bold">Summary Info</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Return No:</span>
                                <span class="font-weight-bold">{{ $return->return_no }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Date:</span>
                                <span class="font-weight-bold">{{ $return->created_at->format('Y-m-d h:i A') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Status:</span>
                                <div>
                                    @if ($return->status === 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif ($return->status === 'cancelled')
                                        <span class="badge badge-danger">Cancelled</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Outlet / User:</span>
                                <span class="font-weight-bold">
                                    {{ $return->outlet ? $return->outlet->name : '-' }}
                                    @if($return->outlet && $return->outlet->outlet_name)
                                        <br><small class="text-muted">({{ $return->outlet->outlet_name }})</small>
                                    @endif
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Original Issue No:</span>
                                <span class="font-weight-bold">
                                    @if($return->issue)
                                        <a href="{{ route('admin.issues.show', $return->issue_id) }}">{{ $return->issue->issue_no }}</a>
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            @if($return->order_id)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Linked Order:</span>
                                <span class="font-weight-bold">
                                    <a href="{{ route('admin.orders.show', $return->order_id) }}">{{ $return->order ? $return->order->order_no : '-' }}</a>
                                </span>
                            </div>
                            @endif
                            <div class="d-flex justify-content-between mb-2 border-top pt-2">
                                <span class="text-muted">Refund Amount:</span>
                                <span class="font-weight-bold text-success">{{ number_format($return->refund_amount, 2) }}</span>
                            </div>
                            @if ($return->approvedBy)
                            <div class="d-flex justify-content-between mb-2 border-top pt-2">
                                <span class="text-muted">Approved By:</span>
                                <span class="font-weight-bold">{{ $return->approvedBy->name }}</span>
                            </div>
                            @endif
                            @if ($return->note)
                            <div class="mt-3">
                                <span class="text-muted">Note:</span>
                                <div class="bg-light p-2 rounded mt-1 small">
                                    {{ $return->note }}
                                </div>
                            </div>
                            @endif
                        </div>
                        @if ($return->status === 'pending')
                            <div class="card-footer bg-white border-top d-flex justify-content-between">
                                <form action="{{ route('admin.issue-returns.approve', $return->id) }}" method="POST" class="approve-form w-50 mr-1">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-block font-weight-bold">
                                        <i class="fas fa-check-circle"></i> Approve
                                    </button>
                                </form>
                                <form action="{{ route('admin.issue-returns.cancel', $return->id) }}" method="POST" class="cancel-form w-50 ml-1">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-block font-weight-bold">
                                        <i class="fas fa-times-circle"></i> Cancel
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.approve-form').on('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Approving this return will increment stock and reconcile accounts.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, approve it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });

            $('.cancel-form').on('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to cancel this return request.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, cancel it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
    </script>
@endpush
