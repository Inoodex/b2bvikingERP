@extends('backend.layouts.master')

@section('title', 'Custome Product Request')

@section('css')
<style>
    .image-preview {
        max-width: 100%;
        max-height: 300px;
        margin-top: 10px;
        border: 2px dashed #ddd;
        padding: 10px;
        display: none;
    }
    .image-preview img {
        max-width: 100%;
        max-height: 280px;
    }
    .custom-product-info {
        background: #f8f9fa;
        border-left: 4px solid #6777ef;
        padding: 15px;
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Create New Custom Product Request</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.custom-product-requests.index') }}">Custom Product Requests</a></li>
                            <li class="breadcrumb-item active">Create New</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="custom-product-info">
                            <h5><i class="fas fa-info-circle"></i> Request a New Product</h5>
                            <p class="mb-0">Use this form to request a product that doesn't exist in our system. Describe the product you need, and our team will review your request.</p>
                        </div>

                        <form action="{{ route('admin.custom-product-requests.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            @if(Auth::user()->can('Manage Custom Product Requests'))
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label class="form-label">Request for Outlet/User <span class="text-danger">*</span></label>
                                    <select name="user_id" class="form-control select2" required>
                                        <option value="">Select Outlet/User</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->outlet_name ?? 'No Outlet' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @endif

                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label class="form-label">Product Name <span class="text-danger">(Optional)</span></label>
                                    <input type="text" name="product_name" class="form-control" placeholder="Enter product name if you know it">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label class="form-label">Product Description / Quote Description <span class="text-danger">*</span></label>
                                    <textarea name="product_description" class="form-control" rows="5" placeholder="Describe the product you need in detail. Include specifications, size, color, material, brand preference, or any other details that would help us identify the product." required></textarea>
                                    <small class="text-muted">Please provide as much detail as possible about the product you need.</small>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Quantity Needed <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity_needed" class="form-control" min="1" value="1" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Expected Price (per unit) <span class="text-muted">(Optional)</span></label>
                                    <input type="number" name="expected_price" class="form-control" min="0" step="0.01" placeholder="0.00">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label class="form-label">Example Photo <span class="text-muted">(Optional)</span></label>
                                    <input type="file" name="example_image" class="form-control" accept="image/*" id="example_image_input">
                                    <small class="text-muted">Upload a photo or screenshot of the product you need. Max size: 2MB. Supported formats: JPEG, PNG, JPG, GIF, WEBP</small>
                                    <div class="image-preview" id="image_preview">
                                        <img src="" alt="Preview" id="preview_img">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Submit Request
                                    </button>
                                    <a href="{{ route('admin.custom-product-requests.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Image preview
    document.getElementById('example_image_input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('image_preview');
        const previewImg = document.getElementById('preview_img');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });
</script>
@endpush
