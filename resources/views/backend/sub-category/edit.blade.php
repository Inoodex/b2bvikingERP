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
                            <h4>Edit Sub Category</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.sub-category.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.sub-category.update', $subCategory->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="inputCategory">Category</label>
                                        <select id="inputCategory" class="form-control" name="category">
                                            <option value="">Select</option>
                                            @foreach ($categories as $category)
                                                <option {{ $category->id == $subCategory->category_id ? 'selected' : '' }}
                                                    value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>SubCategory Name</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ $subCategory->name }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="inputStatus">Status</label>
                                        <select id="inputStatus" class="form-control" name="status">
                                            <option {{ $subCategory->status == 1 ? 'selected' : '' }} value="1">Active
                                            </option>
                                            <option {{ $subCategory->status == 0 ? 'selected' : '' }} value="0">
                                                Inactive
                                            </option>
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
