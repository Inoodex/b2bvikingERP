# 3 — Analytics & Stock Reports Async PDF Engine & Enterprise Cleanup

**Module Directory:** [docs/report/03_analytics_and_stock_reports/](03_analytics_and_stock_reports/)  
**Feature Roadmap:** [docs/report/03_analytics_and_stock_reports/feature.md](03_analytics_and_stock_reports/feature.md)  
**Architecture & Standards:** [docs/report/03_analytics_and_stock_reports/SKILL.md](03_analytics_and_stock_reports/SKILL.md)  
**Main Report Inventory:** [docs/report/reportlist.md](reportlist.md)  

---

## 🎯 Plan Summary

Streamline the **Analytics & Stock Reports** module into an enterprise-grade reporting suite by:
1. **Decommissioning the redundant "All Reports Dashboard":** Eliminating the amateurish link-farm and broken KPI cards (negative gross profit and false low-stock alarms), redirecting directly to the primary commercial report.
2. **Upgrading Commercial & Sales PDF Exports to Async Background Engine:** Standardizing `GenerateReportPdfJob` on the background queue with on-page ready buttons, eliminating HTTP 503 gateway timeout risks.
3. **Fixing Inventory Threshold Calculations:** Repairing the Low Stock Alert query to use actual product thresholds (`min_inventory_qty`) rather than an arbitrary 100-unit limit.

---

## 📋 Scope of Reports (Streamlined 5 Enterprise Reports)

```
ANALYTICS & STOCK REPORTS
├── 1. Order & Sales Report         (admin.reports.orders)              ── Async PDF Engine (<50ms dispatch)
├── 2. Stock Valuation Reports      (admin.reports.stock)               ── Asset Value & Margin Analysis
├── 3. Low Stock Alert              (admin.reports.low-stock)           ── Real min_inventory_qty Threshold
├── 4. Current Stock Report         (admin.reports.current-stock)       ── Vendor/Category Catalog & Excel
└── 5. Sales Rep Performance        (admin.reports.salesperson-performance) ── Commercial Rep Matrix
```

### Detailed Breakdown:

1. **Decommission "All Analytics Reports" (`admin.reports.index`):**
   - Remove the link from sidebar navigation (`navbar.blade.php`).
   - Redirect `admin.reports.index` to `admin.reports.orders` for seamless backwards-compatibility.
   - Eliminates redundant dashboard duplication and mathematical calculation errors.

2. **Order & Sales Report (`admin.reports.orders`):**
   - Eliminate synchronous in-flight PDF generation (`orderReportPdf`) which causes HTTP 503 timeouts.
   - Fix fatal runtime bug in `GenerateReportPdfJob`: add missing `use App\Models\Issue;` import.
   - Standardize ephemeral storage destination to `storage/app/temp_reports/` with automatic pre-purge.
   - Add on-page green **"Download PDF (Ready: {time})"** button with 2s polling.
   - Audit `backend/reports/orders_pdf.blade.php` to ensure zero dummy VAT/CVR numbers.

3. **Stock Valuation Reports (`admin.reports.stock`):**
   - Maintain the executive valuation KPI cards (Total Qty, Total Cost Value, Potential Revenue, Potential Margin).
   - Ensure clean client-side DataTables export and seamless navigation.

4. **Low Stock Alert (`admin.reports.low-stock`):**
   - Fix the broken query from `inventory_stocks_sum_quantity <= 100` to compare against each product's actual reorder threshold:  
     `inventory_stocks_sum_quantity <= COALESCE(products.min_inventory_qty, 10)`.
   - Eliminates the false 677-item alarm and accurately reflects warehouse replenishment needs.

5. **Current Stock Report (`admin.reports.current-stock`):**
   - Maintain as the canonical stock report in the Reports section with Vendor, Category, and Stock Status filters and Excel download.

6. **Sales Rep Performance (`admin.reports.salesperson-performance`):**
   - Maintain the commercial performance matrix (Orders, Gross Revenue, Collections, Dues, and AOV).

---

## 🛡️ Core Rules & Constraints

1. **Enterprise Layout Purity:** No duplicate or unorganized link-farm dashboards in the Reports submenu. Direct access to functional reports only.
2. **Zero HTTP 503 Crashes:** All heavy PDF exports run strictly in CLI Queue Workers (`512MB RAM`, `600s timeout`). Controllers return in `< 50ms`.
3. **Zero Forced Auto-Downloads:** Dedicated green on-page button: `Download PDF (Ready: {time})` appears upon polling completion.
4. **Zero Dummy Data:** No placeholder VAT/CVR numbers (`DK12345678`) or dummy emails; verified live database records only.
5. **Real Reorder Thresholds:** Inventory alerts must respect individual product `min_inventory_qty` configuration.
