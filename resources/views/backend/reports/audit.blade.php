@extends('backend.layouts.master')

@section('title', 'Audit Trail & Forensic Compliance Report')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    /* Executive Font System */
    .audit-page-wrapper,
    .audit-page-wrapper * {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* Executive Header Bar */
    .executive-header-bar {
        background: #ffffff;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
    }
    .executive-header-bar h1 {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        line-height: 1.25;
        letter-spacing: -0.02em;
    }
    .executive-header-bar .subtitle {
        font-size: 12.5px;
        color: #64748b;
        margin-top: 4px;
        font-weight: 500;
    }

    /* 4-Pillar Executive KPI Grid */
    .executive-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }
    .executive-kpi-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 16px 18px;
        position: relative;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .executive-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.07);
    }
    .executive-kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3.5px;
    }
    .executive-kpi-card.stripe-blue::before { background: #2563eb; }
    .executive-kpi-card.stripe-emerald::before { background: #10b981; }
    .executive-kpi-card.stripe-red::before { background: #dc2626; }
    .executive-kpi-card.stripe-slate::before { background: #64748b; }

    .kpi-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .kpi-title {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin: 0;
    }
    .kpi-icon-medallion {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .kpi-icon-medallion.icon-blue { background: #eff6ff; color: #2563eb; }
    .kpi-icon-medallion.icon-emerald { background: #ecfdf5; color: #059669; }
    .kpi-icon-medallion.icon-red { background: #fff1f2; color: #dc2626; }
    .kpi-icon-medallion.icon-slate { background: #f1f5f9; color: #475569; }

    .kpi-number {
        font-size: 24px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.15;
        letter-spacing: -0.03em;
        margin-bottom: 6px;
    }
    .kpi-footer-note {
        font-size: 11.5px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Filter & Quick Presets Section */
    .filter-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 16px 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .preset-pill-group {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 14px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f1f5f9;
    }
    .preset-pill {
        font-size: 11.5px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 6px;
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
        text-decoration: none !important;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .preset-pill:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .preset-pill.active {
        background: #0f172a;
        color: #ffffff;
        border-color: #0f172a;
    }
    .preset-pill.critical-pill.active {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
    }

    /* Filter Form Elements */
    .filter-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 5px;
        display: block;
    }
    .filter-control {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        font-size: 12.5px !important;
        color: #0f172a !important;
        height: 38px !important;
        padding: 6px 12px !important;
        background-color: #ffffff !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .filter-control:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none !important;
    }

    /* Search Control with Guaranteed Icon Indentation */
    .search-input-wrapper {
        position: relative;
        width: 100%;
    }
    .search-input-wrapper .search-icon {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        z-index: 2;
        color: #64748b;
        display: flex;
        align-items: center;
    }
    .filter-control.search-control {
        padding-left: 38px !important;
        padding-right: 34px !important;
    }
    .search-clear-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 18px;
        line-height: 1;
        text-decoration: none !important;
        padding: 2px 6px;
        z-index: 2;
        cursor: pointer;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .search-clear-btn:hover {
        color: #ef4444;
        background: #fee2e2;
    }

    /* Permanent Reset Filters Buttons (Grid & Preset Ribbon) */
    .btn-filter-reset {
        height: 38px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 14px;
        width: 100%;
        text-decoration: none !important;
        transition: all 0.15s ease;
    }
    .btn-filter-reset-active {
        background: #fff1f2 !important;
        color: #dc2626 !important;
        border: 1px solid #fecdd3 !important;
    }
    .btn-filter-reset-active:hover {
        background: #fee2e2 !important;
        color: #b91c1c !important;
        border-color: #fca5a5 !important;
    }
    .btn-filter-reset-idle {
        background: #f8fafc !important;
        color: #475569 !important;
        border: 1px solid #cbd5e1 !important;
    }
    .btn-filter-reset-idle:hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        border-color: #94a3b8 !important;
    }

    .preset-reset-active {
        background: #fff1f2 !important;
        color: #dc2626 !important;
        border: 1px solid #fecdd3 !important;
        border-radius: 8px;
        font-size: 11.5px;
        padding: 5px 12px;
        gap: 5px;
        text-decoration: none !important;
    }
    .preset-reset-active:hover {
        background: #fee2e2 !important;
        color: #b91c1c !important;
    }
    .preset-reset-idle {
        background: #ffffff !important;
        color: #64748b !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px;
        font-size: 11.5px;
        padding: 5px 12px;
        gap: 5px;
        text-decoration: none !important;
    }
    .preset-reset-idle:hover {
        background: #f8fafc !important;
        color: #0f172a !important;
        border-color: #cbd5e1 !important;
    }

    /* Select2 Executive Styling */
    .select2-container--default .select2-selection--single {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        height: 38px !important;
        padding: 5px 8px !important;
        background-color: #ffffff !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 26px !important;
        font-size: 12.5px !important;
        color: #0f172a !important;
        font-weight: 600 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
    .select2-dropdown {
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
        font-size: 12.5px !important;
        z-index: 1050 !important;
    }

    /* Forensic Activity Table Card */
    .table-container-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 25px;
    }
    .table-card-head {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .table-card-title {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-audit-stream {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .table-audit-stream thead th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #475569;
        padding: 13px 18px;
        border-bottom: 2px solid #e2e8f0;
        border-top: none;
        background: #f8fafc;
        white-space: nowrap;
    }
    .table-audit-stream tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.15s ease;
    }
    .table-audit-stream tbody tr:hover {
        background-color: #f8fafc;
    }
    .table-audit-stream tbody td {
        padding: 14px 18px;
        vertical-align: middle;
        font-size: 12.5px;
    }

    /* Event Severity Chips */
    .chip-severity {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        line-height: 1.2;
    }
    .chip-create { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .chip-update { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .chip-delete { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
    .chip-security { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }

    /* Module Pill */
    .chip-module {
        font-size: 10.5px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        background: #f8fafc;
        color: #334155;
        border: 1px solid #e2e8f0;
        letter-spacing: 0.03em;
        display: inline-block;
    }

    /* Reference Code */
    .reference-code {
        font-family: 'Courier New', Courier, monospace;
        font-size: 11px;
        font-weight: 600;
        color: #475569;
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        display: inline-block;
    }

    /* Operator Avatar & IP */
    .operator-block {
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .operator-avatar {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #eff6ff;
        color: #2563eb;
        font-weight: 800;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #bfdbfe;
        flex-shrink: 0;
    }
    .operator-name {
        font-weight: 700;
        color: #0f172a;
        font-size: 12.5px;
        line-height: 1.25;
    }
    .operator-ip {
        font-family: 'Courier New', Courier, monospace;
        font-size: 10.5px;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        margin-top: 2px;
    }

    /* Reference Deep Link */
    .reference-link {
        font-weight: 700;
        font-size: 12px;
        color: #2563eb;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: color 0.15s ease;
    }
    .reference-link:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    /* Event Summary & Badge */
    .event-summary-text {
        font-size: 12.5px;
        font-weight: 500;
        color: #1e293b;
        line-height: 1.45;
    }
    .diff-count-badge {
        font-size: 10.5px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 5px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Inspect Button */
    .btn-inspect-drawer {
        background: #ffffff;
        color: #0f172a;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 11.5px;
        font-weight: 700;
        padding: 6px 13px;
        gap: 6px;
        display: inline-flex;
        align-items: center;
        transition: all 0.15s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .btn-inspect-drawer:hover {
        background: #0f172a;
        color: #ffffff;
        border-color: #0f172a;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.15);
    }

    /* =========================================================================
       SLIDE-OVER FORENSIC INSPECTOR DRAWER (Zero Duplicate DOM Modals)
       ========================================================================= */
    .audit-drawer-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.12);
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        z-index: 100000 !important;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.2s ease, visibility 0.2s ease;
    }
    .audit-drawer-backdrop.is-active {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .audit-drawer {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: min(600px, 92vw);
        height: 100vh;
        background: #ffffff;
        border-left: 1px solid #e2e8f0;
        box-shadow: -15px 0 45px rgba(0, 0, 0, 0.18), -2px 0 8px rgba(0, 0, 0, 0.06);
        z-index: 100001 !important;
        transform: translateX(100%);
        transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .audit-drawer.is-active {
        transform: translateX(0);
    }

    .drawer-header {
        padding: 18px 24px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-shrink: 0;
    }
    .drawer-header-kicker {
        font-size: 10.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 4px;
    }
    .drawer-header-title {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 3px;
        line-height: 1.25;
    }
    .drawer-header-sub {
        font-size: 12px;
        color: #64748b;
        margin: 0;
    }
    .drawer-close-btn {
        height: 34px;
        padding: 0 13px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        flex-shrink: 0;
    }
    .drawer-close-btn:hover {
        background: #fee2e2;
        color: #dc2626;
        border-color: #fca5a5;
    }

    .drawer-body {
        padding: 22px 24px;
        overflow-y: auto;
        flex: 1;
    }

    .drawer-footer {
        padding: 14px 24px;
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-shrink: 0;
    }

    .drawer-info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }
    .drawer-info-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
    }
    .drawer-info-box .info-lbl {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.05em;
        margin-bottom: 3px;
    }
    .drawer-info-box .info-val {
        font-size: 12.5px;
        font-weight: 700;
        color: #0f172a;
        word-break: break-all;
    }

    /* Side-by-side Visual Diff */
    .drawer-diff-section {
        margin-bottom: 20px;
    }
    .drawer-section-title {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #0f172a;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .diff-field-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        padding: 12px;
        margin-bottom: 10px;
    }
    .diff-field-name {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.04em;
        margin-bottom: 8px;
    }
    .diff-row {
        display: grid;
        grid-template-columns: 1fr 24px 1fr;
        gap: 8px;
        align-items: center;
    }
    .diff-box {
        border-radius: 6px;
        padding: 8px 10px;
        font-size: 12px;
        min-height: 48px;
        word-break: break-word;
    }
    .diff-box-old {
        background: #fff1f2;
        border: 1px solid #fecdd3;
        color: #9f1239;
    }
    .diff-box-new {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }
    .diff-box-label {
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 3px;
        display: block;
        opacity: 0.8;
    }

    /* Raw JSON details */
    .drawer-json-details {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #0f172a;
        color: #f8fafc;
        overflow: hidden;
    }
    .drawer-json-summary {
        padding: 10px 14px;
        cursor: pointer;
        font-size: 11.5px;
        font-weight: 700;
        color: #94a3b8;
        background: #1e293b;
        outline: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .drawer-json-summary:hover {
        color: #ffffff;
    }
    .drawer-json-pre {
        padding: 14px;
        font-family: 'Courier New', Courier, monospace;
        font-size: 11px;
        color: #e2e8f0;
        max-height: 250px;
        overflow-y: auto;
        margin: 0;
        white-space: pre-wrap;
    }

    @media (max-width: 991.98px) {
        .executive-kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 575.98px) {
        .executive-kpi-grid {
            grid-template-columns: 1fr;
        }
        .diff-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<section class="section audit-page-wrapper">
    {{-- =========================================================================
         1. EXECUTIVE HEADER BAR & COMPLIANCE ACTION CONTROLS
         ========================================================================= --}}
    <div class="executive-header-bar d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
        <div>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                <h1>Audit Trail & Forensic Compliance</h1>
            </div>
            <div class="subtitle">
                Immutable activity log across enterprise ERP operations &bull; Total Filtered Records: <strong>{{ number_format($summary['count']) }}</strong>
            </div>
        </div>

        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
            {{-- Download PDF Button (Disabled state when no PDF is ready, active green when ready) --}}
            @if(!empty($latestPdf))
                <a href="{{ $latestPdf['url'] }}" id="btn-download-pdf" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" style="background: #ecfdf5; color: #047857; border-radius: 8px; font-size: 12px; padding: 7px 15px; gap: 6px; border: 1px solid #a7f3d0; text-decoration: none;" title="{{ $latestPdf['filename'] }}" target="_blank">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Download PDF
                </a>
            @else
                <button type="button" id="btn-download-pdf-placeholder" class="btn font-weight-bold d-inline-flex align-items-center shadow-none" disabled style="background: #f1f5f9; color: #94a3b8; border-radius: 8px; font-size: 12px; padding: 7px 15px; gap: 6px; border: 1px solid #e2e8f0; cursor: not-allowed; opacity: 0.75;" title="No PDF generated yet. Click 'Export PDF' to generate report first.">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Download PDF
                </button>
                <a href="" id="btn-download-pdf" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" style="background: #ecfdf5; color: #047857; border-radius: 8px; font-size: 12px; padding: 7px 15px; gap: 6px; border: 1px solid #a7f3d0; text-decoration: none; display: none;" target="_blank">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Download PDF
                </a>
            @endif

            {{-- Async PDF Export Trigger (Dark Slate Executive Button) --}}
            <button type="button" id="btn-generate-pdf" class="btn btn-generate-pdf font-weight-bold d-inline-flex align-items-center shadow-sm"
                data-url="{{ route('admin.reports.audit.pdf.async', request()->all()) }}"
                data-type="audit_report"
                data-check-url="{{ route('admin.reports.audit.check-status') }}"
                data-loading-html='<span class="spinner-border spinner-border-sm mr-1" style="width: 12px; height: 12px; border-width: 2px;" role="status" aria-hidden="true"></span> Exporting...'
                style="background: #0f172a; color: #ffffff; border-radius: 8px; font-size: 12px; padding: 7px 15px; gap: 6px; border: 1px solid #0f172a; min-width: 108px; justify-content: center; transition: all 0.2s ease;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                Export PDF
            </button>

            {{-- Export CSV Trigger (Clean White Border Button) --}}
            <button type="button" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" id="btnExportCsv"
                style="background: #ffffff; color: #0f172a; border-radius: 8px; font-size: 12px; padding: 7px 15px; gap: 6px; border: 1px solid #cbd5e1; min-width: 108px; justify-content: center; transition: all 0.2s ease;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                Export CSV
            </button>
        </div>
    </div>

    {{-- =========================================================================
         2. 4-PILLAR EXECUTIVE KPI GRID
         ========================================================================= --}}
    <div class="executive-kpi-grid">
        {{-- Card 1: Total Audited Events --}}
        <div class="executive-kpi-card stripe-blue">
            <div class="kpi-head">
                <span class="kpi-title">Total Audited Events</span>
                <div class="kpi-icon-medallion icon-blue">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
            </div>
            <div class="kpi-number">{{ number_format($summary['count']) }}</div>
            <div class="kpi-footer-note">
                <span>Across {{ number_format($summary['modules']) }} system modules</span>
            </div>
        </div>

        {{-- Card 2: Today's Activity --}}
        <div class="executive-kpi-card stripe-emerald">
            <div class="kpi-head">
                <span class="kpi-title">Today's Activity</span>
                <div class="kpi-icon-medallion icon-emerald">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
            <div class="kpi-number">{{ number_format($summary['today_count']) }}</div>
            <div class="kpi-footer-note">
                <span>Logged in past 24 hours</span>
            </div>
        </div>

        {{-- Card 3: Critical Mutations --}}
        <div class="executive-kpi-card stripe-red">
            <div class="kpi-head">
                <span class="kpi-title">Critical Mutations</span>
                <div class="kpi-icon-medallion icon-red">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </div>
            </div>
            <div class="kpi-number" style="color: {{ ($summary['critical_count'] ?? 0) > 0 ? '#dc2626' : '#0f172a' }};">
                {{ number_format($summary['critical_count'] ?? 0) }}
            </div>
            <div class="kpi-footer-note">
                <span>Deletions, cancels & void actions</span>
            </div>
        </div>

        {{-- Card 4: Active Operators --}}
        <div class="executive-kpi-card stripe-slate">
            <div class="kpi-head">
                <span class="kpi-title">Active Operators</span>
                <div class="kpi-icon-medallion icon-slate">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
            </div>
            <div class="kpi-number">{{ number_format($summary['users']) }}</div>
            <div class="kpi-footer-note">
                <span>Unique staff members involved</span>
            </div>
        </div>
    </div>

    {{-- =========================================================================
         3. SMART FILTER & QUICK DATE PRESET RIBBON
         ========================================================================= --}}
    <div class="filter-card">
        {{-- Quick Date Range & Critical Preset Pills with Integrated Reset Button --}}
        @php
            $isToday = request('start_date') == today()->format('Y-m-d') && request('end_date') == today()->format('Y-m-d') && !request('critical_only');
            $isYesterday = request('start_date') == today()->subDay()->format('Y-m-d') && request('end_date') == today()->subDay()->format('Y-m-d');
            $is7Days = request('start_date') == today()->subDays(6)->format('Y-m-d') && request('end_date') == today()->format('Y-m-d');
            $is30Days = request('start_date') == today()->subDays(29)->format('Y-m-d') && request('end_date') == today()->format('Y-m-d');
            $isCritical = request('critical_only') == '1';
            $isAll = !request('start_date') && !request('end_date') && !$isCritical;
            $hasActiveFilters = request()->hasAny(['module', 'action', 'user_id', 'vendor_id', 'reference', 'start_date', 'end_date', 'critical_only']);
        @endphp
        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9;">
            <div class="preset-pill-group mb-0 p-0" style="border: none;">
                <span class="text-muted font-weight-bold text-uppercase mr-1" style="font-size: 10px; letter-spacing: 0.05em;">Quick Presets:</span>
                <a href="{{ route('admin.reports.audit', array_merge(request()->except(['start_date', 'end_date', 'critical_only']))) }}" class="preset-pill {{ $isAll ? 'active' : '' }}">
                    All Time
                </a>
                <a href="{{ route('admin.reports.audit', array_merge(request()->except(['critical_only']), ['start_date' => today()->format('Y-m-d'), 'end_date' => today()->format('Y-m-d')])) }}" class="preset-pill {{ $isToday ? 'active' : '' }}">
                    Today
                </a>
                <a href="{{ route('admin.reports.audit', array_merge(request()->except(['critical_only']), ['start_date' => today()->subDay()->format('Y-m-d'), 'end_date' => today()->subDay()->format('Y-m-d')])) }}" class="preset-pill {{ $isYesterday ? 'active' : '' }}">
                    Yesterday
                </a>
                <a href="{{ route('admin.reports.audit', array_merge(request()->except(['critical_only']), ['start_date' => today()->subDays(6)->format('Y-m-d'), 'end_date' => today()->format('Y-m-d')])) }}" class="preset-pill {{ $is7Days ? 'active' : '' }}">
                    Last 7 Days
                </a>
                <a href="{{ route('admin.reports.audit', array_merge(request()->except(['critical_only']), ['start_date' => today()->subDays(29)->format('Y-m-d'), 'end_date' => today()->format('Y-m-d')])) }}" class="preset-pill {{ $is30Days ? 'active' : '' }}">
                    Last 30 Days
                </a>
                <a href="{{ route('admin.reports.audit', array_merge(request()->all(), ['critical_only' => $isCritical ? 0 : 1])) }}" class="preset-pill critical-pill {{ $isCritical ? 'active' : '' }}" style="{{ $isCritical ? '' : 'color: #dc2626; border-color: #fecdd3;' }}">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path></svg>
                    Critical Mutations
                </a>
            </div>

            {{-- Reset Filters Button permanently visible in the Filter Ribbon --}}
            <a href="{{ route('admin.reports.audit') }}" class="btn font-weight-bold d-inline-flex align-items-center shadow-none {{ $hasActiveFilters ? 'preset-reset-active' : 'preset-reset-idle' }}" title="{{ $hasActiveFilters ? 'Reset all applied filters' : 'All filters currently at default state' }}">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><polyline points="3 3 3 8 8 8"></polyline></svg>
                Reset Filters
                @if($hasActiveFilters)
                    <span class="badge badge-danger ml-1" style="font-size: 9.5px; padding: 2px 5px; border-radius: 999px;">Active</span>
                @endif
            </a>
        </div>

        {{-- Detailed Search & Select2 Filter Form (Auto-submitting, zero manual click required) --}}
        <form method="GET" action="{{ route('admin.reports.audit') }}" id="auditFilterForm">
            @if(request('critical_only'))
                <input type="hidden" name="critical_only" value="1">
            @endif
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="filter-label">Module</label>
                    <select name="module" class="form-control select2-filter" onchange="document.getElementById('auditFilterForm').submit();">
                        <option value="">All Modules</option>
                        @foreach($modules as $m)
                            <option value="{{ $m }}" {{ request('module') === $m ? 'selected' : '' }}>
                                {{ $enterpriseModules[$m] ?? ucwords(str_replace('_', ' ', $m)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="filter-label">Event Action</label>
                    <select name="action" class="form-control select2-filter" onchange="document.getElementById('auditFilterForm').submit();">
                        <option value="">All Actions</option>
                        @foreach($actions as $a)
                            <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ str_replace('_', ' ', strtoupper($a)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="filter-label">Operator (Staff)</label>
                    <select name="user_id" class="form-control select2-filter" onchange="document.getElementById('auditFilterForm').submit();">
                        <option value="">All Staff Members</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ (string) request('user_id') === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="filter-label">Quick Search</label>
                    <div class="search-input-wrapper">
                        <span class="search-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <input type="text" name="reference" id="filterQuickSearch" value="{{ request('reference') }}" class="form-control filter-control search-control" placeholder="Search ref, IP, keyword..." autocomplete="off">
                        @if(request('reference'))
                            <a href="{{ route('admin.reports.audit', request()->except('reference')) }}" class="search-clear-btn" title="Clear search query">&times;</a>
                        @endif
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                    <label class="filter-label">From Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control filter-control" onchange="document.getElementById('auditFilterForm').submit();">
                </div>

                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                    <label class="filter-label">To Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control filter-control" onchange="document.getElementById('auditFilterForm').submit();">
                </div>

                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                    <label class="filter-label d-none d-lg-block" style="visibility: hidden;">Reset</label>
                    <a href="{{ route('admin.reports.audit') }}" class="btn btn-filter-reset {{ $hasActiveFilters ? 'btn-filter-reset-active' : 'btn-filter-reset-idle' }}" id="btnResetFilters" title="{{ $hasActiveFilters ? 'Clear all applied filters' : 'Filters are currently at default state' }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><polyline points="3 3 3 8 8 8"></polyline></svg>
                        <span>Reset Filters</span>
                        @if($hasActiveFilters)
                            <span class="badge badge-danger ml-1" style="font-size: 9.5px; padding: 2px 5px; border-radius: 999px;">Active</span>
                        @endif
                    </a>
                </div>

                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                    <label class="filter-label d-none d-lg-block" style="visibility: hidden;">Status</label>
                    <div class="d-flex align-items-center justify-content-lg-end" style="height: 38px;">
                        <div class="d-inline-flex align-items-center text-muted" style="font-size: 11.5px; gap: 6px; background: #f8fafc; padding: 7px 12px; border-radius: 8px; border: 1px solid #e2e8f0; width: 100%; justify-content: center;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <span>Auto-filtering active</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- =========================================================================
         4. FORENSIC ACTIVITY STREAM MATRIX TABLE
         ========================================================================= --}}
    <div class="table-container-card">
        <div class="table-card-head">
            <h4 class="table-card-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                Itemized Activity Stream
            </h4>
            <span class="text-muted font-weight-bold" style="font-size: 12px;">Showing {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ number_format($logs->total()) }} logs</span>
        </div>

        <div class="table-responsive">
            <table class="table-audit-stream" id="table-audit-logs">
                <thead>
                    <tr>
                        <th style="width: 22%; min-width: 175px;">Date & Time</th>
                        <th style="width: 10%;">Severity / Event</th>
                        <th style="width: 14%;">Module & Entity</th>
                        <th style="width: 14%;">Operator & Network</th>
                        <th style="width: 31%;">Event Summary</th>
                        <th style="width: 9%; text-align: right;">Forensics</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $act = strtolower($log->action ?? '');
                            $chipClass = 'chip-update';
                            $chipLabel = 'UPDATED';
                            if (str_contains($act, 'created') || str_contains($act, 'create')) {
                                $chipClass = 'chip-create';
                                $chipLabel = 'CREATED';
                            } elseif (str_contains($act, 'deleted') || str_contains($act, 'delete') || str_contains($act, 'void') || str_contains($act, 'cancel')) {
                                $chipClass = 'chip-delete';
                                $chipLabel = 'DELETED';
                            } elseif (str_contains($act, 'auth') || str_contains($act, 'login') || str_contains($act, 'logout') || str_contains($act, 'password') || str_contains($act, 'role') || str_contains($act, 'lockout') || ($log->module ?? '') === 'auth') {
                                $chipClass = 'chip-security';
                                $chipLabel = str_contains($act, 'login') ? 'LOGIN' : (str_contains($act, 'logout') ? 'LOGOUT' : 'SECURITY');
                            }

                            // Operator Initials
                            $userName = $log->user?->name ?: 'System';
                            $words = explode(' ', trim($userName));
                            $initials = strtoupper(substr($words[0] ?? 'S', 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

                            // Payload data for Slide-over Drawer
                            $oldVals = is_array($log->old_values) ? $log->old_values : [];
                            $newVals = is_array($log->new_values) ? $log->new_values : [];
                            $diffCount = count($newVals) ?: count($oldVals);

                            // Entity Deep-link
                            $entityUrl = null;
                            if (($log->module === 'purchases' || $log->entity_type === 'purchase') && !empty($log->entity_id)) {
                                $entityUrl = route('admin.purchase-orders.show', $log->entity_id);
                            } elseif (($log->module === 'vendors' || $log->entity_type === 'vendor') && !empty($log->vendor_id)) {
                                $entityUrl = route('admin.vendor-ledger.show', $log->vendor_id);
                            }
                        @endphp
                        <tr>
                            {{-- 1. Date & Time --}}
                            <td>
                                <div class="font-weight-bold text-dark text-nowrap" style="font-size: 12.5px; line-height: 1.35; white-space: nowrap;">
                                    <span>{{ $log->created_at?->format('d M Y') }}</span>
                                    <span class="text-muted font-weight-normal mx-1">&bull;</span>
                                    <span class="text-secondary font-weight-normal">{{ $log->created_at?->format('h:i A') }}</span>
                                </div>
                                <div class="text-muted d-inline-flex align-items-center mt-1 text-nowrap" style="font-size: 11px; gap: 4px; white-space: nowrap;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <span>{{ $log->created_at?->diffForHumans() }}</span>
                                </div>
                            </td>

                            {{-- 2. Severity / Action (Clean Badge with Icon, Zero Redundant Duplicate Text) --}}
                            <td>
                                <span class="chip-severity {{ $chipClass }}">
                                    @if(str_contains($act, 'login'))
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                                    @elseif(str_contains($act, 'logout'))
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                    @elseif(str_contains($act, 'create'))
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    @elseif(str_contains($act, 'delete') || str_contains($act, 'void') || str_contains($act, 'cancel'))
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    @elseif(str_contains($act, 'auth') || str_contains($act, 'role') || str_contains($act, 'lockout'))
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                    @else
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                                    @endif
                                    {{ $chipLabel }}
                                </span>
                            </td>

                            {{-- 3. Module & Entity --}}
                            <td>
                                <div>
                                    <span class="chip-module">{{ $enterpriseModules[$log->module] ?? strtoupper(str_replace('_', ' ', $log->module ?? 'SYSTEM')) }}</span>
                                </div>
                                <div class="mt-1">
                                    @if($entityUrl)
                                        <a href="{{ $entityUrl }}" target="_blank" class="reference-link" title="Open record in workspace">
                                            {{ $log->reference_no ?: ($log->entity_type ? ucfirst($log->entity_type) . ' #' . $log->entity_id : 'Record') }}
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        </a>
                                    @else
                                        <span class="reference-code">
                                            {{ $log->reference_no ?: '—' }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- 4. Operator & IP Network --}}
                            <td>
                                <div class="operator-block">
                                    <div class="operator-avatar">{{ $initials }}</div>
                                    <div>
                                        <div class="operator-name">{{ $userName }}</div>
                                        <div class="operator-ip">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
                                            {{ $log->ip_address ?: '127.0.0.1' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- 5. Summary & Field Badge --}}
                            <td>
                                <div class="event-summary-text">
                                    {{ $log->description ?: 'System operation completed' }}
                                </div>
                                @if($diffCount > 0)
                                    <div class="mt-1">
                                        <span class="diff-count-badge">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                            {{ $diffCount }} field{{ $diffCount > 1 ? 's' : '' }} modified
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- 6. Forensics Inspect Button --}}
                            <td style="text-align: right;">
                                <button type="button" class="btn-inspect-drawer"
                                    data-log="{{ json_encode([
                                        'id'           => $log->id,
                                        'action'       => str_replace('_', ' ', strtoupper($log->action ?? 'UPDATED')),
                                        'chip_class'   => $chipClass,
                                        'chip_label'   => $chipLabel,
                                        'module'       => strtoupper($log->module ?? 'SYSTEM'),
                                        'description'  => $log->description ?: 'No event description',
                                        'date'         => $log->created_at?->format('d M Y, h:i A'),
                                        'relative'     => $log->created_at?->diffForHumans(),
                                        'operator'     => $userName,
                                        'ip'           => $log->ip_address ?: '127.0.0.1',
                                        'user_agent'   => $log->user_agent ?: 'Unknown Browser/OS',
                                        'reference_no' => $log->reference_no ?: 'N/A',
                                        'entity_url'   => $entityUrl,
                                        'old_values'   => $oldVals,
                                        'new_values'   => $newVals,
                                    ]) }}">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    Inspect
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <div class="font-weight-bold" style="font-size: 14px; color: #475569;">No audit logs found matching the filter criteria</div>
                                <div style="font-size: 12px;">Try adjusting your search filters or click "Reset Filters" above.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Clean Pagination --}}
        @if($logs->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap" style="background: #f8fafc;">
                <span class="text-muted font-weight-bold" style="font-size: 12px;">
                    Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ number_format($logs->total()) }} entries
                </span>
                <div>
                    {{ $logs->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- =========================================================================
         5. REUSABLE SLIDE-OVER FORENSIC INSPECTOR DRAWER (Only 1 DOM element!)
         ========================================================================= --}}
    <div class="audit-drawer-backdrop" id="auditDrawerBackdrop"></div>
    <div class="audit-drawer" id="auditInspectorDrawer">
        {{-- Drawer Header --}}
        <div class="drawer-header">
            <div>
                <div class="d-flex align-items-center mb-1" style="gap: 6px;">
                    <span class="chip-severity" id="drawerChip">EVENT</span>
                    <span class="chip-module" id="drawerModule">MODULE</span>
                </div>
                <h3 class="drawer-header-title" id="drawerActionTitle">Event Title</h3>
                <p class="drawer-header-sub" id="drawerDescription">Event Description</p>
            </div>
            <button type="button" class="drawer-close-btn" id="btnCloseDrawer" title="Close inspector (Esc)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                <span>Close</span>
            </button>
        </div>

        {{-- Drawer Body --}}
        <div class="drawer-body">
            {{-- Forensic Metadata Cards --}}
            <div class="drawer-info-grid">
                <div class="drawer-info-box">
                    <div class="info-lbl">Operator (Staff)</div>
                    <div class="info-val" id="drawerOperator">System</div>
                </div>
                <div class="drawer-info-box">
                    <div class="info-lbl">Timestamp</div>
                    <div class="info-val" id="drawerDate">—</div>
                </div>
                <div class="drawer-info-box">
                    <div class="info-lbl d-flex justify-content-between align-items-center">
                        <span>IP Address</span>
                        <button type="button" id="btnCopyIp" class="btn btn-link p-0 text-primary font-weight-bold" style="font-size: 9.5px; text-decoration: none; line-height: 1;">Copy IP</button>
                    </div>
                    <div class="info-val" id="drawerIp" style="font-family: 'Courier New', Courier, monospace;">127.0.0.1</div>
                </div>
                <div class="drawer-info-box">
                    <div class="info-lbl">Reference / Entity</div>
                    <div class="info-val" id="drawerReference">N/A</div>
                </div>
            </div>

            {{-- Client Browser / User Agent Details --}}
            <div class="drawer-info-box mb-3" style="background: #ffffff;">
                <div class="info-lbl">Client User Agent & Environment</div>
                <div class="info-val text-muted" id="drawerUserAgent" style="font-size: 11px; font-weight: 500; word-break: break-all;">
                    —
                </div>
            </div>

            {{-- Side-by-Side Visual Diff --}}
            <div class="drawer-diff-section">
                <div class="drawer-section-title">
                    <span>Structured Modifications (Old vs New)</span>
                    <span class="badge badge-light border" id="drawerDiffCount">0 changes</span>
                </div>
                <div id="drawerDiffContainer">
                    {{-- Dynamically injected via JavaScript --}}
                </div>
            </div>

            {{-- Raw JSON Inspector Accordion --}}
            <div class="drawer-json-details">
                <details>
                    <summary class="drawer-json-summary">
                        <span>Raw Payload JSON</span>
                        <button type="button" id="btnCopyJson" class="btn btn-sm btn-outline-light py-0 px-2 font-weight-bold" style="font-size: 10px;">
                            Copy JSON
                        </button>
                    </summary>
                    <pre class="drawer-json-pre" id="drawerRawJson">{}</pre>
                </details>
            </div>
        </div>

        {{-- Drawer Footer with Explicit Close Action --}}
        <div class="drawer-footer">
            <span class="text-muted font-weight-normal" style="font-size: 11.5px;">
                Press <kbd style="background: #f1f5f9; color: #334155; padding: 2px 6px; border-radius: 4px; font-size: 10px; border: 1px solid #cbd5e1;">ESC</kbd> or click outside to close
            </span>
            <button type="button" class="btn btn-secondary btn-sm font-weight-bold d-inline-flex align-items-center shadow-sm" id="btnFooterCloseDrawer" style="border-radius: 8px; font-size: 12px; padding: 7px 16px; gap: 6px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                Close Inspector
            </button>
        </div>
    </div>
</section>
@endsection

@push('scripts')
{{-- Include reusable async PDF generation script (Step 1, 2, 3 Pattern) --}}
@include('backend.reports.partials.async_analytics_report_pdf_js')

<script>
$(document).ready(function() {
    // Initialize Select2 on Filter Dropdowns
    if ($.fn.select2) {
        $('.select2-filter').select2({
            dropdownAutoWidth: true,
            width: '100%'
        });
    }

    // =========================================================================
    // SLIDE-OVER FORENSIC DRAWER LOGIC
    // =========================================================================
    // Teleport drawer elements directly to <body> so they escape any component stacking context
    if ($('#auditDrawerBackdrop').parent().not('body').length) {
        $('body').append($('#auditDrawerBackdrop'), $('#auditInspectorDrawer'));
    }

    const $drawer = $('#auditInspectorDrawer');
    const $backdrop = $('#auditDrawerBackdrop');

    function openDrawer(data) {
        // Set basic fields
        $('#drawerChip').attr('class', 'chip-severity ' + data.chip_class).text(data.chip_label);
        $('#drawerModule').text(data.module);
        $('#drawerActionTitle').text(data.action);
        $('#drawerDescription').text(data.description);
        $('#drawerOperator').text(data.operator);
        $('#drawerDate').html(data.date + ' <span class="text-muted font-weight-normal" style="font-size: 11px;">(' + data.relative + ')</span>');
        $('#drawerIp').text(data.ip);
        $('#drawerUserAgent').text(data.user_agent);

        // Reference link
        if (data.entity_url) {
            $('#drawerReference').html('<a href="' + data.entity_url + '" target="_blank" class="reference-link">' + data.reference_no + ' ↗</a>');
        } else {
            $('#drawerReference').text(data.reference_no);
        }

        // Build Visual Diff
        const oldVals = data.old_values || {};
        const newVals = data.new_values || {};
        const allKeys = Array.from(new Set([...Object.keys(oldVals), ...Object.keys(newVals)]));

        let diffHtml = '';
        let changedCount = 0;

        allKeys.forEach(key => {
            const oldVal = oldVals[key];
            const newVal = newVals[key];
            const isDifferent = JSON.stringify(oldVal) !== JSON.stringify(newVal);

            if (isDifferent) {
                changedCount++;
                const oldDisplay = oldVal !== undefined ? (typeof oldVal === 'object' ? JSON.stringify(oldVal) : String(oldVal)) : '—';
                const newDisplay = newVal !== undefined ? (typeof newVal === 'object' ? JSON.stringify(newVal) : String(newVal)) : '—';
                const fieldTitle = key.replace(/_/g, ' ').toUpperCase();

                diffHtml += `
                    <div class="diff-field-card">
                        <div class="diff-field-name">${fieldTitle}</div>
                        <div class="diff-row">
                            <div class="diff-box diff-box-old">
                                <span class="diff-box-label">Old State</span>
                                <strong>${oldDisplay || 'Empty'}</strong>
                            </div>
                            <div class="text-center text-muted" style="font-size: 12px;">➔</div>
                            <div class="diff-box diff-box-new">
                                <span class="diff-box-label">New State</span>
                                <strong>${newDisplay || 'Empty'}</strong>
                            </div>
                        </div>
                    </div>
                `;
            }
        });

        if (changedCount === 0) {
            diffHtml = '<div class="p-3 rounded bg-light text-muted" style="font-size: 12px;">No discrete field changes recorded for this event.</div>';
        }

        $('#drawerDiffCount').text(changedCount + ' field' + (changedCount !== 1 ? 's' : ''));
        $('#drawerDiffContainer').html(diffHtml);

        // Raw JSON
        const rawObj = {
            id: data.id,
            module: data.module,
            action: data.action,
            operator: data.operator,
            ip_address: data.ip,
            timestamp: data.date,
            old_values: oldVals,
            new_values: newVals
        };
        $('#drawerRawJson').text(JSON.stringify(rawObj, null, 2));

        // Reset scroll position to top
        $('#auditInspectorDrawer .drawer-body').scrollTop(0);

        // Open Drawer with smooth transition
        $('#auditDrawerBackdrop').addClass('is-active');
        $('#auditInspectorDrawer').addClass('is-active');
        $('body').css('overflow', 'hidden');
    }

    function closeDrawer() {
        $('#auditInspectorDrawer').removeClass('is-active');
        $('#auditDrawerBackdrop').removeClass('is-active');
        $('body').css('overflow', '');
    }

    // Attach click handler on all inspect buttons
    $(document).on('click', '.btn-inspect-drawer', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const rawData = $(this).data('log');
        openDrawer(rawData);
    });

    // Close on any close button or backdrop click
    $(document).on('click', '#btnCloseDrawer, #btnFooterCloseDrawer, #auditDrawerBackdrop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        closeDrawer();
    });

    // Click anywhere outside the drawer to close it
    $(document).on('click', function(e) {
        if ($('#auditInspectorDrawer').hasClass('is-active')) {
            if (!$(e.target).closest('#auditInspectorDrawer').length && !$(e.target).closest('.btn-inspect-drawer').length) {
                closeDrawer();
            }
        }
    });

    // Prevent clicks inside the drawer from bubbling to the document
    $(document).on('click', '#auditInspectorDrawer', function(e) {
        e.stopPropagation();
    });

    // Keyboard ESC key closes drawer
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#auditInspectorDrawer').hasClass('is-active')) {
            closeDrawer();
        }
    });

    // Copy IP Address Button
    $('#btnCopyIp').on('click', function() {
        const ip = $('#drawerIp').text().trim();
        navigator.clipboard.writeText(ip).then(() => {
            const orig = $(this).text();
            $(this).text('Copied!').css('color', '#059669');
            setTimeout(() => {
                $(this).text(orig).css('color', '');
            }, 1500);
        });
    });

    // Copy JSON Button
    $('#btnCopyJson').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const json = $('#drawerRawJson').text().trim();
        navigator.clipboard.writeText(json).then(() => {
            const orig = $(this).text();
            $(this).text('Copied JSON!').removeClass('btn-outline-light').addClass('btn-success');
            setTimeout(() => {
                $(this).text(orig).removeClass('btn-success').addClass('btn-outline-light');
            }, 1500);
        });
    });

    // =========================================================================
    // EXPORT TABLE TO CSV
    // =========================================================================
    $('#btnExportCsv').on('click', function() {
        const table = document.getElementById('table-audit-logs');
        if (!table) return;

        let csv = [];
        const rows = table.querySelectorAll('tr');

        rows.forEach(row => {
            let rowData = [];
            const cols = row.querySelectorAll('th, td');
            cols.forEach((col, idx) => {
                // Skip the 6th column (Inspect button)
                if (idx < 5) {
                    let text = col.innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
                    text = text.replace(/"/g, '""');
                    rowData.push('"' + text + '"');
                }
            });
            if (rowData.length > 0) {
                csv.push(rowData.join(','));
            }
        });

        const csvContent = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv.join('\n'));
        const link = document.createElement('a');
        link.setAttribute('href', csvContent);
        link.setAttribute('download', 'audit_trail_report_' + new Date().toISOString().slice(0, 10) + '.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // =========================================================================
    // QUICK SEARCH DEBOUNCE & CLEAR HANDLING
    // =========================================================================
    let searchDebounceTimer = null;
    const $quickSearch = $('#filterQuickSearch');

    $quickSearch.on('input', function() {
        clearTimeout(searchDebounceTimer);
        const val = $(this).val();

        // Show or hide clear button dynamically if user types or deletes text
        if (val.length > 0) {
            if ($('.search-input-wrapper .search-clear-btn').length === 0) {
                $('.search-input-wrapper').append('<span class="search-clear-btn dynamic-clear" title="Clear search">&times;</span>');
            }
        } else {
            $('.search-input-wrapper .dynamic-clear').remove();
        }

        searchDebounceTimer = setTimeout(function() {
            $('#auditFilterForm').submit();
        }, 650);
    });

    $(document).on('click', '.search-clear-btn.dynamic-clear', function(e) {
        e.preventDefault();
        $quickSearch.val('');
        $(this).remove();
        clearTimeout(searchDebounceTimer);
        $('#auditFilterForm').submit();
    });

    $quickSearch.on('keydown', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            clearTimeout(searchDebounceTimer);
            $('#auditFilterForm').submit();
        }
    });
});
</script>
@endpush
