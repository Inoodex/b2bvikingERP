@extends('backend.layouts.master')
@section('title', 'Inventory Reorder Risk & PO Pipeline')

@push('css')
<style>
    /* Section Header */
    .reorder-header {
        background: #ffffff;
        border-radius: 14px;
        padding: 16px 22px;
        margin-top: 8px !important;
        margin-bottom: 20px !important;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .reorder-header-icon {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border: 1px solid rgba(225, 24, 53, 0.35);
        color: #E11835;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: 0 4px 12px rgba(225, 24, 53, 0.15);
    }
    .reorder-header-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }
    .reorder-header-sub {
        font-size: 12px;
        color: #64748b;
        margin-top: 3px;
        line-height: 1.3;
    }

    /* Executive Metric Cards */
    .risk-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .risk-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .risk-icon-pill {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        transition: transform 0.2s ease;
    }
    .risk-stat-card:hover .risk-icon-pill {
        transform: scale(1.06);
    }
    .risk-icon-danger {
        background: rgba(225, 24, 53, 0.1);
        color: #E11835;
        border: 1px solid rgba(225, 24, 53, 0.2);
    }
    .risk-icon-primary {
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        border: 1px solid rgba(37, 99, 235, 0.2);
    }
    .risk-icon-success {
        background: rgba(21, 128, 61, 0.12);
        color: #15803d;
        border: 1px solid rgba(21, 128, 61, 0.25);
    }

    .risk-stat-label {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #64748b;
        margin-bottom: 4px;
        line-height: 1.2;
    }
    .risk-stat-value {
        font-size: 1.55rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }
    .risk-stat-meta {
        margin-top: 6px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }
    .risk-tag {
        font-size: 10.5px;
        font-weight: 700;
        border-radius: 6px;
        padding: 2px 8px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        line-height: 1.4;
    }
    .risk-tag-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .risk-tag-primary {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }
    .risk-tag-success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    /* Modern Risk Scope Switcher ("tab ar moto") */
    .risk-scope-nav {
        background: #f1f5f9;
        padding: 4px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px;
    }
    .risk-scope-btn {
        border: 1px solid transparent !important;
        background: transparent;
        padding: 8px 16px;
        font-size: 12.5px;
        font-weight: 600;
        color: #64748b;
        border-radius: 7px;
        transition: all 0.15s ease;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        line-height: 1.4;
        white-space: nowrap;
    }
    .risk-scope-btn:hover {
        color: #1e293b;
        background: #e2e8f0;
    }
    .risk-scope-btn.active-all {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35) !important;
    }
    .risk-scope-btn.active-danger {
        background-color: #E11835 !important;
        border-color: #E11835 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(225, 24, 53, 0.35) !important;
    }
    .risk-scope-btn.active-success {
        background-color: #15803d !important;
        border-color: #15803d !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(21, 128, 61, 0.35) !important;
    }

    /* Reset Button */
    .btn-filter-reset {
        color: #E11835 !important;
        background-color: #fef2f2 !important;
        border: 1.5px solid #fecaca !important;
        border-radius: 8px !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        height: 38px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        transition: all 0.2s ease !important;
        text-decoration: none !important;
    }
    .btn-filter-reset:hover {
        color: #ffffff !important;
        background-color: #E11835 !important;
        border-color: #E11835 !important;
        box-shadow: 0 4px 12px rgba(225, 24, 53, 0.25) !important;
        transform: translateY(-1px) !important;
    }

    /* Modern Table Styling */
    .reorder-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .reorder-table thead th {
        background-color: #2563EB !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.4px !important;
        padding: 12px 14px !important;
        border: none !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }
    .reorder-table thead th * {
        color: #ffffff !important;
    }
    .reorder-table tbody td {
        padding: 12px 14px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9;
        font-size: 12.5px;
    }
    .reorder-table tbody tr:hover td {
        background: rgba(37, 99, 235, 0.04) !important;
    }
    .reorder-product-thumb {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
</style>
@endpush

@section('content')
    <section class="section">
        {{-- Section Header --}}
        <div class="reorder-header d-flex align-items-center justify-content-between flex-wrap" style="gap: 16px;">
            <div class="d-flex align-items-center">
                <div class="reorder-header-icon mr-3">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h4 class="reorder-header-title mb-0">Inventory Reorder Risk & PO Pipeline</h4>
                    <div class="reorder-header-sub">Real-time stockout alerts, procurement pipeline tracking & automated restock recommendations</div>
                </div>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-light px-3 py-1.5 mb-0" style="border-radius: 8px; font-size: 12px; font-weight: 600; border: 1px solid #e2e8f0;">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-muted"><i class="fas fa-home mr-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}" class="text-muted">Reports</a></li>
                    <li class="breadcrumb-item active text-primary" aria-current="page">Reorder Risk</li>
                </ol>
            </nav>
        </div>

        <div class="section-body">

            {{-- Summary KPI Cards --}}
            <div class="row mb-4">
                <div class="col-lg-4 col-md-4 col-sm-12 mb-3 mb-md-0">
                    <div class="risk-stat-card">
                        <div class="d-flex align-items-center">
                            <div class="risk-icon-pill risk-icon-danger mr-3">
                                <i class="fas fa-ban"></i>
                            </div>
                            <div style="min-width: 0;">
                                <div class="risk-stat-label">Stockout & NOT Ordered</div>
                                <div class="risk-stat-value text-danger">
                                    {{ number_format($totalStockoutsUnordered) }} items
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 mb-3 mb-md-0">
                    <div class="risk-stat-card">
                        <div class="d-flex align-items-center">
                            <div class="risk-icon-pill risk-icon-primary mr-3">
                                <i class="fas fa-truck-loading"></i>
                            </div>
                            <div style="min-width: 0;">
                                <div class="risk-stat-label">Active POs in Pipeline</div>
                                <div class="risk-stat-value text-primary">
                                    {{ number_format($totalActivePOsCount) }} orders
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="risk-stat-card">
                        <div class="d-flex align-items-center">
                            <div class="risk-icon-pill risk-icon-success mr-3">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div style="min-width: 0;">
                                <div class="risk-stat-label">Incoming Units on Order</div>
                                <div class="risk-stat-value text-success">
                                    {{ number_format($totalUnitsInPipeline) }} pcs
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter & Tab Scope --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                    <div class="risk-scope-nav" id="riskTabs">
                        <a class="risk-scope-btn {{ $statusFilter === 'all' ? 'active-all' : '' }}" href="{{ route('admin.reports.reorder-risk', ['status_filter' => 'all']) }}">
                            <i class="fas fa-layer-group"></i>
                            <span>All Inventory Items</span>
                        </a>
                        <a class="risk-scope-btn {{ $statusFilter === 'out_of_stock_not_ordered' ? 'active-danger' : '' }}" href="{{ route('admin.reports.reorder-risk', ['status_filter' => 'out_of_stock_not_ordered']) }}">
                            <i class="fas fa-exclamation-circle text-danger mr-0.5"></i>
                            <span>Out of Stock & NOT Ordered</span>
                        </a>
                        <a class="risk-scope-btn {{ $statusFilter === 'on_order' ? 'active-success' : '' }}" href="{{ route('admin.reports.reorder-risk', ['status_filter' => 'on_order']) }}">
                            <i class="fas fa-shipping-fast text-success mr-0.5"></i>
                            <span>On Order / In Pipeline</span>
                        </a>
                    </div>
                </div>
                <div class="card-body p-3 p-lg-4">
                    <form method="GET" action="{{ route('admin.reports.reorder-risk') }}">
                        <input type="hidden" name="status_filter" value="{{ $statusFilter }}">
                        <div class="row align-items-end">
                            <div class="col-lg-4 col-md-6 col-12 mb-3 mb-lg-0">
                                <label class="small text-muted font-weight-bold mb-1 d-block">
                                    <i class="fas fa-tags text-primary mr-1"></i> Category
                                </label>
                                <select name="category_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">-- All Categories --</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
                                <label class="small text-muted font-weight-bold mb-1 d-block">
                                    <i class="fas fa-building text-info mr-1"></i> Supplier / Vendor
                                </label>
                                <select name="vendor_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">-- All Vendors --</option>
                                    @foreach($vendors as $v)
                                        <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>
                                            {{ $v->shop_name ?? $v->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-8 col-12 mb-3 mb-lg-0">
                                <label class="small text-muted font-weight-bold mb-1 d-block">
                                    <i class="fas fa-search text-muted mr-1"></i> Search Product
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0 text-muted" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1; height: 38px; font-size: 13px;">
                                            <i class="fas fa-search text-primary"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="search" class="form-control border-left-0" placeholder="Search name, code, sku..." value="{{ request('search') }}" style="border-radius: 0 8px 8px 0; border-color: #cbd5e1; height: 38px; font-size: 13px;">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-12">
                                <a href="{{ route('admin.reports.reorder-risk') }}" class="btn btn-filter-reset btn-block" title="Reset all filters">
                                    <i class="fas fa-undo"></i>
                                    <span>Reset</span>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="reorder-table-card card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                    <div class="d-flex align-items-center">
                        <div class="mr-2 p-2 rounded text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(37, 99, 235, 0.1);">
                            <i class="fas fa-boxes" style="font-size: 13px;"></i>
                        </div>
                        <div>
                            <strong class="text-dark" style="font-size: 14.5px;">Inventory Status & Pipeline Tracking</strong>
                            <div class="text-muted" style="font-size: 11px;">Monitor live warehouse balances, pending shipments and vendor fulfillment</div>
                        </div>
                    </div>
                    <span class="badge badge-light border text-muted px-3 py-1 font-weight-normal" style="font-size: 12px; border-radius: 8px;">
                        <i class="fas fa-list-ol mr-1 text-primary"></i> Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ number_format($products->total()) }} items
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table reorder-table table-hover align-middle mb-0 w-100" style="font-size: 13px;">
                            <thead>
                                <tr>
                                    <th class="pl-4 py-3" style="width: 30%;">Product Name & SKU</th>
                                    <th class="py-3" style="width: 14%;">Category</th>
                                    <th class="py-3" style="width: 18%;">Preferred Supplier</th>
                                    <th class="text-center py-3" style="width: 14%;">Warehouse Stock</th>
                                    <th class="py-3" style="width: 18%;">Pipeline Status / Milestone</th>
                                    <th class="text-center pr-4 py-3" style="width: 6%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $noImg = asset('uploads/no-image.svg');
                                @endphp
                                @forelse($products as $p)
                                    @php
                                        $stock = (float) ($p->inventory_stocks_sum_quantity ?? 0);
                                        $activePOs = $p->activePurchaseDetails;
                                        $hasActivePO = $activePOs && $activePOs->isNotEmpty();
                                        $imgSrc = (!empty($p->thumb_image) && $p->thumb_image !== 'null') ? asset(ltrim($p->thumb_image, '/')) : $noImg;
                                    @endphp
                                    <tr class="bg-white">
                                        <td class="pl-4">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $imgSrc }}" onerror="this.onerror=null;this.src='{{ $noImg }}';" alt="" class="reorder-product-thumb mr-3">
                                                <div>
                                                    <strong class="text-dark d-block" style="font-size: 13px;">{{ $p->name }}</strong>
                                                    <small class="text-muted" style="font-size: 11px;">{{ $p->product_number ?? $p->sku ?? ('PROD-'.$p->id) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-weight: 600; font-size: 11px; border-radius: 20px; padding: 4px 10px;">{{ $p->category?->name ?? 'General' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-dark font-weight-600" style="font-size: 12.5px;"><i class="fas fa-store text-muted mr-1"></i>{{ $p->vendor?->shop_name ?? ($p->vendor?->name ?? 'Default Vendor') }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($stock <= 0)
                                                <span class="badge px-2.5 py-1.5 font-weight-bold" style="background: rgba(225, 24, 53, 0.12); color: #E11835; border: 1px solid rgba(225, 24, 53, 0.3); border-radius: 20px; font-size: 11px;">
                                                    <i class="fas fa-times-circle mr-1"></i>0 pcs (Stockout)
                                                </span>
                                            @elseif($stock <= ($p->min_inventory_qty ?? 10))
                                                <span class="badge px-2.5 py-1.5 font-weight-bold" style="background: rgba(245, 158, 11, 0.12); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 20px; font-size: 11px;">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>{{ number_format($stock) }} pcs (Low)
                                                </span>
                                            @else
                                                <span class="badge px-2.5 py-1.5 font-weight-bold" style="background: rgba(16, 185, 129, 0.12); color: #15803d; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 20px; font-size: 11px;">
                                                    <i class="fas fa-check-circle mr-1"></i>{{ number_format($stock) }} pcs
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($hasActivePO)
                                                @foreach($activePOs as $apd)
                                                    @php
                                                        $purchase = $apd->purchase;
                                                        $milestone = $purchase?->milestone_status ?? 'ordered';
                                                        $firstShipment = $purchase?->shipments?->first();
                                                        $eta = $firstShipment?->estimated_delivery_date ? \Carbon\Carbon::parse($firstShipment->estimated_delivery_date)->format('M d') : null;
                                                    @endphp
                                                    <div class="d-inline-block mr-1 mb-1">
                                                        @if($milestone === 'shipped')
                                                            <span class="badge px-2.5 py-1 font-weight-bold" style="background: rgba(37, 99, 235, 0.12); color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.3); border-radius: 20px; font-size: 11px;">
                                                                🚢 Shipped {{ $eta ? '(ETA: '.$eta.')' : '' }}
                                                            </span>
                                                        @elseif($milestone === 'goods_partial')
                                                            <span class="badge px-2.5 py-1 font-weight-bold" style="background: rgba(6, 182, 212, 0.12); color: #0891b2; border: 1px solid rgba(6, 182, 212, 0.3); border-radius: 20px; font-size: 11px;">
                                                                📦 Goods Partial
                                                            </span>
                                                        @elseif($milestone === 'in_production')
                                                            <span class="badge px-2.5 py-1 font-weight-bold" style="background: rgba(245, 158, 11, 0.12); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 20px; font-size: 11px;">
                                                                ⚙️ In Production
                                                            </span>
                                                        @else
                                                            <span class="badge px-2.5 py-1 font-weight-bold" style="background: rgba(100, 116, 139, 0.12); color: #475569; border: 1px solid rgba(100, 116, 139, 0.3); border-radius: 20px; font-size: 11px;">
                                                                📄 On Order (#{{ $purchase?->purchase_no ?? $purchase?->id }})
                                                            </span>
                                                        @endif
                                                        <small class="text-muted d-block mt-0.5" style="font-size: 10px;">{{ number_format($apd->qty) }} pcs on PO</small>
                                                    </div>
                                                @endforeach
                                            @elseif($stock <= 0)
                                                <span class="badge px-2.5 py-1.5 font-weight-bold shadow-sm" style="font-size: 11px; background: #E11835; color: #ffffff; border-radius: 6px;">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i> Out of Stock • NOT Ordered
                                                </span>
                                            @else
                                                <span class="badge px-2.5 py-1 font-weight-bold" style="background: rgba(16, 185, 129, 0.08); color: #15803d; border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 20px; font-size: 11px;">
                                                    <i class="fas fa-check mr-1"></i> Normal Stock
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center pr-4">
                                            <button type="button" 
                                                    class="btn btn-outline-warning btn-sm add-to-basket font-weight-bold shadow-sm" 
                                                    data-id="{{ $p->id }}" 
                                                    data-product-name="{{ $p->name }}"
                                                    data-product-img="{{ $imgSrc }}"
                                                    data-vendor-name="{{ $p->vendor?->shop_name ?? ($p->vendor?->name ?? 'Primary Supplier') }}"
                                                    data-sku="{{ $p->sku ?? ($p->product_number ?? '') }}"
                                                    title="Add to Procurement Basket" 
                                                    style="width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                <i class="fas fa-shopping-basket"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 bg-white">
                                            <div class="py-3 text-muted">
                                                <i class="fas fa-check-circle fa-3x text-success mb-3 opacity-50"></i>
                                                <div class="h6 font-weight-bold text-dark">No Reorder Risks Found</div>
                                                <p class="small text-muted mb-0">All inventory items are currently well-stocked or already have active purchase orders.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3 border-top">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                        <div class="text-muted" style="font-size: 13px; color: #64748b;">
                            Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ number_format($products->total()) }} entries
                        </div>
                        <div>
                            {{ $products->appends(request()->query())->links('vendor.pagination.custom-report') }}
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
        // Reapply button state based on cartStore
        function applyButtonStates() {
            var bookingIds = (window.cartStore && window.cartStore.booking && window.cartStore.booking.ids) 
                ? window.cartStore.booking.ids.map(Number) 
                : [];

            $('.add-to-basket').each(function() {
                var pid = Number($(this).data('id'));
                if (bookingIds.includes(pid)) {
                    $(this).addClass('added btn-success text-white')
                           .removeClass('btn-outline-warning')
                           .html('<i class="fas fa-check"></i>')
                           .attr('title', 'Added to Procurement Basket');
                } else {
                    $(this).removeClass('added btn-success text-white')
                           .addClass('btn-outline-warning')
                           .html('<i class="fas fa-shopping-basket"></i>')
                           .attr('title', 'Add to Procurement Basket');
                }
            });
        }

        // Expose globally so cart_drawer.blade.php can trigger it on item remove/clear
        window.reapplyCartButtonStates = applyButtonStates;

        // Initial sync of basket states and full items from server
        if (window.fetch) {
            fetch("{{ route('admin.cart.all-state') }}")
                .then(function(res) { return res.json(); })
                .then(function(res) {
                    if (res && res.booking) {
                        if (window.cartStore) {
                            window.cartStore.booking = res.booking;
                        }
                        if (window.updateGlobalCartBadges) {
                            window.updateGlobalCartBadges(res.booking.count, undefined);
                        }
                        applyButtonStates();
                    }
                })
                .catch(function() {
                    $.get("{{ route('admin.cart.product-ids') }}", { cart_type: 'booking' }, function(res) {
                        if (res && res.ids && window.cartStore && window.cartStore.booking) {
                            window.cartStore.booking.ids = res.ids.map(Number);
                            applyButtonStates();
                        }
                    });
                });
        } else {
            $.get("{{ route('admin.cart.product-ids') }}", { cart_type: 'booking' }, function(res) {
                if (res && res.ids && window.cartStore && window.cartStore.booking) {
                    window.cartStore.booking.ids = res.ids.map(Number);
                    applyButtonStates();
                }
            });
        }

        // Toggle add to procurement basket
        $(document).on('click', '.add-to-basket', function(e) {
            e.preventDefault();
            var $btn = $(this);
            if ($btn.data('is-busy')) return;
            $btn.data('is-busy', true);

            var productId = Number($btn.data('id'));
            var productName = $btn.data('product-name') || 'Product';
            var productImg = $btn.data('product-img') || "{{ asset('uploads/no-image.svg') }}";
            var vendorName = $btn.data('vendor-name') || 'Primary Supplier';
            var sku = $btn.data('sku') || '';

            var isAlreadyAdded = $btn.hasClass('added');
            var desiredAction = isAlreadyAdded ? 'remove' : 'add';

            // Optimistic Store & UI Update (0ms instant response)
            if (isAlreadyAdded) {
                $btn.removeClass('added btn-success text-white')
                    .addClass('btn-outline-warning')
                    .html('<i class="fas fa-shopping-basket"></i>')
                    .attr('title', 'Add to Procurement Basket');

                if (window.cartStore && window.cartStore.booking) {
                    window.cartStore.booking.ids = window.cartStore.booking.ids.filter(function(id) { return Number(id) !== productId; });
                    window.cartStore.booking.items = window.cartStore.booking.items.filter(function(i) { return Number(i.product_id) !== productId; });
                    window.cartStore.booking.count = window.cartStore.booking.items.length;
                    if (window.updateGlobalCartBadges) {
                        window.updateGlobalCartBadges(window.cartStore.booking.count, undefined);
                    }
                }
            } else {
                $btn.addClass('added btn-success text-white')
                    .removeClass('btn-outline-warning')
                    .html('<i class="fas fa-check"></i>')
                    .attr('title', 'Added to Procurement Basket');

                if (window.cartStore && window.cartStore.booking) {
                    if (!window.cartStore.booking.ids.map(Number).includes(productId)) {
                        window.cartStore.booking.ids.push(productId);
                    }
                    var exists = window.cartStore.booking.items.some(function(i) { return Number(i.product_id) === productId && !i.variant_id; });
                    if (!exists) {
                        window.cartStore.booking.items.push({
                            id: 'temp_' + productId,
                            cart_id: 'temp_' + productId,
                            product_id: productId,
                            variant_id: null,
                            variant_name: '',
                            product_name: productName,
                            thumb_image: productImg,
                            vendor_name: vendorName,
                            sku: sku,
                            price: 0,
                            quantity: 1
                        });
                    }
                    window.cartStore.booking.count = window.cartStore.booking.items.length;
                    if (window.updateGlobalCartBadges) {
                        window.updateGlobalCartBadges(window.cartStore.booking.count, undefined);
                    }
                }
            }

            $.ajax({
                url: "{{ route('admin.cart.add') }}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    product_id: productId,
                    cart_type: 'booking',
                    action: desiredAction,
                    quantity: 1
                },
                success: function(response) {
                    $btn.data('is-busy', false);
                    if (response.success) {
                        if (response.action === 'added' && response.item && window.cartStore && window.cartStore.booking) {
                            var idx = window.cartStore.booking.items.findIndex(function(i) { return Number(i.product_id) === productId && !i.variant_id; });
                            if (idx !== -1) {
                                window.cartStore.booking.items[idx] = response.item;
                            } else {
                                window.cartStore.booking.items.push(response.item);
                            }
                        }

                        if (window.toastr) {
                            if (response.action === 'added') {
                                toastr.success(response.message || 'Added to Procurement basket');
                            } else {
                                toastr.info(response.message || 'Removed from Procurement basket');
                            }
                        }

                        if (window.updateGlobalCartBadges && response.count !== undefined) {
                            window.updateGlobalCartBadges(response.count, undefined);
                        }
                    }
                    applyButtonStates();
                },
                error: function(xhr) {
                    $btn.data('is-busy', false);
                    if (window.toastr) toastr.error('Failed to update procurement basket');
                    if (window.fetch) {
                        fetch("{{ route('admin.cart.all-state') }}")
                            .then(function(res) { return res.json(); })
                            .then(function(res) {
                                if (res && res.booking && window.cartStore) {
                                    window.cartStore.booking = res.booking;
                                    applyButtonStates();
                                }
                            });
                    }
                }
            });
        });
    });
</script>
@endpush
