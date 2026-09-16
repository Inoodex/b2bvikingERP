# 3 — Analytics & Stock Reports Async PDF Engine

**Module Directory:** [docs/report/03_analytics_and_stock_reports/](03_analytics_and_stock_reports/)  
**Feature Roadmap:** [docs/report/03_analytics_and_stock_reports/feature.md](03_analytics_and_stock_reports/feature.md)  
**Architecture & Standards:** [docs/report/03_analytics_and_stock_reports/SKILL.md](03_analytics_and_stock_reports/SKILL.md)  
**Main Report Inventory:** [docs/report/reportlist.md](reportlist.md)  

---

## 🎯 Plan Summary

Convert heavy Analytics & Stock PDF exports to the standardized **asynchronous background queue architecture (`ShouldQueue`)** with on-page **"Download PDF (Ready: {time})"** controls, eliminating the primary HTTP 503 Service Unavailable / memory exhaustion crashes.

### Scope of Reports:
1. **Report 11: Order & Sales Report** (`admin.reports.orders` via `ReportController@orderReport`)
   - 🚨 **Primary 503 Risk:** Currently has a synchronous export endpoint (`reports/orders/pdf`) and an unstandardized async endpoint.
   - Refactor `GenerateReportPdfJob`: fix missing model imports (`App\Models\Issue`), standardize ephemeral storage to `storage/app/temp_reports/` with automatic pre-purge.
   - Add on-page green **"Download PDF (Ready: {time})"** button with 2s status polling.
2. **Current Stock Report (Inventory Module)** (`admin.inventory-reports.index` via `InventoryReportController@exportPdf`)
   - 🚨 **Critical Memory Exhaustion:** In-flight synchronous DomPDF generation with heavy thumbnail image resizing and unpaginated stock loading (`512MB RAM`).
   - Migrate to background queue job (`GenerateInventoryReportPdfJob`) with 512MB memory and 600s execution timeout on the CLI worker.
   - Add on-page green **"Download PDF (Ready: {time})"** button with 2s polling in `resources/views/backend/inventory_report/index.blade.php`.
3. **Corporate Templates & Zero Dummy Data:**
   - Audit `backend/reports/orders_pdf.blade.php` and `backend/inventory_report/export_pdf.blade.php` to remove any placeholder VAT numbers (`DK12345678`), dummy company contacts, or hardcoded dates.
4. **Comprehensive Automated Testing:**
   - Create `tests/Feature/Controllers/Reports/AnalyticsStockReportPdfTest.php` asserting non-blocking HTTP dispatch (`< 50ms`), job execution, auto-purge, and status polling.

---

## 🛡️ Core Rules & Constraints

1. **Zero HTTP 503 Server Crashes:** All PDF compilation (especially product image compression) must execute strictly in CLI Queue Workers (`queue:work`). HTTP requests return immediately in `< 50ms`.
2. **Zero Forced Auto-Downloads:** On-page `Download PDF (Ready: {time})` button appears only when background generation is complete.
3. **Zero Dummy Data:** No placeholder CVR/VAT numbers (`DK12345678`) or hardcoded dummy emails.
4. **Pre-generation Auto-Purge:** Automatic deletion of prior ephemeral files for the same user/report in `storage/app/temp_reports/`.
5. **Dual Notification:** On-page button + top navbar bell alert.
