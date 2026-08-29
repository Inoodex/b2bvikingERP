@extends('backend.layouts.master')
@section('title', 'Create Warehouse Zone')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.warehouse-zones.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Create Warehouse Zone</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.warehouse-zones.index') }}">Warehouse Zones</a></div>
            <div class="breadcrumb-item">Create</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Create Warehouse Zone</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.warehouse-zones.index') }}" class="btn btn-primary">
                                <i class="fas fa-list mr-1"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.warehouse-zones.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="outlet_id">Warehouse / Outlet <span class="text-danger">*</span></label>
                                    <select id="outlet_id" name="outlet_id" class="form-control select2 @error('outlet_id') is-invalid @enderror" required>
                                        <option value="">Select Warehouse / Outlet</option>
                                        @foreach($outlets as $outlet)
                                            <option value="{{ $outlet->id }}" {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                                {{ $outlet->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('outlet_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="name">Zone Name <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                                        placeholder="e.g. Raw Materials Zone / Main Storage" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="type">Zone Type <span class="text-danger">*</span></label>
                                    <select id="type" name="type" class="form-control @error('type') is-invalid @enderror" required>
                                        <option value="active" {{ old('type') == 'active' ? 'selected' : '' }}>Active Storage</option>
                                        <option value="quarantine" {{ old('type') == 'quarantine' ? 'selected' : '' }}>Quarantine (QC Hold)</option>
                                        <option value="scrap" {{ old('type') == 'scrap' ? 'selected' : '' }}>Scrap / Damaged</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="status">Status</label>
                                    <select id="status" class="form-control" name="status">
                                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-right mt-3">
                                <a href="{{ route('admin.warehouse-zones.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">Create Zone</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
