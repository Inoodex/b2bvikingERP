@extends('backend.layouts.master')
@section('title', 'Executive Dashboard')
@section('content')
<section class="section">
    {{-- =========================================================================
         1. CLEAN EXECUTIVE HEADER (NO OUTLET SELECTOR, NO NOISY BADGES)
         ========================================================================= --}}
    <style>
    .executive-header-bar {
        border-bottom: 1px solid #e2e8f0;
        padding-top: 28px;
        padding-bottom: 20px;
        margin-bottom: 24px;
    }
    .executive-kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }
    @media (max-width: 1400px) {
        .executive-kpi-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media (max-width: 991px) {
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
    .period-tab-btn {
        padding: 5px 14px;
        font-weight: 600;
        font-size: 12px;
        border-radius: 6px;
        text-decoration: none !important;
        transition: all 0.15s ease;
    }
    .period-tab-btn.active {
        background: #ffffff;
        color: #0f172a !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .period-tab-btn:not(.active) {
        color: #64748b !important;
    }
    .period-tab-btn:not(.active):hover {
        color: #0f172a !important;
    }
    </style>

    {{-- =========================================================================
         1. INTEGRATED EXECUTIVE COMMAND HEADER (FLUSH, SLEEK, ENTERPRISE GRADE)
         ========================================================================= --}}
    <div class="executive-header-bar d-flex justify-content-between align-items-center flex-wrap" style="gap: 16px;">
        <div>
            <h1 class="font-weight-bold text-dark mb-1" style="font-size: 24px; letter-spacing: -0.025em; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;">
                Executive Dashboard
            </h1>
            <p class="text-muted mb-0" style="font-size: 13px;">
                Consolidated commercial revenue, liquidity position, inventory assets & catalog health across all channels.
            </p>
        </div>

        <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
            {{-- Fiscal Period Selector --}}
            <div class="d-inline-flex align-items-center p-1" style="background: #f1f5f9; border-radius: 8px; border: 1px solid #e2e8f0;">
                <a href="{{ route('admin.dashboard', ['period' => 'this_month']) }}" 
                   class="period-tab-btn {{ ($selectedPeriod ?? '') === 'this_month' ? 'active' : '' }}">
                   This Month
                </a>
                <a href="{{ route('admin.dashboard', ['period' => 'this_quarter']) }}" 
                   class="period-tab-btn {{ ($selectedPeriod ?? '') === 'this_quarter' ? 'active' : '' }}">
                   Quarter
                </a>
                <a href="{{ route('admin.dashboard', ['period' => 'ytd']) }}" 
                   class="period-tab-btn {{ ($selectedPeriod ?? '') === 'ytd' ? 'active' : '' }}">
                   YTD
                </a>
                <a href="{{ route('admin.dashboard', ['period' => 'all']) }}" 
                   class="period-tab-btn {{ ($selectedPeriod ?? 'all') === 'all' ? 'active' : '' }}">
                   All Time
                </a>
            </div>

            {{-- Primary Action Button --}}
            <a href="{{ route('admin.sales-orders.create') }}" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" style="background: #0f172a; color: #ffffff; border-radius: 8px; font-size: 12px; padding: 7px 15px; gap: 6px; border: 1px solid #0f172a; transition: all 0.2s ease;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Sales Order
            </a>
        </div>
    </div>

    {{-- =========================================================================
         2. 5-PILLAR EXECUTIVE KPI GRID (INCLUDES TOTAL PRODUCTS & ENTERPRISE METRICS)
         ========================================================================= --}}
    <div class="executive-kpi-grid">
        {{-- KPI 1: Total Sales Revenue --}}
        <div class="executive-kpi-card">
            <div class="kpi-accent-stripe" style="background: #10b981;"></div>
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.06em; color: #64748b;">
                        Total Sales Revenue
                    </span>
                    <div class="d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 7px; background: #ecfdf5; color: #059669;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    </div>
                </div>
                <h3 class="mb-0 font-weight-bold text-dark" style="font-size: 21px; letter-spacing: -0.02em; font-family: 'Plus Jakarta Sans', sans-serif;">
                    {!! formatWithCurrency($totalRevenue) !!}
                </h3>
            </div>
            <div class="pt-2 mt-2" style="border-top: 1px solid #f1f5f9;">
                <div class="d-flex justify-content-between align-items-center" style="font-size: 11px;">
                    <span class="text-muted" style="font-size: 10.5px;" title="Operating Expenses & COGS: {!! formatWithCurrency($cogs) !!}">Net Profit: <strong class="text-dark">kr. {{ $grossProfit >= 1000000 ? number_format($grossProfit / 1000000, 2) . 'M' : number_format($grossProfit, 0) }}</strong></span>
                    <span class="badge font-weight-bold px-1.5 py-0.5" style="font-size: 9.5px; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 4px;">
                        {{ $grossMarginPct }}% Margin
                    </span>
                </div>
            </div>
        </div>

        {{-- KPI 2: Inventory Asset Valuation --}}
        <div class="executive-kpi-card">
            <div class="kpi-accent-stripe" style="background: #2563eb;"></div>
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.06em; color: #64748b;">
                        Inventory Asset Value
                    </span>
                    <div class="d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 7px; background: #eff6ff; color: #2563eb;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </div>
                </div>
                <h3 class="mb-0 font-weight-bold text-dark" style="font-size: 21px; letter-spacing: -0.02em; font-family: 'Plus Jakarta Sans', sans-serif;">
                    {!! formatWithCurrency($inventoryValuation) !!}
                </h3>
            </div>
            <div class="pt-2 mt-2" style="border-top: 1px solid #f1f5f9;">
                <div class="d-flex justify-content-between align-items-center" style="font-size: 11px;">
                    <span class="text-muted" style="font-size: 10.5px;">Multi-Warehouse</span>
                    <span class="badge font-weight-bold px-1.5 py-0.5 text-muted border" style="font-size: 9.5px; background: #f8fafc; border-radius: 4px;">
                        Asset 1040
                    </span>
                </div>
            </div>
        </div>

        {{-- KPI 3: Total Products (Catalog Master) --}}
        <div class="executive-kpi-card">
            <div class="kpi-accent-stripe" style="background: #7c3aed;"></div>
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.06em; color: #64748b;">
                        Total Products
                    </span>
                    <div class="d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 7px; background: #f5f3ff; color: #7c3aed;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path><path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>
                    </div>
                </div>
                <h3 class="mb-0 font-weight-bold text-dark" style="font-size: 21px; letter-spacing: -0.02em; font-family: 'Plus Jakarta Sans', sans-serif;">
                    {{ number_format($totalCatalogProducts ?? $totalProducts) }}
                </h3>
            </div>
            <div class="pt-2 mt-2" style="border-top: 1px solid #f1f5f9;">
                <div class="d-flex justify-content-between align-items-center" style="font-size: 11px;">
                    <span class="text-muted" style="font-size: 10.5px;">{{ number_format($totalActiveProducts) }} Active Products</span>
                    <a href="{{ route('admin.products.index') }}" class="badge font-weight-bold px-1.5 py-0.5 border text-decoration-none" style="font-size: 9.5px; background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; border-radius: 4px;">
                        Manage →
                    </a>
                </div>
            </div>
        </div>

        {{-- KPI 4: Accounts Receivable (AR) --}}
        <div class="executive-kpi-card">
            <div class="kpi-accent-stripe" style="background: #d97706;"></div>
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.06em; color: #64748b;">
                        Accounts Receivable
                    </span>
                    <div class="d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 7px; background: #fffbeb; color: #d97706;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><path d="M12 18V6"></path></svg>
                    </div>
                </div>
                <h3 class="mb-0 font-weight-bold text-dark" style="font-size: 21px; letter-spacing: -0.02em; font-family: 'Plus Jakarta Sans', sans-serif;">
                    {!! formatWithCurrency($accountsReceivable) !!}
                </h3>
            </div>
            <div class="pt-2 mt-2" style="border-top: 1px solid #f1f5f9;">
                <div class="d-flex justify-content-between align-items-center" style="font-size: 11px;">
                    <span class="text-muted">Customer Dues</span>
                    <span class="badge font-weight-bold px-1.5 py-0.5 text-muted border" style="font-size: 9.5px; background: #f8fafc; border-radius: 4px;">
                        GL 1030
                    </span>
                </div>
            </div>
        </div>

        {{-- KPI 5: Accounts Payable (AP) --}}
        <div class="executive-kpi-card">
            <div class="kpi-accent-stripe" style="background: #dc2626;"></div>
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.06em; color: #64748b;">
                        Accounts Payable
                    </span>
                    <div class="d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 7px; background: #fef2f2; color: #dc2626;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                    </div>
                </div>
                <h3 class="mb-0 font-weight-bold text-dark" style="font-size: 21px; letter-spacing: -0.02em; font-family: 'Plus Jakarta Sans', sans-serif;">
                    {!! formatWithCurrency($accountsPayable) !!}
                </h3>
            </div>
            <div class="pt-2 mt-2" style="border-top: 1px solid #f1f5f9;">
                <div class="d-flex justify-content-between align-items-center" style="font-size: 11px;">
                    <span class="text-muted">Supplier Bills</span>
                    <span class="badge font-weight-bold px-1.5 py-0.5 text-muted border" style="font-size: 9.5px; background: #f8fafc; border-radius: 4px;">
                        GL 2010
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================================
         3. OPERATIONAL QUICK STATUS RIBBON (COMPACT & CLEAN)
         ========================================================================= --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0 !important; overflow: hidden; background: #ffffff;">
        <div class="row no-gutters">
            {{-- Velocity 1: Orders Pipeline --}}
            <div class="col-xl-3 col-md-6 col-12 p-3 border-right border-bottom border-xl-bottom-0 d-flex align-items-center justify-content-between" style="border-color: #f1f5f9 !important;">
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; border-radius: 8px; background: #eff6ff; color: #2563eb;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                    </div>
                    <div>
                        <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ number_format($ordersInPipeline) }} Orders</div>
                        <small class="text-muted" style="font-size: 11px;">In Fulfillment Pipeline</small>
                    </div>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-light border py-1 px-2.5 font-weight-bold text-primary" style="font-size: 11px; border-radius: 6px;">
                    View
                </a>
            </div>

            {{-- Velocity 2: Managerial Approvals --}}
            <div class="col-xl-3 col-md-6 col-12 p-3 border-right border-bottom border-xl-bottom-0 d-flex align-items-center justify-content-between" style="border-color: #f1f5f9 !important;">
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; border-radius: 8px; background: #fffbeb; color: #d97706;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect><path d="m9 14 2 2 4-4"></path></svg>
                    </div>
                    <div>
                        <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ number_format($pendingApprovals) }} Pending</div>
                        <small class="text-muted" style="font-size: 11px;">Requisitions & Sign-Offs</small>
                    </div>
                </div>
                <a href="{{ route('admin.approvals.index') }}" class="btn btn-sm btn-light border py-1 px-2.5 font-weight-bold text-warning" style="font-size: 11px; border-radius: 6px;">
                    Approve
                </a>
            </div>

            {{-- Velocity 3: Safety Stock Buffer --}}
            <div class="col-xl-3 col-md-6 col-12 p-3 border-right border-bottom border-md-bottom-0 d-flex align-items-center justify-content-between" style="border-color: #f1f5f9 !important;">
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; border-radius: 8px; background: #fef2f2; color: #dc2626;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    </div>
                    <div>
                        <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ number_format($lowStockCount) }} Low Stock</div>
                        <small class="text-muted" style="font-size: 11px;">Reorder Alerts</small>
                    </div>
                </div>
                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-light border py-1 px-2.5 font-weight-bold text-danger" style="font-size: 11px; border-radius: 6px;">
                    Restock
                </a>
            </div>

            {{-- Velocity 4: Retail Network --}}
            <div class="col-xl-3 col-md-6 col-12 p-3 d-flex align-items-center justify-content-between" style="border-color: #f1f5f9 !important;">
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; border-radius: 8px; background: #ecfdf5; color: #059669;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"></path><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"></path><path d="M2 7h20"></path></svg>
                    </div>
                    <div>
                        <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ number_format($activeOutlets) }} Active Outlets</div>
                        <small class="text-muted" style="font-size: 11px;">Physical Store Network</small>
                    </div>
                </div>
                <span class="badge badge-success font-weight-bold px-2 py-1" style="font-size: 10px;">Operational</span>
            </div>
        </div>
    </div>

    {{-- =========================================================================
         4. CHARTS SECTION: SALES REVENUE TREND & CATEGORY DISTRIBUTION
         ========================================================================= --}}
    <div class="row mb-4">
        {{-- Left: 12-Month Sales Revenue Trend --}}
        <div class="col-lg-8 col-12 mb-4 mb-lg-0">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0 !important; background: #ffffff;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4" style="border-bottom: 1px solid #f1f5f9;">
                    <div>
                        <h4 class="font-weight-bold text-dark mb-0 d-flex align-items-center" style="font-size: 15px; gap: 8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                            Sales & Revenue Growth Trend
                        </h4>
                        <small class="text-muted">Monthly completed commercial sales volume</small>
                    </div>
                    <span class="badge border font-weight-bold px-2.5 py-1 text-dark" style="font-size: 11px; background: #f8fafc; border-color: #e2e8f0;">
                        Currency: DKK (kr.)
                    </span>
                </div>
                <div class="card-body p-4">
                    <div style="position: relative; height: 260px;">
                        <canvas id="executiveRevenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Sales by Category Breakdown --}}
        <div class="col-lg-4 col-12">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0 !important; background: #ffffff;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4" style="border-bottom: 1px solid #f1f5f9;">
                    <div>
                        <h4 class="font-weight-bold text-dark mb-0 d-flex align-items-center" style="font-size: 15px; gap: 8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                            Top Category Sales
                        </h4>
                        <small class="text-muted">Commercial sales share</small>
                    </div>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-center mb-3">
                        <div style="position: relative; width: 160px; height: 160px;">
                            <canvas id="executiveCategoryChart"></canvas>
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none;">
                                <div class="font-weight-bold text-dark" style="font-size: 13px; line-height: 1.1;">
                                    kr. {{ $totalCatSales >= 1000000 ? number_format($totalCatSales / 1000000, 2) . 'M' : number_format($totalCatSales / 1000, 0) . 'k' }}
                                </div>
                                <div class="text-muted" style="font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.05em;">Top 5</div>
                            </div>
                        </div>
                    </div>

                    {{-- Category Progress List --}}
                    <div class="w-100">
                        @foreach($categorySales as $cs)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 11.5px;">
                                <span class="font-weight-bold text-dark d-flex align-items-center" style="gap: 6px;">
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $cs->color }};"></span>
                                    {{ $cs->category }}
                                </span>
                                <span class="text-dark font-weight-bold">
                                    {!! formatWithCurrency($cs->total) !!}
                                    <span class="text-muted font-weight-normal ml-1" style="font-size: 10px;">({{ $cs->percentage }}%)</span>
                                </span>
                            </div>
                            <div class="progress" style="height: 4px; border-radius: 9999px; background: #f1f5f9;">
                                <div class="progress-bar" style="width: {{ $cs->percentage }}%; background: {{ $cs->color }}; border-radius: 9999px;"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================================
         5. 🔥 HERO SECTION: BEST SELLER PRODUCTS & TOP CUSTOMERS (ZERO SCROLL)
         ========================================================================= --}}
    <div class="row mb-4">
        {{-- 🔥 Best Seller Products Leaderboard --}}
        <div class="col-lg-6 col-12 mb-4 mb-lg-0">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0 !important; overflow: hidden; background: #ffffff;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4" style="border-bottom: 1px solid #f1f5f9;">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <span class="d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 6px; background: #fef2f2; color: #ef4444;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </span>
                        <div>
                            <h4 class="font-weight-bold text-dark mb-0" style="font-size: 15px;">Best Seller Products</h4>
                            <small class="text-muted">Highest volume items sold across operations</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.reports.best-sellers') }}" class="btn btn-sm btn-outline-danger font-weight-bold py-1 px-3" style="border-radius: 20px; font-size: 11px;">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($bestSellerProducts as $index => $product)
                        @php
                            $rank = $index + 1;
                            $rankBg = match($rank) {
                                1 => 'background: #fef3c7; color: #b45309; border: 1px solid #f59e0b;',
                                2 => 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;',
                                3 => 'background: #ffedd5; color: #c2410c; border: 1px solid #fb923c;',
                                default => 'background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0;'
                            };
                            $imageSrc = !empty($product->image) ? asset($product->image) : asset('uploads/no-image.svg');
                        @endphp
                        <div class="list-group-item d-flex align-items-center justify-content-between p-3 border-bottom" style="transition: all 0.2s ease; gap: 12px; border-color: #f1f5f9 !important;">
                            {{-- Rank Badge & Product Info --}}
                            <div class="d-flex align-items-center min-w-0" style="gap: 12px; flex: 1;">
                                {{-- Rank Badge --}}
                                <div class="font-weight-bold d-flex align-items-center justify-content-center shadow-none flex-shrink-0" style="width: 26px; height: 26px; border-radius: 50%; font-size: 11.5px; {{ $rankBg }}">
                                    {{ $rank }}
                                </div>

                                {{-- Product Thumbnail --}}
                                <img src="{{ $imageSrc }}" onerror="this.onerror=null; this.src='{{ asset('uploads/no-image.svg') }}';" alt="{{ $product->product_name }}" class="flex-shrink-0" style="width: 44px; height: 44px; border-radius: 8px; object-fit: contain; background: #f8fafc; border: 1px solid #e2e8f0; padding: 2px;">

                                {{-- Title & Meta --}}
                                <div class="min-w-0" style="flex: 1;">
                                    <div class="font-weight-bold text-dark text-truncate" title="{{ $product->product_name }}" style="font-size: 13.5px; line-height: 1.3;">
                                        {{ $product->product_name }}
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap mt-1" style="gap: 6px;">
                                        <span class="badge badge-light border text-muted" style="font-size: 10px; padding: 1.5px 6px; font-weight: 600;">
                                            {{ $product->category ?: 'General' }}
                                        </span>
                                        @if(($product->current_stock ?? 0) > 10)
                                            <span class="badge badge-success font-weight-bold px-2 py-0.5" style="font-size: 9.5px; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                                                <i class="fas fa-check-circle mr-1"></i>{{ number_format($product->current_stock) }} in stock
                                            </span>
                                        @else
                                            <span class="badge badge-danger font-weight-bold px-2 py-0.5" style="font-size: 9.5px; background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;">
                                                <i class="fas fa-exclamation-circle mr-1"></i>Only {{ number_format($product->current_stock ?? 0) }} left
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Revenue & Times Ordered --}}
                            <div class="text-right flex-shrink-0 pl-2">
                                <div class="font-weight-bold text-dark" style="font-size: 14.5px; letter-spacing: -0.01em;">
                                    {!! formatWithCurrency($product->total_revenue) !!}
                                </div>
                                <div class="text-muted small mt-0.5" style="font-size: 11px;">
                                    {{ $product->times_ordered }} orders
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted font-italic">
                            <p class="mb-0">No commercial sales recorded in this period.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- 👑 Top Customers & Outlets Leaderboard --}}
        <div class="col-lg-6 col-12">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0 !important; overflow: hidden; background: #ffffff;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4" style="border-bottom: 1px solid #f1f5f9;">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <span class="d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 6px; background: #fffbeb; color: #d97706;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </span>
                        <div>
                            <h4 class="font-weight-bold text-dark mb-0" style="font-size: 15px;">Top Customers</h4>
                            <small class="text-muted">Highest purchasing branches and wholesale clients</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.reports.top-customers') }}" class="btn btn-sm btn-outline-warning font-weight-bold py-1 px-3" style="border-radius: 20px; font-size: 11px;">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($topCustomers as $index => $customer)
                        @php
                            $rank = $index + 1;
                            $displayName = optional($customer->user)->outlet_name ?: (optional($customer->user)->name ?? 'Client #' . $customer->user_id);
                            $initials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $displayName), 0, 2)) ?: 'CL';
                            $hasDue = ($customer->due_amount ?? 0) > 0.01;
                        @endphp
                        <div class="list-group-item d-flex align-items-center justify-content-between p-3 border-bottom" style="transition: all 0.2s ease; gap: 12px; border-color: #f1f5f9 !important;">
                            {{-- Rank Badge & Customer Info --}}
                            <div class="d-flex align-items-center min-w-0" style="gap: 12px; flex: 1;">
                                {{-- Rank Number --}}
                                <div class="font-weight-bold text-muted flex-shrink-0" style="width: 20px; font-size: 12.5px;">
                                    #{{ $rank }}
                                </div>

                                {{-- Customer Initials Avatar --}}
                                <div class="d-flex align-items-center justify-content-center font-weight-bold text-white flex-shrink-0" style="width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, #1e293b 0%, #334155 100%); font-size: 12.5px; letter-spacing: 0.5px; border: 1px solid #cbd5e1;">
                                    {{ $initials }}
                                </div>

                                {{-- Name & Contact Details --}}
                                <div class="min-w-0" style="flex: 1;">
                                    <div class="font-weight-bold text-dark text-truncate" title="{{ $displayName }}" style="font-size: 13.5px; line-height: 1.3;">
                                        <a href="{{ route('admin.reports.orders', ['user_id' => $customer->user_id]) }}" class="text-dark hover-primary" style="text-decoration: none;">
                                            {{ $displayName }}
                                        </a>
                                    </div>
                                    <div class="d-flex align-items-center text-muted small mt-0.5" style="font-size: 11px; gap: 6px;">
                                        @if(optional($customer->user)->phone)
                                            <span><i class="fas fa-phone mr-1"></i>{{ $customer->user->phone }}</span>
                                            <span>&bull;</span>
                                        @endif
                                        <span class="badge badge-light border text-muted px-1.5 py-0.5" style="font-size: 9.5px;">
                                            {{ $customer->total_orders }} orders
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Spend Value & Outstanding Due Status --}}
                            <div class="text-right flex-shrink-0 pl-2">
                                <div class="font-weight-bold text-dark" style="font-size: 14.5px; letter-spacing: -0.01em;">
                                    {!! formatWithCurrency($customer->total_value) !!}
                                </div>
                                <div class="mt-1">
                                    @if($hasDue)
                                        <span class="badge font-weight-bold px-2 py-0.5" style="font-size: 9.5px; background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;">
                                            kr. {{ number_format($customer->due_amount, 2) }} due
                                        </span>
                                    @else
                                        <span class="badge font-weight-bold px-2 py-0.5" style="font-size: 9.5px; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                                            <i class="fas fa-check-circle mr-1"></i>Cleared
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted font-italic">
                            <p class="mb-0">No customer transactions recorded in this period.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================================
         6. RECENT COMMERCIAL ORDERS STREAM
         ========================================================================= --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0 !important; overflow: hidden; background: #ffffff;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4" style="border-bottom: 1px solid #f1f5f9;">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <span class="d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 6px; background: #eff6ff; color: #2563eb;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </span>
                        <div>
                            <h4 class="font-weight-bold text-dark mb-0" style="font-size: 15px;">Recent Orders</h4>
                            <small class="text-muted">Live commercial sales orders placed across outlets and web portal</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary font-weight-bold py-1 px-3" style="border-radius: 20px; font-size: 11px;">
                        View All Orders
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive mb-0">
                        <table class="table table-hover mb-0" style="font-size: 13px;">
                            <thead class="bg-light text-muted text-uppercase" style="font-size: 10.5px; letter-spacing: 0.05em;">
                                <tr>
                                    <th class="pl-4 py-3" style="width: 140px;">Order No</th>
                                    <th class="py-3">Customer / Outlet</th>
                                    <th class="py-3 text-center">Placed At</th>
                                    <th class="py-3 text-right">Order Total</th>
                                    <th class="py-3 text-center">Fulfillment Status</th>
                                    <th class="py-3 text-right pr-4" style="width: 100px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                @php
                                    $orderStatus = strtolower((string)$order->status);
                                    $statusBadge = match($orderStatus) {
                                        'completed' => 'badge-success',
                                        'approved' => 'badge-info',
                                        'processing' => 'badge-primary',
                                        'credit_hold' => 'badge-danger',
                                        'cancelled', 'rejected' => 'badge-secondary',
                                        default => 'badge-warning'
                                    };
                                    $clientName = optional($order->user)->outlet_name ?: (optional($order->user)->name ?? 'Walk-in Client');
                                @endphp
                                <tr>
                                    <td class="pl-4 font-weight-bold">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="text-primary font-weight-bold" style="text-decoration: none;">
                                            {{ $order->order_no }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold text-dark">{{ $clientName }}</span>
                                    </td>
                                    <td class="text-center text-muted">
                                        {{ $order->created_at?->format('d M Y, H:i') ?? 'N/A' }}
                                    </td>
                                    <td class="text-right font-weight-bold text-dark">
                                        {!! formatWithCurrency($order->total_amount) !!}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $statusBadge }} px-2 py-1 font-weight-bold" style="font-size: 10.5px; text-transform: uppercase;">
                                            {{ str_replace('_', ' ', $order->status) }}
                                        </span>
                                    </td>
                                    <td class="text-right pr-4">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-light border py-1 px-2 font-weight-bold text-dark" style="font-size: 11px; border-radius: 6px;">
                                            Details
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted font-italic">No commercial orders found in this period.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Sales & Revenue Growth Trend (Institutional Area Chart)
    const revCtx = document.getElementById('executiveRevenueChart');
    if (revCtx) {
        const revMonths = @json($salesMonths);
        const revData = @json($salesRevenueTrend);

        const ctx2d = revCtx.getContext('2d');
        const gradient = ctx2d.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.25)');
        gradient.addColorStop(1, 'rgba(79, 70, 229, 0.00)');

        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: revMonths.length ? revMonths : ['No Data'],
                datasets: [{
                    label: 'Sales Revenue (DKK)',
                    data: revData.length ? revData : [0],
                    backgroundColor: gradient,
                    borderColor: '#4f46e5',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#4f46e5',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.32,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    xAxes: [{
                        gridLines: { display: false },
                        ticks: { fontColor: '#64748b', fontSize: 11, fontStyle: 'bold' }
                    }],
                    yAxes: [{
                        gridLines: { color: '#f1f5f9', zeroLineColor: '#e2e8f0', borderDash: [2, 2] },
                        ticks: {
                            fontColor: '#64748b',
                            fontSize: 11,
                            callback: function (value) {
                                if (value >= 1000000) return 'kr. ' + (value / 1000000).toFixed(1) + 'M';
                                if (value >= 1000) return 'kr. ' + (value / 1000).toFixed(0) + 'k';
                                return 'kr. ' + value;
                            }
                        }
                    }]
                },
                tooltips: {
                    backgroundColor: 'rgba(15, 23, 42, 0.92)',
                    titleFontSize: 12,
                    bodyFontSize: 12,
                    xPadding: 10,
                    yPadding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function (tooltipItem) {
                            return ' Revenue: kr. ' + Number(tooltipItem.yLabel).toLocaleString('da-DK', { minimumFractionDigits: 2 });
                        }
                    }
                }
            }
        });
    }

    // 2. Top Category Sales Share (Modern Doughnut)
    const catCtx = document.getElementById('executiveCategoryChart');
    if (catCtx) {
        const catLabels = @json($categoryLabels);
        const catData = @json($categoryTotals);

        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: catLabels.length ? catLabels : ['No Data'],
                datasets: [{
                    data: catData.length ? catData : [1],
                    backgroundColor: [
                        '#4f46e5',
                        '#06b6d4',
                        '#f59e0b',
                        '#10b981',
                        '#ec4899'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 74,
                legend: { display: false },
                tooltips: {
                    backgroundColor: 'rgba(15, 23, 42, 0.92)',
                    titleFontSize: 12,
                    bodyFontSize: 12,
                    xPadding: 8,
                    yPadding: 8,
                    cornerRadius: 8,
                    callbacks: {
                        label: function (tooltipItem, data) {
                            const val = data.datasets[0].data[tooltipItem.index];
                            const label = data.labels[tooltipItem.index] || '';
                            return ' ' + label + ': kr. ' + Number(val).toLocaleString('da-DK', { minimumFractionDigits: 2 });
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
