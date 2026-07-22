@extends('backend.layouts.master')

@section('title', 'Edit Outlet / Warehouse')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Outlet & Warehouse</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit Outlet / Warehouse</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.master.outlets.index') }}" class="btn btn-primary">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.master.outlets.update', $outlet->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Outlet / Warehouse Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $outlet->name) }}" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Outlet Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" value="{{ old('code', $outlet->code) }}" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-control" required>
                                        <option value="warehouse" {{ $outlet->type === 'warehouse' ? 'selected' : '' }}>Central Warehouse</option>
                                        <option value="retail" {{ $outlet->type === 'retail' ? 'selected' : '' }}>Retail Store</option>
                                        <option value="wholesale" {{ $outlet->type === 'wholesale' ? 'selected' : '' }}>Wholesale Hub</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Belongs to Company</label>
                                    <select name="company_id" class="form-control select2">
                                        <option value="">Global / All Sister Concerns</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ $outlet->company_id == $company->id ? 'selected' : '' }}>{{ $company->name }} ({{ $company->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Outlet / Warehouse Manager</label>
                                    <select name="manager_id" class="form-control select2">
                                        <option value="">Select Manager</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ $outlet->manager_id == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Phone Number</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $outlet->phone) }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Email Address</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $outlet->email) }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="inputState">Status</label>
                                    <select id="inputState" name="status" class="form-control">
                                        <option value="1" {{ $outlet->status ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ !$outlet->status ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Address</label>
                                    <input type="text" name="address" class="form-control" value="{{ old('address', $outlet->address) }}">
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary px-4">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
