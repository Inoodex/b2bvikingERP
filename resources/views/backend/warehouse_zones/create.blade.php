@extends('backend.layouts.master')
@section('title', 'Create Warehouse Zone')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Create Warehouse Zone</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.warehouse-zones.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Outlet <span class="text-danger">*</span></label>
                    <select name="outlet_id" class="form-select" required>
                        <option value="">Select Outlet</option>
                        @foreach($outlets as $outlet)
                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Zone Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g., Raw Materials Zone">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="quarantine">Quarantine (QC)</option>
                        <option value="scrap">Scrap/Damaged</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Save Zone</button>
                    <a href="{{ route('admin.warehouse-zones.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
