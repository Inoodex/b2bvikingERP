@extends('backend.layouts.master')

@section('title', 'Shipment Details & Tracking Timeline')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.shipments.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Shipment Details: <code>{{ $shipment->bl_awb_no ?? 'SHIP-'.$shipment->id }}</code></h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.shipments.index') }}">Shipments</a></div>
            <div class="breadcrumb-item">Shipment Details</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <!-- Left Info Panel -->
            <div class="col-lg-4 col-md-5">
                <div class="card card-primary shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-ship text-primary mr-2"></i> Logistics Summary</h4>
                        @if($shipment->status !== 'cancelled' && $shipment->goodsReceiptsCount() == 0)
                            <a href="{{ route('admin.shipments.edit', $shipment->id) }}" class="btn btn-primary btn-sm font-weight-bold">
                                <i class="fas fa-edit mr-1"></i> Edit Logistics
                            </a>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-md table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="font-weight-bold text-muted" width="40%">Status:</td>
                                    <td>{!! $shipment->status_badge !!}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-muted">PO Reference:</td>
                                    <td>
                                        <a href="{{ route('admin.purchase-orders.show', $shipment->purchase_id) }}" class="font-weight-bold text-primary" target="_blank">
                                            {{ $shipment->purchase?->po_no ?? 'PO #'.$shipment->purchase_id }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-muted">Supplier:</td>
                                    <td><strong class="text-dark">{{ $shipment->purchase?->vendor?->name ?? $shipment->purchase?->vendor?->shop_name ?? 'N/A' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-muted">Vessel / Flight:</td>
                                    <td>{{ $shipment->vessel_or_flight ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-muted">Container No:</td>
                                    <td><code class="text-dark font-weight-bold">{{ $shipment->container_no ?? 'N/A' }}</code></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-muted">BL / AWB No:</td>
                                    <td><code class="text-danger font-weight-bold">{{ $shipment->bl_awb_no ?? 'N/A' }}</code></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-muted">Loading Port:</td>
                                    <td>{{ $shipment->port_of_loading ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-muted">Discharge Port:</td>
                                    <td>{{ $shipment->port_of_discharge ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-muted">ETD:</td>
                                    <td>{{ $shipment->etd ? $shipment->etd->format('d M Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-muted">ETA:</td>
                                    <td>{{ $shipment->eta ? $shipment->eta->format('d M Y') : 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>

                        @if($shipment->document_path)
                            <div class="p-3 border-top bg-light">
                                <a href="{{ asset('storage/' . $shipment->document_path) }}" target="_blank" class="btn btn-outline-primary btn-block font-weight-bold">
                                    <i class="fas fa-file-download mr-1"></i> Download BL / Packing List
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card card-warning shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h4 class="text-dark font-weight-bold"><i class="fas fa-sync-alt text-warning mr-2"></i> Update Shipment Status</h4>
                    </div>
                    <div class="card-body">
                        @if($shipment->status === 'cancelled')
                            <div class="alert alert-danger mb-0 font-weight-bold">
                                <i class="fas fa-lock mr-1"></i> Shipment Permanently Cancelled. Status cannot be modified.
                            </div>
                        @elseif($shipment->goodsReceiptsCount() > 0)
                            <div class="alert alert-success mb-0 font-weight-bold">
                                <i class="fas fa-check-circle mr-1"></i> Goods Fully Received in Warehouse (GRN Completed). Shipment status is locked.
                            </div>
                        @else
                            <form action="{{ route('admin.shipments.update-status', $shipment->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="form-group mb-3">
                                    <label for="status" class="font-weight-bold">Current Logistics Milestone</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="in_transit" {{ $shipment->status == 'in_transit' ? 'selected' : '' }}>🚢 In Transit (Vessel Departed)</option>
                                        <option value="arrived" {{ $shipment->status == 'arrived' ? 'selected' : '' }}>⚓ Arrived at Port</option>
                                        <option value="cleared" {{ $shipment->status == 'cleared' ? 'selected' : '' }}>✅ Customs Cleared</option>
                                        <option value="cancelled" {{ $shipment->status == 'cancelled' ? 'selected' : '' }}>❌ Cancelled (Shipment Cancelled)</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-warning btn-block font-weight-bold"><i class="fas fa-save mr-1"></i> Update Status</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Items Panel -->
            <div class="col-lg-8 col-md-7">
                <div class="card card-primary shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h4 class="text-dark font-weight-bold"><i class="fas fa-boxes text-primary mr-2"></i> Purchased Items in Shipment</h4>
                        @if($shipment->status == 'cleared')
                            <a href="{{ route('admin.goods-receipts.create', ['purchase_id' => $shipment->purchase_id]) }}" class="btn btn-success font-weight-bold btn-sm">
                                <i class="fas fa-dolly mr-1"></i> Receive Goods (Create GRN)
                            </a>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center" width="5%">#</th>
                                        <th width="40%">Product Description</th>
                                        <th width="15%">Variant</th>
                                        <th class="text-right" width="15%">Ordered Qty</th>
                                        <th class="text-right" width="15%">Unit Price</th>
                                        <th class="text-right" width="15%">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                     @php $currencySymbol = $shipment->purchase?->vendor?->currency?->symbol ?? $shipment->purchase?->currency?->symbol ?? 'kr.'; @endphp
                                    @forelse($shipment->purchase?->items ?? [] as $index => $item)
                                        @php
                                            $lineTotal = $item->total ?? ($item->qty * $item->unit_cost);
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <strong class="text-dark">{{ $item->product?->name ?? 'N/A' }}</strong>
                                            </td>
                                            <td>{{ $item->variant?->name ?? 'N/A' }}</td>
                                            <td class="text-right font-weight-bold">{{ number_format($item->qty, 2) }}</td>
                                            <td class="text-right">{{ $currencySymbol }} {{ number_format($item->unit_cost, 2) }}</td>
                                            <td class="text-right font-weight-bold text-dark">{{ $currencySymbol }} {{ number_format($lineTotal, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No line items found for this purchase order.</td>
                                        </tr>
                                    @endforelse
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
