@extends('backend.layouts.master')

@section('title', 'Edit Shipment Logistics')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.shipments.show', $shipment->id) }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Edit Shipment Logistics</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.shipments.index') }}">Shipments</a></div>
            <div class="breadcrumb-item">Edit Shipment</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header">
                <h4><i class="fas fa-edit text-primary mr-2"></i> Update Shipment Details: <code>{{ $shipment->bl_awb_no ?? 'SHIP-'.$shipment->id }}</code></h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.shipments.update', $shipment->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Purchase Order (PO Reference)</label>
                            <input type="text" class="form-control" value="{{ $shipment->purchase?->po_no ?? 'PO #'.$shipment->purchase_id }} — {{ $shipment->purchase?->vendor?->name ?? $shipment->purchase?->vendor?->shop_name }}" readonly>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="vessel_or_flight" class="font-weight-bold">Vessel Name / Flight No</label>
                            <input type="text" name="vessel_or_flight" class="form-control" value="{{ old('vessel_or_flight', $shipment->vessel_or_flight) }}" placeholder="e.g. Maersk Mc-Kinney Moller / Flight EK-582">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="container_no" class="font-weight-bold">Container Number</label>
                            <input type="text" name="container_no" class="form-control" value="{{ old('container_no', $shipment->container_no) }}" placeholder="e.g. MSKU9082345 / 40ft High Cube">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="bl_awb_no" class="font-weight-bold">Bill of Lading (BL) / AWB Number</label>
                            <input type="text" name="bl_awb_no" class="form-control" value="{{ old('bl_awb_no', $shipment->bl_awb_no) }}" placeholder="e.g. BL-2026-90412">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="port_of_loading" class="font-weight-bold">Port of Loading</label>
                            <input type="text" name="port_of_loading" class="form-control" value="{{ old('port_of_loading', $shipment->port_of_loading) }}" placeholder="e.g. Port of Ningbo-Zhoushan, China">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="port_of_discharge" class="font-weight-bold">Port of Discharge</label>
                            <input type="text" name="port_of_discharge" class="form-control" value="{{ old('port_of_discharge', $shipment->port_of_discharge) }}" placeholder="e.g. Chattogram Port, Bangladesh">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="etd" class="font-weight-bold">Estimated Time of Departure (ETD)</label>
                            <input type="date" name="etd" class="form-control" value="{{ old('etd', $shipment->etd ? $shipment->etd->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="eta" class="font-weight-bold">Estimated Time of Arrival (ETA)</label>
                            <input type="date" name="eta" class="form-control" value="{{ old('eta', $shipment->eta ? $shipment->eta->format('Y-m-d') : '') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="document" class="font-weight-bold">BL / Packing List File (Upload New to Replace)</label>
                        <input type="file" name="document" class="form-control-file border p-2 rounded">
                        @if($shipment->document_path)
                            <small class="form-text text-muted">
                                Current File: <a href="{{ asset('storage/' . $shipment->document_path) }}" target="_blank"><i class="fas fa-paperclip"></i> View Existing Document</a>
                            </small>
                        @endif
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('admin.shipments.show', $shipment->id) }}" class="btn btn-secondary mr-2">Cancel</a>
                        <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Update Shipment Details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
