@extends('backend.layouts.master')
@section('title', $settings->site_name . ' | Update Permission')
@section('content')

    <section class="section">
        <div class="section-header">
            <h1>Permission</h1>
        </div>
        <div class="section-body">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Update Permission</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.permission.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.permission.update', $permission->id) }}') }}" method="post">
                                @csrf
                                @method('put')
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label>Permission Name</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name') ?? $permission->name }}">
                                    </div>
                                </div>
                                <div class="text-right mt-3">
                                    <button type="submit" class="btn btn-primary px-4">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </section>
@endsection
