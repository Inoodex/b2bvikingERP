@extends('backend.layouts.master')
@section('title', 'Sales Order Details')

@section('content')
    @php
        // Calculate display values dynamically based on displayed items (original or issued)
        $displayItems = isset($items) ? $items : $order->items;
        $displaySubtotal = $displayItems->sum('line_total');
        $displayGrandTotal = isset($items) ? ($displaySubtotal - $order->discount_amount + $order->tax_amount) : $order->total_amount;
        $displayPaid = (float) $order->paid_amount;
        $displayDue = max(0, round($displayGrandTotal - $displayPaid, 2));
    @endphp
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.sales-orders.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Sales Order #{{ $order->order_no }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.sales-orders.index') }}">Sales Orders</a></div>
                <div class="breadcrumb-item">Details</div>
            </div>
        </div>

        <div class="section-body">
            @if($order->status === 'credit_hold')
                <div class="alert alert-danger shadow-sm border-0 mb-4 p-4" style="border-radius: 12px; background: #fff5f5; border-left: 5px solid #dc3545 !important;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center mb-2 mb-md-0">
                            <div class="p-3 bg-danger text-white rounded-circle mr-3">
                                <i class="fas fa-lock fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 text-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Order Flagged Under Credit Hold</h5>
                                <p class="mb-0 text-dark small">This order exceeds the customer's approved credit limit exposure. Fulfillment is blocked until a Credit Manager releases the hold.</p>
                            </div>
                        </div>
                        <div>
                            <button type="button" class="btn btn-danger btn-lg font-weight-bold shadow-sm px-4" data-toggle="modal" data-target="#releaseCreditModal" style="border-radius: 8px;">
                                <i class="fas fa-unlock-alt mr-2"></i> Release Credit Hold
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-list mr-2"></i>Order Items</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.orders.pi-invoice', $order->id) }}" class="btn btn-success" target="_blank"><i class="fas fa-file-signature mr-1"></i> PI Invoice</a>
                                <a href="{{ route('admin.orders.view-invoice', $order->id) }}" class="btn btn-warning" target="_blank"><i class="fas fa-file-invoice mr-1"></i> View Invoice</a>
                                <a href="{{ route('admin.orders.download-invoice', $order->id) }}" class="btn btn-info ml-2"><i class="fas fa-download mr-1"></i> Download PDF</a>
                                <a href="{{ route('admin.orders.download-customer-invoice', $order->id) }}" class="btn btn-dark ml-2"><i class="fas fa-file-invoice mr-1"></i> Customer Invoice</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center" width="5%">#</th>
                                            <th class="text-center" width="12%">Image</th>
                                            <th>Product</th>
                                            <th class="text-center">Variant</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-right">Unit Price</th>
                                            <th class="text-right">Line Total</th>
                                        </tr>
                                    </thead>
                                    @php
                                        $groupedItems = $displayItems->groupBy(function($item) {
                                            return $item->category_name ?: 'General';
                                        })->sortKeys();
                                    @endphp
                                    <tbody>
                                        @forelse($groupedItems as $categoryName => $catItems)
                                            <tr class="bg-secondary text-white font-weight-bold">
                                                <td colspan="7" class="py-2 px-3">
                                                    <i class="fas fa-folder mr-1 text-warning"></i> Category: {{ $categoryName }}
                                                </td>
                                            </tr>
                                            @foreach($catItems as $item)
                                                <tr>
                                                    <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                                    <td class="text-center align-middle">
                                                        @if(!empty($item->product_image))
                                                            <img src="{{ asset($item->product_image) }}" alt="{{ $item->product_name }}" class="rounded shadow-sm" width="50" height="50" style="object-fit: cover;">
                                                        @else
                                                            <div class="bg-light rounded d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                                <i class="fas fa-box text-muted"></i>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="font-weight-bold text-dark d-block">{{ $item->product_name }}</span>
                                                        <small class="text-muted">Item #: {{ $item->product_number }}</small>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <span class="badge badge-light border">{{ $item->variant_label ?: ($item->variant->name ?? 'Standard') }}</span>
                                                    </td>
                                                    <td class="text-center align-middle font-weight-bold">{{ $item->quantity }}</td>
                                                    <td class="text-right align-middle">{{ number_format($item->unit_price, 2) }}</td>
                                                    <td class="text-right align-middle font-weight-bold text-primary">{{ number_format($item->line_total, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">No items in this order.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-light font-weight-bold">
                                        <tr>
                                            <td colspan="6" class="text-right">Subtotal</td>
                                            <td class="text-right">{{ number_format($displaySubtotal, 2) }}</td>
                                        </tr>
                                        @if($order->discount_amount > 0)
                                            <tr>
                                                <td colspan="6" class="text-right text-success">Discount</td>
                                                <td class="text-right text-success">-{{ number_format($order->discount_amount, 2) }}</td>
                                            </tr>
                                        @endif
                                        @if($order->tax_amount > 0)
                                            <tr>
                                                <td colspan="6" class="text-right">VAT / Tax</td>
                                                <td class="text-right">{{ number_format($order->tax_amount, 2) }}</td>
                                            </tr>
                                        @endif
                                        <tr class="h6">
                                            <td colspan="6" class="text-right font-weight-bold">Grand Total</td>
                                            <td class="text-right text-primary font-weight-bold">{{ number_format($displayGrandTotal, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card card-statistic-1 mb-3">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Current Status</h4>
                            </div>
                            <div class="card-body">
                                @php
                                    $status = strtolower((string) $order->status);
                                    $statusColor = match($status) {
                                        'pending' => 'warning',
                                        'credit_hold' => 'danger',
                                        'approved' => 'info',
                                        'processing' => 'primary',
                                        'shipped' => 'primary',
                                        'completed' => 'success',
                                        'rejected', 'cancelled' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <div class="text-{{ $statusColor }} font-weight-bold text-uppercase">{{ str_replace('_', ' ', $order->status) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header border-bottom">
                            <h4>Order Summary</h4>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Order No:</strong> {{ $order->order_no }}</p>
                            <p class="mb-1"><strong>Date:</strong> {{ $order->created_at?->format('d M, Y h:i A') }}</p>
                            <p class="mb-1"><strong>Customer:</strong> {{ $order->user->name ?? $order->billing_name }}</p>
                            <p class="mb-1"><strong>Outlet/Shop:</strong> {{ $order->billing_outlet_name ?: ($order->user->outlet_name ?? 'N/A') }}</p>
                            <p class="mb-3"><strong>Source:</strong> {{ $order->shipping_method ?: 'frontend_checkout' }}</p>

                            <hr>
                            <p class="mb-1 d-flex justify-content-between"><span>Subtotal</span><strong>{{ number_format($displaySubtotal, 2) }}</strong></p>
                            <p class="mb-1 d-flex justify-content-between"><span>Discount</span><strong>-{{ number_format($order->discount_amount, 2) }}</strong></p>
                            <p class="mb-1 d-flex justify-content-between"><span>VAT</span><strong>{{ number_format($order->tax_amount, 2) }}</strong></p>
                            <p class="mb-0 d-flex justify-content-between"><span class="font-weight-bold">Total</span><strong class="text-primary">{{ number_format($displayGrandTotal, 2) }}</strong></p>
                        </div>
                    </div>

                    {{-- Customer Credit Exposure Widget --}}
                    @if(isset($creditEvaluation))
                        <div class="card card-warning mb-3">
                            <div class="card-header border-bottom">
                                <h4><i class="fas fa-shield-alt text-warning mr-2"></i>Customer Credit Exposure</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Approved Credit Limit:</span>
                                    <strong class="text-dark">kr. {{ number_format($creditEvaluation['credit_limit'] ?? 0, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Current Unpaid Dues:</span>
                                    <strong class="text-danger">kr. {{ number_format($creditEvaluation['current_dues'] ?? 0, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">This Order Total:</span>
                                    <strong class="text-primary">kr. {{ number_format($creditEvaluation['new_order_total'] ?? 0, 2) }}</strong>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold text-dark">Total Exposure:</span>
                                    <span class="h6 font-weight-bold {{ ($creditEvaluation['is_exceeded'] ?? false) ? 'text-danger' : 'text-success' }} mb-0">
                                        kr. {{ number_format($creditEvaluation['total_exposure'] ?? 0, 2) }}
                                    </span>
                                </div>
                                @if(($creditEvaluation['is_exceeded'] ?? false))
                                    <div class="alert alert-danger py-2 mb-0 mt-3 small font-weight-bold text-center">
                                        <i class="fas fa-lock mr-1"></i> Credit Limit Exceeded!
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="card card-warning">
                        <div class="card-header">
                            <h4><i class="fas fa-user-cog mr-2"></i>Actions</h4>
                        </div>
                        <div class="card-body">
                            @php
                                $currentStatus = strtolower((string) $order->status);
                                $isLockedStatus = in_array($currentStatus, ['completed', 'rejected'], true);
                            @endphp
                            <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold small text-muted text-uppercase">Change Status</label>
                                    <select name="status" class="form-control" {{ $isLockedStatus ? 'disabled' : '' }}>
                                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ $order->status === 'approved' ? 'selected' : '' }}>Approved (Ready for Delivery)</option>
                                        <option value="credit_hold" {{ $order->status === 'credit_hold' ? 'selected' : '' }}>Credit Hold</option>
                                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        @if(!in_array($order->status, ['pending', 'approved', 'credit_hold', 'cancelled'], true))
                                            <option value="{{ $order->status }}" selected>{{ ucfirst($order->status) }}</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary shadow-sm px-4" {{ $isLockedStatus ? 'disabled' : '' }}>Update Order</button>
                                </div>
                            </form>

                            @if($order->status === 'credit_hold')
                                <div class="border-top pt-3 mt-3">
                                    <button type="button" class="btn btn-danger btn-block font-weight-bold py-2 shadow-sm" data-toggle="modal" data-target="#releaseCreditModal">
                                        <i class="fas fa-unlock-alt mr-1"></i> Release Credit Hold
                                    </button>
                                </div>
                            @endif

                            @can('Manage Inventory')
                            @if(in_array(strtolower((string) $order->status), ['approved', 'processing', 'completed']))
                                <div class="border-top pt-4 mt-3">
                                    <a href="{{ route('admin.delivery-orders.create', ['order_id' => $order->id]) }}" class="btn btn-primary btn-lg btn-block shadow-sm py-3 font-weight-bold mb-2">
                                        <i class="fas fa-truck mr-2"></i> Create Delivery Challan (DO)
                                    </a>
                                    <a href="{{ route('admin.sales-invoices.create', ['order_id' => $order->id]) }}" class="btn btn-info btn-block shadow-sm py-2 font-weight-bold">
                                        <i class="fas fa-file-invoice-dollar mr-2"></i> Generate Sales Invoice
                                    </a>
                                    <p class="text-center text-muted small mt-2 mb-0">Create shipment challan to dispatch goods or generate commercial invoice.</p>
                                </div>
                            @endif
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Credit Hold Release Modal --}}
    @if($order->status === 'credit_hold')
        <div class="modal fade" id="releaseCreditModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-header border-bottom py-3">
                        <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-unlock-alt text-danger mr-2"></i> Authorize Credit Hold Release</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.sales-orders.release-credit', $order->id) }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="alert alert-warning mb-3 small">
                                <strong>Warning:</strong> Releasing credit hold will bypass the customer's credit limit rule for Sales Order #{{ $order->order_no }}.
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-dark">Override Reason / Authorization Notes <span class="text-danger">*</span></label>
                                <textarea name="override_reason" class="form-control" rows="3" placeholder="e.g. Approved by Finance Manager (Payment Promise on 25th)" required style="border-radius: 8px;"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-3 border-top">
                            <button type="button" class="btn btn-secondary px-4 font-weight-bold" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                            <button type="submit" class="btn btn-danger px-4 font-weight-bold shadow-sm" style="border-radius: 8px;">Authorize Release</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
