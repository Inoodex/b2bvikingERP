@extends('backend.layouts.master')
@section('title', $settings->site_name . ' | Reports')

@push('css')
<style>
    :root {
        --pp-amber: #d4a24e;
        --pp-amber-bright: #ecc78b;
        --pp-amber-deep: #b8852a;
        --pp-amber-soft: rgba(212, 162, 78, 0.08);
        --pp-border: rgba(11, 17, 32, 0.07);
        --pp-border-hover: rgba(212, 162, 78, 0.15);
        --pp-ink: #161e2e;
        --pp-ink-soft: #2d3748;
        --pp-muted: #6b788e;
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
        padding-top: 18px;
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
    .pp-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        font-weight: 600;
    }
    .pp-breadcrumb-item {
        color: var(--pp-muted);
        padding-right: 14px;
        position: relative;
    }
    .pp-breadcrumb-item + .pp-breadcrumb-item { padding-left: 14px; }
    .pp-breadcrumb-item + .pp-breadcrumb-item::before {
        content: '/';
        position: absolute;
        left: 0;
        color: rgba(11, 17, 32, 0.15);
    }
    .pp-breadcrumb-item a {
        color: var(--pp-amber);
        text-decoration: none;
        transition: color 0.2s;
    }
    .pp-breadcrumb-item a:hover { color: var(--pp-amber-deep); }
    .pp-breadcrumb-item.active { color: var(--pp-amber-deep); }

    /* Stat Cards */
    .pp-stat-card {
        border-radius: var(--pp-radius-md) !important;
        border: 1px solid var(--pp-border) !important;
        background: #f8f9fc !important;
        box-shadow: var(--pp-shadow-card) !important;
        transition: all 0.3s cubic-bezier(.2,.8,.2,1) !important;
        overflow: hidden;
        padding: 0 !important;
    }
    .pp-stat-card:hover {
        box-shadow: var(--pp-shadow-card-hover) !important;
        border-color: var(--pp-border-hover) !important;
        transform: translateY(-3px);
    }
    .pp-stat-card .card-statistic-1 {
        border: none !important;
        box-shadow: none !important;
        margin-bottom: 0 !important;
        padding: 16px 18px !important;
    }
    .pp-stat-card .card-statistic-1 .card-icon {
        border-radius: 10px !important;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        margin: 0 !important;
        float: left;
    }
    .pp-stat-card .card-statistic-1 .card-wrap {
        margin-left: 54px;
    }
    .pp-stat-card .card-statistic-1 .card-header {
        padding: 0 !important;
        border: none !important;
        background: transparent !important;
        height: auto !important;
        min-height: auto !important;
    }
    .pp-stat-card .card-statistic-1 .card-header h4 {
        font-weight: 600;
        font-size: 11.5px;
        color: var(--pp-muted);
        letter-spacing: 0.2px;
        margin: 0 !important;
        text-transform: none !important;
    }
    .pp-stat-card .card-statistic-1 .card-body {
        font-weight: 700;
        font-size: 14px;
        color: var(--pp-ink);
        padding: 2px 0 0 0 !important;
    }
    .pp-stat-card .card-statistic-1 .card-icon.bg-primary { background: linear-gradient(145deg, #6366f1, #4f46e5) !important; }
    .pp-stat-card .card-statistic-1 .card-icon.bg-success { background: linear-gradient(145deg, #34d399, #16a34a) !important; }
    .pp-stat-card .card-statistic-1 .card-icon.bg-warning { background: linear-gradient(145deg, var(--pp-amber-bright), var(--pp-amber-deep)) !important; }
    .pp-stat-card .card-statistic-1 .card-icon.bg-danger { background: linear-gradient(145deg, #fb7185, #e11d48) !important; }
    .pp-stat-card .card-statistic-1 .card-icon.bg-info { background: linear-gradient(145deg, #38bdf8, #0284c7) !important; }

    /* Link Section */
    .link-section {
        margin-top: 24px;
    }
    .link-section .ls-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .link-section .ls-header h3 {
        font-size: 15px;
        font-weight: 800;
        color: var(--pp-ink);
        margin: 0;
        letter-spacing: -0.2px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .link-section .ls-header h3 i { color: var(--pp-amber); }
    .link-section .ls-header .ls-badge {
        background: linear-gradient(135deg, var(--pp-amber-bright), var(--pp-amber));
        color: #1a1306;
        font-size: 10px;
        font-weight: 700;
        padding: 5px 16px;
        border-radius: 50px;
        box-shadow: 0 3px 12px rgba(212, 162, 78, 0.2);
    }
    .link-section .ls-header .ls-badge i { margin-right: 4px; }

    /* Link Cards */
    .link-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }
    .link-card {
        border-radius: var(--pp-radius-md) !important;
        text-decoration: none !important;
        display: flex;
        align-items: stretch;
        border: 1px solid var(--pp-border) !important;
        transition: all 0.3s cubic-bezier(.2,.8,.2,1) !important;
        position: relative;
        overflow: hidden;
        min-height: 68px;
        cursor: pointer;
        background: #f8f9fc !important;
        box-shadow: var(--pp-shadow-card) !important;
    }
    .link-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--pp-shadow-card-hover) !important;
        border-color: var(--pp-border-hover) !important;
    }
    .link-card:active {
        transform: translateY(-1px);
    }
    .link-card .lc-bar {
        width: 4px;
        flex-shrink: 0;
        align-self: stretch;
    }
    .link-card.purple .lc-bar { background: #6366f1; }
    .link-card.green .lc-bar { background: #10b981; }
    .link-card.blue .lc-bar { background: #06b6d4; }
    .link-card.amber .lc-bar { background: var(--pp-amber); }
    .link-card.dark .lc-bar { background: #475569; }
    .link-card.rose .lc-bar { background: #ef4444; }

    .link-card .lc-body {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px 12px 12px;
        min-width: 0;
    }
    .link-card .lc-body .lc-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        color: #fff;
        flex-shrink: 0;
    }
    .link-card.purple .lc-body .lc-icon { background: #6366f1; }
    .link-card.green .lc-body .lc-icon { background: #10b981; }
    .link-card.blue .lc-body .lc-icon { background: #06b6d4; }
    .link-card.amber .lc-body .lc-icon { background: var(--pp-amber); }
    .link-card.dark .lc-body .lc-icon { background: #475569; }
    .link-card.rose .lc-body .lc-icon { background: #ef4444; }
    .link-card .lc-body .lc-text { flex: 1; min-width: 0; }
    .link-card .lc-body .lc-text .lc-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--pp-ink);
        margin: 0 0 2px 0;
    }
    .link-card .lc-body .lc-text .lc-sub {
        font-size: 11px;
        color: var(--pp-muted);
        margin: 0;
    }
    .link-card .lc-body .lc-arrow {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: var(--pp-surface);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--pp-muted);
        font-size: 8px;
        flex-shrink: 0;
        transition: all 0.25s ease;
    }
    .link-card:hover .lc-body .lc-arrow {
        transform: translateX(3px);
    }
    .link-card.purple:hover .lc-body .lc-arrow { background: #6366f1; color: #fff; }
    .link-card.green:hover .lc-body .lc-arrow { background: #10b981; color: #fff; }
    .link-card.blue:hover .lc-body .lc-arrow { background: #06b6d4; color: #fff; }
    .link-card.amber:hover .lc-body .lc-arrow { background: var(--pp-amber); color: #1a1306; }
    .link-card.dark:hover .lc-body .lc-arrow { background: #475569; color: #fff; }
    .link-card.rose:hover .lc-body .lc-arrow { background: #ef4444; color: #fff; }

    .link-card .lc-ripple {
        position: absolute;
        border-radius: 50%;
        transform: scale(0);
        animation: lcRippleAnim 0.6s ease-out;
        pointer-events: none;
        z-index: 5;
    }
    @keyframes lcRippleAnim {
        to { transform: scale(4); opacity: 0; }
    }

    @keyframes cardFadeIn {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .link-card {
        animation: cardFadeIn 0.4s ease backwards;
    }
    .link-card:nth-child(1) { animation-delay: 0.04s; }
    .link-card:nth-child(2) { animation-delay: 0.08s; }
    .link-card:nth-child(3) { animation-delay: 0.12s; }
    .link-card:nth-child(4) { animation-delay: 0.16s; }
    .link-card:nth-child(5) { animation-delay: 0.20s; }
    .link-card:nth-child(6) { animation-delay: 0.24s; }

    @media (max-width: 1199px) {
        .link-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 767px) {
        .pp-header { flex-direction: column; align-items: flex-start; gap: 8px; }
        .pp-header h1 { font-size: 16px; gap: 8px; }
        .pp-header h1 .pp-icon { width: 28px; height: 28px; min-width: 28px; font-size: 12px; }
        .link-grid { grid-template-columns: 1fr; gap: 10px; }
        .link-card { min-height: 60px; }
        .link-card .lc-body { padding: 10px 12px 10px 10px; gap: 10px; }
        .link-card .lc-body .lc-icon { width: 32px; height: 32px; font-size: 13px; border-radius: 8px; }
        .link-card .lc-body .lc-text .lc-title { font-size: 12px; }
        .link-card .lc-body .lc-text .lc-sub { font-size: 10px; }
        .link-card .lc-body .lc-arrow { width: 22px; height: 22px; font-size: 7px; }
    }
</style>
@endpush

@section('content')
    <section class="section">
        <div class="pp-header">
            <h1>

                All Reports
            </h1>
            <div class="pp-breadcrumb">
                <span class="pp-breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home mr-1"></i>Dashboard</a></span>
                <span class="pp-breadcrumb-item active">Reports</span>
            </div>
        </div>

        <div class="section-body">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                    <div class="card pp-stat-card">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Stock Value</h4>
                                </div>
                                <div class="card-body">
                                    {!! formatWithCurrency($totalStockValue) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                    <div class="card pp-stat-card">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Products</h4>
                                </div>
                                <div class="card-body">
                                    {{ number_format($totalProducts) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                    <div class="card pp-stat-card">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Low Stock Items</h4>
                                </div>
                                <div class="card-body">
                                    <span class="{{ $lowStockCount > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($lowStockCount) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                    <div class="card pp-stat-card">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Monthly Purchases</h4>
                                </div>
                                <div class="card-body">
                                    {!! formatConverted($monthlyPurchases) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 col-md-12 col-sm-12 col-12 mb-3">
                    <div class="card pp-stat-card">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Sale Revenue</h4>
                                </div>
                                <div class="card-body">
                                    {!! formatConverted($totalRevenue) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-sm-12 col-12 mb-3">
                    <div class="card pp-stat-card">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Gross Profit</h4>
                                </div>
                                <div class="card-body">
                                    {!! formatConverted($grossProfit) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Access Reports -->
            <div class="link-section">
                <div class="ls-header">
                    <h3><i class="fas fa-rocket"></i> Quick Access Reports</h3>
                    <span class="ls-badge"><i class="fas fa-file-alt"></i> 6 Reports</span>
                </div>
                <div class="link-grid">
                    <a href="{{ route('admin.reports.stock') }}" class="link-card purple">
                        <div class="lc-bar"></div>
                        <div class="lc-body">
                            <div class="lc-icon"><i class="fas fa-warehouse"></i></div>
                            <div class="lc-text">
                                <h5 class="lc-title">Stock Valuation</h5>
                                <p class="lc-sub">View current stock value</p>
                            </div>
                            <div class="lc-arrow"><i class="fas fa-arrow-right"></i></div>
                        </div>
                    </a>
                    <a href="{{ route('admin.reports.purchase') }}" class="link-card green">
                        <div class="lc-bar"></div>
                        <div class="lc-body">
                            <div class="lc-icon"><i class="fas fa-file-invoice"></i></div>
                            <div class="lc-text">
                                <h5 class="lc-title">Purchase History</h5>
                                <p class="lc-sub">All purchase records</p>
                            </div>
                            <div class="lc-arrow"><i class="fas fa-arrow-right"></i></div>
                        </div>
                    </a>
                    <a href="{{ route('admin.reports.product-purchase-history') }}" class="link-card blue">
                        <div class="lc-bar"></div>
                        <div class="lc-body">
                            <div class="lc-icon"><i class="fas fa-history"></i></div>
                            <div class="lc-text">
                                <h5 class="lc-title">Purchase Tracking</h5>
                                <p class="lc-sub">Product-wise tracking</p>
                            </div>
                            <div class="lc-arrow"><i class="fas fa-arrow-right"></i></div>
                        </div>
                    </a>
                    <a href="{{ route('admin.reports.low-stock') }}" class="link-card amber">
                        <div class="lc-bar"></div>
                        <div class="lc-body">
                            <div class="lc-icon"><i class="fas fa-exclamation-circle"></i></div>
                            <div class="lc-text">
                                <h5 class="lc-title">Low Stock Alert</h5>
                                <p class="lc-sub">Items below threshold</p>
                            </div>
                            <div class="lc-arrow"><i class="fas fa-arrow-right"></i></div>
                        </div>
                    </a>
                    <a href="{{ route('admin.reports.audit') }}" class="link-card dark">
                        <div class="lc-bar"></div>
                        <div class="lc-body">
                            <div class="lc-icon"><i class="fas fa-user-shield"></i></div>
                            <div class="lc-text">
                                <h5 class="lc-title">Audit Report</h5>
                                <p class="lc-sub">Activity &amp; logs</p>
                            </div>
                            <div class="lc-arrow"><i class="fas fa-arrow-right"></i></div>
                        </div>
                    </a>
                    <a href="{{ route('admin.reports.orders') }}" class="link-card rose">
                        <div class="lc-bar"></div>
                        <div class="lc-body">
                            <div class="lc-icon"><i class="fas fa-shopping-cart"></i></div>
                            <div class="lc-text">
                                <h5 class="lc-title">Order &amp; Issue</h5>
                                <p class="lc-sub">Orders &amp; issues summary</p>
                            </div>
                            <div class="lc-arrow"><i class="fas fa-arrow-right"></i></div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.link-card').forEach(function(card) {
                card.addEventListener('click', function(e) {
                    var rect = this.getBoundingClientRect();
                    var x = e.clientX - rect.left;
                    var y = e.clientY - rect.top;
                    var ripple = document.createElement('span');
                    ripple.className = 'lc-ripple';
                    ripple.style.cssText = 'left: ' + x + 'px; top: ' + y + 'px; width: 20px; height: 20px; background: rgba(212, 162, 78, 0.12);';
                    this.appendChild(ripple);
                    setTimeout(function() { ripple.remove(); }, 600);
                });
            });
        });
    </script>
@endsection