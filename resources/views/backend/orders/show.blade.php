@extends('backend.layouts.master')
@section('title', 'Frontend Order Details')

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
                <a href="{{ route('admin.orders.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Order #{{ $order->order_no }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Frontend Orders</a></div>
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
                                {{-- <a href="{{ route('admin.orders.destroy', $order->id) }}" class="btn btn-danger ml-2 delete-item"><i class="fas fa-trash mr-1"></i> Delete</a> --}}
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
                                        // Group items by category and sort alphabetically
                                        $groupedItems = $displayItems->groupBy(function($item) {
                                            return $item->category_name ?: 'General';
                                        })->sortKeys();

                                        // Sort items within each category by product name (letter by letter)
                                        $sortedGroupedItems = $groupedItems->map(function($items) {
                                            return $items->sortBy(function($item) {
                                                return strtolower($item->product_name);
                                            })->values();
                                        });

                                        $globalIndex = 0;
                                    @endphp
                                    <tbody>
                                        @forelse($sortedGroupedItems as $categoryName => $categoryItems)
                                            <tr class="bg-light">
                                                <td colspan="7" class="py-2 px-3 font-weight-bold text-uppercase text-muted" style="background-color: #e9ecef; font-size: 12px;">
                                                    {{ $categoryName }}
                                                </td>
                                            </tr>
                                            @foreach($categoryItems as $item)
                                                @php
                                                    $imagePath = (string) ($item->product_image ?? '');
                                                    $imageUrl = null;
                                                    if ($imagePath !== '') {
                                                        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
                                                            $imageUrl = $imagePath;
                                                        } elseif (is_file(public_path(ltrim($imagePath, '/')))) {
                                                            $imageUrl = asset(ltrim($imagePath, '/'));
                                                        } elseif (str_starts_with($imagePath, 'storage/')) {
                                                            $imageUrl = asset($imagePath);
                                                        } else {
                                                            $imageUrl = asset('storage/' . ltrim($imagePath, '/'));
                                                        }
                                                    }
                                                    $globalIndex++;
                                                @endphp
                                                <tr>
                                                    <td class="text-center font-weight-bold">{{ $globalIndex }}</td>
                                                <td class="text-center">
                                                    @if($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" style="width:44px;height:44px;object-fit:cover;border-radius:4px;border:1px solid #e5e7eb;">
                                                    @else
                                                        <span class="text-muted small">No Image</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="font-weight-600">{{ $item->product_name }}</div>
                                                    <small class="text-muted">{{ $item->category_name ?? 'General' }}</small>
                                                </td>
                                                <td class="text-center">
                                                    @if($item->variant_label)
                                                        <span class="badge badge-primary">{{ $item->variant_label }}</span>
                                                    @elseif($item->variant && $item->variant->name)
                                                        <span class="badge badge-primary">{{ $item->variant->name }}</span>
                                                    @else
                                                        <span class="text-muted">Standard</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-info px-3">{{ $item->quantity }}</span>
                                                </td>
                                                <td class="text-right">{{ formatConverted($item->unit_price, 2) }}</td>
                                                <td class="text-right font-weight-bold text-primary">{{ formatConverted($item->line_total, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">No items found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-whitesmoke">
                                        <tr>
                                            <td colspan="6" class="text-right font-weight-bold text-muted text-uppercase small">Grand Total</td>
                                            <td class="text-right font-weight-bold h6 text-primary mb-0">{{ formatConverted($displayGrandTotal, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    @include('backend.pi._editor', [
                        'title' => 'Manual PI / CTN Info',
                        'subtitle' => 'Save carton and packing details first, then open the PI invoice for review or sharing.',
                        'formAction' => route('admin.orders.pi-info.save', $order->id),
                        'piInvoiceUrl' => route('admin.orders.pi-invoice', $order->id),
                        'items' => isset($items) ? $items : $order->items,
                        'piInfo' => $piInfo,
                        'piTotals' => $piTotals,
                    ])

                    <div class="card">
                        <div class="card-header border-bottom">
                            <h4>Billing & Shipping</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted text-uppercase small mb-3">Billing</h6>
                                    <p class="mb-1"><strong>Name:</strong> {{ $order->billing_name }}</p>
                                    <p class="mb-1"><strong>Phone:</strong> {{ $order->billing_phone }}</p>
                                    <p class="mb-1"><strong>Email:</strong> {{ $order->billing_email }}</p>
                                    <p class="mb-1"><strong>Address:</strong> {{ $order->billing_address }}</p>
                                    <p class="mb-0"><strong>Outlet/Shop:</strong> {{ $order->billing_outlet_name ?: ($order->user->outlet_name ?? 'N/A') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted text-uppercase small mb-3">Shipping</h6>
                                    @if($order->ship_different)
                                        <p class="mb-1"><strong>Name:</strong> {{ $order->shipping_name ?: 'N/A' }}</p>
                                        <p class="mb-1"><strong>Phone:</strong> {{ $order->shipping_phone ?: 'N/A' }}</p>
                                        <p class="mb-1"><strong>Email:</strong> {{ $order->shipping_email ?: 'N/A' }}</p>
                                        <p class="mb-1"><strong>Address:</strong> {{ $order->shipping_address ?: 'N/A' }}</p>
                                        <p class="mb-1"><strong>City/State:</strong> {{ trim(($order->shipping_city ?: '') . ' ' . ($order->shipping_state ?: '')) ?: 'N/A' }}</p>
                                        <p class="mb-1"><strong>Zip/Country:</strong> {{ trim(($order->shipping_zip_code ?: '') . ' ' . ($order->shipping_country ?: '')) ?: 'N/A' }}</p>
                                        <p class="mb-0"><strong>Outlet/Shop:</strong> {{ $order->shipping_outlet_name ?: 'N/A' }}</p>
                                    @else
                                        <div class="alert alert-light border mb-0">Same as billing information.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment History (Admin Only) --}}
                    @role('Admin')
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h4><i class="fas fa-history mr-2"></i>Payment History</h4>
                            @if($order->payments->count() > 0)
                                <div class="card-header-action">
                                    <a href="{{ route('admin.accounts.orders.payments.pdf', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file-pdf mr-1"></i> Download All
                                    </a>
                                    <a href="{{ route('admin.accounts.orders.payments.view', $order->id) }}" class="btn btn-sm btn-outline-primary ml-2" target="_blank">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                </div>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Method</th>
                                            <th>Transaction ID</th>
                                            <th>Receipts</th>
                                            <th>PDF</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($order->payments as $payment)
                                            <tr>
                                                <td>{{ $payment->created_at->format('d M, Y h:i A') }}</td>
                                                <td><span class="badge badge-info">{{ strtoupper($payment->payment_method) }}</span></td>
                                                <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
                                                <td>
                                                    @if($payment->receipts->count() > 0)
                                                        @foreach($payment->receipts as $receipt)
                                                            <div class="mb-1">
                                                                <a href="{{ route('admin.accounts.receipts.download', $receipt->id) }}" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-download mr-1"></i>
                                                                </a>
                                                                <a href="{{ route('admin.accounts.receipts.destroy', $receipt->id) }}" class="btn btn-sm btn-outline-danger delete-item">
                                                                    <i class="fas fa-trash mr-1"></i>
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.accounts.payments.single.pdf', $payment->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-file-pdf mr-1"></i>
                                                    </a>
                                                     <a href="{{ route('admin.accounts.payments.single.view', $payment->id) }}" class="btn btn-sm btn-outline-primary ml-2" target="_blank">
                                        <i class="fas fa-eye mr-1"></i>
                                    </a>
                                                </td>
                                                <td class="text-right font-weight-bold">{{$settings->currency_icon}}{{ number_format($payment->amount, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-3 text-muted">No payments recorded yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($order->payments->count() > 0)
                                        <tfoot class="bg-light">
                                            <tr>
                                                <td colspan="5" class="text-right font-weight-bold">Total Paid</td>
                                                <td class="text-right font-weight-bold text-success">{{$settings->currency_icon}}{{ number_format($order->paid_amount, 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                    @endrole
                </div>

                <div class="col-12 col-lg-4">
                    {{-- Approval Engine UI --}}
                    @if($order->approvals && $order->approvals->count() > 0)
                    <div class="card card-info mb-3">
                        <div class="card-header border-bottom">
                            <h4><i class="fas fa-sitemap mr-2"></i>Approval Chain</h4>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled list-unstyled-border mb-0">
                                @foreach($order->approvals as $approval)
                                    @php
                                        $step = $approval->step;
                                        $isCurrentPending = ($approval->status === 'pending');
                                        $roleName = $step->approverRole ? $step->approverRole->name : '';
                                        $canApprove = $isCurrentPending && (\Illuminate\Support\Facades\Auth::user()?->hasRole($roleName) ?? false);
                                    @endphp
                                    <li class="media mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <div class="media-body">
                                            <div class="mt-0 mb-1 font-weight-bold">Step {{ $step->step_order ?? '' }}: {{ $roleName ?: 'Unknown Role' }}</div>
                                            <div class="text-small">
                                                Status:
                                                @if($approval->status === 'approved')
                                                    <span class="badge badge-success">Approved</span>
                                                @elseif($approval->status === 'rejected')
                                                    <span class="badge badge-danger">Rejected</span>
                                                @else
                                                    <span class="badge badge-warning">Pending</span>
                                                @endif
                                            </div>
                                            
                                            @if($approval->status === 'approved' || $approval->status === 'rejected')
                                                <div class="text-muted small mt-1">
                                                    By: {{ $approval->user->name ?? 'System' }}<br>
                                                    Date: {{ $approval->updated_at->format('d M, Y h:i A') }}
                                                    @if($approval->comments)
                                                        <br><i>"{{ $approval->comments }}"</i>
                                                    @endif
                                                </div>
                                            @endif

                                            @if($canApprove)
                                                <div class="mt-2 d-flex">
                                                    <form action="{{ route('admin.orders.approve', $order->id) }}" method="POST" class="mr-2">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this order?')"><i class="fas fa-check"></i> Approve</button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal"><i class="fas fa-times"></i> Reject</button>
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    {{-- Reject Modal --}}
                    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reject Order</h5>
                                        <button type="button" class="close" data-dismiss="modal">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label>Reason for Rejection</label>
                                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-danger">Reject Order</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

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
                                        'approved' => 'info',
                                        'processing' => 'primary',
                                        'shipped' => 'primary',
                                        'completed' => 'success',
                                        'rejected', 'cancelled' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <div class="text-{{ $statusColor }} text-uppercase">{{ ucfirst($order->status) }}</div>
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

                    {{-- Payment Summary & History (Admin Only) --}}
                    @role('Admin')
                    <div class="card card-success mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="fas fa-money-bill-wave mr-2"></i>Payment Summary</h4>
                            @if($displayDue > 0 && $order->status === 'completed')
                                <a href="{{ route('admin.accounts.record-payment', ['order_no' => $order->order_no]) }}" class="btn btn-sm btn-outline-white">
                                    <i class="fas fa-plus mr-1"></i> Record via Account Module
                                </a>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 border-right">
                                    <div class="text-muted small text-uppercase font-weight-bold">Paid</div>
                                    <div class="h5 font-weight-bold text-success mb-0">{{ number_format($displayPaid, 2) }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small text-uppercase font-weight-bold">Due</div>
                                    <div class="h5 font-weight-bold text-danger mb-0">{{ number_format($displayDue, 2) }}</div>
                                </div>
                            </div>

                            @if($displayDue <= 0 && $displayGrandTotal > 0)
                                <div class="alert alert-success text-center py-2 mb-0 mt-3">
                                    <i class="fas fa-check-circle mr-1"></i> Full Paid
                                </div>
                            @elseif($displayDue > 0)
                                <div class="text-center mt-3">
                                    <span class="badge badge-warning">Partial Payment Pending</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endrole

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
                                    @php
                                        $disableApproveDropdown = false;
                                        if($order->approvals && $order->approvals->where('status', 'pending')->count() > 0) {
                                            $disableApproveDropdown = true;
                                        }
                                    @endphp
                                    <label class="font-weight-bold small text-muted text-uppercase">Change Status</label>
                                    @if($disableApproveDropdown)
                                        <div class="alert alert-warning py-2 mb-2 small"><i class="fas fa-exclamation-triangle mr-1"></i> Manual approval is disabled while pending multi-level approvals.</div>
                                    @endif
                                    <select name="status" class="form-control" {{ $isLockedStatus ? 'disabled' : '' }}>
                                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ $order->status === 'approved' ? 'selected' : '' }} {{ $disableApproveDropdown ? 'disabled' : '' }}>Approve (Create Issue)</option>
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
                                @if($isLockedStatus)
                                    <p class="text-muted small mt-2 mb-0">This order is already {{ ucfirst($order->status) }} and cannot be changed from here.</p>
                                @endif
                            </form>

                            @if($order->status === 'credit_hold')
                                <div class="border-top pt-3 mt-3">
                                    <button type="button" class="btn btn-danger btn-block font-weight-bold py-2 shadow-sm" data-toggle="modal" data-target="#releaseCreditModal">
                                        <i class="fas fa-unlock-alt mr-1"></i> Release Credit Hold
                                    </button>
                                </div>
                            @endif

                            @can('Manage Inventory')
                            @if(strtolower((string) $order->status) === 'approved')
                                <div class="border-top pt-4 mt-3">
                                    <a href="{{ route('admin.issues.create', ['order_id' => $order->id]) }}" class="btn btn-success btn-lg btn-block shadow-sm py-3 font-weight-bold">
                                        <i class="fas fa-box-open mr-2"></i> Create Stock Issue
                                    </a>
                                    <p class="text-center text-muted small mt-2 mb-0">Import order items to issue and confirm stock delivery.</p>
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
                    <form action="{{ route('admin.orders.release-credit', $order->id) }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="alert alert-warning mb-3 small">
                                <strong>Warning:</strong> Releasing credit hold will bypass the customer's credit limit rule for Order #{{ $order->order_no }}.
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
