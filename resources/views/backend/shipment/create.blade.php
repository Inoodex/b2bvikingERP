@extends('backend.layouts.master')

@section('title', 'Register New Shipment')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.shipments.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Register Inbound Shipment</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.shipments.index') }}">Shipments</a></div>
            <div class="breadcrumb-item">New Shipment</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header">
                <h4><i class="fas fa-ship text-primary mr-2"></i> Shipment Tracking & Logistics Information</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.shipments.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="purchase_id">Select Purchase Order (PO) <span class="text-danger">*</span></label>
                            <select name="purchase_id" id="purchase_id" class="form-control select2" required>
                                <option value="">-- Select Approved PO --</option>
                                @foreach($purchases as $po)
                                    <option value="{{ $po->id }}" {{ $purchaseId == $po->id ? 'selected' : '' }}>
                                        {{ $po->po_no ?? 'PO #'.$po->id }} — {{ $po->vendor?->name }} ({{ strtoupper($po->purchase_type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="vessel_or_flight">Vessel Name / Flight No</label>
                            <input type="text" name="vessel_or_flight" class="form-control" placeholder="e.g. Maersk Mc-Kinney Moller / Flight EK-582">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="container_no">Container Number</label>
                            <input type="text" name="container_no" class="form-control" placeholder="e.g. MSKU9082345 / 40ft High Cube">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="bl_awb_no">Bill of Lading (BL) / AWB Number</label>
                            <input type="text" name="bl_awb_no" class="form-control" placeholder="e.g. BL-2026-90412">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="port_of_loading">Port of Loading</label>
                            <input type="text" name="port_of_loading" class="form-control" placeholder="e.g. Port of Ningbo-Zhoushan, China">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="port_of_discharge">Port of Discharge</label>
                            <input type="text" name="port_of_discharge" class="form-control" placeholder="e.g. Copenhagen Port">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="etd">Estimated Time of Departure (ETD)</label>
                            <input type="date" name="etd" class="form-control">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="eta">Estimated Time of Arrival (ETA)</label>
                            <input type="date" name="eta" class="form-control">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="document">Upload BL / Packing List Document</label>
                            <input type="file" name="document" class="form-control-file">
                            <small class="text-muted">PDF, JPG, PNG up to 5MB</small>
                        </div>
                    </div>

                    <div class="form-group text-right mb-0">
                        <a href="{{ route('admin.shipments.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save mr-1"></i> Register Shipment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
