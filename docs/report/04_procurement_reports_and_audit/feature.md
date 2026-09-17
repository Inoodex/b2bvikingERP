# 🚀 Feature Plan: Procurement Reports & Audit Modernization, Legacy Route Decoupling & Async PDF Engine

**Module:** Procurement Reports & Audit (Section 4 of Reports Menu)  
**System:** Copenhagen Tourist Point (b2bviking.com) — B2B Viking ERP  
**Controllers:**
- `App\Http\Controllers\Backend\ReportController.php` (Purchase History, Product Tracking, Audit Log)
- `App\Http\Controllers\Backend\PurchaseReportController.php` (Supplier-wise, Item-wise, Total Value, YoY Comparison, PR Status, PO Registry)  
**DataTables:**
- `App\DataTables\SupplierWisePurchaseDataTable.php`
- `App\DataTables\ItemWisePurchaseDataTable.php`
- `App\DataTables\PrStatusDataTable.php`
- `App\DataTables\PoStatusDataTable.php`  
**Standard:** Enterprise Procurement Lifecycle Integration, Zero N+1 Queries, Zero Server 503 Crashes, Ephemeral Async PDF Engine (`docs/report/04_procurement_reports_and_audit/SKILL.md`)  
**Status:** Completed `[5/5 Completed]` ✅

---

## 🎯 1. Overview & Problem Context

### 1. The Legacy Route Trap (`admin/purchase` vs `admin/purchase-orders`):
- In Phase 2, the ERP migrated from a legacy simple purchase CRUD (`PurchaseController` at `/admin/purchases`) to the **Enterprise Procurement Workflow** (PR ➔ RFQ ➔ CS ➔ Purchase Order (PO) ➔ LC / Shipment ➔ GRN ➔ Vendor Bill).
- In modern procurement, all PO documents live at **`admin.purchase-orders.show`** (`/admin/purchase-orders/{id}`).
- However, in [resources/views/backend/reports/purchase.blade.php (Line 77)](file:///home/agent47/Sites/b2bvikingERP/resources/views/backend/reports/purchase.blade.php#L77), the "View" button is still hardcoded to the deprecated route `admin.purchases.show`. Clicking "View" opens the old deleted/legacy purchase screen instead of the rich Procurement Purchase Order workspace.
- **Action:** Retarget all "View" links in Purchase History to `route('admin.purchase-orders.show', $purchase->id)`.

### 2. Severe N+1 Database Query Performance Leaks:
- **Supplier-wise Purchase Report (`SupplierWisePurchaseDataTable`):** Executes **4 separate SQL count/sum queries for every single supplier row** on the page (`po_count`, `total_base_amount`, `total_paid`, `total_due`). If 50 suppliers are listed, it fires **200+ database queries**!
- **Item-wise Purchase Report (`ItemWisePurchaseDataTable`):** Executes **4 complex joins** on `purchases` and `purchase_details` for every single product row. For 25 products, that's **100+ queries per page reload**!
- **Action:** Refactor both DataTables into optimized, single-pass Eloquent aggregate queries (`GROUP BY` with `SUM()` and `COUNT()`), reducing query execution from 200+ down to **1 single query**.

### 3. Missing Filter Options Across Reports:
- **Product Tracking (`admin.reports.product-purchase-history`):** Only filters by `product_id`. Lacks Date Range (`start_date`, `end_date`) and Vendor filters.
- **Purchase History (`admin.reports.purchase`):** Lacks Procurement Type filter (*Local Purchase vs Foreign Import/LC*) and PO Milestone Status (*Draft, Issued, In Transit, GRN Completed*).
- **Total Purchase Value (`admin.purchase-reports.total-value`):** Lacks Supplier filter (can only view aggregate total, cannot filter by vendor).
- **Purchase vs Last Year (`admin.purchase-reports.vs-last-year`):** Only shows 3 summary cards; completely lacks a 12-month comparative matrix table (e.g., Jan vs Jan, Feb vs Feb) which is required for enterprise management audits.
- **PR Status & Pending (`admin.purchase-reports.pr-status`):** Lacks Department and Approval Status filters.

### 4. Zero Enterprise PDF/Excel Export Capabilities:
- Reports 16, 17, 20, 21, and 24 currently have **zero PDF export** and **zero Excel export**.
- Reports 18, 19, 22, and 23 only use basic client-side DataTables buttons, generating unbranded, raw HTML dumps rather than executive corporate PDFs.
- **Action:** Implement the **Async Background PDF Engine** (`GenerateProcurementReportPdfJob`) with ephemeral storage (`storage/app/temp_reports/`), pre-purge, on-page green ready buttons, and 2s status polling.

---

## 🛠️ 2. Architectural Blueprint & File Map

### Files to Modify:

#### 1. Legacy Route Decoupling & UI Modernization:
- `[MODIFY]` [resources/views/backend/reports/purchase.blade.php](file:///home/agent47/Sites/b2bvikingERP/resources/views/backend/reports/purchase.blade.php)
  - Change line 77 link from `admin.purchases.show` to `admin.purchase-orders.show`.
  - Add filters: Date Range presets, Vendor (Select2), Purchase Type (*Local/Import*), Milestone Status.
  - Add on-page Async PDF and Excel export buttons.
- `[MODIFY]` [resources/views/backend/reports/product_purchase_history.blade.php](file:///home/agent47/Sites/b2bvikingERP/resources/views/backend/reports/product_purchase_history.blade.php)
  - Add Vendor and Date Range filters.
  - Link Invoice No directly to `admin.purchase-orders.show`.
  - Add corporate PDF and Excel export.

#### 2. Performance Query Optimization (Zero N+1):
- `[MODIFY]` [app/DataTables/SupplierWisePurchaseDataTable.php](file:///home/agent47/Sites/b2bvikingERP/app/DataTables/SupplierWisePurchaseDataTable.php)
  - Replace row-by-row N+1 subqueries with a single grouped subquery aggregate.
  - Link Supplier Name to `route('admin.vendor-ledger.show', $vendor->id)` (Supplier Statement).
- `[MODIFY]` [app/DataTables/ItemWisePurchaseDataTable.php](file:///home/agent47/Sites/b2bvikingERP/app/DataTables/ItemWisePurchaseDataTable.php)
  - Replace row-by-row N+1 joins with an eager-loaded single aggregate query.
  - Add Category and Brand filters.

#### 3. Periodic & YoY Comparative Modernization:
- `[MODIFY]` [resources/views/backend/purchase_report/total_value.blade.php](file:///home/agent47/Sites/b2bvikingERP/resources/views/backend/purchase_report/total_value.blade.php)
  - Add Vendor filter.
  - Add corporate PDF export button.
- `[MODIFY]` [app/Services/PurchaseReportService.php](file:///home/agent47/Sites/b2bvikingERP/app/Services/PurchaseReportService.php)
  - Enhance `getPurchaseVsLastYear` to calculate a **12-month comparative matrix** (Month, Last Year Value, Current Year Value, Variance, Growth %).
- `[MODIFY]` [resources/views/backend/purchase_report/value_vs_last_year.blade.php](file:///home/agent47/Sites/b2bvikingERP/resources/views/backend/purchase_report/value_vs_last_year.blade.php)
  - Render the 12-month comparative matrix table below the summary cards.
  - Add PDF export for board/executive review.

#### 4. Background Queue Engine:
- `[NEW]` [app/Jobs/GenerateProcurementReportPdfJob.php](file:///home/agent47/Sites/b2bvikingERP/app/Jobs/GenerateProcurementReportPdfJob.php)
  - Background queueable job supporting:
    1. `purchase_history`: Purchase Orders & Invoices registry.
    2. `supplier_wise`: Supplier spend, paid, and outstanding dues.
    3. `item_wise`: Item procurement volume and landed costs.
    4. `total_value`: Periodic procurement spend breakdown.
    5. `vs_last_year`: 12-month YoY procurement comparative analysis.
    6. `audit_log`: Compliance audit trail.
  - Ephemeral storage in `storage/app/temp_reports/` with automatic pre-purge.
- `[NEW]` [resources/views/backend/reports/pdf/procurement_report_pdf.blade.php](file:///home/agent47/Sites/b2bvikingERP/resources/views/backend/reports/pdf/procurement_report_pdf.blade.php)
  - Clean corporate template with verified Danish company metadata, zero dummy VAT numbers, and currency formatting.

#### 5. Routing & Controller Endpoints:
- `[MODIFY]` [routes/web.php](file:///home/agent47/Sites/b2bvikingERP/routes/web.php)
  - Register async PDF dispatch, status check polling, and secure download routes for procurement reports.
- `[MODIFY]` [app/Http/Controllers/Backend/PurchaseReportController.php](file:///home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Backend/PurchaseReportController.php)
  - Add `exportPdfAsync` and `checkPdfStatus` endpoints.
- `[MODIFY]` [app/Http/Controllers/Backend/ReportController.php](file:///home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Backend/ReportController.php)
  - Add async PDF dispatch for `purchaseReport` and `productPurchaseHistory`.

#### 6. Automated Feature Tests:
- `[NEW]` [tests/Feature/Controllers/Reports/ProcurementReportsAuditTest.php](file:///home/agent47/Sites/b2bvikingERP/tests/Feature/Controllers/Reports/ProcurementReportsAuditTest.php)

---

## 📋 3. Step-by-Step Implementation Checklist

### Phase 1: Legacy Route Decoupling & Navigation Integrity
- [x] Retarget line 77 of `purchase.blade.php` to `admin.purchase-orders.show`.
- [x] Ensure all PO references across procurement reports link to `admin.purchase-orders.show`.
- [x] Ensure Supplier names link to `admin.vendor-ledger.show`.

### Phase 2: Database Query Performance & N+1 Elimination
- [x] Rewrite `SupplierWisePurchaseDataTable` query to use a single SQL aggregate (eliminating 200+ queries).
- [x] Rewrite `ItemWisePurchaseDataTable` query to use an eager-loaded aggregate (eliminating 100+ queries).
- [x] Verify query execution count < 5 queries per report.

### Phase 3: Filter Modernization & 12-Month YoY Matrix
- [x] Add Vendor and Date Range filters to `product_purchase_history.blade.php`.
- [x] Add Vendor filter to `total_value.blade.php`.
- [x] Add Department & Status filters to `pr_status.blade.php`.
- [x] Implement 12-month comparative breakdown matrix in `PurchaseReportService@getPurchaseVsLastYear` and render in `value_vs_last_year.blade.php`.

### Phase 4: Async Background PDF Engine & Corporate Templates
- [x] Create `GenerateProcurementReportPdfJob` implementing `ShouldQueue` (`512MB RAM`, `600s timeout`).
- [x] Create `procurement_report_pdf.blade.php` corporate template with verified company details.
- [x] Add on-page green **"Download PDF (Ready: {time})"** buttons with 2s polling.
- [x] Ensure automatic pre-purge of old user temporary files in `storage/app/temp_reports/`.

### Phase 5: Automated Testing & Verification
- [x] Create `ProcurementReportsAuditTest.php` testing:
  - Route decoupling (view button links to `admin.purchase-orders.show`).
  - Zero N+1 query performance (< 5 queries per DataTable).
  - Async PDF dispatch and background job execution.
  - YoY 12-month comparative matrix generation.
- [x] Run full test suite across Steps 1, 2, 3, and 4.
