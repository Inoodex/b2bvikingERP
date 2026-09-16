---
name: procurement-reports-audit-architecture
description: Enterprise architecture rules, procurement lifecycle invariants, N+1 query elimination, and async PDF queue patterns for Procurement Reports & Audit (Purchase History, Product Tracking, Supplier-wise, Item-wise, Total Value, YoY Comparison, PR Status, PO Registry, Audit Log) with Ephemeral PDF generation and zero HTTP 503 downtime.
---

# 🏛️ Procurement Reports & Audit — Architecture & Standards (SKILL.md)

This skill document defines the architectural standards, procurement lifecycle invariants, database performance rules, and background queue standards for **Procurement Reports & Audit** in B2B Viking ERP.

---

## 📌 1. Architectural Principles

### 1.1 Legacy Route Decoupling & Procurement Integration
- The legacy `Purchase` CRUD (`PurchaseController` at `/admin/purchases`) has been superseded by the modern **Procurement Module** (`PurchaseOrderController` at `/admin/purchase-orders`).
- All report line items and tables displaying Purchase Orders (POs) must link strictly to:
  `route('admin.purchase-orders.show', $po->id)`
- All supplier names must link strictly to the supplier statement:
  `route('admin.vendor-ledger.show', $vendor->id)`
- Under no circumstances should any report link back to `/admin/purchases/{id}`.

### 1.2 Zero N+1 Database Query Mandate
- In DataTables and paginated views, row-level database subqueries (e.g. executing `DB::table(...)` inside `addColumn` closures) are **strictly prohibited**.
- All aggregations (`po_count`, `total_base_amount`, `total_paid`, `total_due`, `avg_unit_cost`, `avg_landed_cost`) must be computed in a **single grouped SQL query** or eager-loaded via optimized subqueries.
- Query count per report execution must be **< 5 queries**, regardless of page size.

### 1.3 Dedicated Background Queue Processing
- Heavy procurement compilations (Purchase History, Supplier-wise, Item-wise, Audit Trail) must execute asynchronously via `App\Jobs\GenerateProcurementReportPdfJob` implementing `ShouldQueue`.
- Memory limit: `512M`, execution timeout: `600s`.
- Controllers must return an immediate response (`< 50ms`), freeing PHP-FPM web workers and eliminating HTTP 503 gateway timeouts.

### 1.4 Pre-generation Auto-Purge & Ephemeral Storage
- Temporary files must be stored in `storage/app/temp_reports/`.
- Prior temporary files for the user and report type are purged automatically before a new PDF is compiled.
- Files older than 24 hours are pruned automatically by scheduled maintenance.

### 1.5 Zero Forced Auto-Downloads
- No unsolicited `window.location.href` triggers.
- The UI renders an on-page green **"Download PDF (Ready: {time})"** button upon completion with 2s polling.

---

## 🛡️ 2. Data Integrity & Financial Consistency

1. **Multi-Currency Normalization:**
   - All monetary summaries must display in Danish Krone (`kr. {{ number_format($val, 2) }}`).
   - Where foreign currency was used (e.g. USD / EUR for import LC/PO), display both base amount and foreign amount clearly.

2. **No Dummy CVR / VAT Numbers:**
   - Under no circumstances should placeholder VAT numbers (`DK12345678`, `DK-99238419`) appear in any PDF report header or footer.
   - Company CVR/VAT must be pulled directly from `GeneralSetting::first()` or `Company::first()`.

3. **YoY Monthly Comparative Matrix:**
   - YoY reports must display a true 12-month matrix (January to December) comparing Last Year vs Current Year spend with exact variance and percentage change.
