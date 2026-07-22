@extends('backend.layouts.master')

@section('title', 'Add Outlet / Warehouse')

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
                        <h4>Create Outlet / Warehouse</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.master.outlets.index') }}" class="btn btn-primary">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.master.outlets.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Outlet / Warehouse Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Central Warehouse Copenhagen, Retail Shop #1" required value="{{ old('name') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Outlet Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" placeholder="WH-CPH, OUT-01" required value="{{ old('code') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-control" required>
                                        <option value="warehouse" selected>Central Warehouse</option>
                                        <option value="retail">Retail Store</option>
                                        <option value="wholesale">Wholesale Hub</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Belongs to Company</label>
                                    <select name="company_id" class="form-control select2">
                                        <option value="">Global / All Sister Concerns</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }} ({{ $company->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Outlet / Warehouse Manager</label>
                                    <select name="manager_id" class="form-control select2">
                                        <option value="">Select Manager</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Phone Number</label>
                                    <input type="text" name="phone" class="form-control" placeholder="+45 99 88 77 66" value="{{ old('phone') }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Email Address</label>
                                    <input type="email" name="email" class="form-control" placeholder="warehouse@b2bviking.com" value="{{ old('email') }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="inputState">Status</label>
                                    <select id="inputState" name="status" class="form-control">
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Address</label>
                                    <input type="text" name="address" class="form-control" placeholder="Location & Street Address" value="{{ old('address') }}">
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary px-4">Create</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
