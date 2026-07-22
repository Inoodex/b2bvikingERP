@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>General Settings</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></div>
                <div class="breadcrumb-item active">General</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4>Site Information</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.general.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Site Name</label>
                                    <input type="text" name="site_name" class="form-control" value="{{ old('site_name', $setting->site_name ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contact Email</label>
                                    <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $setting->contact_email ?? '') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" class="form-control" rows="4">{{ old('address', $setting->address ?? '') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>Site Logo</label>
                            <div class="d-flex align-items-center mb-3" style="gap: 16px; flex-wrap: wrap;">
                                <div style="width: 140px;">
                                    <label for="site-logo-input" class="d-block m-0" style="cursor: pointer;">
                                        <div style="width: 140px; height: 140px; border: 2px dashed #cbd5f5; border-radius: 16px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f8fafc; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08); position: relative;">
                                            <img id="site-logo-preview"
                                                 src="{{ asset(optional($setting)->site_logo ?: 'uploads/logo.png') }}"
                                                 alt="Site Logo"
                                                 style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                            <div class="d-flex align-items-center justify-content-center"
                                                 style="position: absolute; inset: 0; background: rgba(15, 23, 42, 0.45); color: #fff; font-weight: 600; letter-spacing: .08em; font-size: 11px; text-transform: uppercase; opacity: 0; transition: opacity .2s ease;">
                                                Change Logo
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div>
                                    <div class="font-weight-bold">Upload logo</div>
                                    <div class="text-muted small">Allowed: JPG, PNG, WEBP, AVIF (max 4MB)</div>
                                    <div id="site-logo-filename" class="text-muted small mt-1">No file selected</div>
                                </div>
                            </div>
                            <input id="site-logo-input" type="file" name="site_logo" class="d-none" accept="image/*,.avif,.webp">
                            @error('site_logo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-right">
                            <a href="{{ route('admin.settings.index') }}" class="btn btn-light border mr-2">Back</a>
                            <button type="submit" class="btn btn-primary">Save General Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        (function () {
            const input = document.getElementById('site-logo-input');
            const preview = document.getElementById('site-logo-preview');
            const filename = document.getElementById('site-logo-filename');
            if (!input || !preview) return;

            const overlay = preview?.parentElement?.querySelector('div[style*="position: absolute"]');
            if (overlay) {
                preview.parentElement.addEventListener('mouseenter', () => { overlay.style.opacity = '1'; });
                preview.parentElement.addEventListener('mouseleave', () => { overlay.style.opacity = '0'; });
            }

            input.addEventListener('change', () => {
                const file = input.files && input.files[0];
                if (!file) {
                    preview.src = "{{ asset(optional($setting)->site_logo ?: 'uploads/logo.png') }}";
                    if (filename) filename.textContent = 'No file selected';
                    return;
                }
                if (filename) filename.textContent = file.name;
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.src = e.target?.result || preview.src;
                };
                reader.readAsDataURL(file);
            });
        })();
    </script>
@endsection
