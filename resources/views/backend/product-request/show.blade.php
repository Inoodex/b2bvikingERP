@extends('backend.layouts.master')

@section('title', 'Product Request Details')

@section('content')
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.product-requests.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Request #{{ $productRequest->request_no }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.product-requests.index') }}">Product Requests</a></div>
                <div class="breadcrumb-item">Details</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-lg-8">
                    {{-- Items Card --}}
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-list mr-2"></i>Itemized List</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.product-requests.pi-invoice', $productRequest->id) }}" class="btn btn-success" target="_blank"><i class="fas fa-file-signature mr-1"></i> PI Invoice</a>
                                <a href="{{ route('admin.product-requests.view-invoice', $productRequest->id) }}" class="btn btn-warning" target="_blank"><i class="fas fa-file-invoice mr-1"></i> View Invoice</a>
                                <a href="{{ route('admin.product-requests.download-invoice', $productRequest->id) }}" class="btn btn-info ml-2"><i class="fas fa-download mr-1"></i> Download PDF</a>
                                <a href="{{ route('admin.product-requests.download-customer-invoice', $productRequest->id) }}" class="btn btn-dark ml-2"><i class="fas fa-file-invoice mr-1"></i> Customer Invoice</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center" width="5%">#</th>
                                            <th class="text-center" width="10%">Image</th>
                                            <th>Product Details</th>
                                            @can('Manage Product Requests')
                                                <th class="text-center">Shelve No</th>
                                            @endcan
                                            <th class="text-center">Current Stock</th>
                                            <th class="text-center" width="10%">Qty</th>
                                            <th class="text-right">Outlet Price</th>
                                            <th class="text-right">Sell Price</th>
                                            <th class="text-right">Total Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($productRequest->items as $index => $item)
                                            <tr>
                                                <td class="text-center font-weight-bold">{{ $index + 1 }}</td>
                                                <td class="text-center">
                                                    @if($item->product && $item->product->thumb_image)
                                                        <img src="{{ asset('storage/'.$item->product->thumb_image) }}" alt="{{ $item->product->name }}" width="45" class="rounded shadow-sm border">
                                                    @else
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width: 45px; height: 45px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    @if($item->product)
                                                        <div class="font-weight-600 text-dark">{{ $item->product->name }}</div>
                                                        @if($item->variant)
                                                            <div class="mt-1">
                                                                <span class="badge badge-primary py-0 px-2" style="font-size: 10px;">
                                                                    {{ $item->variant->name }}
                                                                    @if($item->variant->color || $item->variant->size)
                                                                        ({{ $item->variant->color->name ?? '' }}{{ $item->variant->color && $item->variant->size ? ' / ' : '' }}{{ $item->variant->size->name ?? '' }})
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <span class="text-danger">Product #{{ $item->product_id }} (Deleted)</span>
                                                    @endif
                                                </td>
                                                @can('Manage Product Requests')
                                                    <td class="text-center align-middle">
                                                        <span class="badge badge-light border">{{ $item->product ? ($item->product->self_number ?? '-') : '-' }}</span>
                                                    </td>
                                                @endcan
                                                <td class="text-center align-middle">
                                                    <span class="badge badge-info px-3">{{ $item->current_stock ?? 0 }}</span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="font-weight-bold h6 mb-0">{{ $item->qty }}</span>
                                                </td>
                                                <td class="text-right align-middle">
                                                    <div class="font-weight-bold">{!! formatConverted($item->unit_price) !!}</div>
                                                </td>
                                                <td class="text-right align-middle">
                                                    @php
                                                        $sellPrice = 0;
                                                        if($item->variant) {
                                                            $sellPrice = ($item->variant->price > 0) ? $item->variant->price : ($item->product->price ?? 0);
                                                        } else {
                                                            $sellPrice = $item->product->price ?? 0;
                                                        }
                                                    @endphp
                                                    <div class="font-weight-bold">{!! formatConverted($sellPrice) !!}</div>
                                                </td>
                                                <td class="text-right align-middle">
                                                    <div class="font-weight-bold text-primary">{!! formatConverted($item->subtotal) !!}</div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <i class="fas fa-box-open fa-3x text-muted mb-3 d-block"></i>
                                                    <div class="h5 text-muted">No items found in this request.</div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-whitesmoke">
                                        <tr>
                                            <td colspan="{{ Auth::user()->can('Manage Product Requests') ? '8' : '7' }}" class="text-right font-weight-bold text-muted text-uppercase small" style="vertical-align: middle;">Total Request Amount</td>
                                            <td class="text-right font-weight-bold h6 text-primary mb-0" style="vertical-align: middle;">
                                                {!! formatConverted($productRequest->total_amount) !!}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    @can('Manage Product Requests')
                        @include('backend.pi._editor', [
                            'title' => 'Manual PI / CTN Info',
                            'subtitle' => 'Save request-wise carton details here before sending or printing the PI invoice.',
                            'formAction' => route('admin.product-requests.pi-info.save', $productRequest->id),
                            'piInvoiceUrl' => route('admin.product-requests.pi-invoice', $productRequest->id),
                            'items' => $productRequest->items,
                            'piInfo' => $piInfo,
                            'piTotals' => $piTotals,
                        ])
                    @else
                        <div class="card shadow-sm border-0">
                            <div class="card-body d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                                <div>
                                    <h4 class="mb-1"><i class="fas fa-file-signature text-primary mr-2"></i>PI Information</h4>
                                    <div class="text-muted small">Your request PI can be viewed once the admin finishes the carton details.</div>
                                </div>
                                <a href="{{ route('admin.product-requests.pi-invoice', $productRequest->id) }}" class="btn btn-success" target="_blank">
                                    <i class="fas fa-external-link-alt mr-1"></i> Open PI Invoice
                                </a>
                            </div>
                        </div>
                    @endcan

                    {{-- Notes Card --}}
                    @if($productRequest->note)
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-sticky-note mr-2"></i>Requester Note</h4>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 bg-light p-3 rounded text-muted font-italic">{{ $productRequest->note }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Payment History (Admin Only) --}}
                    {{-- @role('Admin') --}}
                    @if($productRequest->order)
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h4><i class="fas fa-history mr-2"></i>Payment History</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Method</th>
                                            <th>Transaction ID</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($productRequest->order->payments as $payment)
                                            <tr>
                                                <td>{{ $payment->created_at->format('d M, Y h:i A') }}</td>
                                                <td><span class="badge badge-info">{{ strtoupper($payment->payment_method) }}</span></td>
                                                <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
                                                <td class="text-right font-weight-bold">{!! formatConverted($payment->amount) !!}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">No payments recorded yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($productRequest->order->payments->count() > 0)
                                        <tfoot class="bg-whitesmoke">
                                            <tr>
                                                <td colspan="3" class="text-right font-weight-bold text-uppercase small">Total Paid</td>
                                                <td class="text-right font-weight-bold text-success h6 mb-0">{!! formatConverted($productRequest->order->paid_amount) !!}</td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                    {{-- @endrole --}}
                </div>

                <div class="col-12 col-lg-4">
                    {{-- Status Card --}}
                    <div class="card card-statistic-1 mb-3">
                         <div class="card-icon bg-primary">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Current Status</h4>
                            </div>
                             <div class="card-body">
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'shipped' => 'primary',
                                        'completed' => 'success'
                                    ];
                                    $statusColor = $statusColors[$productRequest->status] ?? 'secondary';
                                @endphp
                                <div class="text-{{ $statusColor }} text-uppercase">{{ $productRequest->status }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Requester Info --}}
                    <div class="card mb-3">
                        <div class="card-header border-bottom">
                            <h4>Requester Profile</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4">
                                <div class="avatar-item mr-3">
                                    <img alt="image" src="{{ $productRequest->user->image ? asset($productRequest->user->image) : 'https://ui-avatars.com/api/?name='.urlencode($productRequest->user->name).'&background=e3eaef&color=3c8dbc' }}" class="rounded-circle shadow-sm" width="60">
                                </div>
                                <div>
                                    <div class="font-weight-bold text-dark h6 mb-0">{{ $productRequest->user->name }}</div>
                                </div>
                            </div>
                            
                            <dl class="row text-small mb-0">
                                <dt class="col-sm-5 text-muted font-weight-normal">{{ $productRequest->user->hasRole('Outlet User') ? 'Outlet Name' : 'Shop Name' }}:</dt>
                                <dd class="col-sm-7 font-weight-bold">{{ $productRequest->user->outlet_name ?? 'N/A' }}</dd>

                                <dt class="col-sm-5 text-muted font-weight-normal">Email:</dt>
                                <dd class="col-sm-7"><a href="mailto:{{ $productRequest->user->email }}">{{ $productRequest->user->email }}</a></dd>

                                <dt class="col-sm-5 text-muted font-weight-normal">Phone:</dt>
                                <dd class="col-sm-7">{{ $productRequest->user->phone ?? 'N/A' }}</dd>

                                <dt class="col-sm-5 text-muted font-weight-normal">Address:</dt>
                                <dd class="col-sm-7 text-muted">{{ $productRequest->user->address ?? 'No address provided' }}</dd>
                                
                                <dt class="col-sm-5 text-muted font-weight-normal">Required Days:</dt>
                                <dd class="col-sm-7">
                                    @if($productRequest->required_days)
                                        @if($productRequest->required_days <= 3)
                                            <span class="badge badge-danger">{{ $productRequest->required_days }} days</span>
                                        @elseif($productRequest->required_days <= 7)
                                            <span class="badge badge-warning">{{ $productRequest->required_days }} days</span>
                                        @else
                                            <span class="badge badge-success">{{ $productRequest->required_days }} days</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>

                    {{-- Payment Summary (Admin Only) --}}
                    @role('Admin')
                    @if($productRequest->order)
                    <div class="card card-success mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="fas fa-money-bill-wave mr-2"></i>Payment Summary</h4>
                            @if($productRequest->order->due_amount > 0)
                                <a href="{{ route('admin.accounts.record-payment', ['order_no' => $productRequest->order->order_no]) }}" class="btn btn-sm btn-outline-white">
                                    <i class="fas fa-plus mr-1"></i> Record Payment
                                </a>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 border-right">
                                    <div class="text-muted small text-uppercase font-weight-bold">Paid</div>
                                    <div class="h5 font-weight-bold text-success mb-0">{!! formatConverted($productRequest->order->paid_amount) !!}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small text-uppercase font-weight-bold">Due</div>
                                    <div class="h5 font-weight-bold text-danger mb-0">{!! formatConverted($productRequest->order->due_amount) !!}</div>
                                </div>
                            </div>
                            
                            @if($productRequest->order->due_amount <= 0 && $productRequest->order->total_amount > 0)
                                <div class="alert alert-success text-center py-2 mb-0 mt-3">
                                    <i class="fas fa-check-circle mr-1"></i> Full Paid
                                </div>
                            @elseif($productRequest->order->due_amount > 0)
                                <div class="text-center mt-3">
                                    <span class="badge badge-warning">Partial Payment Pending</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endrole

                    {{-- Admin Actions --}}

                    <div class="card card-warning">
                        <div class="card-header">
                            <h4><i class="fas fa-user-cog mr-2"></i>Actions</h4>
                        </div>
                        <div class="card-body">
                            @php
                                $canApprovePr = (new \App\Services\ApprovalService())->canUserApproveCurrentStep($productRequest);
                                $isPrApproved = ($productRequest->status === 'approved' || $productRequest->status === 'completed');
                                $pendingPrApproval = $productRequest->approvals->where('status', 'pending')->first();
                                $pendingPrRoleOrUser = $pendingPrApproval->step->approverRole->name ?? $pendingPrApproval->step->approverUser->name ?? 'Approver';
                                $pendingPrStepName = $pendingPrApproval->step->step_name ?? 'Step 1';
                            @endphp

                            @if($isPrApproved)
                                <div class="alert alert-success font-weight-bold mb-3"><i class="fas fa-check-circle mr-1"></i> Requisition Approved</div>
                            @elseif($canApprovePr)
                                <form action="{{ route('admin.product-requests.update-status', $productRequest->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold small text-muted text-uppercase">Approve Requisition</label>
                                        <select name="status" class="form-control select2">
                                            <option value="pending" {{ $productRequest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved">Approve (Enable Stock Issue)</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="font-weight-bold small text-muted text-uppercase">Note</label>
                                        <textarea name="admin_note" class="form-control" style="height: 80px;" placeholder="Internal tracking notes...">{{ $productRequest->admin_note }}</textarea>
                                    </div>

                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary shadow-sm px-4">
                                            Update Request Status
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="alert alert-warning font-weight-bold mb-3">
                                    <i class="fas fa-clock mr-1"></i> ⏳ Waiting for Requisition Approval: {{ $pendingPrStepName }} ({{ $pendingPrRoleOrUser }})
                                </div>
                                <p class="text-muted small">You are not authorized to approve this requisition step.</p>
                            @endif

                                @if($productRequest->status == 'approved')
                                    <div class="border-top pt-4 mt-2">
                                        <a href="{{ route('admin.stock-transfers.create') }}" class="btn btn-success btn-lg btn-block shadow-sm py-3 font-weight-bold">
                                            <i class="fas fa-truck-moving mr-2"></i> Create Stock Transfer
                                        </a>
                                        <p class="text-center text-muted small mt-2 mb-0">Initiate stock transfer for this approved requisition.</p>
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-light border">
                                    <h6 class="alert-heading text-primary font-weight-bold mb-1">Response:</h6>
                                    <p class="mb-0 text-muted small">
                                        {{ $productRequest->admin_note ?? 'No admin notes available.' }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
