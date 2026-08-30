@extends('backend.layouts.master')

@push('css')
<style>
    :root {
        --pp-obsidian: #0b1120;
        --pp-obsidian-2: #060a14;
        --pp-amber: #d4a24e;
        --pp-amber-bright: #ecc78b;
        --pp-amber-deep: #b8852a;
        --pp-amber-soft: rgba(212, 162, 78, 0.08);
        --pp-amber-glow: rgba(212, 162, 78, 0.30);
        --pp-border: rgba(11, 17, 32, 0.07);
        --pp-border-hover: rgba(212, 162, 78, 0.15);
        --pp-ink: #161e2e;
        --pp-ink-soft: #2d3748;
        --pp-muted: #6b788e;
        --pp-danger: #dc5a52;
        --pp-surface: #f8f9fc;
        --pp-surface-hover: #f1f3f8;
        --pp-font: 'Inter', 'Segoe UI', system-ui, sans-serif;
        --pp-radius-sm: 12px;
        --pp-radius-md: 14px;
        --pp-radius-lg: 20px;
        --pp-shadow-card: 0 1px 3px rgba(11,17,32,0.04), 0 8px 20px -12px rgba(11,17,32,0.12);
        --pp-shadow-card-hover: 0 12px 32px -12px rgba(11,17,32,0.16), 0 0 0 1px rgba(212,162,78,0.06);
        --pp-shadow-lift: 0 20px 50px -16px rgba(11,17,32,0.2), 0 0 0 1px rgba(212,162,78,0.1);
    }

    @keyframes ppFadeSlide {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .navbar .nav-link {
        height: 26px !important;
    }
    .modal-backdrop {
        opacity: 0.5;
        z-index: 1040;
    }
    .modal-backdrop.fade { opacity: 0; }
    .modal-backdrop.fade.show { opacity: 0.55; }

    #product-grid-container {
        min-height: 400px;
    }

    @media (min-width: 992px) and (max-width: 1399.98px) {
        .col-lg-5th {
            flex: 0 0 25% !important;
            max-width: 25% !important;
        }
    }
    @media (min-width: 1400px) {
        .col-xl-5th {
            flex: 0 0 20% !important;
            max-width: 20% !important;
        }
    }

    /* ===== Section Header ===== */
    .pp-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
        margin-top: 18px;
        padding: 18px 22px;
        background: #fff;
        border-radius: var(--pp-radius-md);
        box-shadow: 0 1px 3px rgba(11,17,32,0.04), 0 8px 20px -12px rgba(11,17,32,0.12);
        position: relative;
        overflow: hidden;
    }
    .pp-header::before { display: none; }
    .pp-header::after { display: none; }
    .pp-header h1 {
        position: relative;
        z-index: 1;
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
        position: relative;
        z-index: 1;
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
        color: var(--pp-muted);
    }
    .pp-breadcrumb-item a {
        color: var(--pp-amber-deep);
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .pp-breadcrumb-item a:hover { color: var(--pp-amber); }
    .pp-breadcrumb-item.active { color: var(--pp-ink-soft); }

    /* ===== Filter Card ===== */
    .pp-filter-card {
        position: relative;
        border-radius: var(--pp-radius-md) !important;
        border: 1px solid var(--pp-border) !important;
        background: rgba(255,255,255,0.75) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: var(--pp-shadow-card) !important;
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    }
    .pp-filter-card:hover {
        box-shadow: var(--pp-shadow-card-hover) !important;
    }
    .pp-filter-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2.5px;
        background: linear-gradient(90deg, var(--pp-amber-bright), var(--pp-amber) 50%, transparent 96%);
    }
    .pp-filter-card .card-body { padding: 18px 20px 16px !important; }

    .pp-search-wrap {
        border-radius: var(--pp-radius-lg) !important;
        background: #fff !important;
        border: 1.5px solid var(--pp-border) !important;
        transition: all 0.25s ease;
        overflow: hidden;
    }
    .pp-search-wrap:focus-within {
        border-color: var(--pp-amber) !important;
        box-shadow: 0 0 0 3px var(--pp-amber-soft), 0 4px 12px -8px rgba(212,162,78,0.15) !important;
        background: #fff !important;
    }
    .pp-search-wrap .form-control {
        font-size: 12.5px;
        height: 36px !important;
        background: transparent !important;
        font-weight: 500;
        color: var(--pp-ink);
    }
    .pp-search-wrap .form-control::placeholder { color: #aab2c0; }
    .pp-search-wrap .input-group-text { font-size: 12px; color: #aab2c0; }

    .pp-select2 .select2-selection--single {
        height: 36px !important;
        border-radius: var(--pp-radius-lg) !important;
        border: 1.5px solid var(--pp-border) !important;
        background: #fff !important;
        display: flex !important;
        align-items: center;
        transition: all 0.25s ease;
    }
    .pp-select2 .select2-selection--single .select2-selection__rendered {
        color: var(--pp-ink);
        font-size: 12px;
        font-weight: 500;
        padding-left: 14px;
        line-height: 34px;
    }
    .pp-select2 .select2-selection--single .select2-selection__arrow {
        height: 34px;
        right: 10px;
    }
    .pp-select2 .select2-selection--single .select2-selection__arrow b {
        border-color: var(--pp-amber) transparent transparent transparent !important;
        border-width: 4px 4px 0 !important;
    }
    .pp-select2.select2-container--open .select2-selection--single,
    .pp-select2.select2-container--focus .select2-selection--single {
        border-color: var(--pp-amber) !important;
        box-shadow: 0 0 0 3px var(--pp-amber-soft) !important;
        background: #fff !important;
    }
    .pp-select2 .select2-dropdown {
        border-radius: var(--pp-radius-sm) !important;
        border: 1px solid var(--pp-border) !important;
        box-shadow: var(--pp-shadow-lift) !important;
        overflow: hidden;
        margin-top: 4px;
    }
    .pp-select2 .select2-results__option {
        font-size: 12px !important;
        padding: 8px 12px !important;
        transition: background 0.15s;
    }
    .pp-select2 .select2-results__option--highlighted[aria-selected] {
        background: linear-gradient(135deg, var(--pp-amber-bright), var(--pp-amber)) !important;
        color: #1a1306 !important;
    }
    .pp-select2 .select2-results__option[aria-selected=true] {
        background: var(--pp-amber-soft) !important;
        color: var(--pp-ink) !important;
    }
    .pp-select2 .select2-search--dropdown .select2-search__field {
        border-radius: 8px !important;
        border: 1px solid var(--pp-border) !important;
        padding: 6px 10px !important;
        font-size: 12px !important;
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
    .pp-btn-emerald {
        background: linear-gradient(145deg, #34d399, #16a34a) !important;
        color: #fff !important;
        box-shadow: 0 4px 14px -4px rgba(22, 163, 74, 0.34);
    }
    .pp-btn-emerald:hover { filter: brightness(1.06); box-shadow: 0 8px 24px -6px rgba(22, 163, 74, 0.4); }
    .pp-btn-reset {
        background: #fff !important;
        color: var(--pp-amber-deep) !important;
        border: 1.5px solid rgba(212, 162, 78, 0.25) !important;
        border-radius: 10px !important;
        font-weight: 700 !important;
        font-size: 11.5px !important;
        padding: 7px 14px !important;
        letter-spacing: 0.2px;
        transition: all 0.2s ease !important;
        box-shadow: 0 2px 8px rgba(212, 162, 78, 0.06);
    }
    .pp-btn-reset:hover {
        background: rgba(212, 162, 78, 0.07) !important;
        border-color: var(--pp-amber) !important;
        box-shadow: 0 4px 16px -6px rgba(212, 162, 78, 0.2);
        transform: translateY(-1px);
    }
    .pp-btn-reset i { font-size: 11px; }

    /* ===== Modals ===== */
    .pp-modal .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 30px 60px -20px rgba(11,17,32,0.45);
    }
    .pp-modal .modal-header {
        background: linear-gradient(135deg, #0a0e1a, #131a2b);
        border-bottom: none;
        padding: 14px 20px;
        position: relative;
    }
    .pp-modal .modal-header::after {
        content: '';
        position: absolute;
        left: 0; right: 0; bottom: -1px;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--pp-amber-bright), transparent);
        opacity: 0.6;
    }
    .pp-modal .modal-title {
        color: #f5f2ea;
        font-weight: 700;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pp-modal .modal-title i { color: var(--pp-amber-bright) !important; font-size: 15px; }
    .pp-modal .close {
        color: rgba(255,255,255,0.5);
        text-shadow: none;
        font-size: 20px;
        transition: all 0.2s;
    }
    .pp-modal .close:hover { opacity: 1; color: var(--pp-amber-bright); }
    .pp-modal .modal-body { padding: 18px 20px !important; }
    .pp-modal .modal-footer {
        background: #f8f9fc;
        border-top: 1px solid var(--pp-border);
        padding: 12px 20px;
        gap: 8px;
    }
    .pp-modal .modal-footer .btn {
        border-radius: var(--pp-radius-lg);
        font-weight: 600;
        font-size: 11.5px;
        padding: 7px 18px;
        transition: all 0.2s ease;
    }
    .pp-modal .btn-amber {
        background: linear-gradient(145deg, var(--pp-amber-bright), var(--pp-amber-deep));
        border: none;
        color: #1a1306;
        box-shadow: 0 4px 14px -4px rgba(212, 162, 78, 0.4);
    }
    .pp-modal .btn-amber:hover { filter: brightness(1.06); transform: translateY(-1px); }
    .pp-modal .btn-secondary {
        background: #e8ebf0;
        border: 1px solid var(--pp-border);
        color: var(--pp-ink-soft);
    }
    .pp-modal .btn-secondary:hover { background: #dee2e9; border-color: var(--pp-amber); }

    .pp-star-rating .rating-star { transition: all 0.2s ease; }
    .pp-star-rating .rating-star:hover { transform: scale(1.2) rotate(-5deg); color: #f59e0b !important; }

    /* ===== Product Card ===== */
    .pp-card {
        border-radius: var(--pp-radius-md) !important;
        border: 1px solid var(--pp-border) !important;
        background: #fff !important;
        box-shadow: var(--pp-shadow-card) !important;
        transition: all 0.3s cubic-bezier(.2,.8,.2,1) !important;
        overflow: hidden;
        animation: ppFadeSlide 0.4s ease both;
    }
    .pp-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--pp-shadow-card-hover) !important;
        border-color: var(--pp-border-hover) !important;
    }
    .pp-card-img-wrap {
        height: 180px;
        background: linear-gradient(180deg, #fafbfc 0%, #f4f5f8 100%);
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 1px solid var(--pp-border);
    }
    .pp-card-img-wrap::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(255,255,255,0) 60%, rgba(248,249,252,0.8) 100%);
        pointer-events: none;
    }
    .pp-card-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s cubic-bezier(.2,.8,.2,1);
        position: relative;
        z-index: 0;
    }
    .pp-card:hover .pp-card-img-wrap img {
        transform: scale(1.1);
    }
    .pp-card-body {
        padding: 10px 12px 12px;
    }
    .pp-card-title {
        font-weight: 700;
        font-size: 13.5px;
        color: var(--pp-ink);
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 6px !important;
        letter-spacing: -0.1px;
        transition: color 0.3s;
    }
    .pp-card:hover .pp-card-title {
        color: var(--pp-amber-deep);
    }
    .pp-badge-category {
        background: var(--pp-surface);
        color: var(--pp-muted);
        font-size: 9px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 12px;
        border: 1px solid var(--pp-border);
        transition: all 0.25s;
    }
    .pp-card:hover .pp-badge-category {
        background: var(--pp-amber-soft);
        border-color: var(--pp-border-hover);
        color: var(--pp-amber-deep);
    }
    .pp-badge-stock {
        font-size: 9px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 12px;
        transition: all 0.25s;
    }
    .pp-badge-stock.in-stock {
        background: rgba(22, 163, 74, 0.08);
        color: #16a34a;
        border: 1px solid rgba(22, 163, 74, 0.15);
    }
    .pp-badge-stock.out-of-stock {
        background: rgba(220, 90, 82, 0.08);
        color: #dc5a52;
        border: 1px solid rgba(220, 90, 82, 0.15);
    }
    .pp-badge-type {
        font-size: 8px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255,255,255,0.2);
    }
    .pp-variant-scroll {
        max-height: 110px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--pp-amber) transparent;
    }
    .pp-variant-scroll::-webkit-scrollbar { width: 3px; }
    .pp-variant-scroll::-webkit-scrollbar-thumb {
        background: var(--pp-amber);
        border-radius: 10px;
    }
    .pp-variant-scroll::-webkit-scrollbar-track { background: transparent; }

    .pp-price-box {
        background: var(--pp-surface);
        border-radius: 10px;
        padding: 8px 11px;
        border: 1px solid var(--pp-border);
        transition: all 0.3s;
    }
    .pp-card:hover .pp-price-box {
        background: linear-gradient(135deg, #fefcf8, #faf7f0);
        border-color: var(--pp-border-hover);
    }
    .pp-price-label {
        font-size: 9.5px;
        color: var(--pp-muted);
        font-weight: 600;
        letter-spacing: 0.2px;
    }
    .pp-price-value {
        font-weight: 700;
        font-size: 12.5px;
        color: var(--pp-ink-soft);
    }
    .pp-btn-card {
        border-radius: var(--pp-radius-lg) !important;
        font-weight: 700 !important;
        font-size: 10.5px !important;
        padding: 5px 13px !important;
        transition: all 0.25s cubic-bezier(.2,.8,.2,1) !important;
    }
    .pp-btn-card:hover { transform: translateY(-1.5px); box-shadow: 0 4px 12px -4px rgba(212,162,78,0.2); }
    .pp-btn-outline {
        border: 1.5px solid var(--pp-border) !important;
        color: var(--pp-ink-soft) !important;
        background: transparent !important;
    }
    .pp-btn-outline:hover {
        border-color: var(--pp-amber) !important;
        background: var(--pp-amber-soft) !important;
        color: var(--pp-amber-deep) !important;
    }
    .pp-edit-btn {
        border-radius: var(--pp-radius-lg) !important;
        font-weight: 600 !important;
        font-size: 10px !important;
        padding: 5px 11px !important;
        transition: all 0.25s ease !important;
    }
    .pp-edit-btn:hover {
        transform: translateY(-1.5px);
        box-shadow: 0 4px 12px -4px rgba(11,17,32,0.1);
    }
    .pp-status-switch .custom-switch-indicator {
        border-radius: 16px !important;
        width: 31px !important;
        height: 17px !important;
        transition: all 0.25s ease !important;
        border: 1px solid var(--pp-border);
    }
    .pp-status-switch .custom-switch-indicator::after {
        width: 13px !important;
        height: 13px !important;
        top: 2px !important;
        left: 2px !important;
        transition: all 0.25s ease !important;
    }
    .pp-status-switch .custom-switch-input:checked ~ .custom-switch-indicator {
        background: linear-gradient(135deg, var(--pp-amber-bright), var(--pp-amber)) !important;
        border-color: var(--pp-amber) !important;
        box-shadow: 0 2px 8px -2px rgba(212, 162, 78, 0.3);
    }
    .pp-count-badge {
        background: #fff;
        border: 1px solid var(--pp-border);
        color: var(--pp-muted);
        font-size: 11px;
        font-weight: 600;
        padding: 6px 16px;
        border-radius: var(--pp-radius-lg);
        box-shadow: 0 1px 3px rgba(11,17,32,0.03);
    }
    .pp-count-badge strong { color: var(--pp-amber-deep); }
    .pp-pagination .pagination .page-link {
        border-radius: 10px !important;
        border: 1px solid var(--pp-border) !important;
        margin: 0 2px;
        font-weight: 600;
        font-size: 11px;
        color: var(--pp-muted);
        padding: 6px 11px;
        transition: all 0.2s cubic-bezier(.2,.8,.2,1);
        background: #fff;
    }
    .pp-pagination .pagination .page-link:hover {
        border-color: var(--pp-amber) !important;
        background: var(--pp-amber-soft) !important;
        color: var(--pp-amber-deep) !important;
        transform: translateY(-1px);
    }
    .pp-pagination .pagination .page-item.active .page-link {
        background: linear-gradient(145deg, var(--pp-amber-bright), var(--pp-amber)) !important;
        border-color: var(--pp-amber) !important;
        color: #1a1306 !important;
        box-shadow: 0 4px 14px -3px rgba(212, 162, 78, 0.35);
        transform: translateY(-1px);
    }
    .pp-pagination .pagination .page-item.disabled .page-link {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .pp-pagination .pagination .page-item:first-child .page-link,
    .pp-pagination .pagination .page-item:last-child .page-link {
        border-radius: 10px !important;
    }
    .pp-rating-star-small {
        font-size: 11px;
    }
    .pp-card-badges {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 11;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .pp-card-actions {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 6px;
    }

/* ===== Mobile Responsive ===== */
@media (max-width: 991.98px) {
    .pp-header { flex-direction: column; align-items: flex-start; padding: 14px 16px; gap: 8px; }
    .pp-header h1 { font-size: 16px; gap: 8px; }
    .pp-header h1 .pp-icon { width: 28px; height: 28px; min-width: 28px; font-size: 12px; }
    .pp-breadcrumb { font-size: 11px; }
    .pp-breadcrumb-item { padding-right: 10px; }
    .pp-breadcrumb-item + .pp-breadcrumb-item { padding-left: 10px; }
}

@media (max-width: 767.98px) {
    .pp-header { margin-bottom: 14px; }
    .pp-header h1 { font-size: 14px; }
    .pp-filter-card .card-body { padding: 12px 12px 10px !important; }
    .pp-search-wrap .form-control { font-size: 11px !important; height: 32px !important; }
    #filter-form .col-12 { margin-bottom: 10px; }
    #filter-form .pp-btn { width: 100%; text-align: center; padding: 6px 12px !important; font-size: 10.5px !important; display: block; }
    .pp-card-img-wrap { height: 110px; }
    .pp-card-body { padding: 8px 10px 10px; }
    .pp-card-title { font-size: 11.5px; }
    .pp-badge-category, .pp-badge-stock { font-size: 8px; padding: 2px 7px; }
    .pp-price-label { font-size: 8px; }
    .pp-price-value { font-size: 10.5px; }
    .pp-btn-card { font-size: 9px !important; padding: 4px 10px !important; }
    .pp-edit-btn { font-size: 9px !important; padding: 4px 9px !important; }
    .pp-variant-scroll { max-height: 80px; }
    .pp-count-badge { font-size: 9px; padding: 4px 10px; }
    .pp-pagination .pagination .page-link { font-size: 9px !important; padding: 4px 8px !important; margin: 0 1px; }
    #floating-baskets-container { bottom: 16px !important; right: 16px !important; gap: 10px !important; }
    .basket-fab { width: 38px !important; height: 38px !important; }
    .basket-fab i { font-size: 14px; }
    #request-basket-count, #basket-count { min-width: 18px; height: 18px; font-size: 9px; top: -5px; right: -5px; }
}

@media (max-width: 575.98px) {
    .pp-card-img-wrap { height: 90px; }
    .pp-card-title { font-size: 10.5px; -webkit-line-clamp: 1; }
    .pp-card-body { padding: 6px 8px 8px; }
    .pp-price-box { padding: 5px 8px; }
    .pp-price-value { font-size: 9.5px; }
    .pp-price-label { font-size: 7.5px; }
    .pp-badge-category, .pp-badge-stock { font-size: 7px; padding: 1px 6px; }
    .pp-btn-card { font-size: 8px !important; padding: 3px 8px !important; }
    .pp-edit-btn { font-size: 8px !important; padding: 3px 7px !important; }
}

</style>
@endpush

@section('content')
    <section class="section">
        <div class="pp-header">
            <h1>
                Products
            </h1>
            <div class="pp-breadcrumb">
                <span class="pp-breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></span>
                <span class="pp-breadcrumb-item active">Products</span>
            </div>
        </div>

        <div class="section-body">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card border-0 pp-filter-card">
                        <div class="card-body">
                            <form id="filter-form">
                                <div class="row g-4 align-items-end">
                                    <div class="col-12 col-md-3">
                                        <div class="input-group pp-search-wrap shadow-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text border-0 pl-3 pr-1 bg-transparent">
                                                    <i class="fas fa-search" style="color: #aab2c0;"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control search-input border-0 pl-1" name="search" placeholder="Search..." value="{{ request('search') }}" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <select name="category" id="category" class="form-control select2 pp-select2">
                                            <option value="">All Categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <select name="sub_category" id="sub_category" class="form-control select2 pp-select2">
                                            <option value="">Sub Category</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <select name="child_category" id="child_category" class="form-control select2 pp-select2">
                                            <option value="">Child Category</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        @can('Manage Products')
                                            <a href="{{ route('admin.products.import.view') }}" class="btn pp-btn pp-btn-emerald shadow-sm d-block mb-1">
                                                <i class="fas fa-file-import mr-1"></i> Import
                                            </a>
                                            <a href="{{ route('admin.products.create') }}" class="btn pp-btn pp-btn-amber shadow-sm d-block">
                                                <i class="fas fa-plus mr-1"></i> Create
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                                <hr style="border-top: 1px solid var(--pp-border); margin: 6px 0 10px;">
                                <div class="row g-4 align-items-end">
                                    <div class="col-12 col-md-3">
                                        <select name="sort" id="sort" class="form-control select2 pp-select2">
                                            <option value="">Sort by</option>
                                            <option value="latest" {{ request('sort') == 'latest' || !request('sort') ? 'selected' : '' }}>Latest</option>
                                            <option value="a-z" {{ request('sort') == 'a-z' ? 'selected' : '' }}>A-Z</option>
                                            <option value="z-a" {{ request('sort') == 'z-a' ? 'selected' : '' }}>Z-A</option>
                                            <option value="active" {{ request('sort') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ request('sort') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <select name="alphabet" id="alphabet-dropdown" class="form-control select2 pp-select2">
                                            <option value="">Alphabet (All)</option>
                                            @foreach(range('A', 'Z') as $char)
                                                <option value="{{ $char }}" {{ request('alphabet') == $char ? 'selected' : '' }}>{{ $char }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <select name="product_type" id="product_type_filter" class="form-control select2 pp-select2">
                                            <option value="">Occasion / Type</option>
                                            <option value="new_arrival" {{ request('product_type') == 'new_arrival' ? 'selected' : '' }}>New Arrival</option>
                                            <option value="upcoming" {{ request('product_type') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                            @foreach ($productTypes as $type)
                                                <option value="{{ $type->id }}" {{ request('product_type') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if(isset($vendors) && $vendors->count() > 0)
                                    <div class="col-12 col-md-3">
                                        <select name="vendor" id="vendor_filter" class="form-control select2 pp-select2">
                                            <option value="">Select Vendor</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}" {{ request('vendor') == $vendor->id ? 'selected' : '' }}>{{ $vendor->shop_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    <div class="col-12 col-md-1">
                                        <button type="button" id="reset-filters" class="btn pp-btn pp-btn-reset btn-sm shadow-sm w-100">
                                            <i class="fas fa-redo mr-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div id="product-grid-container">
                @include('backend.product.product_grid')
            </div>
        </div>
    </section>

<!-- Quick Variant Selection Modal -->
<div class="modal fade pp-modal" id="quickVariantModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 520px; width: 95%;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title font-weight-bold text-dark mb-0" id="quickVariantModalTitle" style="font-size: 15px;">
                    <i class="fas fa-layer-group mr-2"></i>Select Product Variants
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3 p-md-4" style="max-height: 70vh; overflow-y: auto;">
                <div class="d-flex align-items-center mb-3 p-2 bg-light rounded" style="gap: 12px; border: 1px solid #e2e8f0;">
                    <img id="variant_modal_product_img" src="{{ asset('uploads/no-image.svg') }}" onerror="this.onerror=null; this.src='{{ asset('uploads/no-image.svg') }}';" style="width: 48px; height: 48px; object-fit: contain; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; padding: 2px;">
                    <div class="flex-fill" style="min-width: 0;">
                        <h6 id="variant_modal_product_name" class="mb-0 text-truncate font-weight-bold text-dark" style="font-size: 14px;">Product Name</h6>
                        <small class="text-muted d-block text-truncate" id="variant_modal_product_vendor">Supplier</small>
                    </div>
                </div>

                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-sm table-bordered mb-0" style="table-layout: fixed; width: 100%;">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 48%;">Variant Name</th>
                                <th class="text-center" style="width: 24%;">Stock</th>
                                <th class="text-center" style="width: 28%;">Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="quick_variant_modal_tbody">
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">
                                    <div class="spinner-border spinner-border-sm text-warning mr-1"></div> Loading variants...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3 border-top d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Cancel</button>
                <button type="button" class="btn btn-amber btn-sm px-4 font-weight-bold shadow-sm" id="btn_submit_quick_variants" style="border-radius: 6px;">
                    <i class="fas fa-check mr-1"></i> Add Selected to Cart
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

<!-- Rating Modal -->
@push('scripts')
    <style>
        .hover-white { transition: color 0.2s ease; }
        .hover-white:hover { color: #fff !important; }
        .cursor-pointer { cursor: pointer; }

        /* In-Cart State Indicator */
        .add-to-basket.added, .add-to-request-basket.added {
            background: #28a745 !important;
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: #fff !important;
        }
    </style>
    <script>
        let initialLoad = true;
        let currentModalContext = { productId: 0, cartType: 'booking', productName: '', productImg: '', vendorName: '' };

        $(document).ready(function() {

            // Fast Toastr defaults
            if (window.toastr) {
                toastr.options.timeOut = 1800;
                toastr.options.showDuration = 150;
                toastr.options.hideDuration = 150;
            }

            // Ensure grid is visible on page load - initial state should be opacity 1
            if ($('#product-grid-container').data('loaded')) {
                $('#product-grid-container').stop(true, true).css('opacity', '1');
            }
            $('#grid-loader').hide();

            // --- Fast Database Cart Logic ---
            let activeBookingIds = [];
            let activeRequestIds = [];

            function syncCartState() {
                if (window.cartStore && ((window.cartStore.booking && window.cartStore.booking.ids.length > 0) || (window.cartStore.request && window.cartStore.request.ids.length > 0))) {
                    activeBookingIds = (window.cartStore.booking.ids || []).map(Number);
                    activeRequestIds = (window.cartStore.request.ids || []).map(Number);
                    applyButtonStates();
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.cart.all-state') }}",
                    method: 'GET',
                    success: function(data) {
                        if (data && data.booking && data.request) {
                            if (window.cartStore) {
                                // Only initialize if store doesn't already have local optimistic items
                                if (!window.cartStore.booking.items.length) window.cartStore.booking = data.booking;
                                if (!window.cartStore.request.items.length) window.cartStore.request = data.request;
                            }
                            activeBookingIds = (data.booking.ids || []).map(Number);
                            activeRequestIds = (data.request.ids || []).map(Number);
                            
                            if (window.updateGlobalCartBadges) {
                                window.updateGlobalCartBadges(data.booking.count, data.request.count);
                            }
                            applyButtonStates();
                        }
                    }
                });
            }

            function applyButtonStates() {
                const bIds = (window.cartStore && window.cartStore.booking && window.cartStore.booking.ids) ? window.cartStore.booking.ids.map(Number) : activeBookingIds;
                const rIds = (window.cartStore && window.cartStore.request && window.cartStore.request.ids) ? window.cartStore.request.ids.map(Number) : activeRequestIds;

                $('.add-to-basket').each(function() {
                    const id = Number($(this).data('id'));
                    if (bIds.includes(id)) {
                        $(this).addClass('added').html('<i class="fas fa-check"></i>').attr('title', 'Added to Procurement Basket');
                    } else {
                        $(this).removeClass('added').html('<i class="fas fa-shopping-basket"></i>').attr('title', 'Add to Procurement Basket');
                    }
                });

                $('.add-to-request-basket').each(function() {
                    const id = Number($(this).data('id'));
                    if (rIds.includes(id)) {
                        $(this).addClass('added').html('<i class="fas fa-check"></i>').attr('title', 'Added to Stock Transfer');
                    } else {
                        $(this).removeClass('added').html('<i class="fas fa-file-import"></i>').attr('title', 'Add to Stock Transfer');
                    }
                });
            }

            // Initial sync on page load
            syncCartState();

            // Open Variant Modal or Direct Toggle for Booking Basket
            $(document).on('click', '.add-to-basket', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const $btn = $(this);
                const productId = Number($btn.data('id'));
                const hasVariants = Number($btn.data('has-variants')) === 1;
                if (!productId) return;

                const $card = $btn.closest('.pp-card');
                const productName = $card.find('.pp-card-title').text().trim() || 'Product';
                const productImg = $card.find('.pp-card-img-wrap img').attr('src') || "{{ asset('uploads/no-image.svg') }}";
                const vendorName = $card.find('.pp-badge-category').text().trim() || 'Primary Supplier';

                if (hasVariants) {
                    openQuickVariantModal(productId, 'booking', productName, productImg, vendorName);
                    return;
                }

                // Direct Toggle (Single product)
                handleDirectCartToggle($btn, productId, 'booking', productName, productImg, vendorName);
            });

            // Open Variant Modal or Direct Toggle for Request Basket
            $(document).on('click', '.add-to-request-basket', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const $btn = $(this);
                const productId = Number($btn.data('id'));
                const hasVariants = Number($btn.data('has-variants')) === 1;
                if (!productId) return;

                const $card = $btn.closest('.pp-card');
                const productName = $card.find('.pp-card-title').text().trim() || 'Product';
                const productImg = $card.find('.pp-card-img-wrap img').attr('src') || "{{ asset('uploads/no-image.svg') }}";
                const vendorName = $card.find('.pp-badge-category').text().trim() || 'Primary Supplier';

                if (hasVariants) {
                    openQuickVariantModal(productId, 'request', productName, productImg, vendorName);
                    return;
                }

                // Direct Toggle (Single product)
                handleDirectCartToggle($btn, productId, 'request', productName, productImg, vendorName);
            });

            function openQuickVariantModal(productId, cartType, productName, productImg, vendorName) {
                currentModalContext = { productId, cartType, productName, productImg, vendorName };
                $('#variant_modal_product_name').text(productName);
                const defaultImg = "{{ asset('uploads/no-image.svg') }}";
                $('#variant_modal_product_img').attr('src', productImg || defaultImg);
                $('#variant_modal_product_vendor').text(vendorName);
                $('#quickVariantModalTitle').html(`<i class="fas ${cartType === 'booking' ? 'fa-shopping-basket text-warning' : 'fa-truck-moving text-danger'} mr-2"></i>Select Variants for ${cartType === 'booking' ? 'Procurement' : 'Stock Transfer'}`);
                
                $('#quick_variant_modal_tbody').html('<tr><td colspan="3" class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm text-warning mr-1"></div> Loading variants...</td></tr>');
                $('#quickVariantModal').modal('show');

                $.ajax({
                    url: `/admin/products/${productId}/variants`,
                    type: 'GET',
                    success: function(res) {
                        if (res && res.status === 'success' && res.variants && res.variants.length > 0) {
                            if (res.product) {
                                if (res.product.name) $('#variant_modal_product_name').text(res.product.name);
                                if (res.product.thumb_image) $('#variant_modal_product_img').attr('src', res.product.thumb_image);
                                if (res.product.vendor || res.product.category) $('#variant_modal_product_vendor').text(res.product.vendor || res.product.category);
                            }

                            let rows = '';
                            const storeItems = (window.cartStore && window.cartStore[cartType] && window.cartStore[cartType].items) ? window.cartStore[cartType].items : [];
                            
                            res.variants.forEach(function(v) {
                                let vName = v.name;
                                if (!vName) {
                                    const parts = [];
                                    if (v.color) parts.push(v.color);
                                    if (v.size) parts.push(v.size);
                                    vName = parts.length > 0 ? parts.join(' - ') : 'Variant #' + v.id;
                                }

                                const existingItem = storeItems.find(i => Number(i.product_id) === productId && Number(i.variant_id) === Number(v.id));
                                // Default quantity is 0 on initial add, or keep existing cart quantity if already added
                                const defaultQty = existingItem ? (parseFloat(existingItem.quantity) || 0) : 0;

                                rows += `
                                    <tr>
                                        <td class="align-middle text-truncate" title="${vName}" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <strong style="font-size: 13px;">${vName}</strong>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge ${parseFloat(v.qty) > 0 ? 'badge-success' : 'badge-danger'} font-weight-bold" style="font-size: 11px; padding: 3px 7px;">${parseFloat(v.qty || 0)}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <input type="number" step="any" min="0" class="form-control form-control-sm text-center variant-bulk-qty font-weight-bold" data-variant-id="${v.id}" data-variant-name="${vName}" data-price="${v.price || 0}" value="${defaultQty}" onfocus="if(this.value==='0') this.select();" style="border-radius: 5px;">
                                        </td>
                                    </tr>
                                `;
                            });
                            $('#quick_variant_modal_tbody').html(rows);
                        } else {
                            $('#quick_variant_modal_tbody').html('<tr><td colspan="3" class="text-center py-3 text-muted">No variants found for this product.</td></tr>');
                        }
                    },
                    error: function() {
                        $('#quick_variant_modal_tbody').html('<tr><td colspan="3" class="text-center py-3 text-danger">Failed to load variants.</td></tr>');
                    }
                });
            }

            // Submit Selected Variants from Modal (0ms Instant Reactivity)
            $('#btn_submit_quick_variants').on('click', function() {
                const { productId, cartType, productName, productImg, vendorName } = currentModalContext;
                const targetBtnClass = (cartType === 'booking') ? '.add-to-basket' : '.add-to-request-basket';
                const variantsToAdd = [];

                $('.variant-bulk-qty').each(function() {
                    const vId = $(this).data('variant-id');
                    const vName = $(this).data('variant-name');
                    const vPrice = parseFloat($(this).data('price')) || 0;
                    const qty = parseFloat($(this).val()) || 0;

                    if (qty > 0) {
                        variantsToAdd.push({
                            variant_id: vId,
                            variant_name: vName,
                            price: vPrice,
                            quantity: qty
                        });
                    }
                });

                if (variantsToAdd.length === 0) {
                    // Remove variants for this product
                    if (window.cartStore && window.cartStore[cartType]) {
                        window.cartStore[cartType].items = window.cartStore[cartType].items.filter(i => Number(i.product_id) !== productId);
                        window.cartStore[cartType].ids = window.cartStore[cartType].ids.filter(id => Number(id) !== productId);
                        window.cartStore[cartType].count = window.cartStore[cartType].items.length;
                        if (window.updateGlobalCartBadges) {
                            if (cartType === 'booking') window.updateGlobalCartBadges(window.cartStore.booking.count, undefined);
                            else window.updateGlobalCartBadges(undefined, window.cartStore.request.count);
                        }
                    }

                    const defaultLabel = (cartType === 'booking') 
                        ? '<i class="fas fa-shopping-basket"></i>' 
                        : '<i class="fas fa-file-import"></i>';
                    $(`${targetBtnClass}[data-id="${productId}"]`).removeClass('added').html(defaultLabel);
                    if (window.toastr) toastr.info(`Removed from ${cartType === 'booking' ? 'Procurement' : 'Stock Transfer'} basket`);
                    $('#quickVariantModal').modal('hide');

                    $.ajax({
                        url: "{{ route('admin.cart.add') }}",
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        data: {
                            product_id: productId,
                            cart_type: cartType,
                            action: 'bulk_variants',
                            is_bulk_variants: 1,
                            variants: []
                        }
                    });
                    return;
                }

                // 1. Optimistic Store Update (0ms)
                if (window.cartStore && window.cartStore[cartType]) {
                    // Remove previous variants for this product
                    window.cartStore[cartType].items = window.cartStore[cartType].items.filter(i => Number(i.product_id) !== productId);
                    
                    variantsToAdd.forEach(function(v) {
                        window.cartStore[cartType].items.push({
                            id: `temp_${productId}_${v.variant_id}`,
                            cart_id: `temp_${productId}_${v.variant_id}`,
                            product_id: productId,
                            variant_id: v.variant_id,
                            variant_name: v.variant_name,
                            product_name: productName,
                            thumb_image: productImg,
                            vendor_name: vendorName,
                            sku: '',
                            price: v.price,
                            quantity: v.quantity
                        });
                    });

                    if (!window.cartStore[cartType].ids.map(Number).includes(productId)) {
                        window.cartStore[cartType].ids.push(productId);
                    }
                    window.cartStore[cartType].count = window.cartStore[cartType].items.length;

                    if (cartType === 'booking') {
                        window.updateGlobalCartBadges(window.cartStore.booking.count, undefined);
                    } else {
                        window.updateGlobalCartBadges(undefined, window.cartStore.request.count);
                    }
                }

                // 2. Instant Button Update & Snappy Toastr (0ms)
                $(`${targetBtnClass}[data-id="${productId}"]`).addClass('added').html('<i class="fas fa-check"></i>').attr('title', 'Added');
                if (window.toastr) toastr.success(`Added ${variantsToAdd.length} variant(s) to ${cartType === 'booking' ? 'Procurement' : 'Stock Transfer'} basket`);
                $('#quickVariantModal').modal('hide');

                // 3. Background MySQL sync
                $.ajax({
                    url: "{{ route('admin.cart.add') }}",
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        product_id: productId,
                        cart_type: cartType,
                        action: 'bulk_variants',
                        is_bulk_variants: 1,
                        variants: variantsToAdd.map(v => ({ variant_id: v.variant_id, quantity: v.quantity }))
                    }
                });
            });

            function handleDirectCartToggle($btn, productId, cartType, productName, productImg, vendorName) {
                // Prevent duplicate rapid click race conditions
                if ($btn.data('is-busy')) return;
                $btn.data('is-busy', true);

                const isAlreadyAdded = $btn.hasClass('added');
                const desiredAction = isAlreadyAdded ? 'remove' : 'add';
                const defaultLabel = (cartType === 'booking') 
                    ? '<i class="fas fa-shopping-basket"></i>' 
                    : '<i class="fas fa-file-import"></i>';

                // Optimistic visual & store update (0ms)
                if (isAlreadyAdded) {
                    $btn.removeClass('added').html(defaultLabel);
                    if (window.toastr) toastr.info(`Removed from ${cartType === 'booking' ? 'Procurement' : 'Stock Transfer'} basket`);

                    if (window.cartStore && window.cartStore[cartType]) {
                        window.cartStore[cartType].ids = window.cartStore[cartType].ids.filter(id => Number(id) !== productId);
                        window.cartStore[cartType].items = window.cartStore[cartType].items.filter(i => Number(i.product_id) !== productId);
                        window.cartStore[cartType].count = window.cartStore[cartType].items.length;
                        if (window.updateGlobalCartBadges) {
                            if (cartType === 'booking') window.updateGlobalCartBadges(window.cartStore.booking.count, undefined);
                            else window.updateGlobalCartBadges(undefined, window.cartStore.request.count);
                        }
                    }
                } else {
                    $btn.addClass('added').html('<i class="fas fa-check"></i>');
                    if (window.toastr) toastr.success(`Added to ${cartType === 'booking' ? 'Procurement' : 'Stock Transfer'} basket`);

                    if (window.cartStore && window.cartStore[cartType]) {
                        if (!window.cartStore[cartType].ids.map(Number).includes(productId)) {
                            window.cartStore[cartType].ids.push(productId);
                        }
                        // Check if item already in store to avoid duplicate temp entries
                        const existsInStore = window.cartStore[cartType].items.some(i => Number(i.product_id) === productId && !i.variant_id);
                        if (!existsInStore) {
                            window.cartStore[cartType].items.push({
                                id: `temp_${productId}`,
                                cart_id: `temp_${productId}`,
                                product_id: productId,
                                variant_id: null,
                                variant_name: '',
                                product_name: productName,
                                thumb_image: productImg,
                                vendor_name: vendorName,
                                sku: '',
                                price: 0,
                                quantity: 1
                            });
                        }
                        window.cartStore[cartType].count = window.cartStore[cartType].items.length;
                        if (window.updateGlobalCartBadges) {
                            if (cartType === 'booking') window.updateGlobalCartBadges(window.cartStore.booking.count, undefined);
                            else window.updateGlobalCartBadges(undefined, window.cartStore.request.count);
                        }
                    }
                }

                $.ajax({
                    url: "{{ route('admin.cart.add') }}",
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        product_id: productId,
                        cart_type: cartType,
                        action: desiredAction
                    },
                    success: function(response) {
                        if (response.success && response.item && window.cartStore && window.cartStore[cartType]) {
                            const idx = window.cartStore[cartType].items.findIndex(i => Number(i.product_id) === productId && !i.variant_id);
                            if (idx !== -1) {
                                window.cartStore[cartType].items[idx] = response.item;
                            }
                        }
                        applyButtonStates();
                    },
                    error: function(xhr) {
                        // Rollback on server error
                        if (isAlreadyAdded) {
                            $btn.addClass('added').html('<i class="fas fa-check"></i>');
                        } else {
                            $btn.removeClass('added').html(defaultLabel);
                        }
                        if (window.toastr) toastr.error('Error updating basket');
                    },
                    complete: function() {
                        $btn.data('is-busy', false);
                    }
                });
            }

            // Re-apply UI states after pagination/search
            window.reapplyCartButtonStates = applyButtonStates;

            // Prevent Enter key from submitting form
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
            });

            function fetchProducts(url = "{{ route('admin.products.index') }}", scrollToTop = false) {
                // Ensure search and filters are captured correctly
                let search = $('.search-input').val();
                let category = $('#category').val();
                let sub_category = $('#sub_category').val();
                let child_category = $('#child_category').val();
                let alphabet = $('#alphabet-dropdown').val();
                let product_type = $('#product_type_filter').val();
                let vendor = $('#vendor_filter').val();
                let sort = $('#sort').val();

                $.ajax({
                    url: url,
                    method: 'GET',
                    data: { 
                        search: search,
                        category: category,
                        sub_category: sub_category,
                        child_category: child_category,
                        alphabet: alphabet,
                        product_type: product_type,
                        vendor: vendor,
                        sort: sort
                    },
                    beforeSend: function() {
                        // Show skeleton loader only on subsequent loads, not on initial page load
                        if (!initialLoad) {
                            $('#grid-loader').show();
                            $('#product-grid-container').css('opacity', '0');
                        } else {
                            $('#product-grid-container').css('opacity', '0.5');
                        }
                    },
                    success: function(response) {
                        // Hide loader
                        $('#grid-loader').hide();

                        // Update content
                        $('#product-grid-container').html(response);
                        $('#product-grid-container').attr('data-loaded', 'true');

                        if (window.reapplyCartButtonStates) {
                            window.reapplyCartButtonStates();
                        }

                        // Fade in with smooth transition
                        $('#product-grid-container').stop(true, true).css('opacity', '1');

                        // Mark initial load as complete
                        initialLoad = false;

                        // Update history API without reloading
                        let params = new URLSearchParams({
                            search: search,
                            category: category,
                            sub_category: sub_category,
                            child_category: child_category,
                            alphabet: alphabet,
                            product_type: product_type,
                            sort: sort
                        });

                        // Handle pagination page if in URL
                        let pageMatch = url.match(/page=(\d+)/);
                        if (pageMatch) {
                            params.set('page', pageMatch[1]);
                        }

                        let newUrl = "{{ route('admin.products.index') }}" + '?' + params.toString();
                        window.history.replaceState({path: newUrl}, '', newUrl);

                        // Scroll to top only if requested (e.g., from pagination)
                        if (scrollToTop && $(window).scrollTop() > 200) {
                            $('html, body').stop().animate({ scrollTop: 0 }, 400);
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr);
                        $('#grid-loader').hide();
                        $('#product-grid-container').stop(true, true).css('opacity', '1');
                    }
                });
            }

            // Status Change
            $('body').on('change', '.change-status', function() {
                let isChecked = $(this).is(':checked');
                let id = $(this).data('id');
                let $this = $(this); // Store reference

                $.ajax({
                    url: "{{ route('admin.products.change-status') }}",
                    method: 'PUT',
                    data: {
                        status: isChecked,
                        id: id
                    },
                    success: function(data) {
                         toastr.success(data.message); 
                    },
                    error: function(xhr, status, error) {
                        console.error("Status update error:", error);
                        console.log("Response:", xhr.responseText);
                        if(xhr.status !== 200) {
                             $this.prop('checked', !isChecked);
                             toastr.error('Failed to update status');
                        }
                    }
                })
            })

            // Auto Search
            let timeout = null;
            $('body').on('input', '.search-input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    fetchProducts();
                }, 300); 
            });

            // Cache for category data
            const categoryCache = {
                sub: {},
                child: {}
            };

            // Category Change
            $('body').on('change', '#category', function(e, isInitialLoad = false) {
                let id = $(this).val();

                // Clear and reset sub/child categories silently without triggering 'change' event
                 // We don't want to trigger child change events that fetch products again
                $('#sub_category').html('<option value="">--Sub Category--</option>');
                $('#child_category').html('<option value="">--Child Category--</option>');

                if (id) {
                    if (categoryCache.sub[id]) {
                        // Use cached data
                        $.each(categoryCache.sub[id], function(i, item) {
                            $('#sub_category').append(`<option value="${item.id}">${item.name}</option>`);
                        });
                    } else {
                        // Fetch from server and cache
                        $.ajax({
                            url: "{{ route('admin.get-subCategories') }}",
                            method: 'GET',
                            data: { id: id },
                            success: function(data) {
                                categoryCache.sub[id] = data; // Cache results
                                $.each(data, function(i, item) {
                                    $('#sub_category').append(`<option value="${item.id}">${item.name}</option>`);
                                });
                            }
                        });
                    }
                }

                // Only fetch products if this wasn't called during the initial page load setup
                if (!isInitialLoad) {
                    fetchProducts();
                }
            });

            // Sub Category Change
            $('body').on('change', '#sub_category', function(e, isInitialLoad = false) {
                let id = $(this).val();

                // Clear and reset child categories silently
                $('#child_category').html('<option value="">--Child Category--</option>');

                if (id) {
                    if (categoryCache.child[id]) {
                        // Use cached data
                        $.each(categoryCache.child[id], function(i, item) {
                            $('#child_category').append(`<option value="${item.id}">${item.name}</option>`);
                        });
                    } else {
                        // Fetch from server and cache
                        $.ajax({
                            url: "{{ route('admin.get-child-categories') }}",
                            method: 'GET',
                            data: { id: id },
                            success: function(data) {
                                categoryCache.child[id] = data; // Cache results
                                $.each(data, function(i, item) {
                                    $('#child_category').append(`<option value="${item.id}">${item.name}</option>`);
                                });
                            }
                        });
                    }
                }

                if (!isInitialLoad) {
                    fetchProducts();
                }
            });

            // Child Category Filter
            $('body').on('change', '#child_category', function() {
                fetchProducts();
            });

            // Sort Change
            $('body').on('change', '#sort', function() {
                fetchProducts();
            });

            // Alphabet Dropdown Change
            $('body').on('change', '#alphabet-dropdown', function() {
                fetchProducts();
            });

            // Product Type Filter Change
            $('body').on('change', '#product_type_filter', function() {
                fetchProducts();
            });

            // Vendor Filter Change
            $('body').on('change', '#vendor_filter', function() {
                fetchProducts();
            });

            // Reset Filters
            $('body').on('click', '#reset-filters', function() {
                $('.search-input').val('');

                // Reset select2 and triggers without calling fetchProducts multiple times
                $('#category').val('');
                $('#sub_category').html('<option value="">Sub Category</option>');
                $('#child_category').html('<option value="">Child Category</option>');
                $('#alphabet-dropdown').val('');
                $('#product_type_filter').val('');
                $('#vendor_filter').val('');
                $('#sort').val('latest');

                // Re-trigger select2 UI update without triggering 'change' listener
                $('.select2').trigger('change.select2'); 

                fetchProducts();
            });

            // Handle Pagination clicks via AJAX
             $('body').on('click', '.pagination a', function(e) {
                e.preventDefault();
                initialLoad = false; // Allow pagination to trigger
                let url = $(this).attr('href');
                fetchProducts(url, true); // Pass true to scroll to top
            });

        })
    </script>
@endpush