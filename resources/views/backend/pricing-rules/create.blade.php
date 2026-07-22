@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Create Pricing Rule</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.pricing-rules.index') }}">Pricing Rules</a></div>
                <div class="breadcrumb-item">Create</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>New Rule</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.pricing-rules.store') }}" method="POST">
                                @csrf

                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Sale Multiplier</label>
                                            <input type="number" class="form-control" name="sale_multiplier" step="any" value="{{ old('sale_multiplier', 2) }}" required>
                                            <small class="text-muted">Example: 2 means Sale Price = Purchase Price × 2</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Outlet Multiplier</label>
                                            <input type="number" class="form-control" name="outlet_multiplier" step="any" value="{{ old('outlet_multiplier', 3) }}" required>
                                            <small class="text-muted">Example: 3 means Outlet Price = Purchase Price × 3</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Default</label>
                                            <select name="is_default" class="form-control">
                                                <option value="0" {{ old('is_default', '0') == '0' ? 'selected' : '' }}>No</option>
                                                <option value="1" {{ old('is_default') == '1' ? 'selected' : '' }}>Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="status" class="form-control" required>
                                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Save</button>
                                <a href="{{ route('admin.pricing-rules.index') }}" class="btn btn-light">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

