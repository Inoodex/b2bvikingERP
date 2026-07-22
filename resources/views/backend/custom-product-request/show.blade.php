@extends('backend.layouts.master')
@section('title', 'Custom Product Request Details')

@push('css')
<style>
    .status-pill {
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d4edda; color: #155724; }
    .status-rejected { background: #f8d7da; color: #721c24; }
    .info-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #6c757d;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .info-value {
        font-size: 15px;
        color: #1f2937;
        font-weight: 600;
    }
    .request-image {
        width: 160px;
        height: 120px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
    }
    .avatar-circle {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #6777ef;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 20px;
    }
</style>
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.custom-product-requests.index') }}" class="btn btn-icon">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            <h1>Custom Request</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.custom-product-requests.index') }}">Custom Requests</a></div>
                <div class="breadcrumb-item active">Details</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                            <div>
                                <h4 class="mb-1">Request #{{ $customProductRequest->request_no }}</h4>
                                <div class="text-muted small">Submitted on {{ $customProductRequest->created_at->format('d M Y, h:i A') }}</div>
                            </div>
                            @if($customProductRequest->status == 'pending')
                                <span class="status-pill status-pending"><i class="fas fa-clock mr-1"></i> Pending</span>
                            @elseif($customProductRequest->status == 'approved')
                                <span class="status-pill status-approved"><i class="fas fa-check mr-1"></i> Approved</span>
                            @else
                                <span class="status-pill status-rejected"><i class="fas fa-times mr-1"></i> Rejected</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="info-label">Product Name</div>
                                    <div class="info-value">{{ $customProductRequest->product_name ?? 'Not specified' }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="info-label">Quantity Needed</div>
                                    <div class="info-value">{{ $customProductRequest->quantity_needed }} units</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="info-label">Product Description</div>
                                <div class="info-value font-weight-normal text-muted" style="font-size: 14px;">
                                    {{ $customProductRequest->product_description }}
                                </div>
                            </div>

                            @if($customProductRequest->expected_price)
                                <div class="mb-3">
                                    <div class="info-label">Expected Price (per unit)</div>
                                    <div class="info-value">{{ $settings->currency_icon }}{{ number_format($customProductRequest->expected_price, 2) }}</div>
                                </div>
                            @endif

                            @php
                                $exampleImages = $customProductRequest->example_image ?? [];
                                if (is_string($exampleImages)) {
                                    $exampleImages = [$exampleImages];
                                }
                            @endphp
                            @if(!empty($exampleImages))
                                <div class="mb-3">
                                    <div class="info-label">Example Photos</div>
                                    <div class="d-flex flex-wrap" style="gap: 12px;">
                                        @foreach($exampleImages as $index => $image)
                                            @if($customProductRequest->resolveExampleImagePath($index))
                                                <img src="{{ route('admin.custom-product-requests.images.show', [$customProductRequest->id, $index]) }}" alt="Product Image" class="request-image">
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h4 class="mb-0">Requested By</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center" style="gap: 12px;">
                                <div class="avatar-circle">{{ substr($customProductRequest->user->name, 0, 1) }}</div>
                                <div>
                                    <div class="font-weight-bold">{{ $customProductRequest->user->name }}</div>
                                    <div class="text-muted small">{{ $customProductRequest->user->outlet_name ?? 'No Outlet' }}</div>
                                </div>
                            </div>
                            <hr>
                            <div class="info-label">Request Date</div>
                            <div class="info-value">{{ $customProductRequest->created_at->format('d M Y') }}</div>
                        </div>
                    </div>

                    @if(Auth::user()->hasRole('Admin'))
                        <div class="card mb-3">
                            <div class="card-header">
                                <h4 class="mb-0">Update Status</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.custom-product-requests.update-status', $customProductRequest->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="mb-3">
                                        <select name="status" class="form-control" required>
                                            <option value="pending" {{ $customProductRequest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ $customProductRequest->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="rejected" {{ $customProductRequest->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <textarea name="admin_note" class="form-control" rows="3" placeholder="Add a note...">{{ $customProductRequest->admin_note }}</textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-save mr-1"></i> Update Status
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($customProductRequest->admin_note && !Auth::user()->can('Manage Custom Product Requests'))
                        <div class="card">
                            <div class="card-header">
                                <h4 class="mb-0">Admin Note</h4>
                            </div>
                            <div class="card-body">
                                <p class="mb-0 text-muted">{{ $customProductRequest->admin_note }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
