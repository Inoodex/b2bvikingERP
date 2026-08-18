# Phase 3 — Step 3.12 Implementation Plan: Enterprise Sales Reports & AR Aging Receivables Dashboard Engine

This plan implements a **Tier-1 Enterprise Sales Analytics, AR Aging Receivables & Dashboard Widgets Engine (SAP S/4HANA F.30 / Odoo 17 Sales Reporting Parity)** for B2B Viking ERP.

---

## 📑 User Review Required

> [!IMPORTANT]
> **Executive Sales & Accounting Reporting Hub:**
> 1. **Customer AR Aging Receivables Report (`http://127.0.0.1:8000/admin/reports/ar-aging`):**
>    - Categorizes unpaid customer balances into **Current (0-30 Days)**, **31-60 Days**, **61-90 Days**, and **Over 90 Days (High Risk Dues)**.
> 2. **Salesperson Performance Report (`http://127.0.0.1:8000/admin/reports/salesperson-performance`):**
>    - Tracks sales count, total revenue generated, average deal size, and commission calculations.
> 3. **Executive Dashboard Live Analytics Widgets:**
>    - Live KPI summary cards & ApexCharts visual graphs integrated into the Admin Dashboard.

---

## 🛠️ Proposed Changes

### 1. Controllers & Reports Engine

#### [NEW] [SalesReportController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/SalesReportController.php)
- `arAging()`: Render Customer AR Aging Receivables report view & DataTables API.
- `salespersonPerformance()`: Render Sales Rep performance analytics.
- `exportPdf()` / `exportExcel()`: Download report exports.

---

### 2. Views & Analytics Components

#### [NEW] [reports/ar_aging.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/reports/ar_aging.blade.php)
- Executive AR Aging Receivables report with 0-30, 31-60, 61-90, 90+ days aging buckets and color-coded risk indicators.

#### [NEW] [reports/salesperson_performance.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/reports/salesperson_performance.blade.php)
- Performance matrix DataTable for sales reps and account managers.

---

## 🧪 Verification Plan

### Automated Verification
- Create test script `scratch/test_sales_reports.php` to verify aging math calculations and report data rendering.

### Manual Verification
- Test in browser at `http://127.0.0.1:8000/admin/reports/ar-aging`.
