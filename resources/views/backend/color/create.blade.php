@extends('backend.layouts.master')
@section('title', 'Color')
@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Color</h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Create Color</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.colors.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.colors.store') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Color Name</label>
                                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Hex Code (Optional)</label>
                                        <input type="color" class="form-control" name="hex_code" value="{{ old('hex_code', '#000000') }}">
                                        <small class="form-text text-muted">Pick a color for visual representation</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="inputState">Status</label>
                                        <select id="inputState" class="form-control" name="status">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="text-right mt-3">
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
