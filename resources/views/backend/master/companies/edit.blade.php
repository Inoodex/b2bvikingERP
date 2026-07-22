@extends('backend.layouts.master')

@section('title', 'Edit Company')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Company</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit Company</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.master.companies.index') }}" class="btn btn-primary">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.master.companies.update', $company->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Company Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $company->name) }}" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Company Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" value="{{ old('code', $company->code) }}" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Email Address</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $company->email) }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Phone Number</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $company->phone) }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>VAT / Tax Registration Number</label>
                                    <input type="text" name="vat_number" class="form-control" value="{{ old('vat_number', $company->vat_number) }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Operating Currency</label>
                                    <select name="currency_id" class="form-control select2">
                                        <option value="">Select Operating Currency</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}" {{ $company->currency_id == $currency->id ? 'selected' : '' }}>{{ $currency->code }} ({{ $currency->symbol }}) - {{ $currency->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="inputState">Status</label>
                                    <select id="inputState" name="status" class="form-control">
                                        <option value="1" {{ $company->status ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ !$company->status ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Registered Address</label>
                                    <input type="text" name="address" class="form-control" value="{{ old('address', $company->address) }}">
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
