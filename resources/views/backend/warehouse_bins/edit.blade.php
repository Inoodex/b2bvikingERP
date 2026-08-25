@extends('backend.layouts.master')
@section('title', 'Edit Warehouse Bin')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Edit Warehouse Bin</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.warehouse-bins.update', $warehouseBin->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Warehouse Zone <span class="text-danger">*</span></label>
                    <select name="zone_id" class="form-select" required>
                        <option value="">Select Zone</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}" {{ $warehouseBin->zone_id == $zone->id ? 'selected' : '' }}>{{ $zone->name }} ({{ $zone->outlet->name ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Bin Name / Location Code <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ $warehouseBin->name }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option value="1" {{ $warehouseBin->status == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $warehouseBin->status == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Update Bin</button>
                    <a href="{{ route('admin.warehouse-bins.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
