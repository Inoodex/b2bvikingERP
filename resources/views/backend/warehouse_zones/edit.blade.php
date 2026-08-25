@extends('backend.layouts.master')
@section('title', 'Edit Warehouse Zone')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Edit Warehouse Zone</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.warehouse-zones.update', $warehouseZone->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Outlet <span class="text-danger">*</span></label>
                    <select name="outlet_id" class="form-select" required>
                        <option value="">Select Outlet</option>
                        @foreach($outlets as $outlet)
                            <option value="{{ $outlet->id }}" {{ $warehouseZone->outlet_id == $outlet->id ? 'selected' : '' }}>{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Zone Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ $warehouseZone->name }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="active" {{ $warehouseZone->type == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="quarantine" {{ $warehouseZone->type == 'quarantine' ? 'selected' : '' }}>Quarantine (QC)</option>
                        <option value="scrap" {{ $warehouseZone->type == 'scrap' ? 'selected' : '' }}>Scrap/Damaged</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option value="1" {{ $warehouseZone->status == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $warehouseZone->status == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Update Zone</button>
                    <a href="{{ route('admin.warehouse-zones.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
