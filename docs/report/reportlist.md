# 📊 B2B Viking ERP — Comprehensive Reports Inventory & PDF Export Audit

**System:** Copenhagen Tourist Point (b2bviking.com) — B2B Viking ERP  
**Location:** Admin Sidebar Navigation (`resources/views/backend/layouts/navbar.blade.php`)  
**Objective:** Complete audit of all system reports to transition high-memory, long-running PDF exports to asynchronous Queue Jobs to eliminate HTTP 503 Service Unavailable / Gateway Timeout server crashes.

---

## 🎯 1. Executive Summary & Problem Context

When users trigger direct (synchronous) PDF downloads on large datasets—specifically with **DomPDF** rendering multi-page tables, aggregated financial matrices, and product images:
- **PHP-FPM Worker Starvation:** A single PDF download blocks a web worker process for 30s to 120s+ while consuming 256MB to 512MB+ of memory.
- **Server Crash (HTTP 503):** The Nginx/Apache gateway times out or exhausts all available PHP-FPM workers, rendering the entire live ERP unavailable to all users.
- **The Solution:** Offload all heavy PDF generation to Laravel Queue background workers (`queue:work` / Supervisor) with isolated execution timeout and memory limits, returning instantaneous non-blocking responses to the client with smart download notifications.

---

## 📋 2. Comprehensive Reports Inventory (Sidebar Navigation)

Below is the complete list of all 24 report endpoints found under the **Reports** menu in the sidebar navigation:

### 1. Core Financial Statements
| # | Report Name | Route Name | Controller & Method | Export Status / Type | Crash Risk |
|:--|:---|:---|:---|:---|:---:|
| 1 | **General Ledger** | `admin.reports.general-ledger` | `FinancialReportController@generalLedger` | Yajra DataTables (Web) + Ephemeral PDF (`reports/general-ledger/pdf`) | ✅ Safe (0 MB RAM leak) |
| 2 | **Trial Balance** | `admin.reports.trial-balance` | `FinancialReportController@trialBalance` | Blade View + Ephemeral PDF (`reports/trial-balance/pdf`) | ✅ Safe (0 MB RAM leak) |
| 3 | **Profit & Loss (P&L)** | `admin.reports.profit-loss` | `FinancialReportController@profitAndLoss` | Blade View + Ephemeral PDF (`reports/profit-loss/pdf`) | ✅ Safe (0 MB RAM leak) |
| 4 | **Balance Sheet** | `admin.reports.balance-sheet` | `FinancialReportController@balanceSheet` | Blade View + Ephemeral PDF (`reports/balance-sheet/pdf`) | ✅ Safe (0 MB RAM leak) |

---

### 2. Payables & Receivables Statements
| # | Report Name | Route Name | Controller & Method | Export Status / Type | Crash Risk |
|:--|:---|:---|:---|:---|:---:|
| 5 | **Customer Transaction Ledger** | `admin.accounts.index` | `AccountController@index` | Synchronous PDF (`accounts/payments/pdf`) | Medium |
| 6 | **AR Customer Aging** | `admin.reports.ar-aging` | `SalesReportController@arAging` | **Synchronous PDF** (`reports/ar-aging/pdf` via `exportArAgingPdf`) | 🚨 **High** |
| 7 | **Vendor Payment Ledger** | `admin.accounts.vendor-payments.index` | `AccountController@vendorPaymentHistory` | Synchronous PDF (`accounts/vendor-payments/pdf`) | Medium |
| 8 | **AP Vendor Aging** | `admin.vendor-ledger.aging` | `VendorLedgerController@agingReport` | Blade View | Low |
| 9 | **Supplier Ledger & Statement** | `admin.vendor-ledger.index` | `VendorLedgerController@index` | Synchronous PDF (`vendor-ledger/{id}/pdf`) | Medium |

---

### 3. Analytics & Stock Reports
| # | Report Name | Route Name | Controller & Method | Export Status / Type | Crash Risk |
|:--|:---|:---|:---|:---|:---:|
| 10 | **All Analytics Reports** | `admin.reports.index` | `ReportController@index` | Dashboard View & Mini KPIs | Low |
| 11 | **Order & Sales Report** | `admin.reports.orders` | `ReportController@orderReport` | 🔥 **Synchronous PDF** (`reports/orders/pdf` via `orderReportPdf`)<br>*Async partial exists: `reports/orders/pdf/async` via `GenerateReportPdfJob`* | 🚨 **CRITICAL (Primary 503 Source)** |
| 12 | **Stock Valuation Reports** | `admin.reports.stock` | `ReportController@stockReport` | Blade View / AJAX pagination | Low |
| 13 | **Low Stock Alert** | `admin.reports.low-stock` | `ReportController@lowStockReport` | Blade View / AJAX alert checks | Low |
| 14 | **Current Stock Report** | `admin.reports.current-stock` | `ReportController@currentStockReport` | Excel Export (`.xlsx` via `CurrentStockExport`) | Low |
| * | *Current Stock (Inventory Module)* | `admin.inventory-reports.index` | `InventoryReportController@index` | 🚨 **Synchronous PDF** (`inventory-reports/export-pdf` via `exportPdf`)<br>*Loads ALL stocks + image compression in-flight* | 🚨 **CRITICAL (Massive Memory Exhaustion)** |
| 15 | **Sales Rep Performance** | `admin.reports.salesperson-performance` | `SalesReportController@salespersonPerformance` | Blade View | Low |

---

### 4. Procurement Reports & Audit
| # | Report Name | Route Name | Controller & Method | Export Status / Type | Crash Risk |
|:--|:---|:---|:---|:---|:---:|
| 16 | **Purchase History** | `admin.reports.purchase` | `ReportController@purchaseReport` | Blade View | Low |
| 17 | **Product Tracking** | `admin.reports.product-purchase-history` | `ReportController@productPurchaseHistory` | Blade View | Low |
| 18 | **Supplier-wise Purchase** | `admin.purchase-reports.supplier-wise` | `PurchaseReportController@supplierWise` | Yajra DataTables | Low |
| 19 | **Item-wise Purchase** | `admin.purchase-reports.item-wise` | `PurchaseReportController@itemWise` | Yajra DataTables | Low |
| 20 | **Total Purchase Value** | `admin.purchase-reports.total-value` | `PurchaseReportController@totalValue` | Blade View | Low |
| 21 | **Purchase vs Last Year** | `admin.purchase-reports.vs-last-year` | `PurchaseReportController@vsLastYear` | Blade View | Low |
| 22 | **PR Status & Pending** | `admin.purchase-reports.pr-status` | `PurchaseReportController@prStatus` | Yajra DataTables | Low |
| 23 | **PO Issued & Items** | `admin.purchase-reports.po-status` | `PurchaseReportController@poStatus` | Yajra DataTables | Low |
| 24 | **Audit Log Report** | `admin.reports.audit` | `ReportController@auditReport` | Blade View / Paginated | Low |

---

## 🚨 3. High-Risk Offenders Causing Live Server 503 Crashes

### 1. Order & Sales Report (`Order & Issue Report`)
- **Location:** `ReportController@orderReportPdf`
- **Why it crashes:** Aggregates thousands of orders, customer metadata, issue items, line totals, and payment records across selected date ranges or entire years.
- **Current Status:** A background job `App\Jobs\GenerateReportPdfJob` was partially written but the UI button still frequently routes users to synchronous generation or lacks an intuitive completion flow.

### 2. Current Stock / Inventory Report
- **Location:** `InventoryReportController@exportPdf`
- **Why it crashes:** Executes `->get()` without pagination across the entire inventory database, loops over each stock record to run `PdfImageHelper::optimize(...)` on product images, and feeds the massive HTML into DomPDF. This instantly triggers PHP memory limit errors or fastcgi timeouts.

### 3. Customer AR Aging Report
- **Location:** `SalesReportController@exportArAgingPdf`
- **Why it crashes:** Iterates across all posted unpaid invoices, calculates aging buckets (0-30, 31-60, 61-90, 90+ days), and streams DomPDF directly to the browser.

---

## 🏗️ 4. Recommended Target Architecture: Async Queue PDF Engine

To ensure zero live server downtime and rock-solid PDF generation:

1. **Non-Blocking Dispatch:**
   - User clicks "Export PDF".
   - Controller immediately validates filters, dispatches a queueable job, and returns an instant response (flash toastr or AJAX feedback) in < 150ms.
   - Web workers are freed immediately.

2. **Background Execution (`ShouldQueue`):**
   - Runs in isolated CLI context with `set_time_limit(0)` and configurable memory limit.
   - Renders PDF and writes file to `storage/app/public/reports/`.

3. **User Notification & Secure Download:**
   - On completion, dispatches an in-app notification (bell icon) and cache entry containing the authenticated download link.
   - Optional Auto-Cleanup: Downloaded files or files older than 24–48 hours are automatically pruned by a scheduled artisan command (`schedule:run`).
