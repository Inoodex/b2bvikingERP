@extends('backend.layouts.master')

@push('css')
<style>
    .executive-header-bar {
        border-bottom: 1px solid #e2e8f0;
        padding-top: 14px;
        padding-bottom: 18px;
        margin-bottom: 22px;
    }
    .executive-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }
    @media (max-width: 1200px) {
        .executive-kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 576px) {
        .executive-kpi-grid {
            grid-template-columns: 1fr;
        }
    }
    .executive-kpi-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 16px 18px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .executive-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.06);
        border-color: #cbd5e1;
    }
    .kpi-accent-stripe {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
    }
</style>
@endpush

@section('content')
<section class="section">
    {{-- =========================================================================
         1. EXECUTIVE COMMAND HEADER (CLEAN, SLEEK, ENTERPRISE GRADE)
         ========================================================================= --}}
    <div class="executive-header-bar d-flex justify-content-between align-items-center flex-wrap" style="gap: 16px;">
        <div>
            <h1 class="font-weight-bold text-dark mb-1" style="font-size: 24px; letter-spacing: -0.025em; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;">
                Stationery & Supplies Master
            </h1>
            <p class="text-muted mb-0" style="font-size: 13px;">
                Internal warehouse packaging materials, shipping supplies & office consumables inventory tracking.
            </p>
        </div>

        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
            <button type="button" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" data-toggle="modal" data-target="#addItemModal" style="background: #0f172a; color: #ffffff; border-radius: 8px; font-size: 12px; padding: 8px 16px; gap: 6px; border: 1px solid #0f172a; transition: all 0.2s ease;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add Supply Item
            </button>
            <a href="{{ route('admin.stationery-supplies.purchases.create') }}" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" style="background: #059669; color: #ffffff; border-radius: 8px; font-size: 12px; padding: 8px 16px; gap: 6px; border: 1px solid #059669; transition: all 0.2s ease;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                Stock In (Purchase)
            </a>
            <a href="{{ route('admin.stationery-supplies.consumptions.create') }}" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" style="background: #d97706; color: #ffffff; border-radius: 8px; font-size: 12px; padding: 8px 16px; gap: 6px; border: 1px solid #d97706; transition: all 0.2s ease;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                Stock Out (Issue)
            </a>
        </div>
    </div>

    <div class="section-body">
        {{-- =========================================================================
             2. EXECUTIVE KPI GRID (4 PILLARS MATCHING DASHBOARD)
             ========================================================================= --}}
        <div class="executive-kpi-grid">
            {{-- KPI 1: Total Supplies --}}
            <div class="executive-kpi-card">
                <div class="kpi-accent-stripe" style="background: #2563eb;"></div>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.06em; color: #64748b;">
                            Total Active Supplies
                        </span>
                        <div class="d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 7px; background: #eff6ff; color: #2563eb;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                        </div>
                    </div>
                    <h3 class="mb-0 font-weight-bold text-dark" style="font-size: 22px; letter-spacing: -0.02em; font-family: 'Plus Jakarta Sans', sans-serif;">
                        {{ number_format($kpis['total_items'] ?? 0) }}
                    </h3>
                </div>
                <div class="pt-2 mt-2" style="border-top: 1px solid #f1f5f9;">
                    <span class="text-muted" style="font-size: 11px;">Tracked internal consumables</span>
                </div>
            </div>

            {{-- KPI 2: Live Stock Valuation --}}
            <div class="executive-kpi-card">
                <div class="kpi-accent-stripe" style="background: #10b981;"></div>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.06em; color: #64748b;">
                            Live Stock Valuation
                        </span>
                        <div class="d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 7px; background: #ecfdf5; color: #059669;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                        </div>
                    </div>
                    <h3 class="mb-0 font-weight-bold text-success" style="font-size: 22px; letter-spacing: -0.02em; font-family: 'Plus Jakarta Sans', sans-serif;">
                        {!! formatWithCurrency($kpis['total_valuation'] ?? 0) !!}
                    </h3>
                </div>
                <div class="pt-2 mt-2" style="border-top: 1px solid #f1f5f9;">
                    <span class="text-muted" style="font-size: 11px;">Weighted Average Cost basis</span>
                </div>
            </div>

            {{-- KPI 3: Reorder Alerts --}}
            <div class="executive-kpi-card">
                <div class="kpi-accent-stripe" style="background: #f59e0b;"></div>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.06em; color: #64748b;">
                            Reorder Alerts
                        </span>
                        <div class="d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 7px; background: #fffbeb; color: #d97706;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        </div>
                    </div>
                    <h3 class="mb-0 font-weight-bold text-danger" style="font-size: 22px; letter-spacing: -0.02em; font-family: 'Plus Jakarta Sans', sans-serif;">
                        {{ number_format($kpis['low_stock_count'] ?? 0) }}
                    </h3>
                </div>
                <div class="pt-2 mt-2" style="border-top: 1px solid #f1f5f9;">
                    <span class="text-muted" style="font-size: 11px;">{{ number_format($kpis['out_of_stock_count'] ?? 0) }} items depleted to 0</span>
                </div>
            </div>

            {{-- KPI 4: Month Consumption Expense --}}
            <div class="executive-kpi-card">
                <div class="kpi-accent-stripe" style="background: #ef4444;"></div>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.06em; color: #64748b;">
                            This Month's Consumption
                        </span>
                        <div class="d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 7px; background: #fef2f2; color: #dc2626;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        </div>
                    </div>
                    <h3 class="mb-0 font-weight-bold text-danger" style="font-size: 22px; letter-spacing: -0.02em; font-family: 'Plus Jakarta Sans', sans-serif;">
                        {!! formatWithCurrency($kpis['month_expense'] ?? 0) !!}
                    </h3>
                </div>
                <div class="pt-2 mt-2" style="border-top: 1px solid #f1f5f9;">
                    <span class="text-muted" style="font-size: 11px;">Issued operational packing expense</span>
                </div>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0;">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('admin.stationery-supplies.index') }}" class="form-row align-items-center">
                    <div class="col-md-4 my-1">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="search" class="form-control border-left-0" placeholder="Search by item name or code..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-md-3 my-1">
                        <select name="category_id" class="form-control">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 my-1">
                        <select name="status" class="form-control">
                            <option value="">All Health Statuses</option>
                            <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>⚠️ Low Stock (≤ Min Alert)</option>
                            <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>🚫 Out of Stock (0 pcs)</option>
                            <option value="healthy" {{ request('status') == 'healthy' ? 'selected' : '' }}>✅ Adequate / Healthy</option>
                        </select>
                    </div>

                    <div class="col-md-2 my-1 d-flex" style="gap: 5px;">
                        <button type="submit" class="btn font-weight-bold flex-fill shadow-sm" style="background: #0f172a; color: #fff; border-radius: 8px;">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.stationery-supplies.index') }}" class="btn btn-light border" title="Reset Filters" style="border-radius: 8px;">
                            <i class="fas fa-undo text-muted"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Items Master Table --}}
        <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 15px;">
                    <i class="fas fa-boxes mr-2 text-primary"></i>Supplies Inventory Registry
                </h5>
                <span class="badge badge-light border text-muted px-2.5 py-1" style="font-size: 11.5px; border-radius: 6px;">
                    Showing {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} items
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light text-uppercase" style="font-size: 10.5px; letter-spacing: 0.5px; color: #64748b;">
                            <tr>
                                <th class="text-center" style="width: 90px;">Code</th>
                                <th>Supply Item</th>
                                <th>Category</th>
                                <th class="text-center">Current Stock</th>
                                <th class="text-right">Avg Unit Cost</th>
                                <th class="text-right">Valuation</th>
                                <th class="text-center">Min Alert Qty</th>
                                <th class="text-center">Health Status</th>
                                <th class="text-center" style="width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                @php
                                    $status = $item->stock_status;
                                @endphp
                                <tr>
                                    <td class="text-center font-weight-bold">
                                        <span class="badge badge-light border text-dark" style="font-size: 11px; border-radius: 4px;">
                                            {{ $item->item_code }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center" style="gap: 10px;">
                                            <img src="{{ $item->image_url }}" 
                                                 loading="lazy" decoding="async"
                                                 onerror="this.onerror=null;this.src='{{ asset('uploads/no-image.svg') }}';" 
                                                 style="width: 38px; height: 38px; object-fit: contain; border-radius: 6px; background: #f8fafc; padding: 2px;" 
                                                 class="border flex-shrink-0" alt="">
                                            <div class="min-w-0">
                                                <strong class="text-dark d-block text-truncate" style="font-size: 13.5px; line-height: 1.25;" title="{{ $item->name }}">
                                                    {{ $item->name }}
                                                </strong>
                                                <span class="text-muted" style="font-size: 11px;">Unit: <strong>{{ $item->unit_name }}</strong></span>
                                                {{-- Commercial sale badge commented out for pure internal usage --}}
                                                {{-- @if($item->allow_sale) <span class="badge badge-success px-1.5 py-0.5 ml-1" style="font-size: 9.5px;">Saleable</span> @endif --}}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 10.5px; border-radius: 4px;">
                                            <i class="fas fa-tag mr-1 text-muted"></i>{{ $item->category_name }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="font-weight-bold text-dark" style="font-size: 13.5px;">
                                            {{ number_format($item->current_stock) }}
                                        </span>
                                        <small class="text-muted d-block" style="font-size: 10.5px;">{{ $item->unit_name }}</small>
                                    </td>
                                    <td class="text-right font-weight-bold text-dark">
                                        {!! formatWithCurrency($item->unit_cost) !!}
                                    </td>
                                    <td class="text-right font-weight-bold text-primary">
                                        {!! formatWithCurrency($item->total_valuation) !!}
                                    </td>
                                    <td class="text-center text-muted font-weight-bold">
                                        {{ number_format($item->min_alert_qty) }} {{ $item->unit_name }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $status['badge_class'] }} px-2.5 py-1 font-weight-bold shadow-sm" style="font-size: 10.5px; border-radius: 6px;">
                                            <i class="fas {{ $status['icon'] }} mr-1"></i>{{ $status['label'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary edit-item-btn" 
                                                data-id="{{ $item->id }}"
                                                data-name="{{ $item->name }}"
                                                data-category-id="{{ $item->stationery_category_id }}"
                                                data-unit-id="{{ $item->unit_id }}"
                                                data-unit-name="{{ $item->unit_name }}"
                                                data-min-alert="{{ $item->min_alert_qty }}"
                                                data-notes="{{ $item->notes }}"
                                                data-image="{{ $item->image ? asset('storage/' . $item->image) : '' }}"
                                                data-toggle="modal" data-target="#editItemModal" title="Edit Item Details">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            {{-- Commercial sale toggle button commented out for pure internal focus --}}
                                            {{--
                                            <form action="{{ route('admin.stationery-supplies.toggle-sale', $item->id) }}" method="POST" class="d-inline" title="Toggle Commercial Sale">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $item->allow_sale ? 'btn-success' : 'btn-outline-secondary' }}">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </button>
                                            </form>
                                            --}}

                                            <a href="{{ route('admin.stationery-supplies.ledger', ['item_id' => $item->id]) }}" class="btn btn-sm btn-outline-info" title="View Audit Movement Ledger">
                                                <i class="fas fa-history"></i>
                                            </a>

                                            <form action="{{ route('admin.stationery-supplies.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this stationery item?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Archive / Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                        <p class="mb-2 font-weight-bold">No stationery or packaging supplies found.</p>
                                        <button type="button" class="btn btn-sm font-weight-bold" data-toggle="modal" data-target="#addItemModal" style="background: #0f172a; color: #fff; border-radius: 8px;">
                                            <i class="fas fa-plus-circle mr-1"></i> Add Your First Item
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 px-4 border-top flex-wrap" style="gap: 12px;">
                    <div class="text-muted small" style="color: #64748b; font-size: 13px;">
                        Showing {{ $items->firstItem() ?? ($items->total() > 0 ? 1 : 0) }} to {{ $items->lastItem() ?? $items->total() }} of {{ number_format($items->total()) }} entries
                    </div>
                    <div>
                        {{ $items->withQueryString()->links('vendor.pagination.custom-report') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- MODAL: Add New Stationery Item --}}
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.stationery-supplies.store') }}" enctype="multipart/form-data" class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
            @csrf
            <div class="modal-header" style="background: #0f172a; color: #ffffff;">
                <h5 class="modal-title font-weight-bold" style="font-size: 16px;"><i class="fas fa-plus-circle mr-2 text-primary"></i>Add New Supply Item</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Item Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Brown Packaging Tape 48mm x 66m" required style="border-radius: 8px;">
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6 mb-3">
                        <label class="font-weight-bold text-dark">Dynamic Category <span class="text-danger">*</span></label>
                        <select name="stationery_category_id" class="form-control" required style="border-radius: 8px;">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-6 mb-3">
                        <label class="font-weight-bold text-dark">Packaging Unit <span class="text-danger">*</span></label>
                        <select name="unit_name" class="form-control" required style="border-radius: 8px;">
                            <optgroup label="Standard Packaging Units">
                                <option value="Pcs" selected>Pcs (Pieces)</option>
                                <option value="Box">Box</option>
                                <option value="Carton">Carton</option>
                                <option value="Roll">Roll (Tape / Film / Stickers)</option>
                                <option value="Ream">Ream (Paper 500 sheets)</option>
                                <option value="Pack">Pack / Bundle</option>
                                <option value="Set">Set</option>
                            </optgroup>
                            @if($units->count() > 0)
                                <optgroup label="Other Warehouse Units">
                                    @foreach($units as $u)
                                        <option value="{{ $u->name }}">{{ $u->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                </div>

                {{-- Image Upload with Live Preview --}}
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Item Image <small class="text-muted font-weight-normal">(Optional for visual identification)</small></label>
                    <input type="file" name="image" id="add_item_image" class="form-control-file border p-2 w-100" accept="image/*" style="border-radius: 8px; background: #f8fafc;">
                    <div id="add_image_preview_wrap" class="mt-2.5 d-none align-items-center p-2 rounded" style="background: #f8fafc; border: 1px dashed #cbd5e1; gap: 12px;">
                        <img id="add_image_preview" src="" style="width: 50px; height: 50px; object-fit: contain; border-radius: 6px; background: #fff;" class="border p-1">
                        <div>
                            <span class="badge badge-success px-2 py-0.5 font-weight-bold" style="font-size: 10px;">Selected Image Preview</span>
                            <small class="d-block text-muted" id="add_image_filename" style="font-size: 11px;"></small>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4 mb-3">
                        <label class="font-weight-bold text-dark">Min Alert Qty <span class="text-danger">*</span></label>
                        <input type="number" step="any" name="min_alert_qty" class="form-control" value="10" required style="border-radius: 8px;" title="Triggers low stock alert when stock falls below this level">
                    </div>

                    <div class="form-group col-md-4 mb-3">
                        <label class="font-weight-bold text-dark">Initial Stock</label>
                        <input type="number" step="any" name="initial_stock" class="form-control" value="0" placeholder="0" style="border-radius: 8px;">
                    </div>

                    <div class="form-group col-md-4 mb-3">
                        <label class="font-weight-bold text-dark">Cost ({{ $settings->currency_icon ?? 'DKK' }})</label>
                        <input type="number" step="0.01" name="initial_cost" class="form-control" value="0.00" placeholder="0.00" style="border-radius: 8px;">
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold text-dark">Specifications / Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Dimensions, supplier reference code, etc." style="border-radius: 8px;"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5">
                <button type="button" class="btn btn-light border font-weight-bold" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                <button type="submit" class="btn font-weight-bold shadow-sm" style="background: #0f172a; color: #fff; border-radius: 8px;">
                    <i class="fas fa-check mr-1"></i> Save Supply Item
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: Edit Stationery Item --}}
<div class="modal fade" id="editItemModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" id="editItemForm" action="" enctype="multipart/form-data" class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
            @csrf
            @method('PUT')
            <div class="modal-header" style="background: #0f172a; color: #ffffff;">
                <h5 class="modal-title font-weight-bold" style="font-size: 16px;"><i class="fas fa-edit mr-2 text-primary"></i>Edit Supply Item</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Item Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="edit_name" class="form-control" required style="border-radius: 8px;">
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6 mb-3">
                        <label class="font-weight-bold text-dark">Dynamic Category <span class="text-danger">*</span></label>
                        <select name="stationery_category_id" id="edit_category_id" class="form-control" required style="border-radius: 8px;">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-6 mb-3">
                        <label class="font-weight-bold text-dark">Packaging Unit <span class="text-danger">*</span></label>
                        <select name="unit_name" id="edit_unit_name" class="form-control" required style="border-radius: 8px;">
                            <optgroup label="Standard Packaging Units">
                                <option value="Pcs">Pcs (Pieces)</option>
                                <option value="Box">Box</option>
                                <option value="Carton">Carton</option>
                                <option value="Roll">Roll (Tape / Film / Stickers)</option>
                                <option value="Ream">Ream (Paper 500 sheets)</option>
                                <option value="Pack">Pack / Bundle</option>
                                <option value="Set">Set</option>
                            </optgroup>
                            @if($units->count() > 0)
                                <optgroup label="Other Warehouse Units">
                                    @foreach($units as $u)
                                        <option value="{{ $u->name }}">{{ $u->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                </div>

                {{-- Image Update with Live Preview --}}
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Update Item Image <small class="text-muted font-weight-normal">(Optional)</small></label>
                    <input type="file" name="image" id="edit_item_image" class="form-control-file border p-2 w-100" accept="image/*" style="border-radius: 8px; background: #f8fafc;">
                    
                    {{-- Current or Newly Selected Image Preview Box --}}
                    <div id="edit_image_preview_wrap" class="mt-2.5 d-none align-items-center p-2 rounded" style="background: #f8fafc; border: 1px dashed #cbd5e1; gap: 12px;">
                        <img id="edit_image_preview" src="" style="width: 50px; height: 50px; object-fit: contain; border-radius: 6px; background: #fff;" class="border p-1">
                        <div>
                            <span class="badge badge-primary px-2 py-0.5 font-weight-bold" id="edit_image_status_badge" style="font-size: 10px;">Current Image</span>
                            <small class="d-block text-muted" id="edit_image_filename" style="font-size: 11px;"></small>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Min Alert Qty <span class="text-danger">*</span></label>
                    <input type="number" step="any" name="min_alert_qty" id="edit_min_alert" class="form-control" required style="border-radius: 8px;">
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold text-dark">Specifications / Notes</label>
                    <textarea name="notes" id="edit_notes" class="form-control" rows="2" style="border-radius: 8px;"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5">
                <button type="button" class="btn btn-light border font-weight-bold" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                <button type="submit" class="btn font-weight-bold shadow-sm" style="background: #0f172a; color: #fff; border-radius: 8px;">
                    <i class="fas fa-save mr-1"></i> Update Item
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        function handleImagePreview(inputSelector, wrapSelector, imgSelector, nameSelector, badgeSelector, badgeText) {
            $(inputSelector).on('change', function(e) {
                var file = e.target.files && e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(evt) {
                        $(imgSelector).attr('src', evt.target.result);
                        if (nameSelector) $(nameSelector).text(file.name);
                        if (badgeSelector) $(badgeSelector).text(badgeText || 'New Image Preview').removeClass('badge-primary').addClass('badge-success');
                        $(wrapSelector).removeClass('d-none').addClass('d-flex');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        handleImagePreview('#add_item_image', '#add_image_preview_wrap', '#add_image_preview', '#add_image_filename', null, null);
        handleImagePreview('#edit_item_image', '#edit_image_preview_wrap', '#edit_image_preview', '#edit_image_filename', '#edit_image_status_badge', 'New Image Selected');

        $('.edit-item-btn').on('click', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var categoryId = $(this).data('category-id');
            var unitName = $(this).data('unit-name');
            var minAlert = $(this).data('min-alert');
            var notes = $(this).data('notes');
            var imageUrl = $(this).data('image');

            var updateUrl = "{{ route('admin.stationery-supplies.update', ':id') }}".replace(':id', id);
            $('#editItemForm').attr('action', updateUrl);

            $('#edit_name').val(name);
            $('#edit_category_id').val(categoryId);
            $('#edit_unit_name').val(unitName);
            $('#edit_min_alert').val(minAlert);
            $('#edit_notes').val(notes);

            $('#edit_item_image').val(''); // Reset file input
            if (imageUrl) {
                $('#edit_image_preview').attr('src', imageUrl);
                $('#edit_image_status_badge').text('Current Image').removeClass('badge-success').addClass('badge-primary');
                $('#edit_image_filename').text('Existing uploaded image');
                $('#edit_image_preview_wrap').removeClass('d-none').addClass('d-flex');
            } else {
                $('#edit_image_preview_wrap').removeClass('d-flex').addClass('d-none');
            }
        });
    });
</script>
@endpush
