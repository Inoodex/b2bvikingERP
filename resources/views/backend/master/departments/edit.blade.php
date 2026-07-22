@extends('backend.layouts.master')

@section('title', 'Edit Department')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Department</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit Department</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.master.departments.index') }}" class="btn btn-primary">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.master.departments.update', $department->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Department Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $department->name) }}" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Department Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" value="{{ old('code', $department->code) }}" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Belongs to Company</label>
                                    <select name="company_id" class="form-control select2">
                                        <option value="">Global / All Sister Concerns</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ $department->company_id == $company->id ? 'selected' : '' }}>{{ $company->name }} ({{ $company->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Department Manager</label>
                                    <select name="manager_id" class="form-control select2">
                                        <option value="">Select Manager</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ $department->manager_id == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="inputState">Status</label>
                                    <select id="inputState" name="status" class="form-control">
                                        <option value="1" {{ $department->status ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ !$department->status ? 'selected' : '' }}>Inactive</option>
                                    </select>
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
