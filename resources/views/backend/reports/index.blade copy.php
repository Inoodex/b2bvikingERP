@extends('backend.layouts.master')

@section('content')
<style>
    /* ============================================
       REACT-STYLE LINK CARDS
    ============================================ */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

    .reports-section {
        background: #f0f4f8;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        padding-bottom: 20px;
    }

    .section-header h1 {
        font-weight: 800 !important;
        color: #171827 !important;
        letter-spacing: -0.5px;
    }

    .section-header h1 i {
        color: #6c5ce7;
    }

    .section-header-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
        content: '\f105';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        color: #94a3b8;
    }

    .breadcrumb {
        background: transparent !important;
        padding: 0 !important;
    }

    .breadcrumb-item a {
        color: #6c5ce7 !important;
        font-weight: 500;
    }

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
        font-size: 17px;
        font-weight: 800;
        color: #1a1a2e;
        margin: 0;
        letter-spacing: -0.3px;
        position: relative;
        padding-left: 14px;
    }

    .link-section .ls-header h3::before {
        content: '';
        position: absolute;
        left: 0;
        top: 2px;
        bottom: 2px;
        width: 3px;
        background: linear-gradient(180deg, #6c5ce7, #a29bfe);
        border-radius: 10px;
    }

    .link-section .ls-header h3 i {
        color: #6c5ce7;
        margin-right: 8px;
    }

    .link-section .ls-header .ls-badge {
        background: linear-gradient(135deg, #6c5ce7, #a29bfe);
        color: #fff;
        font-size: 10px;
        font-weight: 600;
        padding: 5px 16px;
        border-radius: 50px;
        box-shadow: 0 3px 12px rgba(108,92,231,0.2);
    }

    .link-section .ls-header .ls-badge i {
        margin-right: 4px;
    }

    .link-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .link-card {
        border-radius: 16px;
        text-decoration: none !important;
        display: flex;
        align-items: stretch;
        border: 1px solid #eaedf0;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
        min-height: 68px;
        cursor: pointer;
        background: #fff;
        animation: cardFadeIn 0.45s ease backwards;
    }

    .link-card:nth-child(1) { animation-delay: 0.04s; }
    .link-card:nth-child(2) { animation-delay: 0.08s; }
    .link-card:nth-child(3) { animation-delay: 0.12s; }
    .link-card:nth-child(4) { animation-delay: 0.16s; }
    .link-card:nth-child(5) { animation-delay: 0.20s; }
    .link-card:nth-child(6) { animation-delay: 0.24s; }

    @keyframes cardFadeIn {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .link-card .lc-bar {
        width: 4px;
        flex-shrink: 0;
        align-self: stretch;
    }

    .link-card .lc-body {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px 12px 12px;
        min-width: 0;
    }

    .link-card .lc-body .lc-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        color: #fff;
        flex-shrink: 0;
    }

    .link-card .lc-body .lc-text {
        flex: 1;
        min-width: 0;
    }

    .link-card .lc-body .lc-text .lc-title {
        font-size: 13px;
        font-weight: 600;
        color: #1a1a2e;
        margin: 0 0 2px 0;
    }

    .link-card .lc-body .lc-text .lc-sub {
        font-size: 11px;
        color: #94a3b8;
        margin: 0;
    }

    .link-card .lc-body .lc-arrow {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #f1f4f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 8px;
        flex-shrink: 0;
        transition: all 0.25s ease;
    }

    .link-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    }

    .link-card:active {
        transform: translateY(-1px);
    }

    .link-card:hover .lc-body .lc-arrow {
        transform: translateX(3px);
    }

    /* ===== THEMES ===== */

    .link-card.purple { background: #f8f7ff; border-color: #e8e3ff; }
    .link-card.purple .lc-bar { background: #7c6cf0; }
    .link-card.purple .lc-body .lc-icon { background: #7c6cf0; }
    .link-card.purple:hover { border-color: #d5ccff; box-shadow: 0 8px 24px rgba(124,108,240,0.1); }
    .link-card.purple:hover .lc-body .lc-arrow { background: #7c6cf0; color: #fff; }

    .link-card.green { background: #f4fcf8; border-color: #d1f0e3; }
    .link-card.green .lc-bar { background: #10b981; }
    .link-card.green .lc-body .lc-icon { background: #10b981; }
    .link-card.green:hover { border-color: #a8e4cd; box-shadow: 0 8px 24px rgba(16,185,129,0.1); }
    .link-card.green:hover .lc-body .lc-arrow { background: #10b981; color: #fff; }

    .link-card.blue { background: #f4fafc; border-color: #cee9f0; }
    .link-card.blue .lc-bar { background: #06b6d4; }
    .link-card.blue .lc-body .lc-icon { background: #06b6d4; }
    .link-card.blue:hover { border-color: #a2dce8; box-shadow: 0 8px 24px rgba(6,182,212,0.1); }
    .link-card.blue:hover .lc-body .lc-arrow { background: #06b6d4; color: #fff; }

    .link-card.amber { background: #fefaf2; border-color: #f7e9cc; }
    .link-card.amber .lc-bar { background: #f59e0b; }
    .link-card.amber .lc-body .lc-icon { background: #f59e0b; }
    .link-card.amber:hover { border-color: #f2daa3; box-shadow: 0 8px 24px rgba(245,158,11,0.1); }
    .link-card.amber:hover .lc-body .lc-arrow { background: #f59e0b; color: #fff; }

    .link-card.dark { background: #f6f7f9; border-color: #d9dce3; }
    .link-card.dark .lc-bar { background: #475569; }
    .link-card.dark .lc-body .lc-icon { background: #475569; }
    .link-card.dark:hover { border-color: #c1c5cf; box-shadow: 0 8px 24px rgba(71,85,105,0.08); }
    .link-card.dark:hover .lc-body .lc-arrow { background: #475569; color: #fff; }

    .link-card.rose { background: #fef5f5; border-color: #f7d4d0; }
    .link-card.rose .lc-bar { background: #ef4444; }
    .link-card.rose .lc-body .lc-icon { background: #ef4444; }
    .link-card.rose:hover { border-color: #f1b8b1; box-shadow: 0 8px 24px rgba(239,68,68,0.1); }
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

    /* Responsive */
    @media (max-width: 1199px) {
        .link-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 767px) {
        .link-grid { grid-template-columns: 1fr; gap: 10px; }
        .link-card { min-height: 60px; }
        .link-card .lc-body { padding: 10px 12px 10px 10px; gap: 10px; }
        .link-card .lc-body .lc-icon { width: 32px; height: 32px; font-size: 13px; border-radius: 8px; }
        .link-card .lc-body .lc-text .lc-title { font-size: 12px; }
        .link-card .lc-body .lc-text .lc-sub { font-size: 10px; }
        .link-card .lc-body .lc-arrow { width: 22px; height: 22px; font-size: 7px; }
    }

    /* Print */
    @media print {
        .link-card { break-inside: avoid; box-shadow: none !important; border: 1px solid #dee2e6 !important; }
        .link-card .lc-bar, .link-card .lc-body .lc-icon { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>

<section class="section reports-section">
    <div class="section-header">
        <h1>
            <i class="fas fa-chart-pie text-primary"></i> 
            All Reports
        </h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
            <div class="breadcrumb-item active">
                <i class="fas fa-file-alt"></i> Reports
            </div>
        </div>
    </div>

    <div class="section-body">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
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
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
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
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
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
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
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

        <!-- Revenue & Profit -->
        <div class="row">
            <div class="col-lg-6 col-md-12 col-sm-12 col-12">
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
            <div class="col-lg-6 col-md-12 col-sm-12 col-12">
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
                ripple.style.cssText = `
                    left: ${x}px;
                    top: ${y}px;
                    width: 20px;
                    height: 20px;
                    background: rgba(108, 92, 231, 0.08);
                `;

                this.appendChild(ripple);

                setTimeout(function() {
                    ripple.remove();
                }, 600);
            });
        });
    });
</script>

@endsection
