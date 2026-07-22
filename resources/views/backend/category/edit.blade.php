@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Category</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Edit Category</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.category.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.category.update', $category->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                {{-- <div class="form-group">
                                    <label>Preview</label>
                                    <br>
                                    <img src="{{ asset($category->image) }}" width="200px" alt="" class="img-thumbnail mb-3">
                                </div>
                                <div class="form-group">
                                    <label>Image (Optional)</label>
                                    <div id="image-preview" class="image-preview">
                                        <label for="image-upload" id="image-label">Choose File</label>
                                        <input type="file" name="image" id="image-upload" />
                                    </div>
                                </div> --}}
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Category Name</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ $category->name }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="inputState">Status</label>
                                        <select id="inputState" class="form-control" name="status">
                                            <option {{ $category->status == 1 ? 'selected' : '' }} value="1">Active
                                            </option>
                                            <option {{ $category->status == 0 ? 'selected' : '' }} value="0">Inactive
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="frontendShow">Show on Home</label>
                                        <select id="frontendShow" class="form-control" name="frontend_show">
                                            <option value="1" {{ $category->frontend_show ? 'selected' : '' }}>On</option>
                                            <option value="0" {{ !$category->frontend_show ? 'selected' : '' }}>Off</option>
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

@push('scripts')
    <script>
        $(document).ready(function() {
            $.uploadPreview({
                input_field: "#image-upload", // Default: .image-upload
                preview_box: "#image-preview", // Default: .image-preview
                label_field: "#image-label", // Default: .image-label
                label_default: "Choose File", // Default: Choose File
                label_selected: "Change File", // Default: Change File
                no_label: false, // Default: false
                success_callback: null // Default: null
            });
        });
    </script>
@endpush
