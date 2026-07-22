@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Sub Category</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Create Sub Category</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.sub-category.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.sub-category.store') }}" method="POST">
                                @csrf
                                <div class="row">

                                    <div class="form-group col-md-6">
                                        <label for="inputCategory">Select Category</label>
                                        <select id="inputCategory" class="form-control" name="category">
                                            <option value="">Select</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="inputStatus">Status</label>
                                        <select id="inputStatus" class="form-control" name="status">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="form-group  col-md-12">
                                        <label>SubCategory Name</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name') }}">
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
