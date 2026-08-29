@extends('backend.layouts.master')
@section('title', 'Edit Warehouse Bin')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.warehouse-bins.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Edit Warehouse Bin</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.warehouse-bins.index') }}">Warehouse Bins</a></div>
            <div class="breadcrumb-item">Edit</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit Warehouse Bin</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.warehouse-bins.show', $warehouseBin->id) }}" target="_blank" class="btn btn-info mr-2">
                                <i class="fas fa-barcode mr-1"></i> Print Barcode
                            </a>
                            <a href="{{ route('admin.warehouse-bins.index') }}" class="btn btn-primary">
                                <i class="fas fa-list mr-1"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.warehouse-bins.update', $warehouseBin->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="zone_id">Warehouse Zone <span class="text-danger">*</span></label>
                                    <select id="zone_id" name="zone_id" class="form-control select2 @error('zone_id') is-invalid @enderror" required>
                                        <option value="">Select Zone</option>
                                        @foreach($zones as $zone)
                                            <option value="{{ $zone->id }}" {{ (old('zone_id', $warehouseBin->zone_id) == $zone->id) ? 'selected' : '' }}>
                                                {{ $zone->name }} ({{ $zone->outlet->name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('zone_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="name">Bin Name / Location Code <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                                        value="{{ old('name', $warehouseBin->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="status">Status</label>
                                    <select id="status" class="form-control" name="status">
                                        <option value="1" {{ old('status', $warehouseBin->status) == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status', $warehouseBin->status) == 0 ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Barcode</label>
                                    <input type="text" class="form-control bg-light font-weight-bold" value="{{ $warehouseBin->barcode }}" readonly>
                                </div>
                            </div>

                            <div class="text-right mt-3">
                                <a href="{{ route('admin.warehouse-bins.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">Update Bin</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
