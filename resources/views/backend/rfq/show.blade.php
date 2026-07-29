@extends('backend.layouts.master')
@section('title', 'RFQ Details - ' . $rfq->rfq_no)

@push('css')
<style>
    .rfq-header-box {
        background: #ffffff;
        border-radius: 8px;
        padding: 20px 24px;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }
    .rfq-stepper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 20px;
    }
    .stepper-step {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #94a3b8;
    }
    .stepper-step.active {
        color: #6777ef;
    }
    .stepper-step.completed {
        color: #47c363;
    }
    .stepper-step .step-icon {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }
    .stepper-step.completed .step-icon {
        background: #47c363;
        color: #ffffff;
    }
    .stepper-step.active .step-icon {
        background: #6777ef;
        color: #ffffff;
    }
    .stepper-line {
        flex: 1;
        height: 2px;
        background: #e2e8f0;
        margin: 0 12px;
    }
    .stepper-line.completed {
        background: #47c363;
    }
    .product-thumb-square {
        width: 42px;
        height: 42px;
        border-radius: 6px;
        object-fit: cover;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
    }
    .table-rfq-items th {
        background-color: #f4f6f9 !important;
        color: #34395e !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        border-bottom: 2px solid #e9ecef !important;
        padding: 12px 16px !important;
    }
    .table-rfq-items td {
        padding: 14px 16px !important;
        vertical-align: middle !important;
    }
    .cs-enterprise-card {
        border-left: 4px solid #6777ef !important;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
</style>
@endpush

@section('content')
    <section class="section">
        <!-- Seamless Stisla Page Header & Action Bar -->
        <div class="section-header d-block p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <div class="section-header-back mr-3">
                        <a href="{{ route('admin.rfqs.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
                    </div>
                    <h1 class="mb-0 text-dark font-weight-bold" style="font-size: 22px;">RFQ Details: {{ $rfq->rfq_no }}</h1>
                </div>
                <div class="section-header-breadcrumb" style="position: relative; top: 0; right: 0;">
                    <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.rfqs.index') }}">Procurement</a></div>
                    <div class="breadcrumb-item">RFQ Details</div>
                </div>
            </div>

            <div class="pt-3 border-top d-flex justify-content-between align-items-center flex-wrap">
                <span class="badge badge-light border text-dark py-2 px-3 font-weight-bold" style="font-size: 12px;">
                    <i class="fas fa-calendar-alt text-warning mr-1"></i> Bidding Deadline: {{ $rfq->due_date ? \Carbon\Carbon::parse($rfq->due_date)->format('d M, Y') : 'N/A' }}
                </span>
                <div class="mt-2 mt-sm-0">
                    <a href="{{ route('admin.rfqs.edit', $rfq->id) }}" class="btn btn-warning btn-sm font-weight-bold mr-1"><i class="fas fa-edit mr-1"></i> Edit RFQ</a>
                    <form action="{{ route('admin.rfqs.send-emails', $rfq->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm font-weight-bold"><i class="fas fa-paper-plane mr-1"></i> Send RFQ Email</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="section-body">
            
            <!-- Workflow Stepper Progress Bar -->
            <div class="rfq-stepper mb-4">
                <div class="stepper-step completed">
                    <div class="step-icon"><i class="fas fa-check"></i></div>
                    <span>1. Created</span>
                </div>
                <div class="stepper-line {{ $rfq->vendors->count() > 0 ? 'completed' : '' }}"></div>

                <div class="stepper-step {{ $rfq->vendors->count() > 0 ? 'completed' : 'active' }}">
                    <div class="step-icon">
                        <i class="fas {{ $rfq->vendors->count() > 0 ? 'fa-check' : 'fa-envelope' }}"></i>
                    </div>
                    <span>2. Vendors Invited</span>
                </div>
                <div class="stepper-line {{ $rfq->quotations->count() > 0 ? 'completed' : '' }}"></div>

                <div class="stepper-step {{ $rfq->quotations->count() > 0 ? 'completed' : ($rfq->vendors->count() > 0 ? 'active' : '') }}">
                    <div class="step-icon">
                        <i class="fas {{ $rfq->quotations->count() > 0 ? 'fa-check' : 'fa-file-invoice-dollar' }}"></i>
                    </div>
                    <span>3. Quotes Received ({{ $rfq->quotations->count() }})</span>
                </div>
                <div class="stepper-line {{ isset($cs) && $cs ? 'completed' : '' }}"></div>

                <div class="stepper-step {{ isset($cs) && $cs ? 'completed' : '' }}">
                    <div class="step-icon">
                        <i class="fas {{ isset($cs) && $cs ? 'fa-check' : 'fa-balance-scale' }}"></i>
                    </div>
                    <span>4. CS Evaluated</span>
                </div>
            </div>

            <!-- Summary KPI Stat Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 mb-0 border">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-cubes"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Requested Items</h4>
                            </div>
                            <div class="card-body">
                                {{ $rfq->items->count() }} Items
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 mb-0 border">
                        <div class="card-icon bg-info">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Invited Suppliers</h4>
                            </div>
                            <div class="card-body">
                                {{ $rfq->vendors->count() }} Vendors
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 mb-0 border">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Quotes Submitted</h4>
                            </div>
                            <div class="card-body">
                                {{ $rfq->quotations->count() }} Quotes
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 mb-0 border">
                        <div class="card-icon {{ $rfq->status === 'open' ? 'bg-success' : 'bg-secondary' }}">
                            <i class="fas fa-flag"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>RFQ Status</h4>
                            </div>
                            <div class="card-body">
                                {{ ucfirst($rfq->status) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comparison Statement (CS Card) -->
            @if(isset($cs) && $cs)
                <div class="card cs-enterprise-card mb-4 border">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom py-3">
                        <h5 class="mb-0 text-dark font-weight-bold">
                            <i class="fas fa-balance-scale text-primary mr-2"></i> Comparison Statement (CS Ref: {{ $cs->cs_no }})
                        </h5>
                        <div>
                            <a href="{{ route('admin.rfqs.cs.pdf.view', ['rfq' => $rfq->id, 'cs' => $cs->id]) }}" target="_blank" class="btn btn-sm btn-danger mr-1"><i class="fas fa-eye mr-1"></i>CS PDF</a>
                            <a href="{{ route('admin.rfqs.cs.pdf', ['rfq' => $rfq->id, 'cs' => $cs->id]) }}" class="btn btn-sm btn-outline-danger mr-1"><i class="fas fa-download mr-1"></i>PDF</a>
                            <a href="{{ route('admin.rfqs.cs.create', $rfq->id) }}" class="btn btn-sm btn-primary mr-1"><i class="fas fa-table mr-1"></i> CS Matrix</a>
                            @if($cs->approval_status === 'approved')
                                <form action="{{ route('admin.purchase-orders.generate-from-cs', $cs->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success font-weight-bold"><i class="fas fa-file-invoice mr-1"></i> Generate PO</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="card-body py-3 px-4">
                        <div class="row align-items-center">
                            <div class="col-lg-5 col-md-5 mb-2 mb-md-0 border-right">
                                <small class="text-muted font-weight-bold text-uppercase d-block" style="font-size: 10px;">Award Strategy & Recommended Winner</small>
                                <div class="mt-1">
                                    @if($cs->recommendedVendor)
                                        <span class="badge badge-success px-3 py-1 font-weight-bold text-truncate d-inline-block mw-100" style="max-width: 100%;" title="Single Vendor: {{ $cs->recommendedVendor->shop_name }}">
                                            <i class="fas fa-trophy mr-1 text-warning"></i> {{ $cs->recommendedVendor->shop_name }}
                                        </span>
                                    @else
                                        <span class="badge badge-info px-3 py-1 font-weight-bold"><i class="fas fa-sitemap mr-1"></i> Split Award (Item-by-Item)</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 mb-2 mb-md-0 border-right">
                                <small class="text-muted font-weight-bold text-uppercase d-block" style="font-size: 10px;">Total Evaluated Value</small>
                                <div class="mt-1 font-weight-bold text-primary h4 mb-0" style="font-size: 20px;">
                                    kr.{{ number_format($cs->total_amount, 2) }}
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3">
                                <small class="text-muted font-weight-bold text-uppercase d-block" style="font-size: 10px;">Approval Status</small>
                                <div class="mt-1">
                                    @if($cs->approval_status === 'approved')
                                        <span class="badge badge-success px-3 py-1 font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Fully Approved</span>
                                    @elseif($cs->approval_status === 'rejected')
                                        <span class="badge badge-danger px-3 py-1 font-weight-bold"><i class="fas fa-times-circle mr-1"></i> Rejected</span>
                                    @else
                                        <span class="badge badge-warning px-3 py-1 text-dark font-weight-bold"><i class="fas fa-clock mr-1"></i> Pending Approval</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Main Tabbed Content Card (Full Width Table Layout) -->
            <div class="card card-primary border shadow-sm">
                <div class="card-header bg-white border-bottom p-0">
                    <ul class="nav nav-tabs px-3" id="rfqMainTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold py-3 px-4" id="tab-items" data-toggle="tab" href="#content-items" role="tab">
                                <i class="fas fa-list-ul text-primary mr-2"></i> Requested Line Items ({{ $rfq->items->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold py-3 px-4" id="tab-vendors" data-toggle="tab" href="#content-vendors" role="tab">
                                <i class="fas fa-store text-info mr-2"></i> Invited Suppliers & Quotes ({{ $rfq->vendors->count() }})
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0">
                    <div class="tab-content" id="rfqMainTabsContent">
                        
                        <!-- TAB 1: Requested Line Items -->
                        <div class="tab-pane fade show active" id="content-items" role="tabpanel">
                            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-boxes text-muted mr-2"></i> Item Specifications & Quantities</h6>
                                @if(!isset($cs) || !$cs)
                                    <a href="{{ route('admin.rfqs.cs.create', $rfq->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-balance-scale mr-1"></i> Generate CS</a>
                                @endif
                            </div>

                            <div class="table-responsive p-3">
                                <table id="rfq-items-table" class="table table-hover table-striped table-rfq-items mb-0 w-100">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 60px;">#</th>
                                            <th class="text-center" style="width: 90px;">Image</th>
                                            <th style="width: 50%;">Product Description</th>
                                            <th class="text-center" style="width: 20%;">Unit Type</th>
                                            <th class="text-center" style="width: 20%;">Requested Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rfq->items as $index => $item)
                                            @php
                                                $img = optional($item->product)->thumb_image;
                                                $hasImage = $img && file_exists(public_path($img));
                                            @endphp
                                            <tr>
                                                <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                                <td class="text-center">
                                                    @if($hasImage)
                                                        <img src="{{ asset($img) }}" alt="product" class="product-thumb-square shadow-sm">
                                                    @else
                                                        <div class="product-thumb-square d-flex align-items-center justify-content-center text-muted mx-auto">
                                                            <i class="fas fa-image text-secondary"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold text-dark h6 mb-1">{{ $item->product->name ?? 'N/A' }}</div>
                                                    @if($item->variant)
                                                        <span class="badge badge-light border text-muted"><i class="fas fa-tag mr-1"></i> Variant: {{ $item->variant->name }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-light border font-weight-bold text-dark px-3 py-2">
                                                        {{ $item->product->unit->name ?? 'Pcs' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-primary font-weight-bold px-3 py-2" style="font-size: 13px;">
                                                        {{ number_format($item->qty) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 2: Invited Suppliers & Quotes -->
                        <div class="tab-pane fade" id="content-vendors" role="tabpanel">
                            <div class="table-responsive p-3">
                                <table id="rfq-vendors-table" class="table table-hover table-striped table-rfq-items mb-0 w-100">
                                    <thead>
                                        <tr>
                                            <th>Supplier / Shop Name</th>
                                            <th>Invitation Sent</th>
                                            <th>Quotation Status</th>
                                            <th class="text-center" style="width: 200px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rfq->vendors as $rv)
                                            @php
                                                $quote = $rfq->quotations->where('vendor_id', $rv->vendor_id)->first();
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold text-dark h6 mb-0">{{ $rv->vendor->shop_name ?? 'N/A' }}</div>
                                                    <small class="text-muted">{{ $rv->vendor->email ?? '' }}</small>
                                                </td>
                                                <td>{{ $rv->invited_at ? $rv->invited_at->format('d M, Y H:i') : 'N/A' }}</td>
                                                <td>
                                                    @if($quote)
                                                        <span class="badge badge-success px-3 py-2"><i class="fas fa-check-circle mr-1"></i> Submitted ({{ $quote->currency->symbol ?? '' }}{{ number_format($quote->items->sum(fn($i) => $i->qty * $i->unit_price), 2) }})</span>
                                                    @else
                                                        <span class="badge badge-warning px-3 py-2 text-dark"><i class="fas fa-clock mr-1"></i> Awaiting Quote</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if(!$quote && $rfq->status === 'open')
                                                        <a href="{{ route('admin.rfqs.quotations.create', ['rfq' => $rfq->id, 'vendor' => $rv->vendor_id]) }}" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i> Submit Quote</a>
                                                    @elseif($quote)
                                                        <a href="{{ route('admin.rfqs.quotations.show', ['rfq' => $rfq->id, 'quotation' => $quote->id]) }}" class="btn btn-sm btn-info shadow-sm">
                                                            <i class="fas fa-external-link-alt mr-1"></i> View Quote Details
                                                        </a>
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
            </div>

        </div>
    </section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#rfq-items-table').DataTable({
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "language": {
                    "search": "<i class='fas fa-search'></i> Search Items:",
                    "lengthMenu": "Show _MENU_ items"
                }
            });
            $('#rfq-vendors-table').DataTable({
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "language": {
                    "search": "<i class='fas fa-search'></i> Search Suppliers:",
                    "lengthMenu": "Show _MENU_ suppliers"
                }
            });
        }
    });
</script>
@endpush
