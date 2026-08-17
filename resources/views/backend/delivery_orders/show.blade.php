@extends('backend.layouts.master')
@section('title', 'Delivery Order Details')

@section('content')
    <section class="section">
        {{-- Standard Stisla Section Header --}}
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.delivery-orders.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Delivery Order #{{ $deliveryOrder->delivery_no }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.delivery-orders.index') }}">Delivery Orders</a></div>
                <div class="breadcrumb-item">Details</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-md-8 col-12">
                    {{-- Delivery Details & Items Table Card --}}
                    <div class="card card-primary">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="fas fa-truck mr-2"></i>Outbound Shipping Challan Details</h4>
                            <div>
                                <a href="{{ route('admin.delivery-orders.pdf', $deliveryOrder->id) }}" target="_blank" class="btn btn-danger font-weight-bold mr-2 shadow-sm">
                                    <i class="fas fa-file-pdf mr-1"></i> Packing Slip PDF
                                </a>
                                @if ($deliveryOrder->status === 'pending')
                                    <form action="{{ route('admin.delivery-orders.dispatch', $deliveryOrder->id) }}" method="POST" class="d-inline" id="dispatch-form">
                                        @csrf
                                        <button type="button" class="btn btn-success font-weight-bold shadow-sm" id="btn-dispatch-order">
                                            <i class="fas fa-paper-plane mr-1"></i> Dispatch & Ship Order
                                        </button>
                                    </form>
                                @else
                                    <span class="badge badge-success px-3 py-2 font-weight-bold">
                                        <i class="fas fa-check-circle mr-1"></i> Dispatched & Shipped
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Logistics Banner --}}
                            <div class="row bg-light p-3 rounded mb-4 mx-0">
                                <div class="col-md-4 col-12">
                                    <span class="text-muted d-block small">Carrier / Logistics:</span>
                                    <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-shipping-fast text-primary mr-1"></i> {{ $deliveryOrder->carrier_name ?: 'Standard Delivery' }}</h6>
                                </div>
                                <div class="col-md-4 col-12 text-md-center mt-2 mt-md-0">
                                    <span class="text-muted d-block small">AWB / Tracking No:</span>
                                    <span class="badge badge-primary px-3 py-1 font-weight-bold"><i class="fas fa-barcode mr-1"></i> {{ $deliveryOrder->awb_number ?: 'N/A' }}</span>
                                </div>
                                <div class="col-md-4 col-12 text-md-right mt-2 mt-md-0">
                                    <span class="text-muted d-block small">Shipping Method:</span>
                                    <span class="badge badge-info px-3 py-1 font-weight-bold"><i class="fas fa-route mr-1"></i> {{ $deliveryOrder->shipping_method ?: 'Road Freight' }}</span>
                                </div>
                            </div>

                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-striped">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Product / Variant</th>
                                            <th class="text-center">Dispatched Qty</th>
                                            <th class="text-right">Unit Price</th>
                                            <th class="text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $grandTotal = 0; @endphp
                                        @foreach ($deliveryOrder->items as $index => $item)
                                            @php
                                                $lineSubtotal = (float)$item->qty_delivered * (float)$item->unit_price;
                                                $grandTotal += $lineSubtotal;
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong class="text-dark">{{ $item->product ? $item->product->name : 'Product #' . $item->product_id }}</strong>
                                                    @if ($item->variant)
                                                        <br><small class="text-muted">
                                                            @if($item->variant->color) Color: {{ $item->variant->color->name }} @endif
                                                            @if($item->variant->size) Size: {{ $item->variant->size->name }} @endif
                                                            {{ $item->variant->name }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td class="text-center font-weight-bold h6 text-primary mb-0">{{ number_format((float)$item->qty_delivered, 2) }}</td>
                                                <td class="text-right">kr. {{ number_format((float)$item->unit_price, 2) }}</td>
                                                <td class="text-right font-weight-bold">kr. {{ number_format($lineSubtotal, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-right font-weight-bold">Total Shipment Value:</th>
                                            <th class="text-right font-weight-bold text-success h5 mb-0">kr. {{ number_format($grandTotal, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            @if($deliveryOrder->notes)
                                <div class="alert alert-light border">
                                    <strong class="text-dark"><i class="fas fa-sticky-note mr-1"></i> Driver Notes:</strong> {{ $deliveryOrder->notes }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar Order & Shipping Summary Card --}}
                <div class="col-md-4 col-12">
                    {{-- Linked Order Info --}}
                    <div class="card card-info">
                        <div class="card-header">
                            <h4><i class="fas fa-shopping-bag mr-2"></i>Linked Order Ref</h4>
                        </div>
                        <div class="card-body">
                            @if ($deliveryOrder->order)
                                @php
                                    $orderObj = $deliveryOrder->order;
                                    $totalOrdered = $orderObj ? (float)$orderObj->items->sum('quantity') : 0;
                                    $totalDelivered = $orderObj ? (float)\App\Models\DeliveryOrderItem::whereHas('deliveryOrder', function ($q) use ($orderObj) {
                                        $q->where('order_id', $orderObj->id)->whereIn('status', ['dispatched', 'shipped', 'delivered']);
                                    })->sum('qty_delivered') : 0;

                                    $calculatedFulfillment = 'unfulfilled';
                                    if ($totalDelivered >= $totalOrdered && $totalOrdered > 0) {
                                        $calculatedFulfillment = 'fully_delivered';
                                    } elseif ($totalDelivered > 0) {
                                        $calculatedFulfillment = 'partially_delivered';
                                    }
                                    $fulfillmentStatus = ($orderObj && $orderObj->fulfillment_status) ? $orderObj->fulfillment_status : $calculatedFulfillment;
                                @endphp
                                <p class="mb-1"><strong>Order No:</strong> <a href="{{ route('admin.orders.show', $deliveryOrder->order->id) }}">#{{ $deliveryOrder->order->order_no }}</a></p>
                                <p class="mb-1"><strong>Customer:</strong> {{ $deliveryOrder->order->user ? ($deliveryOrder->order->user->outlet_name ?: $deliveryOrder->order->user->name) : 'Guest / Cash' }}</p>
                                <p class="mb-1"><strong>Order Date:</strong> {{ $deliveryOrder->order->created_at ? $deliveryOrder->order->created_at->format('d M Y') : '-' }}</p>
                                <p class="mb-0">
                                    <strong>Fulfillment Status:</strong> 
                                    @if ($fulfillmentStatus === 'fully_delivered')
                                        <span class="badge badge-success px-2">Fully Delivered</span>
                                    @elseif ($fulfillmentStatus === 'partially_delivered')
                                        <span class="badge badge-warning px-2">Partially Delivered</span>
                                    @else
                                        <span class="badge badge-secondary px-2">Unfulfilled</span>
                                    @endif
                                </p>
                            @else
                                <p class="text-muted mb-0">No linked order</p>
                            @endif
                        </div>
                    </div>

                    {{-- Dispatcher Audit Card --}}
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h4><i class="fas fa-user-shield mr-2"></i>Audit Information</h4>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Created By:</strong> {{ $deliveryOrder->creator ? $deliveryOrder->creator->name : 'System' }}</p>
                            <p class="mb-1"><strong>Created At:</strong> {{ $deliveryOrder->created_at ? $deliveryOrder->created_at->format('d M Y, h:i A') : '-' }}</p>
                            <p class="mb-0"><strong>Dispatched By:</strong> {{ $deliveryOrder->dispatcher ? $deliveryOrder->dispatcher->name : ($deliveryOrder->status === 'dispatched' ? 'Warehouse Dispatcher' : 'Pending Dispatch') }}</p>
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
            $('#btn-dispatch-order').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Dispatch & Ship Delivery Order?',
                    text: 'Are you sure you want to dispatch Delivery Order #{{ $deliveryOrder->delivery_no }}? Physical inventory stock will be deducted and logged in StockLedger.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#27ae60',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Dispatch & Ship!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#dispatch-form').submit();
                    }
                });
            });
        });
    </script>
@endpush
