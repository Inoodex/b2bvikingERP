@extends('backend.layouts.master')
@section('title', 'Quotation Details - ' . ($quotation->vendor->shop_name ?? 'Vendor'))

@push('css')
<style>
    .vendor-profile-hero {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        padding: 28px;
    }
    .vendor-avatar-icon {
        width: 60px;
        height: 60px;
        border-radius: 14px;
        background: linear-gradient(135deg, #6777ef 0%, #3f4ecc 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(103,119,239,0.3);
    }
    .hero-stat-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px 20px;
    }
    .info-pill {
        background: #f1f5f9;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 13px;
    }
    .product-thumb-square {
        width: 44px;
        height: 44px;
        border-radius: 6px;
        object-fit: cover;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
    }
    .table-quote-items th {
        background-color: #f8fafc !important;
        color: #34395e !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 12px 16px !important;
    }
    .table-quote-items td {
        padding: 14px 16px !important;
        vertical-align: middle !important;
    }
</style>
@endpush

@section('content')
    <section class="section">
        <!-- Native Stisla Page Header -->
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.rfqs.show', $rfq->id) }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Vendor Quotation Details</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.rfqs.index') }}">Procurement</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.rfqs.show', $rfq->id) }}">RFQ Details</a></div>
                <div class="breadcrumb-item">Quote Details</div>
            </div>
        </div>

        <div class="section-body">
            
            <!-- Ultra-Premium Vendor Profile Hero Banner -->
            <div class="vendor-profile-hero mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="vendor-avatar-icon mr-3">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h3 class="text-dark font-weight-bold mb-0 mr-2" style="line-height: 1.2;">
                                    {{ $quotation->vendor->shop_name ?? 'N/A' }}
                                </h3>
                                <span class="badge badge-success px-3 py-1 font-weight-normal" style="font-size: 12px;">
                                    <i class="fas fa-check-circle mr-1"></i> Verified Supplier Quote
                                </span>
                            </div>
                            <p class="text-muted mb-0 small">
                                <i class="fas fa-clock text-warning mr-1"></i> Submitted on {{ $quotation->created_at ? $quotation->created_at->format('d M, Y \a\t H:i') : 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <!-- Total Value Hero Display -->
                    <div class="hero-stat-card text-right">
                        <small class="text-muted font-weight-bold text-uppercase d-block" style="font-size: 10px; letter-spacing: 0.5px;">Offered Quote Amount</small>
                        <span class="h2 font-weight-bold text-success mb-0">
                            {{ $quotation->currency->symbol ?? '' }}{{ number_format($quotation->items->sum(fn($i) => $i->qty * $i->unit_price), 2) }}
                        </span>
                    </div>
                </div>

                <!-- Contact & Location Grid Pills -->
                <div class="row mt-4 pt-3 border-top">
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <div class="info-pill">
                            <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size: 9px;">Contact Email</small>
                            <span class="font-weight-bold text-dark text-truncate d-block" title="{{ $quotation->vendor->email }}">
                                <i class="fas fa-envelope text-primary mr-1"></i> {{ $quotation->vendor->email ?? 'N/A' }}
                            </span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <div class="info-pill">
                            <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size: 9px;">Phone Number</small>
                            <span class="font-weight-bold text-dark text-truncate d-block">
                                <i class="fas fa-phone text-info mr-1"></i> {{ $quotation->vendor->phone ?? 'N/A' }}
                            </span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <div class="info-pill">
                            <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size: 9px;">Bidding Currency</small>
                            <span class="font-weight-bold text-dark text-truncate d-block">
                                <i class="fas fa-coins text-warning mr-1"></i> {{ $quotation->currency->name ?? 'Base' }} ({{ $quotation->currency->symbol ?? '' }})
                            </span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="info-pill">
                            <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size: 9px;">Offered Items</small>
                            <span class="font-weight-bold text-dark text-truncate d-block">
                                <i class="fas fa-boxes text-success mr-1"></i> {{ $quotation->items->count() }} Line Items
                            </span>
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <div class="info-pill bg-light border">
                            <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size: 9px;">Registered Business Address</small>
                            <span class="font-weight-bold text-dark" style="line-height: 1.4;">
                                <i class="fas fa-map-marker-alt text-danger mr-1"></i> {{ $quotation->vendor->address ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Full-Width Offered Line Items Table -->
            <div class="card card-primary border shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 text-dark font-weight-bold">
                        <i class="fas fa-list-alt text-primary mr-2"></i> Offered Line Items & Unit Prices
                    </h5>
                    <span class="badge badge-primary px-3 py-2"><i class="fas fa-cubes mr-1"></i> {{ $quotation->items->count() }} Line Items</span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive p-3">
                        <table id="quotation-items-table" class="table table-hover table-striped table-quote-items mb-0 w-100">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th class="text-center" style="width: 80px;">Thumbnail</th>
                                    <th style="width: 35%;">Product Name & Specs</th>
                                    <th class="text-center" style="width: 15%;">Unit Type</th>
                                    <th class="text-center" style="width: 15%;">Offered Qty</th>
                                    <th class="text-right" style="width: 15%;">Unit Price ({{ $quotation->currency->symbol ?? '' }})</th>
                                    <th class="text-right" style="width: 15%;">Subtotal ({{ $quotation->currency->symbol ?? '' }})</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotation->items as $index => $item)
                                    @php
                                        $img = optional($item->product)->thumb_image;
                                        $hasImage = $img && file_exists(public_path($img));
                                    @endphp
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                        <td class="text-center">
                                            @if($hasImage)
                                                <img src="{{ asset($img) }}" loading="lazy" alt="product" class="product-thumb-square shadow-sm">
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
                                        <td class="text-center font-weight-bold text-dark">
                                            {{ number_format($item->qty, 2) }}
                                        </td>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td class="text-right font-weight-bold text-primary">
                                            {{ number_format($item->qty * $item->unit_price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <th colspan="6" class="text-right font-weight-bold text-dark h6 mb-0 py-3">Total Quote Amount:</th>
                                    <th class="text-right text-success font-weight-bold h5 mb-0 py-3">
                                        {{ $quotation->currency->symbol ?? '' }}{{ number_format($quotation->items->sum(fn($i) => $i->qty * $i->unit_price), 2) }}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
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
            $('#quotation-items-table').DataTable({
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "language": {
                    "search": "<i class='fas fa-search'></i> Search Items:",
                    "lengthMenu": "Show _MENU_ items"
                }
            });
        }
    });
</script>
@endpush
