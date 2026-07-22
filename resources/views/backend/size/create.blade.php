@extends('backend.layouts.master')
@section('title', 'Size')
@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Size</h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Create Size</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.sizes.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.sizes.store') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Size Name</label>
                                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                        <small class="form-text text-muted">e.g., S, M, L, XL, XXL</small>
                                    </div>
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
