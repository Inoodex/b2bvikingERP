@extends('backend.layouts.master')

@section('title', 'Edit Currency')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Currencies</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit Currency</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.master.currencies.index') }}" class="btn btn-primary">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.master.currencies.update', $currency->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Currency Code (ISO) <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" value="{{ old('code', $currency->code) }}" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Currency Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $currency->name) }}" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Symbol <span class="text-danger">*</span></label>
                                    <input type="text" name="symbol" class="form-control" value="{{ old('symbol', $currency->symbol) }}" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Exchange Rate <span class="text-danger">*</span></label>
                                    <input type="number" step="0.0001" name="exchange_rate" class="form-control" value="{{ old('exchange_rate', $currency->exchange_rate) }}" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="inputState">Status</label>
                                    <select id="inputState" name="status" class="form-control">
                                        <option value="1" {{ $currency->status ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ !$currency->status ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6 d-flex align-items-center pt-4">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_base" name="is_base" value="1" {{ $currency->is_base ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold text-dark" for="is_base">Set as Primary System Base Currency</label>
                                    </div>
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
