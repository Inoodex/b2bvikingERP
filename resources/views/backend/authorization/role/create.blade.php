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
        --pp-border: rgba(11, 17, 32, 0.08);
        --pp-border-hover: rgba(212, 162, 78, 0.35);
        --pp-ink: #0f172a;
        --pp-ink-soft: #334155;
        --pp-muted: #64748b;
        --pp-surface: #f8fafc;
        --pp-radius-sm: 8px;
        --pp-radius-md: 12px;
        --pp-radius-lg: 16px;
        --pp-shadow-card: 0 4px 16px -4px rgba(15, 23, 42, 0.05), 0 1px 3px rgba(15, 23, 42, 0.03);
        --pp-shadow-card-hover: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 1px 3px rgba(212, 162, 78, 0.15);
    }

    .pp-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .pp-header h1 {
        display: flex;
        align-items: center;
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
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 14px;
        color: #1a1306;
        background: linear-gradient(145deg, var(--pp-amber-bright), var(--pp-amber));
        box-shadow: 0 4px 14px rgba(212, 162, 78, 0.35);
        margin-right: 12px;
    }

    .pp-btn {
        border: none !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        border-radius: 20px !important;
        padding: 7px 16px !important;
        transition: all 0.2s cubic-bezier(.2,.8,.2,1);
        cursor: pointer;
    }
    .pp-btn:hover { transform: translateY(-1px); }
    .pp-btn-amber {
        background: linear-gradient(145deg, var(--pp-amber-bright), var(--pp-amber-deep)) !important;
        color: #1a1306 !important;
        box-shadow: 0 4px 14px -4px rgba(212, 162, 78, 0.45);
    }
    .pp-btn-amber:hover { filter: brightness(1.05); }
    .pp-btn-outline {
        border: 1.5px solid var(--pp-border) !important;
        color: var(--pp-ink-soft) !important;
        background: #fff !important;
    }
    .pp-btn-outline:hover {
        border-color: var(--pp-amber) !important;
        background: var(--pp-amber-soft) !important;
        color: var(--pp-amber-deep) !important;
    }

    /* Role Top Setup Card */
    .role-setup-card {
        background: #ffffff;
        border: 1px solid var(--pp-border);
        border-radius: var(--pp-radius-lg);
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: var(--pp-shadow-card);
    }
    .form-label-title {
        font-weight: 800;
        font-size: 13px;
        color: var(--pp-ink);
        margin-bottom: 8px;
        display: block;
    }
    .pp-input {
        border-radius: 12px !important;
        border: 1.5px solid var(--pp-border) !important;
        font-size: 13.5px !important;
        padding: 10px 16px !important;
        font-weight: 600;
        color: var(--pp-ink);
        transition: all 0.2s ease !important;
        background: #fff !important;
    }
    .pp-input:focus {
        border-color: var(--pp-amber) !important;
        box-shadow: 0 0 0 3px var(--pp-amber-soft) !important;
        outline: none;
    }

    /* Live Counter Pill */
    .role-counter-pill {
        display: inline-flex;
        align-items: center;
        background: #f1f5f9;
        border: 1px solid rgba(11, 17, 32, 0.06);
        border-radius: 20px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 700;
        color: var(--pp-ink-soft);
    }
    .role-counter-pill span {
        color: var(--pp-amber-deep);
        margin-right: 4px;
        font-weight: 800;
    }

    /* Masonry Layout for Balanced Module Cards */
    .modules-masonry {
        column-count: 2;
        column-gap: 20px;
    }
    @media (max-width: 991px) {
        .modules-masonry {
            column-count: 1;
        }
    }

    .module-card {
        break-inside: avoid;
        display: inline-block;
        width: 100%;
        margin-bottom: 20px;
        border: 1px solid var(--pp-border);
        border-radius: var(--pp-radius-lg);
        background: #ffffff;
        box-shadow: var(--pp-shadow-card);
        transition: all 0.25s ease;
        overflow: hidden;
    }
    .module-card:hover {
        border-color: var(--pp-border-hover);
        box-shadow: var(--pp-shadow-card-hover);
    }
    .module-header {
        background: linear-gradient(to right, #fcfdfd, #f8fafc);
        padding: 12px 18px;
        border-bottom: 1px solid var(--pp-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .module-header-left {
        display: flex;
        align-items: center;
    }
    .module-badge {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 3px 9px;
        border-radius: 6px;
        background: #eef2f6;
        color: #475569;
        margin-right: 10px;
    }
    .module-title {
        font-weight: 800;
        font-size: 13.5px;
        color: var(--pp-ink);
        margin: 0;
    }
    .module-count {
        font-size: 11px;
        font-weight: 700;
        color: var(--pp-muted);
        background: rgba(100, 116, 139, 0.1);
        padding: 2px 7px;
        border-radius: 10px;
        margin-left: 6px;
    }
    .module-body {
        padding: 14px 16px;
    }

    /* Dynamic CSS Grid for Permission Cards */
    .module-permissions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
    }

    .pp-permission-card {
        border-radius: 10px;
        border: 1.5px solid var(--pp-border);
        background: #fbfcfd;
        padding: 9px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(.2,.8,.2,1);
        user-select: none;
    }
    .pp-permission-card:hover {
        border-color: rgba(212, 162, 78, 0.45);
        background: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px -4px rgba(0,0,0,0.06);
    }
    .pp-permission-card.is-active {
        background: #fffdf9 !important;
        border-color: rgba(212, 162, 78, 0.5) !important;
        box-shadow: 0 3px 10px -3px rgba(212, 162, 78, 0.2) !important;
    }
    .pp-permission-name {
        font-weight: 600;
        font-size: 12px;
        color: var(--pp-ink-soft);
        line-height: 1.35;
        margin-right: 8px;
    }
    .pp-permission-card.is-active .pp-permission-name {
        color: #1a1306;
        font-weight: 700;
    }

    /* Modern Pill Switches */
    .pp-toggle .custom-switch-indicator {
        border-radius: 16px !important;
        width: 32px !important;
        height: 18px !important;
        transition: all 0.25s ease !important;
        border: 1px solid var(--pp-border);
        background: #e2e8f0;
    }
    .pp-toggle .custom-switch-indicator::after {
        width: 14px !important;
        height: 14px !important;
        top: 1px !important;
        left: 2px !important;
        transition: all 0.25s ease !important;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .pp-toggle .custom-switch-input:checked ~ .custom-switch-indicator {
        background: linear-gradient(135deg, var(--pp-amber-bright), var(--pp-amber)) !important;
        border-color: var(--pp-amber) !important;
        box-shadow: 0 2px 8px -2px rgba(212, 162, 78, 0.4);
    }
    .pp-toggle .custom-switch-input:checked ~ .custom-switch-indicator::after {
        left: 14px !important;
    }
</style>
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.role.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Create New Role</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.role.index') }}">Roles Management</a></div>
                <div class="breadcrumb-item">Create</div>
            </div>
        </div>

        <div class="section-body">
            <form action="{{ route('admin.role.store') }}" method="post" id="roleCreateForm">
                @csrf

                {{-- Role Setup Top Panel --}}
                <div class="role-setup-card">
                    <div class="row align-items-center">
                        <div class="col-lg-5 col-md-6 mb-3 mb-md-0">
                            <label for="role_name" class="form-label-title">
                                <i class="fas fa-id-badge text-warning mr-1"></i> Role Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control pp-input" id="role_name" name="name" 
                                value="{{ old('name') }}" required placeholder="e.g. Sales Manager, Warehouse Lead">
                        </div>
                        <div class="col-lg-7 col-md-6 text-md-right">
                            <div class="d-inline-flex align-items-center flex-wrap" style="gap: 8px;">
                                <div class="role-counter-pill mr-2 mb-1">
                                    <span id="activePermissionCount">0</span> / {{ $permissions->count() }} Permissions Selected
                                </div>
                                <button type="button" class="btn pp-btn pp-btn-outline mr-2 mb-1" id="selectAllGlobalBtn">
                                    <i class="fas fa-check-square mr-1 text-success"></i> Select All
                                </button>
                                <button type="button" class="btn pp-btn pp-btn-outline mb-1" id="deselectAllGlobalBtn">
                                    <i class="fas fa-square mr-1 text-danger"></i> Deselect All
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 12 Serial-wise Enterprise Module Cards in Balanced Masonry Layout --}}
                <div class="modules-masonry">
                    @foreach ($permissionGroups as $groupKey => $group)
                        <div class="module-card">
                            <div class="module-header">
                                <div class="module-header-left">
                                    <span class="module-badge"><i class="{{ $group['icon'] }}"></i> {{ $group['badge'] }}</span>
                                    <h5 class="module-title">{{ $group['title'] }}</h5>
                                    <span class="module-count">({{ count($group['permissions']) }})</span>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm pp-btn pp-btn-outline py-1 px-2 toggle-module-btn" 
                                        data-target="module-{{ $groupKey }}" style="font-size: 11px;">
                                        <i class="fas fa-check-double mr-1"></i> Toggle All
                                    </button>
                                </div>
                            </div>
                            <div class="module-body module-{{ $groupKey }}">
                                <div class="module-permissions-grid">
                                    @foreach ($group['permissions'] as $item)
                                        @php
                                            $checked = is_array(old('permissions')) && in_array($item->id, old('permissions'));
                                        @endphp
                                        <div class="pp-permission-card {{ $checked ? 'is-active' : '' }}" onclick="togglePermissionCard(this, event)">
                                            <span class="pp-permission-name">{{ $item->name }}</span>
                                            <label class="custom-switch pp-toggle mt-0 mb-0" onclick="event.stopPropagation();">
                                                <input type="checkbox" name="permissions[]"
                                                    value="{{ $item->id }}" 
                                                    class="custom-switch-input permission-checkbox" 
                                                    {{ $checked ? 'checked' : '' }}
                                                    onchange="updateCardState(this)">
                                                <span class="custom-switch-indicator"></span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Bottom Action Bar --}}
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 pb-4 border-top">
                    <a href="{{ route('admin.role.index') }}" class="btn btn-secondary px-4 py-2 font-weight-bold">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-5 py-2 font-weight-bold shadow-sm" style="font-size: 14px;">
                        <i class="fas fa-check mr-1"></i> Create Role
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
<script>
function updateActiveCounter() {
    var total = $('.permission-checkbox:checked').length;
    $('#activePermissionCount').text(total);
}

function updateCardState(checkbox) {
    var card = $(checkbox).closest('.pp-permission-card');
    if ($(checkbox).prop('checked')) {
        card.addClass('is-active');
    } else {
        card.removeClass('is-active');
    }
    updateActiveCounter();
}

function togglePermissionCard(cardElement, event) {
    var checkbox = $(cardElement).find('.permission-checkbox');
    checkbox.prop('checked', !checkbox.prop('checked'));
    updateCardState(checkbox[0]);
}

$(document).ready(function() {
    updateActiveCounter();

    // 1. Global Select All
    $('#selectAllGlobalBtn').on('click', function() {
        $('.permission-checkbox').prop('checked', true);
        $('.pp-permission-card').addClass('is-active');
        updateActiveCounter();
    });

    // 2. Global Deselect All
    $('#deselectAllGlobalBtn').on('click', function() {
        $('.permission-checkbox').prop('checked', false);
        $('.pp-permission-card').removeClass('is-active');
        updateActiveCounter();
    });

    // 3. Module Group Toggle
    $('.toggle-module-btn').on('click', function() {
        var targetClass = $(this).data('target');
        var checkboxes = $('.' + targetClass + ' .permission-checkbox');
        var allChecked = true;
        checkboxes.each(function() {
            if (!$(this).prop('checked')) {
                allChecked = false;
            }
        });
        checkboxes.prop('checked', !allChecked);
        checkboxes.each(function() {
            updateCardState(this);
        });
    });
});
</script>
@endpush

