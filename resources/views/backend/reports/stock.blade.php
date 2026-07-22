@extends('backend.layouts.master')
@section('title', $settings->site_name . ' | Stock Report')

@push('css')
<style>
    :root {
        --sr-amber: #d4a24e;
        --sr-amber-bright: #ecc78b;
        --sr-amber-deep: #b8852a;
        --sr-amber-soft: rgba(212, 162, 78, 0.08);
        --sr-border: rgba(11, 17, 32, 0.07);
        --sr-border-hover: rgba(212, 162, 78, 0.18);xzx
        --sr-ink: #161e2e;
        --sr-ink-soft: #2d3748;
        --sr-muted: #6b788e;
        --sr-surface: #f8f9fc;
        --sr-radius: 14px;
        --sr-radius-lg: 20px;
        --sr-shadow: 0 1px 3px rgba(11,17,32,0.04), 0 8px 20px -12px rgba(11,17,32,0.12);
        --sr-shadow-hover: 0 12px 32px -12px rgba(11,17,32,0.16), 0 0 0 1px rgba(212,162,78,0.06);
        --sr-font: 'Inter', 'Segoe UI', system-ui, sans-serif;
    }

    /* ============================
       HEADER
       ============================ */
    .sr-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 22px;
        padding-top: 18px;
    }
    .sr-page-header h1 {
        display: flex;
        align-items: center;
        gap: 12px;
        font-family: var(--sr-font);
        font-weight: 800;
        font-size: 21px;
        color: var(--sr-ink);
        letter-spacing: -0.3px;
        margin: 0;
    }
    .sr-page-header .sr-icon-badge {
        width: 36px; height: 36px; min-width: 36px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 11px;
        font-size: 14px;
        color: #1a1306;
        background: linear-gradient(145deg, var(--sr-amber-bright), var(--sr-amber));
        box-shadow: 0 4px 14px rgba(212,162,78,0.35), inset 0 1px 0 rgba(255,255,255,0.3);
    }
    .sr-breadcrumb {
        display: flex; align-items: center; gap: 6px;
        font-size: 12.5px; font-weight: 600;
    }
    .sr-breadcrumb span { color: var(--sr-muted); position: relative; padding-right: 14px; }
    .sr-breadcrumb span + span { padding-left: 14px; }
    .sr-breadcrumb span + span::before {
        content: '/'; position: absolute; left: 0; color: rgba(11,17,32,0.15);
    }
    .sr-breadcrumb a { color: var(--sr-amber); text-decoration: none; transition: color 0.2s; }
    .sr-breadcrumb a:hover { color: var(--sr-amber-deep); }
    .sr-breadcrumb .active { color: var(--sr-amber-deep); }

    /* ============================
       STAT CARDS — Desktop Grid
       ============================ */
    .sr-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }
    .sr-stat-card {
        background: #fff;
        border: 1px solid var(--sr-border);
        border-radius: var(--sr-radius);
        padding: 18px 20px;
        box-shadow: var(--sr-shadow);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: all 0.3s cubic-bezier(.2,.8,.2,1);
        position: relative;
        overflow: hidden;
    }
    .sr-stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 3px;
        border-radius: var(--sr-radius) var(--sr-radius) 0 0;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .sr-stat-card:hover {
        box-shadow: var(--sr-shadow-hover);
        border-color: var(--sr-border-hover);
        transform: translateY(-3px);
    }
    .sr-stat-card:hover::before { opacity: 1; }
    .sr-stat-card:nth-child(1)::before { background: linear-gradient(90deg, #6366f1, #818cf8); }
    .sr-stat-card:nth-child(2)::before { background: linear-gradient(90deg, #10b981, #34d399); }
    .sr-stat-card:nth-child(3)::before { background: linear-gradient(90deg, #0ea5e9, #38bdf8); }
    .sr-stat-card:nth-child(4)::before { background: linear-gradient(90deg, var(--sr-amber), var(--sr-amber-bright)); }

    .sr-stat-icon {
        width: 44px; height: 44px; min-width: 44px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 12px;
        font-size: 16px;
        color: #fff;
        flex-shrink: 0;
    }
    .sr-stat-icon.indigo { background: linear-gradient(145deg, #818cf8, #6366f1); box-shadow: 0 4px 12px -3px rgba(99,102,241,0.35); }
    .sr-stat-icon.emerald { background: linear-gradient(145deg, #34d399, #10b981); box-shadow: 0 4px 12px -3px rgba(16,185,129,0.35); }
    .sr-stat-icon.sky { background: linear-gradient(145deg, #38bdf8, #0ea5e9); box-shadow: 0 4px 12px -3px rgba(14,165,233,0.35); }
    .sr-stat-icon.amber { background: linear-gradient(145deg, var(--sr-amber-bright), var(--sr-amber)); box-shadow: 0 4px 12px -3px rgba(212,162,78,0.35); color: #1a1306; }

    .sr-stat-info { flex: 1; min-width: 0; }
    .sr-stat-label {
        font-size: 11px; font-weight: 700;
        color: var(--sr-muted);
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sr-stat-value {
        font-size: 18px; font-weight: 800;
        color: var(--sr-ink);
        letter-spacing: -0.3px;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ============================
       FILTER CARD
       ============================ */
    .sr-filter-card {
        background: #fff;
        border: 1px solid var(--sr-border);
        border-radius: var(--sr-radius);
        box-shadow: var(--sr-shadow);
        overflow: hidden;
        margin-bottom: 22px;
    }
    .sr-filter-head {
        padding: 14px 20px;
        background: linear-gradient(135deg, #fafbfc, #f4f5f8);
        border-bottom: 1px solid var(--sr-border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .sr-filter-head h4 {
        font-family: var(--sr-font);
        font-weight: 800; font-size: 13.5px;
        color: var(--sr-ink); margin: 0;
        display: flex; align-items: center; gap: 8px;
    }
    .sr-filter-head h4 i { color: var(--sr-amber); font-size: 13px; }
    .sr-filter-head .sr-collapse-btn {
        width: 28px; height: 28px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        background: var(--sr-surface);
        border: 1px solid var(--sr-border);
        color: var(--sr-muted);
        cursor: pointer;
        transition: all 0.2s;
        font-size: 11px;
    }
    .sr-filter-head .sr-collapse-btn:hover { background: var(--sr-amber-soft); color: var(--sr-amber); border-color: var(--sr-border-hover); }
    .sr-filter-body { padding: 18px 20px; }
    .sr-filter-body label {
        font-family: var(--sr-font);
        font-weight: 700; font-size: 11.5px;
        color: var(--sr-ink-soft);
        margin-bottom: 5px;
        display: block;
    }
    .sr-filter-body .select2-selection--single {
        height: 38px !important;
        border-radius: 10px !important;
        border: 1.5px solid var(--sr-border) !important;
        display: flex !important; align-items: center;
    }
    .sr-filter-body .select2-selection--single .select2-selection__rendered {
        color: var(--sr-ink); font-size: 12.5px; font-weight: 500; padding-left: 14px; line-height: 36px;
    }
    .sr-filter-body .select2-selection--single .select2-selection__arrow { height: 36px; right: 10px; }
    .sr-filter-body .select2-container--focus .select2-selection--single,
    .sr-filter-body .select2-container--open .select2-selection--single {
        border-color: var(--sr-amber) !important;
        box-shadow: 0 0 0 3px var(--sr-amber-soft) !important;
    }

    /* ============================
       BUTTONS
       ============================ */
    .sr-btn {
        border: none;
        font-family: var(--sr-font);
        font-weight: 700;
        font-size: 11px;
        border-radius: var(--sr-radius-lg);
        padding: 7px 16px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(.2,.8,.2,1);
        text-decoration: none;
        white-space: nowrap;
    }
    .sr-btn:hover { transform: translateY(-1.5px); }
    .sr-btn.indigo { background: linear-gradient(145deg, #6366f1, #4f46e5); color: #fff; box-shadow: 0 4px 12px -4px rgba(99,102,241,0.3); }
    .sr-btn.indigo:hover { filter: brightness(1.06); box-shadow: 0 6px 18px -4px rgba(99,102,241,0.4); color: #fff; }
    .sr-btn.emerald { background: linear-gradient(145deg, #34d399, #16a34a); color: #fff; box-shadow: 0 4px 12px -4px rgba(22,163,74,0.3); }
    .sr-btn.emerald:hover { filter: brightness(1.06); box-shadow: 0 6px 18px -4px rgba(22,163,74,0.4); color: #fff; }
    .sr-btn.rose { background: linear-gradient(145deg, #fb7185, #e11d48); color: #fff; box-shadow: 0 4px 12px -4px rgba(225,29,72,0.3); }
    .sr-btn.rose:hover { filter: brightness(1.06); box-shadow: 0 6px 18px -4px rgba(225,29,72,0.4); color: #fff; }
    .sr-btn.amber { background: linear-gradient(145deg, var(--sr-amber-bright), var(--sr-amber-deep)); color: #1a1306; box-shadow: 0 4px 12px -4px rgba(212,162,78,0.3); }
    .sr-btn.amber:hover { filter: brightness(1.06); box-shadow: 0 6px 18px -4px rgba(212,162,78,0.4); color: #1a1306; }

    /* ============================
       TABLE CARD
       ============================ */
    .sr-table-card {
        background: #fff;
        border: 1px solid var(--sr-border);
        border-radius: var(--sr-radius);
        box-shadow: var(--sr-shadow);
        overflow: hidden;
    }
    .sr-table-head {
        padding: 14px 20px;
        background: linear-gradient(135deg, #fafbfc, #f4f5f8);
        border-bottom: 1px solid var(--sr-border);
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .sr-table-head h4 {
        font-family: var(--sr-font);
        font-weight: 800; font-size: 13.5px;
        color: var(--sr-ink); margin: 0;
        display: flex; align-items: center; gap: 8px;
    }
    .sr-table-head h4 i { color: var(--sr-amber); font-size: 13px; }
    .sr-table-actions { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }

    /* TABLE */
    .sr-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    #table-stock {
        font-size: 12.5px;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        margin: 0;
    }
    #table-stock thead th {
        background: linear-gradient(135deg, #fafbfc, #f4f5f8);
        color: var(--sr-ink-soft);
        font-family: var(--sr-font);
        font-weight: 700;
        font-size: 10.5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        border: none;
        border-bottom: 2px solid var(--sr-border);
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    #table-stock tbody td {
        padding: 12px 16px;
        vertical-align: middle;
        color: var(--sr-ink);
        border-bottom: 1px solid var(--sr-border);
        background: #fff;
        transition: background 0.15s;
        font-weight: 500;
    }
    #table-stock tbody tr:hover td { background: var(--sr-amber-soft); }
    #table-stock tbody tr:nth-child(even) td { background: var(--sr-surface); }
    #table-stock tbody tr:nth-child(even):hover td { background: var(--sr-amber-soft); }

    /* Product cell */
    .sr-product-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 180px;
    }
    .sr-product-img {
        width: 36px; height: 36px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
        border: 1px solid var(--sr-border);
    }
    .sr-product-placeholder {
        width: 36px; height: 36px;
        border-radius: 8px;
        background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
        display: flex; align-items: center; justify-content: center;
        font-size: 9px; font-weight: 700;
        color: #64748b;
        flex-shrink: 0;
    }
    .sr-product-name {
        font-weight: 700;
        font-size: 12.5px;
        color: var(--sr-ink);
        line-height: 1.3;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    /* Category badge */
    .sr-cat-badge {
        display: inline-block;
        background: var(--sr-surface);
        color: var(--sr-ink-soft);
        font-size: 10.5px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 50px;
        border: 1px solid var(--sr-border);
        margin-bottom: 3px;
    }
    .sr-brand-text {
        font-size: 10.5px;
        color: var(--sr-muted);
        font-weight: 500;
    }

    /* Stock badge */
    .sr-stock-badge {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 42px;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
    }
    .sr-stock-badge.ok { background: rgba(22,163,74,0.1); color: #16a34a; }
    .sr-stock-badge.low { background: rgba(220,38,38,0.1); color: #dc2626; animation: sr-pulse 2s infinite; }

    @keyframes sr-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(220,38,38,0.15); }
        50% { box-shadow: 0 0 0 6px rgba(220,38,38,0); }
    }

    /* Money cells */
    .sr-money { white-space: nowrap; }
    .sr-money.asset { color: #4f46e5; }
    .sr-money.profit { color: #16a34a; }

    /* Grand Total Bar */
    .sr-grand-total {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 22px;
        border-top: 2px solid var(--sr-amber);
        background: linear-gradient(135deg, #fffdf7, #fffbeb);
    }
    .sr-gt-label {
        font-family: var(--sr-font);
        font-size: 13px;
        font-weight: 800;
        color: var(--sr-ink);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex; align-items: center; gap: 8px;
    }
    .sr-gt-label i { color: var(--sr-amber); font-size: 14px; }
    .sr-gt-values { display: flex; align-items: center; gap: 20px; }
    .sr-gt-chip {
        display: flex; flex-direction: column; align-items: flex-end;
    }
    .sr-gt-chip-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
    .sr-gt-chip-val { font-size: 16px; font-weight: 800; font-family: 'JetBrains Mono', 'Fira Code', monospace; }
    .sr-gt-chip.asset .sr-gt-chip-label { color: #6366f1; }
    .sr-gt-chip.asset .sr-gt-chip-val { color: #4f46e5; }
    .sr-gt-chip.profit .sr-gt-chip-label { color: #10b981; }
    .sr-gt-chip.profit .sr-gt-chip-val { color: #16a34a; }

    /* Pagination */
    .sr-pagination-wrap {
        padding: 16px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
    .sr-pagination-info {
        font-size: 12.5px; font-weight: 500;
        color: var(--sr-muted);
    }
    .sr-pagination-info strong { color: var(--sr-ink); }
    .sr-pagination-wrap .pagination { margin: 0; }
    .sr-pagination-wrap .pagination .page-link {
        border-radius: 10px;
        border: 1px solid var(--sr-border);
        margin: 0 2px;
        font-weight: 600;
        font-size: 11px;
        color: var(--sr-muted);
        padding: 6px 11px;
        transition: all 0.2s cubic-bezier(.2,.8,.2,1);
        background: #fff;
    }
    .sr-pagination-wrap .pagination .page-link:hover {
        border-color: var(--sr-amber);
        background: var(--sr-amber-soft);
        color: var(--sr-amber-deep);
    }
    .sr-pagination-wrap .pagination .page-item.active .page-link {
        background: linear-gradient(145deg, var(--sr-amber-bright), var(--sr-amber));
        border-color: var(--sr-amber);
        color: #1a1306;
        box-shadow: 0 4px 14px -3px rgba(212,162,78,0.35);
    }

    /* ============================
       MOBILE CARD VIEW (< 768px)
       ============================ */
    .sr-mobile-cards { display: none; }

    @media (max-width: 767.98px) {
        .sr-table-wrap { display: none !important; }
        .sr-mobile-cards { display: block; padding: 12px; }

        .sr-mobile-card {
            background: #fff;
            border: 1px solid var(--sr-border);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            transition: all 0.2s ease;
        }
        .sr-mobile-card:active { transform: scale(0.99); background: #fafbfc; }

        .sr-mobile-card-top {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--sr-border);
        }
        .sr-mobile-card-img {
            width: 40px; height: 40px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            border: 1px solid var(--sr-border);
        }
        .sr-mobile-card-placeholder {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 700; color: #64748b;
            flex-shrink: 0;
        }
        .sr-mobile-card-name {
            flex: 1; min-width: 0;
        }
        .sr-mobile-card-name .name {
            font-weight: 700; font-size: 13px; color: var(--sr-ink);
            line-height: 1.3; margin-bottom: 3px;
            overflow: hidden; text-overflow: ellipsis;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        }
        .sr-mobile-card-name .meta {
            display: flex; gap: 6px; align-items: center; flex-wrap: wrap;
        }

        .sr-mobile-card-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 12px;
        }
        .sr-mobile-card-field {
            display: flex; flex-direction: column;
        }
        .sr-mobile-card-field .label {
            font-size: 9.5px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px;
            color: var(--sr-muted);
            margin-bottom: 2px;
        }
        .sr-mobile-card-field .value {
            font-size: 13px; font-weight: 700;
            color: var(--sr-ink);
        }
        .sr-mobile-card-field .value.asset { color: #4f46e5; }
        .sr-mobile-card-field .value.profit { color: #16a34a; }
    }

    /* ============================
       TABLET (768px - 991px)
       ============================ */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .sr-stats-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .sr-stat-value { font-size: 16px; }
        .sr-stat-icon { width: 40px; height: 40px; min-width: 40px; font-size: 15px; }
        #table-stock { min-width: 750px; }
        #table-stock thead th { font-size: 9.5px; padding: 10px 12px; }
        #table-stock tbody td { font-size: 11.5px; padding: 10px 12px; }
        .sr-product-name { max-width: 150px; font-size: 11.5px; }
        .sr-product-img, .sr-product-placeholder { width: 30px; height: 30px; }
        .sr-grand-total { padding: 14px 18px; }
        .sr-gt-chip-val { font-size: 14px; }
        .sr-table-actions { gap: 4px; }
        .sr-btn { font-size: 10px; padding: 6px 12px; }
    }

    /* ============================
       MOBILE (< 768px)
       ============================ */
    @media (max-width: 767.98px) {
        .sr-page-header { flex-direction: column; align-items: flex-start; gap: 6px; margin-bottom: 16px; }
        .sr-page-header h1 { font-size: 17px; gap: 10px; }
        .sr-page-header .sr-icon-badge { width: 30px; height: 30px; min-width: 30px; font-size: 12px; border-radius: 9px; }
        .sr-breadcrumb { font-size: 11px; }

        .sr-stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 16px; }
        .sr-stat-card { padding: 14px 14px; gap: 12px; }
        .sr-stat-icon { width: 36px; height: 36px; min-width: 36px; font-size: 14px; border-radius: 10px; }
        .sr-stat-label { font-size: 9.5px; letter-spacing: 0.3px; }
        .sr-stat-value { font-size: 14px; }

        .sr-filter-card { margin-bottom: 16px; }
        .sr-filter-head { padding: 12px 16px; }
        .sr-filter-body { padding: 14px 16px; }
        .sr-filter-body .col-md-5 { margin-bottom: 10px; }
        .sr-filter-body .col-md-2 { margin-top: 0; }
        .sr-filter-body .col-md-2 .sr-btn { width: 100%; justify-content: center; }
        .select2-container { width: 100% !important; }

        .sr-table-head { flex-direction: column; align-items: flex-start; padding: 12px 16px; gap: 8px; }
        .sr-table-actions { width: 100%; }
        .sr-btn { flex: 1; justify-content: center; font-size: 10px; padding: 7px 10px; }

        .sr-grand-total {
            flex-direction: column; gap: 10px;
            padding: 14px 16px;
            align-items: flex-start;
        }
        .sr-gt-values { width: 100%; justify-content: space-between; }
        .sr-gt-chip { align-items: flex-start; }
        .sr-gt-chip-val { font-size: 15px; }

        .sr-pagination-wrap { padding: 14px 16px; }
        .sr-pagination-info { font-size: 11px; }
        .sr-pagination-wrap .pagination .page-link { font-size: 10px; padding: 4px 9px; }
    }

    /* ============================
       SMALL PHONES (< 375px)
       ============================ */
    @media (max-width: 374.98px) {
        .sr-stats-grid { grid-template-columns: 1fr; gap: 8px; }
        .sr-stat-card { padding: 12px; gap: 10px; }
        .sr-stat-value { font-size: 15px; }
        .sr-stat-icon { width: 34px; height: 34px; min-width: 34px; font-size: 13px; }

        .sr-mobile-card-grid { grid-template-columns: 1fr 1fr; gap: 6px 10px; }
        .sr-mobile-card-field .value { font-size: 12px; }

        .sr-page-header h1 { font-size: 15px; }
        .sr-breadcrumb { font-size: 10px; }
    }

    /* ============================
       LARGE DESKTOPS (≥ 1400px)
       ============================ */
    @media (min-width: 1400px) {
        .sr-stat-value { font-size: 20px; }
        .sr-stat-icon { width: 48px; height: 48px; font-size: 18px; }
        .sr-stat-card { padding: 22px 24px; gap: 18px; }
        #table-stock { font-size: 13px; }
        #table-stock thead th { font-size: 11px; padding: 14px 18px; }
        #table-stock tbody td { padding: 14px 18px; }
        .sr-product-name { max-width: 280px; font-size: 13px; }
    }

    /* ============================
       PRINT
       ============================ */
    @media print {
        .sr-filter-card, .sr-table-actions, .sr-page-header, .sr-pagination-wrap { display: none !important; }
        .sr-stats-grid { display: none !important; }
        .sr-table-card { border: none !important; box-shadow: none !important; }
        .sr-table-head { display: none !important; }
        #table-stock thead th { background: #f0f0f0 !important; }
    }
</style>
@endpush

@section('content')
    <section class="section">
        {{-- Page Header --}}
        <div class="sr-page-header">
            <h1>

                Stock Report
            </h1>
            <div class="sr-breadcrumb">
                <span><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home mr-1"></i>Dashboard</a></span>
                <span><a href="{{ route('admin.reports.index') }}">Reports</a></span>
                <span class="active">Stock</span>
            </div>
        </div>

        <div class="section-body">
            {{-- Summary Stat Cards --}}
            <div class="sr-stats-grid">
                <div class="sr-stat-card">
                    <div class="sr-stat-icon indigo"><i class="fas fa-boxes"></i></div>
                    <div class="sr-stat-info">
                        <div class="sr-stat-label">Total Stock Qty</div>
                        <div class="sr-stat-value" id="span-total-qty">{{ number_format($totalQty) }}</div>
                    </div>
                </div>
                <div class="sr-stat-card">
                    <div class="sr-stat-icon emerald"><i class="fas fa-dollar-sign"></i></div>
                    <div class="sr-stat-info">
                        <div class="sr-stat-label">Total Asset Value</div>
                        <div class="sr-stat-value" id="span-total-value">{{ $settings->currency_icon }}{{ number_format($totalValue, 2) }}</div>
                    </div>
                </div>
                <div class="sr-stat-card">
                    <div class="sr-stat-icon sky"><i class="fas fa-tags"></i></div>
                    <div class="sr-stat-info">
                        <div class="sr-stat-label">Potential Revenue</div>
                        <div class="sr-stat-value" id="span-potential-revenue">{{ $settings->currency_icon }}{{ number_format($potentialRevenue, 2) }}</div>
                    </div>
                </div>
                <div class="sr-stat-card">
                    <div class="sr-stat-icon amber"><i class="fas fa-chart-line"></i></div>
                    <div class="sr-stat-info">
                        <div class="sr-stat-label">Potential Profit</div>
                        <div class="sr-stat-value" id="span-potential-profit">{{ $settings->currency_icon }}{{ number_format($potentialProfit, 2) }}</div>
                    </div>
                </div>
            </div>

            {{-- Filter Section --}}
            <div class="sr-filter-card">
                <div class="sr-filter-head">
                    <h4><i class="fas fa-filter"></i> Filter Options</h4>
                    <a data-collapse="#sr-filter-collapse" class="sr-collapse-btn" href="#"><i class="fas fa-minus"></i></a>
                </div>
                <div class="collapse show" id="sr-filter-collapse">
                    <div class="sr-filter-body">
                        <form id="stock-filter-form" action="{{ route('admin.reports.stock') }}" method="GET">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label>Category</label>
                                    <select name="category_id" class="form-control select2">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ request()->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label>Brand</label>
                                    <select name="brand_id" class="form-control select2">
                                        <option value="">All Brands</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ request()->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.reports.stock') }}" class="sr-btn rose w-100" style="margin-top: 4px; justify-content: center;">
                                        <i class="fas fa-undo"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Stock Table --}}
            <div class="sr-table-card">
                <div class="sr-table-head">
                    <h4><i class="fas fa-table"></i> Detailed Stock List</h4>
                    <div class="sr-table-actions">
                        <button type="button" class="sr-btn indigo" id="btn-export-excel">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="sr-btn emerald" id="btn-export-pdf">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button type="button" class="sr-btn rose" id="btn-print">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
                <div class="card-body" style="padding: 0 !important;">
                    {{-- Export Header (hidden) --}}
                    <div class="export-header d-none">
                        <div style="text-align: center; margin-bottom: 10px;">
                            <h3 style="margin: 0; color: #333;">{{ $settings->site_name ?? 'Inventory Management System' }}</h3>
                            @if($settings->contact_email)
                                <p style="margin: 5px 0; color: #666;"><strong>Email:</strong> {{ $settings->contact_email }}</p>
                            @endif
                            @if($settings->address)
                                <p style="margin: 5px 0; color: #666;"><strong>Address:</strong> {{ $settings->address }}</p>
                            @endif
                            <hr style="margin: 10px 0; border-top: 2px solid #007bff;">
                            <h4 style="margin: 10px 0; color: #007bff;">Stock Valuation Report</h4>
                            <p style="margin: 5px 0; color: #666; font-size: 14px;"><strong>Generated on:</strong> {{ date('F d, Y h:i A') }}</p>
                        </div>
                    </div>

                    {{-- Desktop Table --}}
                    <div class="sr-table-wrap">
                        <table class="table" id="table-stock">
                            <thead>
                                <tr>
                                    <th>Product Info</th>
                                    <th>Category / Brand</th>
                                    <th class="text-center">Stock Qty</th>
                                    <th class="text-right">Unit Cost</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">Total Asset Value</th>
                                    <th class="text-right">Profit Potential</th>
                                </tr>
                            </thead>
                            <tbody id="stock-table-body">
                                @foreach ($products as $product)
                                    @php
                                        $qty = $product->inventory_stocks_sum_quantity ?? 0;
                                        $assetValue = $qty * $product->purchase_price;
                                        $potentialSale = $qty * $product->price;
                                        $profit = $potentialSale - $assetValue;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="sr-product-cell">
                                                @if($product->thumb_image)
                                                    <img src="{{ asset('storage/'.$product->thumb_image) }}" alt="" class="sr-product-img">
                                                @else
                                                    <div class="sr-product-placeholder">N/A</div>
                                                @endif
                                                <div class="sr-product-name font-weight-bold">{{ $product->name }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="sr-cat-badge">{{ $product->category->name ?? '-' }}</div>
                                            <div class="sr-brand-text">{{ $product->brand->name ?? '-' }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if($qty <= $product->min_inventory_qty)
                                                <span class="sr-stock-badge low" data-toggle="tooltip" title="Low Stock!">{{ number_format($qty) }}</span>
                                            @else
                                                <span class="sr-stock-badge ok">{{ number_format($qty) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right"><span class="sr-money cost">{{ $settings->currency_icon }}{{ number_format($product->purchase_price, 2) }}</span></td>
                                        <td class="text-right"><span class="sr-money price">{{ $settings->currency_icon }}{{ number_format($product->price, 2) }}</span></td>
                                        <td class="text-right"><span class="sr-money asset">{{ $settings->currency_icon }}{{ number_format($assetValue, 2) }}</span></td>
                                        <td class="text-right"><span class="sr-money profit">{{ $settings->currency_icon }}{{ number_format($profit, 2) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Card View --}}
                    <div class="sr-mobile-cards">
                        @foreach ($products as $product)
                            @php
                                $qty = $product->inventory_stocks_sum_quantity ?? 0;
                                $assetValue = $qty * $product->purchase_price;
                                $potentialSale = $qty * $product->price;
                                $profit = $potentialSale - $assetValue;
                            @endphp
                            <div class="sr-mobile-card">
                                <div class="sr-mobile-card-top">
                                    @if($product->thumb_image)
                                        <img src="{{ asset('storage/'.$product->thumb_image) }}" alt="" class="sr-mobile-card-img">
                                    @else
                                        <div class="sr-mobile-card-placeholder">N/A</div>
                                    @endif
                                    <div class="sr-mobile-card-name">
                                        <div class="name">{{ $product->name }}</div>
                                        <div class="meta">
                                            <span class="sr-cat-badge" style="font-size: 9.5px; padding: 2px 8px;">{{ $product->category->name ?? '-' }}</span>
                                            <span class="sr-brand-text">{{ $product->brand->name ?? '-' }}</span>
                                        </div>
                                    </div>
                                    @if($qty <= $product->min_inventory_qty)
                                        <span class="sr-stock-badge low">{{ number_format($qty) }}</span>
                                    @else
                                        <span class="sr-stock-badge ok">{{ number_format($qty) }}</span>
                                    @endif
                                </div>
                                <div class="sr-mobile-card-grid">
                                    <div class="sr-mobile-card-field">
                                        <span class="label">Unit Cost</span>
                                        <span class="value">{{ $settings->currency_icon }}{{ number_format($product->purchase_price, 2) }}</span>
                                    </div>
                                    <div class="sr-mobile-card-field">
                                        <span class="label">Unit Price</span>
                                        <span class="value">{{ $settings->currency_icon }}{{ number_format($product->price, 2) }}</span>
                                    </div>
                                    <div class="sr-mobile-card-field">
                                        <span class="label">Asset Value</span>
                                        <span class="value asset">{{ $settings->currency_icon }}{{ number_format($assetValue, 2) }}</span>
                                    </div>
                                    <div class="sr-mobile-card-field">
                                        <span class="label">Profit Potential</span>
                                        <span class="value profit">{{ $settings->currency_icon }}{{ number_format($profit, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Grand Total --}}
                    <div class="sr-grand-total">
                        <span class="sr-gt-label"> Grand Total</span>
                        <span class="sr-gt-values">
                            <span class="sr-gt-chip asset">
                                <span class="sr-gt-chip-label">Asset Value</span>
                                <span class="sr-gt-chip-val" id="span-grand-total-value">{{ $settings->currency_icon }}{{ number_format($totalValue, 2) }}</span>
                            </span>
                            <span class="sr-gt-chip profit">
                                <span class="sr-gt-chip-label">Profit Potential</span>
                                <span class="sr-gt-chip-val" id="span-grand-total-profit">{{ $settings->currency_icon }}{{ number_format($potentialProfit, 2) }}</span>
                            </span>
                        </span>
                    </div>

                    {{-- Pagination --}}
                    <div class="sr-pagination-wrap">
                        <div class="sr-pagination-info">
                            Showing
                            <strong>{{ $products->firstItem() ?? 0 }} – {{ $products->lastItem() ?? 0 }}</strong>
                            of
                            <strong>{{ $products->total() }}</strong>
                            products
                        </div>
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const exportMeta = {
            site_name: @json($settings->site_name ?? 'Inventory Management System'),
            contact_email: @json($settings->contact_email ?? 'N/A'),
            address: @json($settings->address ?? 'N/A'),
            generated_on: @json(date('F d, Y h:i A'))
        };
        const exportFileName = 'stock-report-' + @json(date('Y-m-d'));

        function buildExportHeaderText() {
            return [
                exportMeta.site_name,
                'Email: ' + exportMeta.contact_email,
                'Address: ' + exportMeta.address,
                '-------------------------------------------',
                'Stock Valuation Report',
                'Generated on: ' + exportMeta.generated_on,
                '-------------------------------------------',
                ''
            ].join('\n');
        }

        function buildPdfHeaderBlocks() {
            return [
                { text: exportMeta.site_name + '\n', fontSize: 16, bold: true, alignment: 'center' },
                { text: 'Email: ' + exportMeta.contact_email + '\n', fontSize: 10, alignment: 'center' },
                { text: 'Address: ' + exportMeta.address + '\n\n', fontSize: 10, alignment: 'center' },
                { text: 'Stock Valuation Report\n', fontSize: 14, bold: true, alignment: 'center', color: '#007bff' },
                { text: 'Generated on: ' + exportMeta.generated_on + '\n\n', fontSize: 10, alignment: 'center' }
            ];
        }

        function initStockDataTable() {
            const cleanCellText = function (value) {
                return $('<div>').html(value).text().replace(/\s+/g, ' ').trim();
            };
            const commonExportOptions = {
                format: {
                    body: function (data, row, column, node) {
                        if (column === 0) {
                            const nameElement = $(node).find('.sr-product-name, .font-weight-bold').first();
                            if (nameElement.length) return nameElement.text().trim();
                        }
                        if (column === 1) {
                            const category = $(node).find('.sr-cat-badge').first().text().trim() || '-';
                            const brand = $(node).find('.sr-brand-text').first().text().trim() || '-';
                            return 'Category: ' + category + '\nBrand: ' + brand;
                        }
                        return cleanCellText(data);
                    }
                }
            };
            const table = $('#table-stock').DataTable({
                dom: 'Brt',
                paging: false,
                info: false,
                searching: false,
                buttons: [
                    { extend: 'copy', messageTop: buildExportHeaderText(), exportOptions: commonExportOptions },
                    { extend: 'csv', messageTop: buildExportHeaderText(), filename: exportFileName, exportOptions: commonExportOptions },
                    { extend: 'excel', messageTop: buildExportHeaderText(), title: 'Stock Report', filename: exportFileName, exportOptions: commonExportOptions },
                    {
                        extend: 'pdf', messageTop: '', title: 'Stock Report', filename: exportFileName,
                        exportOptions: commonExportOptions,
                        customize: function (doc) { doc.content.splice(0, 0, { text: buildPdfHeaderBlocks() }); }
                    },
                    {
                        extend: 'print', messageTop: function () { return $('.export-header').html(); }, title: ''
                    }
                ]
            });
            $('#table-stock_wrapper .dt-buttons').hide();
            return table;
        }

        let table = initStockDataTable();

        function initExportButtons() {
            $('#btn-export-excel').off('click').on('click', function () { table.button(2).trigger(); });
            $('#btn-export-pdf').off('click').on('click', function () { table.button(3).trigger(); });
            $('#btn-print').off('click').on('click', function () { table.button(4).trigger(); });
        }
        initExportButtons();

        $(document).on('change select2:select', '#stock-filter-form select[name="category_id"], #stock-filter-form select[name="brand_id"]', function () {
            $('#stock-filter-form').trigger('submit');
        });
    </script>
@endpush