@extends('backend.layouts.master')

@section('title', 'Add Company')

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
                        <h4>Create Company</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.master.companies.index') }}" class="btn btn-primary">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.master.companies.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Company Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Copenhagen Tourist Point ApS" required value="{{ old('name') }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Company Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" placeholder="e.g. CTP-DK" required value="{{ old('code') }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Email Address</label>
                                    <input type="email" name="email" class="form-control" placeholder="info@b2bviking.com" value="{{ old('email') }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Phone Number</label>
                                    <input type="text" name="phone" class="form-control" placeholder="+45 1234 5678" value="{{ old('phone') }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>VAT / Tax Registration Number</label>
                                    <input type="text" name="vat_number" class="form-control" placeholder="DK12345678" value="{{ old('vat_number') }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Operating Currency</label>
                                    <select name="currency_id" class="form-control select2">
                                        <option value="">Select Operating Currency</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->code }} ({{ $currency->symbol }}) - {{ $currency->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="inputState">Status</label>
                                    <select id="inputState" name="status" class="form-control">
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Registered Address</label>
                                    <input type="text" name="address" class="form-control" placeholder="Official Business Address" value="{{ old('address') }}">
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
