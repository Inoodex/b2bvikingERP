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
                <h4><i id="header-transport-icon" class="fas fa-ship text-primary mr-2"></i> Inbound Shipment Tracking & Logistics Information</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.shipments.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="purchase_id" class="font-weight-bold">Select Purchase Order (PO) <span class="text-danger">*</span></label>
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
                            <label for="transport_mode" class="font-weight-bold">Transportation Mode <span class="text-danger">*</span></label>
                            <select name="transport_mode" id="transport_mode" class="form-control select2" required>
                                <option value="sea" {{ old('transport_mode', 'sea') == 'sea' ? 'selected' : '' }}>🚢 Sea Freight (Ocean Vessel & Container)</option>
                                <option value="air" {{ old('transport_mode') == 'air' ? 'selected' : '' }}>✈️ Air Cargo (Flight & Air Waybill)</option>
                                <option value="road" {{ old('transport_mode') == 'road' ? 'selected' : '' }}>🚚 Road Freight (Truck / Trailer & CMR)</option>
                                <option value="rail" {{ old('transport_mode') == 'rail' ? 'selected' : '' }}>🚆 Rail Freight (Train / Wagon & CIM)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="carrier_name" id="lbl-carrier" class="font-weight-bold">Shipping Line / Ocean Carrier</label>
                            <input type="text" name="carrier_name" id="carrier_name" class="form-control" value="{{ old('carrier_name') }}" placeholder="e.g. Maersk / MSC / COSCO / Hapag-Lloyd">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="vessel_or_flight" id="lbl-identifier" class="font-weight-bold">Vessel Name & Voyage</label>
                            <input type="text" name="vessel_or_flight" id="vessel_or_flight" class="form-control" value="{{ old('vessel_or_flight') }}" placeholder="e.g. Maersk Mc-Kinney Moller / Voyage 2401W">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="container_no" id="lbl-container" class="font-weight-bold">Container Number (FCL / LCL)</label>
                            <input type="text" name="container_no" id="container_no" class="form-control" value="{{ old('container_no') }}" placeholder="e.g. MSKU9082345 / 40ft High Cube">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="bl_awb_no" id="lbl-document" class="font-weight-bold">Bill of Lading (BL) Number</label>
                            <input type="text" name="bl_awb_no" id="bl_awb_no" class="form-control" value="{{ old('bl_awb_no') }}" placeholder="e.g. BL-2026-90412">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="port_of_loading" id="lbl-origin" class="font-weight-bold">Port of Loading (POL)</label>
                            <input type="text" name="port_of_loading" id="port_of_loading" class="form-control" value="{{ old('port_of_loading') }}" placeholder="e.g. Port of Ningbo-Zhoushan, China">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="port_of_discharge" id="lbl-destination" class="font-weight-bold">Port of Discharge (POD)</label>
                            <input type="text" name="port_of_discharge" id="port_of_discharge" class="form-control" value="{{ old('port_of_discharge') }}" placeholder="e.g. Copenhagen Port, Denmark">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="etd" class="font-weight-bold">Estimated Time of Departure (ETD)</label>
                            <input type="date" name="etd" id="etd" class="form-control" value="{{ old('etd') }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="eta" class="font-weight-bold">Estimated Time of Arrival (ETA)</label>
                            <input type="date" name="eta" id="eta" class="form-control" value="{{ old('eta') }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="document" class="font-weight-bold">Upload Transport Document (BL/AWB/CMR)</label>
                            <input type="file" name="document" id="document" class="form-control-file border p-1 rounded">
                            <small class="text-muted">PDF, JPG, PNG up to 5MB</small>
                        </div>
                    </div>

                    <div class="form-group text-right mb-0 border-top pt-3">
                        <a href="{{ route('admin.shipments.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-lg font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Register Shipment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const modeConfigs = {
        sea: {
            icon: 'fas fa-ship text-primary',
            carrierLabel: 'Shipping Line / Ocean Carrier',
            carrierPlaceholder: 'e.g. Maersk / MSC / COSCO / Hapag-Lloyd',
            identifierLabel: 'Vessel Name & Voyage',
            identifierPlaceholder: 'e.g. Maersk Mc-Kinney Moller / Voyage 2401W',
            containerLabel: 'Container Number (FCL / LCL)',
            containerPlaceholder: 'e.g. MSKU9082345 / 40ft High Cube',
            docLabel: 'Bill of Lading (BL) Number',
            docPlaceholder: 'e.g. BL-2026-90412',
            originLabel: 'Port of Loading (POL)',
            originPlaceholder: 'e.g. Port of Ningbo-Zhoushan, China',
            destLabel: 'Port of Discharge (POD)',
            destPlaceholder: 'e.g. Copenhagen Port, Denmark'
        },
        air: {
            icon: 'fas fa-plane-departure text-info',
            carrierLabel: 'Airline / Air Cargo Carrier',
            carrierPlaceholder: 'e.g. Emirates SkyCargo / Qatar Airways Cargo / Lufthansa Cargo',
            identifierLabel: 'Flight Number',
            identifierPlaceholder: 'e.g. EK-582 / QR-638 / LH-820',
            containerLabel: 'ULD / Container Unit No (Optional)',
            containerPlaceholder: 'e.g. PMC12345EK / AKE90812',
            docLabel: 'Air Waybill (AWB) Number',
            docPlaceholder: 'e.g. 176-12345678',
            originLabel: 'Departure Airport (Origin)',
            originPlaceholder: 'e.g. PVG - Shanghai Pudong, China',
            destLabel: 'Arrival Airport (Destination)',
            destPlaceholder: 'e.g. CPH - Copenhagen Airport, Denmark'
        },
        road: {
            icon: 'fas fa-truck text-warning',
            carrierLabel: 'Logistics / Trucking Company',
            carrierPlaceholder: 'e.g. DHL Freight / DSV Road / DB Schenker',
            identifierLabel: 'Truck License / Trailer Reg Plate',
            identifierPlaceholder: 'e.g. DK-TR-8812 / Trailer #29',
            containerLabel: 'Trailer Box / Container No',
            containerPlaceholder: 'e.g. BOX-40-EU',
            docLabel: 'Consignment Note (CMR) Number',
            docPlaceholder: 'e.g. CMR-2026-9901',
            originLabel: 'Origin Hub / Dispatch Terminal',
            originPlaceholder: 'e.g. Hamburg Freight Hub, Germany',
            destLabel: 'Destination Hub / Delivery Point',
            destPlaceholder: 'e.g. Taastrup Central Depot, Denmark'
        },
        rail: {
            icon: 'fas fa-train text-secondary',
            carrierLabel: 'Rail Freight Operator',
            carrierPlaceholder: 'e.g. DB Cargo / China Railway Express / PKP Cargo',
            identifierLabel: 'Train / Wagon Number',
            identifierPlaceholder: 'e.g. CR-Express 9012 / Wagon #8',
            containerLabel: 'Wagon / Container Unit No',
            containerPlaceholder: 'e.g. WGN-5501 / Container CT-40',
            docLabel: 'Railway Waybill (CIM) Number',
            docPlaceholder: 'e.g. CIM-2026-7734',
            originLabel: 'Origin Rail Terminal / Yard',
            originPlaceholder: 'e.g. Xi\'an International Trade & Logistics Park',
            destLabel: 'Destination Rail Terminal',
            destPlaceholder: 'e.g. Duisburg Intermodal Terminal, Germany'
        }
    };

    function applyModeUI(mode) {
        const config = modeConfigs[mode] || modeConfigs.sea;
        $('#header-transport-icon').attr('class', config.icon + ' mr-2');
        $('#lbl-carrier').text(config.carrierLabel);
        $('#carrier_name').attr('placeholder', config.carrierPlaceholder);
        $('#lbl-identifier').text(config.identifierLabel);
        $('#vessel_or_flight').attr('placeholder', config.identifierPlaceholder);
        $('#lbl-container').text(config.containerLabel);
        $('#container_no').attr('placeholder', config.containerPlaceholder);
        $('#lbl-document').text(config.docLabel);
        $('#bl_awb_no').attr('placeholder', config.docPlaceholder);
        $('#lbl-origin').text(config.originLabel);
        $('#port_of_loading').attr('placeholder', config.originPlaceholder);
        $('#lbl-destination').text(config.destLabel);
        $('#port_of_discharge').attr('placeholder', config.destPlaceholder);
    }

    $('#transport_mode').on('change', function() {
        applyModeUI($(this).val());
    });

    // Initialize on load
    applyModeUI($('#transport_mode').val() || 'sea');
});
</script>
@endpush
