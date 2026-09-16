---
name: analytics-stock-reports-pdf-architecture
description: Enterprise architecture rules, clean direct navigation, real inventory thresholds, and queue patterns for Analytics & Stock Reports (Order & Sales Report, Stock Valuation, Low Stock Alert, Current Stock, Sales Rep Performance) with Ephemeral PDF generation and zero HTTP 503 downtime.
---

# 🏛️ Analytics & Stock Reports — Architecture & PDF Standards (SKILL.md)

This skill document defines the architectural standards, navigation principles, inventory thresholds, and background queue rules for **Analytics & Stock Reports** in B2B Viking ERP.

---

## 📌 1. Architectural Principles

### 1.1 Direct Functional Navigation (Enterprise Pattern)
Tier-1 enterprise ERP systems (SAP, NetSuite, Dynamics 365) avoid generic "link-farm" dashboards inside report sub-menus. The main executive dashboard (`/admin/dashboard`) serves as the central KPI cockpit. Under the **Analytics & Stock Reports** section, staff navigate directly to specific, actionable reports with date range and category filters:

1. **Order & Sales Report:** Commercial sales volume, best sellers, customer orders vs issued items.
2. **Stock Valuation Reports:** Inventory asset values (weighted average purchase cost vs retail price, potential profit).
3. **Low Stock Alert:** Reorder alerts based on actual minimum inventory quantities.
4. **Current Stock Report:** Canonical catalog stock report by Vendor and Category with Excel export.
5. **Sales Rep Performance:** Commercial rep matrix (Sales, Collections, Dues, AOV).

### 1.2 Dedicated Background Queue Processing
- Heavy sales and order compilation must execute asynchronously via `App\Jobs\GenerateReportPdfJob` implementing `ShouldQueue`.
- Memory limit: `512M`, execution timeout: `600s`.
- Controllers must return an immediate response (`< 50ms`), freeing PHP-FPM web workers and eliminating HTTP 503 gateway timeouts.

### 1.3 Pre-generation Auto-Purge & Ephemeral Storage
- Temporary files must be stored in `storage/app/temp_reports/`.
- Prior temporary files for the user and report type are purged automatically before a new PDF is compiled.

### 1.4 Zero Forced Auto-Downloads
- No unsolicited `window.location.href` triggers.
- The UI renders an on-page green **"Download PDF (Ready: {time})"** button upon completion with 2s polling.

---

## 🛡️ 2. Inventory Threshold & Data Integrity Constraints

1. **Real Reorder Thresholds:**
   - Low stock queries must never hardcode arbitrary limits (such as `<= 100`).
   - Queries must compare against each product's configured `min_inventory_qty` (defaulting to 10 only if null).

2. **No Dummy CVR / VAT Numbers:**
   - Under no circumstances should placeholder VAT numbers (`DK12345678`, `DK-99238419`) appear in any PDF report header or footer.
   - Company CVR/VAT must be pulled directly from `GeneralSetting::first()` or `Company::first()`.

3. **Currency & Formatting:**
   - All amounts in Danish Krone (`kr. {{ number_format($val, 2) }}`).
