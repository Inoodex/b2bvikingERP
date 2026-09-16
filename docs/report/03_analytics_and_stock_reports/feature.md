# 🚀 Feature Plan: Analytics & Stock Reports Async PDF Engine & Enterprise Cleanup

**Module:** Analytics & Stock Reports (Sub-section of Reports Menu)  
**System:** Copenhagen Tourist Point (b2bviking.com) — B2B Viking ERP  
**Controllers:**
- `App\Http\Controllers\Backend\ReportController.php` (Order & Sales Report, Stock Valuation, Low Stock, Current Stock)
- `App\Http\Controllers\Backend\SalesReportController.php` (Sales Rep Performance)  
**Standard:** Enterprise Direct Navigation, Ephemeral Dynamic PDF Generation, Real Reorder Thresholds, Zero Server 503 Crashes (`docs/report/03_analytics_and_stock_reports/SKILL.md`)  
**Status:** In Progress `[0/4 Completed]` ⏳

---

## 🎯 1. Overview & Problem Context

1. **Decommission "All Reports Dashboard" (`admin.reports.index`):**
   - The generic "All Reports" dashboard is redundant with the main dashboard (`/admin/dashboard`), creates an unorganized link-farm with mismatched shortcuts, and displays broken metrics (negative Gross Profit of `-Kr. 718k` and 677 false low-stock items).
   - In enterprise ERP architecture, users navigate directly to specific functional reports.
   - We remove this link from the sidebar navigation and redirect `admin.reports.index` directly to `admin.reports.orders`.

2. **Order & Sales Report (`admin.reports.orders`):**
   - Currently has synchronous PDF export (`orderReportPdf`) that causes HTTP 503 timeout crashes on large date ranges.
   - The async job `GenerateReportPdfJob` has a fatal bug (missing `use App\Models\Issue;` import).
   - Lacks standardized ephemeral storage (`storage/app/temp_reports/`), pre-purge, and on-page status polling.

3. **Low Stock Alert (`admin.reports.low-stock`):**
   - Hardcoded `<= 100` filter causes half the product catalog (677 products) to show up as critical low stock.
   - Must be updated to use the product's actual configured minimum threshold (`products.min_inventory_qty`).

4. **Module Cleanliness:**
   - Keep internal warehouse inventory management tools inside the Inventory module (`InventoryReportController`) rather than mixing them into the commercial Reports section.

---

## 🛠️ 2. Architectural Blueprint & File Map

### Files to Modify:

#### 1. Sidebar & Routing:
- `[MODIFY]` [navbar.blade.php](file:///home/agent47/Sites/b2bvikingERP/resources/views/backend/layouts/navbar.blade.php)
  - Remove "All Analytics Reports" link from the sidebar menu.
- `[MODIFY]` [routes/web.php](file:///home/agent47/Sites/b2bvikingERP/routes/web.php)
  - Redirect `admin.reports.index` to `admin.reports.orders`.
  - Register standardized async PDF routes and status polling endpoint for Order & Sales report.

#### 2. Queue Engine:
- `[MODIFY]` [GenerateReportPdfJob.php](file:///home/agent47/Sites/b2bvikingERP/app/Jobs/GenerateReportPdfJob.php)
  - Add missing `use App\Models\Issue;` import.
  - Standardize ephemeral storage to `storage/app/temp_reports/`.
  - Implement pre-generation auto-purge of old user temporary files.
  - Update notification payload.

#### 3. Controllers:
- `[MODIFY]` [ReportController.php](file:///home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Backend/ReportController.php)
  - Refactor `orderReportPdf` to dispatch in `< 50ms`.
  - Pass `$latestPdf` to `orderReport` view.
  - Fix `lowStockReport` query to compare against `COALESCE(products.min_inventory_qty, 10)` instead of hardcoded 100.

#### 4. Blade Views & Corporate Templates:
- `[MODIFY]` [backend/reports/orders.blade.php](file:///home/agent47/Sites/b2bvikingERP/resources/views/backend/reports/orders.blade.php)
  - Add on-page green `Download PDF (Ready: {time})` button with 2s polling.
- `[MODIFY]` [backend/reports/orders_pdf.blade.php](file:///home/agent47/Sites/b2bvikingERP/resources/views/backend/reports/orders_pdf.blade.php)
  - Clean template of dummy VAT/CVR numbers and placeholder contact information.

#### 5. Automated Feature Tests:
- `[NEW]` [AnalyticsStockReportPdfTest.php](file:///home/agent47/Sites/b2bvikingERP/tests/Feature/Controllers/Reports/AnalyticsStockReportPdfTest.php)

---

## 📋 3. Step-by-Step Implementation Checklist

### Phase 1: Navigation & Routing Streamlining
- [ ] Remove "All Analytics Reports" from `navbar.blade.php`.
- [ ] Update `routes/web.php` so `admin.reports.index` redirects to `admin.reports.orders`.
- [ ] Ensure clean direct access to the 5 enterprise reports in `ANALYTICS & STOCK REPORTS`.

### Phase 2: Background Queue Engine & Bug Fixes
- [ ] Fix `GenerateReportPdfJob`: import `App\Models\Issue`.
- [ ] Standardize ephemeral storage destination to `storage/app/temp_reports/`.
- [ ] Implement pre-generation auto-purge of prior temporary files for the user.

### Phase 3: Controller & Query Enhancements
- [ ] Refactor `ReportController@orderReportPdf` to dispatch in `< 50ms`.
- [ ] Pass `$latestPdf` to `orderReport` view.
- [ ] Fix `ReportController@lowStockReport` to compare against `products.min_inventory_qty`.

### Phase 4: UI Blade & Template Refinements
- [ ] Add on-page `Download PDF (Ready: {time})` button and 2s polling to `orders.blade.php`.
- [ ] Audit `orders_pdf.blade.php` to ensure zero dummy VAT/CVR numbers.

### Phase 5: Automated Testing & Verification
- [ ] Create `AnalyticsStockReportPdfTest.php` asserting non-blocking dispatch, job generation, auto-purge, and status polling.
- [ ] Run full regression test suite across Steps 1, 2, and 3.
