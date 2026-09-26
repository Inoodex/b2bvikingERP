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
                            <h4>Create Category</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.category.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.category.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                {{-- <div class="form-group">
                                    <label>Image (Optional)</label>
                                    <div id="image-preview" class="image-preview">
                                        <label for="image-upload" id="image-label">Choose File</label>
                                        <input type="file" name="image" id="image-upload" />
                                    </div>
                                </div> --}}
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Category Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name') }}" required>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="inputState">Status</label>
                                        <select id="inputState" class="form-control" name="status">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="frontendShow">Show on Home</label>
                                        <select id="frontendShow" class="form-control" name="frontend_show">
                                            <option value="1" {{ old('frontend_show') == '1' ? 'selected' : '' }}>On</option>
                                            <option value="0" {{ old('frontend_show', '0') == '0' ? 'selected' : '' }}>Off</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>
                                            <i class="fas fa-barcode text-primary mr-1"></i> GS1 GPC Brick Code (8 Digits)
                                            <a href="https://gpc-browser.gs1.org/" target="_blank" class="badge badge-info ml-1" style="font-size: 11px;" title="Lookup official GS1 code">
                                                <i class="fas fa-external-link-alt"></i> GS1 Browser
                                            </a>
                                        </label>
                                        <input type="text" class="form-control font-weight-bold" name="gpc_code"
                                            placeholder="e.g. 10001363" maxlength="20"
                                            value="{{ old('gpc_code') }}">
                                        <small class="form-text text-muted">Used as prefix for all barcode labels under this category (e.g. 10001363 for Apparel).</small>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>GS1 GPC Classification Title</label>
                                        <input type="text" class="form-control" name="gpc_title"
                                            placeholder="e.g. Clothing - Tops/Shirts/Apparel"
                                            value="{{ old('gpc_title') }}">
                                        <small class="form-text text-muted">Official GS1 Brick name or descriptive taxonomy title.</small>
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
