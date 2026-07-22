@extends('backend.layouts.master')
@section('title', $settings->site_name . ' | Create Role')

@push('css')
<style>
    :root {
        --pp-obsidian: #0b1120;
        --pp-amber: #d4a24e;
        --pp-amber-bright: #ecc78b;
        --pp-amber-deep: #b8852a;
        --pp-amber-soft: rgba(212, 162, 78, 0.08);
        --pp-border: rgba(11, 17, 32, 0.07);
        --pp-border-hover: rgba(212, 162, 78, 0.15);
        --pp-ink: #161e2e;
        --pp-ink-soft: #2d3748;
        --pp-muted: #6b788e;
        --pp-surface: #f8f9fc;
        --pp-radius-md: 14px;
        --pp-radius-lg: 20px;
        --pp-shadow-card: 0 1px 3px rgba(11,17,32,0.04), 0 8px 20px -12px rgba(11,17,32,0.12);
        --pp-shadow-card-hover: 0 12px 32px -12px rgba(11,17,32,0.16), 0 0 0 1px rgba(212,162,78,0.06);
    }

    .pp-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
        padding: 0;
        position: relative;
    }
    .pp-header h1 {
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 800;
        font-size: 20px;
        color: var(--pp-ink);
        letter-spacing: -0.3px;
        margin: 0;
    }
    .pp-header h1 .pp-icon {
        width: 34px;
        height: 34px;
        min-width: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 14px;
        color: #1a1306;
        background: linear-gradient(145deg, var(--pp-amber-bright), var(--pp-amber));
        box-shadow: 0 4px 14px rgba(212, 162, 78, 0.35), inset 0 1px 0 rgba(255,255,255,0.3);
    }

    .pp-btn {
        border: none !important;
        font-weight: 700 !important;
        font-size: 11.5px !important;
        letter-spacing: 0.2px;
        border-radius: var(--pp-radius-lg) !important;
        padding: 7px 16px !important;
        transition: all 0.25s cubic-bezier(.2,.8,.2,1);
        position: relative;
        overflow: hidden;
    }
    .pp-btn:hover { transform: translateY(-2px); }
    .pp-btn:active { transform: translateY(0) scale(0.97); }
    .pp-btn-amber {
        background: linear-gradient(145deg, var(--pp-amber-bright), var(--pp-amber-deep)) !important;
        color: #1a1306 !important;
        box-shadow: 0 4px 14px -4px rgba(212, 162, 78, 0.45), inset 0 1px 0 rgba(255,255,255,0.25);
    }
    .pp-btn-amber:hover { filter: brightness(1.06); box-shadow: 0 8px 24px -6px rgba(212, 162, 78, 0.5); }
    .pp-btn-outline {
        border: 1.5px solid var(--pp-border) !important;
        color: var(--pp-ink-soft) !important;
        background: transparent !important;
        border-radius: var(--pp-radius-lg) !important;
        font-weight: 600 !important;
        font-size: 11.5px !important;
        padding: 7px 16px !important;
        transition: all 0.2s ease !important;
    }
    .pp-btn-outline:hover {
        border-color: var(--pp-amber) !important;
        background: var(--pp-amber-soft) !important;
        color: var(--pp-amber-deep) !important;
    }

    .pp-card {
        border-radius: var(--pp-radius-md) !important;
        border: 1px solid var(--pp-border) !important;
        background: #fff !important;
        box-shadow: var(--pp-shadow-card) !important;
        transition: all 0.3s cubic-bezier(.2,.8,.2,1) !important;
        overflow: hidden;
    }
    .pp-card:hover {
        box-shadow: var(--pp-shadow-card-hover) !important;
        border-color: var(--pp-border-hover) !important;
    }
    .pp-card-header {
        padding: 14px 20px !important;
        background: linear-gradient(135deg, #fafbfc, #f4f5f8);
        border-bottom: 1px solid var(--pp-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pp-card-header h4 {
        font-weight: 800;
        font-size: 14px;
        color: var(--pp-ink);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pp-card-header h4 i { color: var(--pp-amber); }

    .form-label {
        font-weight: 700;
        font-size: 12.5px;
        color: var(--pp-ink);
        margin-bottom: 6px;
    }
    .pp-input {
        border-radius: var(--pp-radius-lg) !important;
        border: 1.5px solid var(--pp-border) !important;
        font-size: 12.5px !important;
        padding: 8px 14px !important;
        font-weight: 500;
        color: var(--pp-ink);
        transition: all 0.25s ease !important;
        background: #fff !important;
    }
    .pp-input:focus {
        border-color: var(--pp-amber) !important;
        box-shadow: 0 0 0 3px var(--pp-amber-soft), 0 4px 12px -8px rgba(212,162,78,0.15) !important;
        outline: none;
    }
    .pp-input::placeholder { color: #aab2c0; }

    .pp-permission-card {
        border-radius: 12px !important;
        border: 1px solid var(--pp-border) !important;
        background: #fff !important;
        box-shadow: 0 1px 2px rgba(11,17,32,0.03) !important;
        transition: all 0.25s ease !important;
        padding: 12px 14px !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 100%;
    }
    .pp-permission-card:hover {
        border-color: var(--pp-border-hover) !important;
        box-shadow: 0 4px 12px -8px rgba(212,162,78,0.12) !important;
        background: #fefcf8 !important;
    }
    .pp-permission-name {
        font-weight: 700;
        font-size: 12px;
        color: var(--pp-ink-soft);
        text-transform: capitalize;
    }

    .pp-toggle .custom-switch-indicator {
        border-radius: 16px !important;
        width: 31px !important;
        height: 17px !important;
        transition: all 0.25s ease !important;
        border: 1px solid var(--pp-border);
        background: #e8ebf0;
    }
    .pp-toggle .custom-switch-indicator::after {
        width: 13px !important;
        height: 13px !important;
        top: 2px !important;
        left: 2px !important;
        transition: all 0.25s ease !important;
        background: #fff;
    }
    .pp-toggle .custom-switch-input:checked ~ .custom-switch-indicator {
        background: linear-gradient(135deg, var(--pp-amber-bright), var(--pp-amber)) !important;
        border-color: var(--pp-amber) !important;
        box-shadow: 0 2px 8px -2px rgba(212, 162, 78, 0.3);
    }
    .pp-toggle .custom-switch-input:checked ~ .custom-switch-indicator::after {
        left: 16px !important;
    }

    @media (max-width: 767.98px) {
        .pp-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .pp-header h1 {
            font-size: 16px;
            gap: 8px;
        }
        .pp-header h1 .pp-icon {
            width: 28px;
            height: 28px;
            min-width: 28px;
            font-size: 12px;
        }
        .pp-card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .pp-card-header h4 { font-size: 13px; }
        .pp-permission-card { padding: 10px 12px !important; }
        .pp-permission-name { font-size: 11px; }
        .pp-input { font-size: 11.5px !important; padding: 7px 12px !important; }
    }
</style>
@endpush

@section('content')
    <section class="section">
        <div class="pp-header">
            <h1>
                <span class="pp-icon"><i class="fas fa-plus-circle"></i></span>
                Create Role
            </h1>
            <div>
                <a href="{{ route('admin.role.index') }}" class="btn pp-btn pp-btn-outline shadow-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card pp-card">
                        <div class="pp-card-header">
                            <h4><i class="fas fa-shield-alt"></i> New Role Details</h4>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.role.store') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <label for="role_name" class="form-label">Role Name</label>
                                        <input type="text" class="form-control pp-input" id="role_name" name="name"
                                            value="{{ old('name') }}" placeholder="Enter role name">
                                    </div>
                                    <div class="col-md-8">
                                        <h5 class="mb-3" style="font-weight: 700; font-size: 13px; color: var(--pp-ink); display: flex; align-items: center; gap: 8px;">
                                            <i class="fas fa-key" style="color: var(--pp-amber);"></i> Permissions
                                        </h5>
                                        <div class="row g-2">
                                            @foreach ($permissions as $item)
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="pp-permission-card">
                                                        <span class="pp-permission-name">{{ $item->name }}</span>
                                                        <label class="custom-switch pp-toggle mt-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                value="{{ $item->id }}" class="custom-switch-input">
                                                            <span class="custom-switch-indicator"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn pp-btn pp-btn-amber shadow-sm">
                                        <i class="fas fa-check mr-1"></i> Create Role
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
