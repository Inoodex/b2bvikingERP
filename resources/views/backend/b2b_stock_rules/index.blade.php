@extends('backend.layouts.master')

@section('title', 'B2B Customer Stock Rules & Availability Matrix')

@push('css')
<style>
    /* Prevent full-page shaking / layout shifts */
    html {
        overflow-y: scroll !important;
        scrollbar-gutter: stable;
    }
    body.modal-open {
        overflow: hidden !important;
        padding-right: 0 !important;
    }
    #view-mode-table-wrapper,
    #view-mode-matrix-wrapper {
        min-height: 550px;
    }

    /* Segmented Tab Switcher ("tab ar moto") */
    .b2b-header-tabs {
        background: #f1f5f9;
        padding: 4px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .b2b-tab-btn {
        border: 1px solid transparent !important;
        background: transparent;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        border-radius: 7px;
        transition: background-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        line-height: 1.4;
        outline: none !important;
        box-shadow: none !important;
        white-space: nowrap;
        user-select: none;
        -webkit-user-select: none;
        transform: none !important;
    }
    .b2b-tab-btn:hover {
        color: #1e293b;
        background: #e2e8f0;
        transform: none !important;
    }
    .b2b-tab-btn:focus,
    .b2b-tab-btn:active {
        outline: none !important;
        box-shadow: none !important;
        transform: none !important;
    }
    .b2b-tab-btn.active {
        background-color: #6777ef !important;
        border-color: #6777ef !important;
        color: #ffffff !important;
        font-weight: 600;
        box-shadow: 0 2px 6px #acb5f6 !important;
        transform: none !important;
    }
    .b2b-tab-btn.active:focus,
    .b2b-tab-btn.active:active {
        box-shadow: 0 2px 6px #acb5f6 !important;
        transform: none !important;
    }
    .b2b-tab-btn.active i {
        color: #ffffff !important;
    }
    .b2b-tab-btn:not(.active) i {
        color: #64748b;
    }

    /* Red-styled Reset Button ("reset ta red ar moto") */
    .btn-filter-reset {
        color: #ef4444 !important;
        background-color: #fef2f2 !important;
        border: 1.5px solid #fecaca !important;
        border-radius: 8px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        padding: 5px 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        transition: all 0.2s ease !important;
        outline: none !important;
        box-shadow: 0 1px 2px rgba(239, 68, 68, 0.05) !important;
    }
    .btn-filter-reset:hover,
    .btn-filter-reset:focus,
    .btn-filter-reset:active {
        color: #ffffff !important;
        background-color: #dc2626 !important;
        border-color: #dc2626 !important;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25) !important;
        transform: translateY(-1px) !important;
    }
    .btn-filter-reset i {
        font-size: 11px;
        transition: transform 0.2s ease;
    }
    .btn-filter-reset:hover i {
        transform: rotate(-45deg);
    }

    /* Target Scope Segmented Tabs (Company / Outlet / Buyer Phone) */
    #matrix-scope-group,
    #modal-scope-pills {
        background: #f1f5f9 !important;
        padding: 3px !important;
        border-radius: 9px !important;
        border: 1px solid #cbd5e1 !important;
        display: flex !important;
        align-items: center !important;
        gap: 3px !important;
        height: 38px !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
        width: 100% !important;
    }
    #matrix-scope-group label.btn,
    #modal-scope-pills label.btn {
        border: none !important;
        background: transparent !important;
        color: #64748b !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        border-radius: 6px !important;
        padding: 0 6px !important;
        height: 32px !important;
        transition: all 0.15s ease !important;
        box-shadow: none !important;
        outline: none !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 5px !important;
        cursor: pointer !important;
        margin-bottom: 0 !important;
        flex: 1 1 0 !important;
        min-width: 0 !important;
        white-space: nowrap !important;
        user-select: none !important;
        transform: none !important;
    }
    #matrix-scope-group label.btn:hover,
    #modal-scope-pills label.btn:hover {
        color: #1e293b !important;
        background: #e2e8f0 !important;
    }
    #matrix-scope-group label.btn.active,
    #modal-scope-pills label.btn.active {
        background-color: #6777ef !important;
        border-color: #6777ef !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 6px rgba(103, 119, 239, 0.4) !important;
    }
    #matrix-scope-group label.btn.active i,
    #modal-scope-pills label.btn.active i {
        color: #ffffff !important;
    }
    #matrix-scope-group label.btn:not(.active) i,
    #modal-scope-pills label.btn:not(.active) i {
        color: #64748b !important;
    }

    /* Enterprise Load Matrix Button */
    #btn-load-matrix {
        background-color: #6777ef !important;
        border-color: #6777ef !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px #acb5f6 !important;
        border-radius: 8px !important;
        height: 38px !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        transition: all 0.2s ease !important;
        outline: none !important;
    }
    #btn-load-matrix:hover {
        background-color: #5a67d8 !important;
        border-color: #5a67d8 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(103, 119, 239, 0.35) !important;
        transform: translateY(-1px) !important;
    }
    #btn-load-matrix:active,
    #btn-load-matrix:focus {
        box-shadow: 0 2px 6px #acb5f6 !important;
        transform: translateY(0) !important;
        outline: none !important;
    }
    #btn-load-matrix i {
        transition: transform 0.3s ease;
    }
    #btn-load-matrix:hover i {
        transform: rotate(180deg);
    }


    .select2-container {
        width: 100% !important;
    }
    .select2-container .select2-selection--single {
        height: 38px !important;
        border-radius: 8px !important;
        border: 1px solid #cbd5e1 !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px !important;
        font-size: 13px !important;
    }
    .select2-container .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
    .btn-matrix-toggle {
        font-size: 11px !important;
        padding: 4px 10px !important;
        border-radius: 6px !important;
        transition: all 0.2s ease !important;
    }

    /* Enterprise Slide-Over Drawer Styling (ZERO FLASH ON REFRESH) */
    .audit-drawer-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.45);
        z-index: 100000 !important;
        opacity: 0;
        display: none;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }
    .audit-drawer-backdrop.is-active {
        opacity: 1;
        pointer-events: auto;
    }
    .audit-drawer {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: min(680px, 95vw);
        height: 100vh;
        background: #ffffff;
        border-left: 1px solid #e2e8f0;
        box-shadow: -15px 0 45px rgba(0, 0, 0, 0.18), -2px 0 8px rgba(0, 0, 0, 0.06);
        z-index: 100001 !important;
        transform: translateX(100%);
        transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        display: none;
        flex-direction: column;
        overflow: hidden;
    }
    .audit-drawer.is-active {
        transform: translateX(0);
    }
    .audit-drawer .drawer-header {
        padding: 18px 24px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-shrink: 0;
    }
    .audit-drawer .drawer-close-btn {
        width: 34px;
        height: 34px;
        padding: 0;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #64748b;
        font-size: 15px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    .audit-drawer .drawer-close-btn:hover {
        background: #fee2e2 !important;
        color: #ef4444 !important;
        border-color: #fca5a5 !important;
    }

    /* Clean Universal Scrollbars */
    .b2b-datatable-wrapper {
        position: relative;
        width: 100%;
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    .b2b-datatable-wrapper::-webkit-scrollbar {
        height: 6px;
    }
    .b2b-datatable-wrapper::-webkit-scrollbar-track {
        background: transparent;
    }
    .b2b-datatable-wrapper::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .b2b-datatable-wrapper::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    #view-mode-table-wrapper .table-responsive {
        border: none !important;
        overflow-x: auto !important;
        scrollbar-width: thin !important;
        scrollbar-color: #cbd5e1 transparent !important;
    }
</style>
@endpush

@section('content')
<section class="section">
    {{-- Section Header --}}
    <div class="section-header border-0 shadow-sm mb-4" style="background: #ffffff; border-radius: 16px; padding: 20px 24px; margin-top: 25px !important;">
        <div class="d-flex align-items-center flex-wrap w-100 justify-content-between" style="gap: 16px;">
            <div class="d-flex align-items-center mb-2 mb-md-0">
                <div class="mr-3 p-3 rounded-circle text-white shadow-sm" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border: 1px solid rgba(212, 162, 78, 0.3);">
                    <i class="fas fa-users-cog text-warning" style="font-size: 1.35rem;"></i>
                </div>
                <div>
                    <h4 class="mb-1 font-weight-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">Customer Stock Rules</h4>
                    <p class="text-muted mb-0 small">B2B client and outlet-specific product stock visibility overrides & VIP allocation matrix</p>
                </div>
            </div>
            <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                {{-- Segmented Tab Switcher ("tab ar moto") --}}
                <div class="b2b-header-tabs" id="view-mode-toggle">
                    <button type="button" class="b2b-tab-btn active" id="btn-mode-table" data-mode="table">
                        <i class="fas fa-list-check mr-2"></i>
                        <span>Master Rules Table</span>
                    </button>
                    <button type="button" class="b2b-tab-btn" id="btn-mode-matrix" data-mode="matrix">
                        <i class="fas fa-th mr-2"></i>
                        <span>Customer Matrix</span>
                    </button>
                </div>

                {{-- Create Rule Action Button: Opens Slide-Over Drawer --}}
                <button type="button" class="btn btn-primary font-weight-bold px-3 py-2 shadow-sm" id="btn-open-create-drawer" style="border-radius: 8px;">
                    <i class="fas fa-plus mr-1"></i>
                    <span>Create Stock Rule</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Metrics Ribbon --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
            <div class="card border-0 shadow-sm mb-0" style="border-radius: 14px; background: #ffffff; border-left: 4px solid #3b82f6 !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="mr-3" style="width: 44px; height: 44px; border-radius: 10px; background: rgba(59, 130, 246, 0.12); display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                        <i class="fas fa-shield-alt" style="font-size: 20px;"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 11px;">Total Active Rules</div>
                        <div class="h4 mb-0 font-weight-bold text-dark mt-1">{{ number_format($stats['total_rules'] ?? 0) }}</div>
                        <small class="text-muted" style="font-size: 10px;">Across all catalog items</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
            <div class="card border-0 shadow-sm mb-0" style="border-radius: 14px; background: #ffffff; border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="mr-3" style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center; color: #10b981;">
                        <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 11px;">Priority In-Stock</div>
                        <div class="h4 mb-0 font-weight-bold text-success mt-1">{{ number_format($stats['priority_in_stock'] ?? 0) }}</div>
                        <small class="text-muted" style="font-size: 10px;">Guaranteed VIP availability</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
            <div class="card border-0 shadow-sm mb-0" style="border-radius: 14px; background: #ffffff; border-left: 4px solid #ef4444 !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="mr-3" style="width: 44px; height: 44px; border-radius: 10px; background: rgba(239, 68, 68, 0.12); display: flex; align-items: center; justify-content: center; color: #ef4444;">
                        <i class="fas fa-ban" style="font-size: 20px;"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 11px;">Restricted (Out of Stock)</div>
                        <div class="h4 mb-0 font-weight-bold text-danger mt-1">{{ number_format($stats['force_out_of_stock'] ?? 0) }}</div>
                        <small class="text-muted" style="font-size: 10px;">Blocked booking policies</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm mb-0" style="border-radius: 14px; background: #ffffff; border-left: 4px solid #64748b !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="mr-3" style="width: 44px; height: 44px; border-radius: 10px; background: rgba(100, 116, 139, 0.12); display: flex; align-items: center; justify-content: center; color: #64748b;">
                        <i class="fas fa-building" style="font-size: 20px;"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 11px;">Configured Accounts</div>
                        <div class="h5 mb-0 font-weight-bold text-dark mt-1">{{ $stats['companies_count'] }} Co / {{ $stats['outlets_count'] }} Outlets</div>
                        <small class="text-muted" style="font-size: 10px;">Distinct entities with rules</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- VIEW MODE 1: MASTER RULES DATATABLE (MODE B) --}}
    {{-- ========================================== --}}
    <div id="view-mode-table-wrapper">
        {{-- Fast Server-Side Multi-Filter Card --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; background: #ffffff;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
                <div class="d-flex align-items-center">
                    <div class="mr-2 p-2 rounded text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(103, 119, 239, 0.1);">
                        <i class="fas fa-filter" style="font-size: 13px;"></i>
                    </div>
                    <div>
                        <strong class="text-dark" style="font-size: 14px;">Filter Stock Rules</strong>
                        <div class="text-muted" style="font-size: 11px;">Filter rules across scopes, accounts and policies</div>
                    </div>
                </div>
                <button type="button" class="btn btn-filter-reset" id="btn-reset-filters" title="Reset all filters to default">
                    <i class="fas fa-undo"></i>
                    <span>Reset Filters</span>
                </button>
            </div>
            <div class="card-body p-4">
                <form id="filter-form">
                    <div class="row">
                        {{-- Row 1: Target Scope, Company, and Outlet --}}
                        <div class="col-lg-4 col-md-6 col-12 mb-3">
                            <label class="small text-muted font-weight-bold mb-1 d-block">
                                <i class="fas fa-layer-group text-primary mr-1"></i> Target Scope
                            </label>
                            <select name="target_scope" id="filter_target_scope" class="form-control select2 filter-select w-100">
                                <option value="">-- All Target Scopes --</option>
                                <option value="company">🏢 Companies Only</option>
                                <option value="outlet">🏬 Retail Outlets Only</option>
                                <option value="buyer">📞 Buyers & Phone Numbers Only</option>
                            </select>
                        </div>

                        <div class="col-lg-4 col-md-6 col-12 mb-3">
                            <label class="small text-muted font-weight-bold mb-1 d-block">
                                <i class="fas fa-building text-info mr-1"></i> Target Company
                            </label>
                            <select name="company_id" id="filter_company_id" class="form-control select2 filter-select w-100">
                                <option value="">-- All Companies --</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-4 col-md-12 col-12 mb-3">
                            <label class="small text-muted font-weight-bold mb-1 d-block">
                                <i class="fas fa-store text-warning mr-1"></i> Retail Outlet Branch
                            </label>
                            <select name="outlet_id" id="filter_outlet_id" class="form-control select2 filter-select w-100">
                                <option value="">-- All {{ count($outlets) }} Outlets --</option>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }} [{{ $outlet->code }}]</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Row 2: Product Category & Availability Policy --}}
                        <div class="col-lg-6 col-md-6 col-12 mb-1">
                            <label class="small text-muted font-weight-bold mb-1 d-block">
                                <i class="fas fa-tags text-success mr-1"></i> Product Category
                            </label>
                            <select name="category_id" id="filter_category_id" class="form-control select2 filter-select w-100">
                                <option value="">-- All Categories --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-6 col-md-6 col-12 mb-1">
                            <label class="small text-muted font-weight-bold mb-1 d-block">
                                <i class="fas fa-shield-alt text-danger mr-1"></i> Availability Policy
                            </label>
                            <select name="visibility_mode" id="filter_visibility_mode" class="form-control select2 filter-select w-100">
                                <option value="">-- All Rule Policies --</option>
                                <option value="force_in_stock">🟢 Priority In-Stock</option>
                                <option value="force_out_of_stock">🔴 Restricted (Out of Stock)</option>
                                <option value="hide_product">🚫 Catalog Exclusion (Hidden)</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Master DataTable Card --}}
        <div class="card border-0 shadow-sm" style="border-radius: 14px; overflow: hidden; background: #ffffff;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="fas fa-list-alt text-primary mr-2"></i>
                    <strong class="text-dark" style="font-size: 15px;">Customer Stock Rules</strong>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table table-striped table-hover align-middle w-100 mb-0', 'id' => 'b2b-visibility-table']) }}
                </div>
            </div>
        </div>
    </div>

    {{-- ==================================================== --}}
    {{-- VIEW MODE 2: CUSTOMER-CENTRIC INTERACTIVE MATRIX (MODE A) --}}
    {{-- ==================================================== --}}
    <div id="view-mode-matrix-wrapper" style="display: none;">
        {{-- Card 1: Customer Matrix Filters --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; background: #ffffff;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
                <div class="d-flex align-items-center">
                    <div class="mr-2 p-2 rounded text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(103, 119, 239, 0.1);">
                        <i class="fas fa-sliders-h" style="font-size: 13px;"></i>
                    </div>
                    <div>
                        <strong class="text-dark" style="font-size: 14px;">Filter Customer Matrix</strong>
                        <div class="text-muted" style="font-size: 11px;">Select target customer account and filter catalog products</div>
                    </div>
                </div>
                <button type="button" class="btn btn-filter-reset" id="btn-matrix-reset-filter" title="Reset all matrix filters">
                    <i class="fas fa-undo"></i>
                    <span>Reset Filters</span>
                </button>
            </div>
            <div class="card-body p-4">
                {{-- Row 1: Target Account Selection (Scope, Account, Load Button) --}}
                <div class="row align-items-end mb-3">
                    <div class="col-lg-4 col-md-12 mb-3 mb-lg-0">
                        <label class="small text-muted font-weight-bold mb-1 d-block">
                            <i class="fas fa-layer-group text-primary mr-1"></i> 1. Select Target Type
                        </label>
                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons" id="matrix-scope-group">
                            <label class="btn btn-sm active font-weight-bold flex-fill" data-target="company">
                                <input type="radio" name="matrix_scope" value="company" checked autocomplete="off">
                                <i class="fas fa-building mr-1"></i> Company
                            </label>
                            <label class="btn btn-sm font-weight-bold flex-fill" data-target="outlet">
                                <input type="radio" name="matrix_scope" value="outlet" autocomplete="off">
                                <i class="fas fa-store mr-1"></i> Outlet
                            </label>
                            <label class="btn btn-sm font-weight-bold flex-fill" data-target="phone">
                                <input type="radio" name="matrix_scope" value="phone" autocomplete="off">
                                <i class="fas fa-phone mr-1"></i> Buyer Phone
                            </label>
                        </div>
                    </div>

                    <div class="col-lg-5 col-md-8 col-12 mb-3 mb-lg-0">
                        <label class="small text-muted font-weight-bold mb-1 d-block">
                            <i class="fas fa-building text-info mr-1"></i> 2. Choose Specific Account
                        </label>
                        
                        {{-- Company Selector --}}
                        <div class="matrix-scope-box" id="matrix-scope-company">
                            <select id="matrix_company_id" class="form-control select2 matrix-select2" style="width: 100%;">
                                <option value="">-- Choose Company Account --</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Outlet Selector --}}
                        <div class="matrix-scope-box" id="matrix-scope-outlet" style="display: none;">
                            <select id="matrix_outlet_id" class="form-control select2 matrix-select2" style="width: 100%;">
                                <option value="">-- Choose Retail Branch Outlet --</option>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }} [{{ $outlet->code }}]</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Buyer / Phone Selector --}}
                        <div class="matrix-scope-box" id="matrix-scope-phone" style="display: none;">
                            <div class="row" style="margin-left: -4px; margin-right: -4px;">
                                <div class="col-7" style="padding-left: 4px; padding-right: 4px;">
                                    <select id="matrix_user_id" class="form-control select2 matrix-select2" style="width: 100%;">
                                        <option value="">-- Registered Buyer --</option>
                                        @foreach($registeredBuyers as $buyer)
                                            <option value="{{ $buyer->id }}" data-phone="{{ $buyer->phone }}">{{ $buyer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-5" style="padding-left: 4px; padding-right: 4px;">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white text-muted border-right-0" style="height: 38px; border-radius: 8px 0 0 8px; border-color: #cbd5e1; font-size: 11px;">
                                                <i class="fas fa-phone-alt"></i>
                                            </span>
                                        </div>
                                        <input type="text" id="matrix_phone_number" class="form-control border-left-0" placeholder="Phone" style="height: 38px; border-radius: 0 8px 8px 0; border-color: #cbd5e1; font-size: 12.5px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-12">
                        <button type="button" class="btn btn-primary btn-block font-weight-bold shadow-sm" id="btn-load-matrix" style="height: 38px; border-radius: 8px;">
                            <i class="fas fa-sync-alt mr-2"></i>
                            <span>Load Matrix Grid</span>
                        </button>
                    </div>
                </div>

                {{-- Row 2: Product Search & Category Filter --}}
                <div class="row align-items-center pt-3 border-top">
                    <div class="col-lg-6 col-md-6 col-12 mb-3 mb-md-0">
                        <label class="small text-muted font-weight-bold mb-1 d-block">
                            <i class="fas fa-search text-primary mr-1"></i> Search Catalog Products
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0 text-muted" style="height: 38px; border-radius: 8px 0 0 8px; border-color: #cbd5e1; font-size: 12px;">
                                    <i class="fas fa-search text-primary"></i>
                                </span>
                            </div>
                            <input type="text" id="matrix-search-input" class="form-control border-left-0" placeholder="Search by product name, SKU, or barcode..." style="height: 38px; border-radius: 0 8px 8px 0; border-color: #cbd5e1; font-size: 13px;">
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 col-12">
                        <label class="small text-muted font-weight-bold mb-1 d-block">
                            <i class="fas fa-tags text-success mr-1"></i> Product Category
                        </label>
                        <select id="matrix-category-filter" class="form-control select2 matrix-select2 w-100">
                            <option value="">-- All Categories --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Interactive Matrix Table --}}
        <div class="card border-0 shadow-sm" style="border-radius: 14px; overflow: hidden; background: #ffffff;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-th text-primary mr-2" style="font-size: 15px;"></i>
                    <strong class="text-dark" style="font-size: 15px;">Customer Stock Availability Matrix</strong>
                </div>
                <div id="matrix-selected-account-label">
                    <span class="badge badge-light border text-muted px-3 py-1 font-weight-normal" style="font-size: 12px;">
                        <i class="fas fa-info-circle text-info mr-1"></i> Select an account above and click Load Matrix Grid
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle w-100 mb-0" id="matrix-table" style="font-size: 13px;">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 38%;">Product & Category</th>
                                <th style="width: 18%; text-align: center;">Physical Warehouse Stock</th>
                                <th style="width: 20%; text-align: center;">Active Override Status</th>
                                <th style="width: 24%; text-align: right;">1-Click Status Override</th>
                            </tr>
                        </thead>
                        <tbody id="matrix-table-body">
                            <tr>
                                <td colspan="4" class="text-center py-5 bg-white">
                                    <div class="py-4 text-muted">
                                        <i class="fas fa-hand-pointer fa-3x mb-3 opacity-50 text-primary"></i>
                                        <div class="h6 font-weight-bold text-dark">No Account Selected</div>
                                        <p class="small text-muted mb-0">Select a Company, Outlet, or Buyer above and click <strong>"Load Matrix Grid"</strong> to begin.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Immediate zero-flash tab sync script on page reload --}}
    <script>
        (function() {
            var savedTab = localStorage.getItem('b2b_stock_rules_tab');
            var hash = window.location.hash;
            if (hash === '#matrix' || (!hash && savedTab === 'matrix')) {
                var tableWrap = document.getElementById('view-mode-table-wrapper');
                var matrixWrap = document.getElementById('view-mode-matrix-wrapper');
                var btnTable = document.getElementById('btn-mode-table');
                var btnMatrix = document.getElementById('btn-mode-matrix');
                if (tableWrap) tableWrap.style.display = 'none';
                if (matrixWrap) matrixWrap.style.display = 'block';
                if (btnTable) btnTable.classList.remove('active');
                if (btnMatrix) btnMatrix.classList.add('active');
            }
        })();
    </script>
</section>

{{-- ==================================================== --}}
{{-- SLIDE-OVER DRAWER: CREATE / EDIT CUSTOMER STOCK RULE --}}
{{-- (Hidden with inline display: none to guarantee ZERO flash on page refresh) --}}
{{-- ==================================================== --}}
<div class="audit-drawer-backdrop" id="b2bRuleDrawerBackdrop" style="display: none;"></div>
<div class="audit-drawer" id="b2bRuleDrawer" style="display: none; width: min(680px, 95vw);">
    <div class="drawer-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <div class="mr-3 p-2 rounded-circle" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                <i class="fas fa-shield-alt font-size-18"></i>
            </div>
            <div>
                <h5 class="drawer-title font-weight-bold text-dark mb-0" id="b2bRuleDrawerTitle" style="font-size: 16px;">Configure Customer Stock Rule</h5>
                <small class="text-muted">Set specific stock availability overrides for this product and account</small>
            </div>
        </div>
        <button type="button" class="drawer-close-btn" id="b2bRuleDrawerClose" aria-label="Close Drawer" title="Close Drawer">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <form id="form-manage-rule" class="d-flex flex-column" style="flex: 1; min-height: 0; overflow: hidden;">
        @csrf
        <input type="hidden" name="rule_id" id="modal_rule_id" value="">

        <div class="drawer-body p-4 bg-light" style="flex: 1; overflow-y: auto; background: #fafbfe !important;">
            {{-- 1. Product Selection (Select2 AJAX Remote Search) --}}
            <div class="form-group mb-3">
                <label class="font-weight-bold text-dark small mb-1">
                    1. Select Catalog Product <span class="text-danger">*</span>
                </label>
                <select name="product_id" id="modal_product_id" class="form-control" style="width: 100%;" required>
                    <option value="">Search by product name, SKU or barcode...</option>
                </select>
            </div>

            {{-- 2. Target Scope Segmented Control --}}
            <div class="form-group mb-3">
                <label class="font-weight-bold text-dark small mb-1">
                    2. Target Customer / Account Scope <span class="text-danger">*</span>
                </label>
                <div class="btn-group btn-group-toggle w-100 mb-2" data-toggle="buttons" id="modal-scope-pills">
                    <label class="btn btn-sm active font-weight-bold flex-fill" data-target="company">
                        <input type="radio" name="target_scope" value="company" checked autocomplete="off">
                        <i class="fas fa-building mr-1"></i> Company
                    </label>
                    <label class="btn btn-sm font-weight-bold flex-fill" data-target="outlet">
                        <input type="radio" name="target_scope" value="outlet" autocomplete="off">
                        <i class="fas fa-store mr-1"></i> Outlet (23 Branches)
                    </label>
                    <label class="btn btn-sm font-weight-bold flex-fill" data-target="phone">
                        <input type="radio" name="target_scope" value="phone" autocomplete="off">
                        <i class="fas fa-phone mr-1"></i> Buyer / Phone
                    </label>
                </div>

                {{-- Scope Field 1: Company --}}
                <div class="modal-scope-container" id="modal-box-company">
                    <select name="company_id" id="modal_company_id" class="form-control select2 modal-select2" style="width: 100%;">
                        <option value="">-- Choose Target Company --</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Scope Field 2: Outlet (23 retail branches) --}}
                <div class="modal-scope-container" id="modal-box-outlet" style="display: none;">
                    <select name="outlet_id" id="modal_outlet_id" class="form-control select2 modal-select2" style="width: 100%;">
                        <option value="">-- Choose Retail Branch Outlet --</option>
                        @foreach($outlets as $outlet)
                            <option value="{{ $outlet->id }}">{{ $outlet->name }} [{{ $outlet->code }}]</option>
                        @endforeach
                    </select>
                </div>

                {{-- Scope Field 3: Buyer & Phone --}}
                <div class="modal-scope-container" id="modal-box-phone" style="display: none;">
                    <div class="row no-gutters">
                        <div class="col-7 pr-1">
                            <select name="user_id" id="modal_user_id" class="form-control select2 modal-select2" style="width: 100%;">
                                <option value="">-- Registered Buyer --</option>
                                @foreach($registeredBuyers as $buyer)
                                    <option value="{{ $buyer->id }}" data-phone="{{ $buyer->phone }}">{{ $buyer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-5 pl-1">
                            <input type="text" name="phone_number" id="modal_phone_number" class="form-control" placeholder="Buyer Phone / ID" style="height: 38px; border-radius: 8px; border-color: #cbd5e1; font-size: 13px;">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Availability Rule Mode --}}
            <div class="form-group mb-3">
                <label class="font-weight-bold text-dark small mb-1">
                    3. Availability Override Policy <span class="text-danger">*</span>
                </label>
                <select name="visibility_mode" id="modal_visibility_mode" class="form-control select2 modal-select2 font-weight-bold" style="width: 100%;" required>
                    <option value="force_in_stock">🟢 Priority Available (In-Stock for this Buyer)</option>
                    <option value="force_out_of_stock">🔴 Restricted (Out of Stock for this Buyer)</option>
                    <option value="hide_product">🚫 Catalog Exclusion (Hide Product Completely)</option>
                </select>
                <small class="text-muted d-block mt-1">
                    Priority Available overrides zero warehouse stock; Restricted blocks booking regardless of actual inventory.
                </small>
            </div>

            {{-- 4. Reason / Notes --}}
            <div class="form-group mb-0">
                <label class="font-weight-bold text-dark small mb-1">4. Reason / Contract Reference (Optional)</label>
                <input type="text" name="notes" id="modal_notes" class="form-control" placeholder="e.g. VIP contract reserved allocation / Regional distributor restriction" style="border-radius: 8px; height: 38px; border-color: #cbd5e1; font-size: 13px;">
            </div>
        </div>

        <div class="drawer-footer bg-white py-3 px-4 border-top d-flex justify-content-between align-items-center" style="flex-shrink: 0;">
            <button type="button" class="btn btn-secondary font-weight-bold px-3 py-2" id="btn-cancel-drawer" style="border-radius: 8px;">
                Cancel
            </button>
            <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2 shadow-sm" id="btn-save-modal-rule" style="border-radius: 8px;">
                <i class="fas fa-check mr-1"></i> Save Stock Rule
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
{{ $dataTable->scripts(attributes: ['type' => 'module']) }}

<script>
    $(document).ready(function() {
        // Initialize standard Select2 on filters
        $('.filter-select, .matrix-select2').select2({ width: '100%' });

        // Remote Product Search Select2 for Drawer
        $('#modal_product_id').select2({
            dropdownParent: $('#b2bRuleDrawer'),
            width: '100%',
            placeholder: 'Search product by name, SKU or barcode...',
            minimumInputLength: 1,
            ajax: {
                url: "{{ route('admin.b2b-stock-rules.search-products') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return { results: data };
                },
                cache: true
            },
            templateResult: function(product) {
                if (product.loading) return product.text;
                return $(`
                    <div class="d-flex align-items-center py-1">
                        <img src="${product.thumb}" class="rounded mr-2 border" style="width: 36px; height: 36px; object-fit: cover;" onerror="this.onerror=null; this.src='{{ asset('uploads/no-image.svg') }}';">
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 12.5px;">${product.name}</div>
                            <div class="text-muted small" style="font-size: 11px;">SKU: ${product.sku} &bull; ${product.category}</div>
                        </div>
                    </div>
                `);
            },
            templateSelection: function(product) {
                return product.name ? (product.name + ' [' + product.sku + ']') : product.text;
            }
        });

        // -------------------------------------------------------------
        // Enterprise Drawer Functions (Zero Flash on Refresh)
        // -------------------------------------------------------------
        function openRuleDrawer(rule = null) {
            $('#form-manage-rule')[0].reset();
            $('#modal_rule_id').val('');
            $('#modal_product_id').val(null).trigger('change');
            $('#b2bRuleDrawer .modal-select2').val('').trigger('change');

            if (rule) {
                $('#b2bRuleDrawerTitle').text('Edit Customer Stock Rule #' + rule.id);
                $('#modal_rule_id').val(rule.id);

                let option = new Option(rule.product_name, rule.product_id, true, true);
                $('#modal_product_id').empty().append(option).trigger('change');

                $('#modal-scope-pills label[data-target="' + rule.target_type + '"]').click();
                if (rule.target_type === 'company') {
                    $('#modal_company_id').val(rule.company_id).trigger('change');
                } else if (rule.target_type === 'outlet') {
                    $('#modal_outlet_id').val(rule.outlet_id).trigger('change');
                } else {
                    $('#modal_user_id').val(rule.user_id).trigger('change');
                    $('#modal_phone_number').val(rule.phone_number);
                }

                $('#modal_visibility_mode').val(rule.visibility_mode).trigger('change');
                $('#modal_notes').val(rule.notes);
            } else {
                $('#b2bRuleDrawerTitle').text('Configure Customer Stock Rule');
                $('#modal-scope-pills label[data-target="company"]').click();
            }

            // Zero-flash display transition
            $('#b2bRuleDrawer').css('display', 'flex');
            $('#b2bRuleDrawerBackdrop').css('display', 'block');
            void $('#b2bRuleDrawer')[0].offsetWidth;

            $('body').addClass('modal-open');
            $('#b2bRuleDrawerBackdrop').addClass('is-active');
            $('#b2bRuleDrawer').addClass('is-active');
        }

        function closeRuleDrawer() {
            $('#b2bRuleDrawer').removeClass('is-active');
            $('#b2bRuleDrawerBackdrop').removeClass('is-active');
            $('body').removeClass('modal-open');
            setTimeout(function() {
                if (!$('#b2bRuleDrawer').hasClass('is-active')) {
                    $('#b2bRuleDrawer').css('display', 'none');
                    $('#b2bRuleDrawerBackdrop').css('display', 'none');
                }
            }, 300);
        }

        $('#btn-open-create-drawer').on('click', function() {
            openRuleDrawer();
        });

        $('#b2bRuleDrawerClose, #btn-cancel-drawer, #b2bRuleDrawerBackdrop').on('click', function() {
            closeRuleDrawer();
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#b2bRuleDrawer').hasClass('is-active')) {
                closeRuleDrawer();
            }
        });

        // -------------------------------------------------------------
        // View Mode Switcher (Mode B Table vs Mode A Matrix)
        // -------------------------------------------------------------
        function switchViewMode(mode, updateHistory) {
            if (updateHistory === undefined) updateHistory = true;
            $('.b2b-tab-btn').removeClass('active');
            $('.b2b-tab-btn[data-mode="' + mode + '"]').addClass('active');

            if (mode === 'matrix') {
                $('#view-mode-table-wrapper').hide();
                $('#view-mode-matrix-wrapper').show();
                localStorage.setItem('b2b_stock_rules_tab', 'matrix');
                if (updateHistory && window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', window.location.pathname + '#matrix');
                }
            } else {
                $('#view-mode-matrix-wrapper').hide();
                $('#view-mode-table-wrapper').show();
                localStorage.setItem('b2b_stock_rules_tab', 'table');
                if (updateHistory && window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', window.location.pathname + '#table');
                }
                let table = window.LaravelDataTables['b2b-visibility-table'];
                if (table) table.columns.adjust().responsive.recalc();
            }
        }

        $('.b2b-tab-btn').on('click', function(e) {
            e.preventDefault();
            if ($(this).hasClass('active')) {
                return;
            }
            let mode = $(this).data('mode');
            switchViewMode(mode);
        });

        // Restore active tab on page refresh
        let savedTab = localStorage.getItem('b2b_stock_rules_tab');
        let currentHash = window.location.hash;
        if (currentHash === '#matrix' || (!currentHash && savedTab === 'matrix')) {
            switchViewMode('matrix', false);
        }

        // -------------------------------------------------------------
        // Instant Server-Side Filter Engine
        // -------------------------------------------------------------
        $('.filter-select').on('change', function() {
            let table = window.LaravelDataTables['b2b-visibility-table'];
            if (table) {
                let params = $('#filter-form').serialize();
                table.ajax.url("{{ route('admin.b2b-stock-rules.index') }}?" + params).load();
            }
        });

        $('#btn-reset-filters').on('click', function(e) {
            e.preventDefault();
            $('#filter-form')[0].reset();
            $('.filter-select').val('').trigger('change');
            let table = window.LaravelDataTables['b2b-visibility-table'];
            if (table) {
                table.ajax.url("{{ route('admin.b2b-stock-rules.index') }}").load();
            }
        });

        // -------------------------------------------------------------
        // Drawer Scope Switching
        // -------------------------------------------------------------
        $('#modal-scope-pills label').on('click', function() {
            $('#modal-scope-pills label').removeClass('active');
            $(this).addClass('active');
            let target = $(this).data('target');
            $('.modal-scope-container').hide();
            $('#modal-box-' + target).show();
            $('#modal-box-' + target + ' .modal-select2').select2({
                dropdownParent: $('#b2bRuleDrawer'),
                width: '100%'
            });
        });

        // Auto-fill buyer phone when selecting registered user in drawer
        $('#modal_user_id').on('change', function() {
            let phone = $(this).find(':selected').data('phone');
            if (phone) {
                $('#modal_phone_number').val(phone);
            }
        });

        // Save / Update Rule via AJAX in Drawer
        $('#form-manage-rule').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = $('#btn-save-modal-rule');
            let ruleId = $('#modal_rule_id').val();
            let isUpdate = Boolean(ruleId);

            let url = isUpdate 
                ? "{{ route('admin.b2b-stock-rules.update', ':id') }}".replace(':id', ruleId)
                : "{{ route('admin.b2b-stock-rules.store') }}";
            let method = isUpdate ? 'PUT' : 'POST';

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

            $.ajax({
                url: url,
                method: method,
                data: form.serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Save Stock Rule');
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        closeRuleDrawer();
                        let table = window.LaravelDataTables['b2b-visibility-table'];
                        if (table) table.ajax.reload(null, false);
                        if ($('#view-mode-matrix-wrapper').is(':visible')) {
                            loadCustomerMatrix(1);
                        }
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Save Stock Rule');
                    let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error saving rule';
                    toastr.error(msg);
                }
            });
        });

        // Edit Rule Trigger: Opens Slide-Over Drawer
        $('body').on('click', '.btn-edit-visibility', function() {
            let rule = $(this).data('rule');
            if (!rule) return;
            openRuleDrawer(rule);
        });

        // Delete Rule with Swal.fire
        $('body').on('click', '.btn-delete-rule', function(e) {
            e.preventDefault();
            let ruleId = $(this).data('id');
            if (!ruleId) return;

            Swal.fire({
                title: "Remove Customer Stock Rule?",
                text: "This customer or outlet will immediately revert to standard warehouse inventory.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ef4444",
                cancelButtonColor: "#64748b",
                confirmButtonText: "<i class='fas fa-trash-alt mr-1'></i> Yes, remove it!",
                cancelButtonText: "Cancel",
                reverseButtons: true,
                focusConfirm: false
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = "{{ route('admin.b2b-stock-rules.destroy', ':id') }}".replace(':id', ruleId);
                    $.ajax({
                        url: url,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                toastr.success(res.message);
                                let table = window.LaravelDataTables['b2b-visibility-table'];
                                if (table) table.ajax.reload(null, false);
                                if ($('#view-mode-matrix-wrapper').is(':visible')) {
                                    loadCustomerMatrix(1);
                                }
                            }
                        },
                        error: function(xhr) {
                            toastr.error('Error removing rule');
                        }
                    });
                }
            });
        });

        // -------------------------------------------------------------
        // Mode A: Interactive Customer Matrix Implementation
        // -------------------------------------------------------------
        $('#matrix-scope-group label').on('click', function() {
            $('#matrix-scope-group label').removeClass('active');
            $(this).addClass('active');
            let target = $(this).data('target');
            $('.matrix-scope-box').hide();
            $('#matrix-scope-' + target).show();
            $('#matrix-scope-' + target + ' .matrix-select2').select2({ width: '100%' });
        });

        $('#matrix_user_id').on('change', function() {
            let phone = $(this).find(':selected').data('phone');
            if (phone) {
                $('#matrix_phone_number').val(phone);
            }
        });

        function loadCustomerMatrix(page = 1) {
            let scope = $('#matrix-scope-group input[name="matrix_scope"]:checked').val() || 'company';
            let companyId = $('#matrix_company_id').val();
            let outletId = $('#matrix_outlet_id').val();
            let userId = $('#matrix_user_id').val();
            let phone = $('#matrix_phone_number').val();
            let categoryId = $('#matrix-category-filter').val();
            let search = $('#matrix-search-input').val();

            if (scope === 'company' && !companyId) {
                toastr.warning('Please select a Company first.');
                return;
            }
            if (scope === 'outlet' && !outletId) {
                toastr.warning('Please select an Outlet first.');
                return;
            }
            if (scope === 'phone' && !userId && !phone) {
                toastr.warning('Please enter a phone number or select a buyer.');
                return;
            }

            // Update Label
            let label = '';
            let icon = '';
            if (scope === 'company') {
                label = $('#matrix_company_id option:selected').text();
                icon = '<i class="fas fa-building text-primary mr-1"></i>';
            } else if (scope === 'outlet') {
                label = $('#matrix_outlet_id option:selected').text();
                icon = '<i class="fas fa-store text-info mr-1"></i>';
            } else {
                label = phone || $('#matrix_user_id option:selected').text();
                icon = '<i class="fas fa-phone-alt text-dark mr-1"></i>';
            }
            $('#matrix-selected-account-label').html(`
                <span class="badge px-3 py-1 font-weight-bold" style="background: rgba(103, 119, 239, 0.12); color: #6777ef; border: 1px solid rgba(103, 119, 239, 0.3); border-radius: 20px; font-size: 12px;">
                    ${icon} Active Target: ${label}
                </span>
            `);

            $('#matrix-table-body').html(`
                <tr>
                    <td colspan="4" class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                        <div class="text-muted small">Loading availability matrix...</div>
                    </td>
                </tr>
            `);

            $.ajax({
                url: "{{ route('admin.b2b-stock-rules.matrix-data') }}",
                method: 'GET',
                data: {
                    page: page,
                    target_scope: scope,
                    company_id: companyId,
                    outlet_id: outletId,
                    user_id: userId,
                    phone_number: phone,
                    category_id: categoryId,
                    search: search
                },
                success: function(html) {
                    $('#matrix-table-body').html(html);
                },
                error: function() {
                    $('#matrix-table-body').html(`
                        <tr>
                            <td colspan="4" class="text-center py-4 text-danger">
                                Failed to load matrix. Please try again.
                            </td>
                        </tr>
                    `);
                }
            });
        }

        $('#btn-load-matrix').on('click', function() {
            loadCustomerMatrix(1);
        });

        $('#matrix_company_id, #matrix_outlet_id, #matrix_user_id').on('change', function() {
            if ($(this).val()) {
                loadCustomerMatrix(1);
            }
        });

        $('#matrix-category-filter').on('change', function() {
            loadCustomerMatrix(1);
        });

        $('#btn-matrix-reset-filter').on('click', function(e) {
            e.preventDefault();
            $('#matrix-search-input').val('');
            $('#matrix-category-filter').val('').trigger('change');
            $('#matrix_company_id').val('').trigger('change');
            $('#matrix_outlet_id').val('').trigger('change');
            $('#matrix_user_id').val('').trigger('change');
            $('#matrix_phone_number').val('');
            $('#matrix-selected-account-label').html(`
                <span class="badge badge-light border text-muted px-3 py-1 font-weight-normal" style="font-size: 12px;">
                    <i class="fas fa-info-circle text-info mr-1"></i> Select an account above and click Load Matrix Grid
                </span>
            `);
            $('#matrix-table-body').html(`
                <tr>
                    <td colspan="4" class="text-center py-5 bg-white">
                        <div class="py-4 text-muted">
                            <i class="fas fa-hand-pointer fa-3x mb-3 opacity-50 text-primary"></i>
                            <div class="h6 font-weight-bold text-dark">No Account Selected</div>
                            <p class="small text-muted mb-0">Select a Company, Outlet, or Buyer above and click <strong>"Load Matrix Grid"</strong> to begin.</p>
                        </div>
                    </td>
                </tr>
            `);
        });

        let matrixSearchTimeout;
        $('#matrix-search-input').on('keyup', function() {
            clearTimeout(matrixSearchTimeout);
            matrixSearchTimeout = setTimeout(function() {
                loadCustomerMatrix(1);
            }, 300);
        });

        // Matrix Pagination Click
        $('body').on('click', '.matrix-pagination a', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            if (url) {
                let urlParams = new URLSearchParams(url.split('?')[1] || '');
                let page = urlParams.get('page') || 1;
                loadCustomerMatrix(page);
            }
        });

        // 1-Click Matrix Toggle Action
        $('body').on('click', '.btn-matrix-toggle', function(e) {
            e.preventDefault();
            let btn = $(this);
            let mode = btn.data('mode');
            let group = btn.closest('.matrix-toggle-group');
            let productId = group.data('product-id');

            let scope = $('#matrix-scope-group input[name="matrix_scope"]:checked').val() || 'company';
            let companyId = $('#matrix_company_id').val();
            let outletId = $('#matrix_outlet_id').val();
            let userId = $('#matrix_user_id').val();
            let phone = $('#matrix_phone_number').val();

            btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.b2b-stock-rules.matrix-toggle') }}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: {
                    product_id: productId,
                    target_scope: scope,
                    mode: mode,
                    company_id: companyId,
                    outlet_id: outletId,
                    user_id: userId,
                    phone_number: phone
                },
                success: function(res) {
                    btn.prop('disabled', false);
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        // Update active state in button group
                        group.find('.btn-matrix-toggle').removeClass('active text-white');
                        group.find('[data-mode="force_in_stock"]').removeClass('btn-success').addClass('btn-outline-success');
                        group.find('[data-mode="force_out_of_stock"]').removeClass('btn-danger').addClass('btn-outline-danger');
                        group.find('[data-mode="standard"]').removeClass('btn-secondary').addClass('btn-outline-secondary');

                        if (mode === 'force_in_stock') {
                            btn.removeClass('btn-outline-success').addClass('btn-success text-white font-weight-bold active');
                        } else if (mode === 'force_out_of_stock') {
                            btn.removeClass('btn-outline-danger').addClass('btn-danger text-white font-weight-bold active');
                        } else {
                            btn.removeClass('btn-outline-secondary').addClass('btn-secondary text-white font-weight-bold active');
                        }

                        // Update Status Cell
                        let statusCell = $('#matrix-product-row-' + productId).find('td:eq(2)');
                        if (mode === 'force_in_stock') {
                            statusCell.html(`
                                <span class="badge px-3 py-1 font-weight-bold" style="background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 20px; font-size: 11px;">
                                    <i class="fas fa-check-circle mr-1"></i> Priority In-Stock
                                </span>
                            `);
                        } else if (mode === 'force_out_of_stock') {
                            statusCell.html(`
                                <span class="badge px-3 py-1 font-weight-bold" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 20px; font-size: 11px;">
                                    <i class="fas fa-ban mr-1"></i> Restricted (OOS)
                                </span>
                            `);
                        } else {
                            statusCell.html(`
                                <span class="badge badge-light border text-muted px-2 py-1 font-weight-bold" style="font-size: 11px;">
                                    <i class="fas fa-cube mr-1"></i> Real Warehouse Stock
                                </span>
                            `);
                        }
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    toastr.error('Failed to update status');
                }
            });
        });
    });
</script>
@endpush
