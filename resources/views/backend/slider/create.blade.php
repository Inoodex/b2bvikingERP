@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Slider</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Create Slider</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.slider.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.slider.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Title</label>
                                        <input type="text" class="form-control" name="title" value="{{ old('title') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Starting Price</label>
                                        <input type="number" step="0.01" class="form-control" name="starting_price" value="{{ old('starting_price') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Description (Optional)</label>
                                    <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Button Url</label>
                                        <input type="text" class="form-control" name="button_url" value="{{ old('button_url') }}">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Serial (Order)</label>
                                        <input type="number" class="form-control" name="serial" value="{{ old('serial', $nextSerial ?? 1) }}" placeholder="1 = first">
                                        <small class="text-muted">Lower serial shows first on homepage.</small>
                                        <small class="text-muted d-block">Preview: next suggested serial {{ $nextSerial ?? 1 }}</small>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="inputState">Status</label>
                                        <select id="inputState" class="form-control" name="status">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Banner (Max 2MB)</label>
                                    <div id="image-preview" class="image-preview">
                                        <label for="image-upload" id="image-label">Choose File</label>
                                        <input type="file" name="banner" id="image-upload" />
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
                input_field: "#image-upload",
                preview_box: "#image-preview",
                label_field: "#image-label",
                label_default: "Choose File",
                label_selected: "Change File",
                no_label: false,
                success_callback: null
            });
        });
    </script>
@endpush
