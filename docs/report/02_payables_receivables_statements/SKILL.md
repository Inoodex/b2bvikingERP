---
name: payables-receivables-statements-pdf-architecture
description: Enterprise architecture rules, design guidelines, and code patterns for Payables & Receivables Statements (AR Aging, Customer Ledger, Vendor Payment Ledger, AP Aging, Supplier Statement) with Ephemeral Dynamic PDF Generation and zero server downtime.
---

# 🏛️ Payables & Receivables Statements — Architecture & PDF Standards (SKILL.md)

This skill document defines the architectural standards, performance guidelines, security practices, and implementation rules for **Payables & Receivables Statements** in B2B Viking ERP.

---

## 📌 1. Architectural Principles

### 1.1 The Ephemeral Dynamic Storage Model
Payables and receivables data changes in real-time as customer invoice payments are recorded, credit holds are released, and supplier purchase bills are settled. Serving stale cached calculations is strictly prohibited.

```
[Client Request: Export PDF with Filters]
                 │
                 ▼
[1. Controller: Immediate Dispatch] ── Dispatches GeneratePayablesReceivablesReportPdfJob (< 50ms)
                 │                    Frees PHP-FPM web worker immediately!
                 ▼
[2. Background Queue Worker] ── Fetches up-to-the-second live database records.
                 │             Purges prior temporary files for this user/report.
                 ▼
[3. Generate Ephemeral PDF] ── Writes to `storage/app/temp_reports/{unique_name}.pdf`.
                 │
                 ▼
[4. Bell Icon Notification] ── Dispatches download notification to navbar bell icon.
                 │
                 ▼
[5. Client Polling & On-Page Button] ── Client detects completion; displays "Download PDF (Ready: {time})".
                 │                     Zero forced popups; user downloads at their convenience.
                 ▼
[Result: 100% Live Data, Zero Server 503 Crashes, Seamless User Experience]
```

### 1.2 Dedicated Background Queue Processing
- All payables & receivables PDF generation must be executed asynchronously via `App\Jobs\GeneratePayablesReceivablesReportPdfJob` implementing `ShouldQueue`.
- The controller must never render DomPDF directly in the HTTP worker thread.
- Memory limit: `512M`, execution timeout: `600s`.
- Controllers must return an immediate response (`< 50ms`), either JSON for AJAX requests or a redirect for standard GET requests.

### 1.3 Zero Forced Auto-Downloads (User-Controlled Flow)
- Automated `window.location.href` trigger after polling is strictly prohibited.
- Reason: Browser pop-up blockers in Chrome, Edge, and Safari interrupt the user experience.
- The standard is an on-page green **"Download PDF (Ready: {time})"** button that becomes visible when the background worker completes generation.

---

## 🛡️ 2. Zero Dummy Data Constraints

1. **No CVR / VAT Numbers:**
   - Under no circumstances should placeholder or seeded VAT numbers (such as `DK12345678` or `DK-99238419`) appear in any PDF report header or footer.
   - Do not display CVR / VAT labels on internal managerial statements unless specifically mandated and verified against live company settings.

2. **No Fallback Dummy Contact Information:**
   - Never use placeholder emails (e.g. `billing@b2bviking.com`, `finance@b2bviking.com`) or dummy addresses (`Corporate Headquarters`, `Copenhagen, Denmark`).
   - Read directly from `Company::first()` or `GeneralSetting::first()`. If an address, phone, or email is empty, render nothing for that field.

3. **Real User Accountability:**
   - Printed/Generated metadata must reflect the actual authenticated user name who requested the export (`$user->name`), not a generic `"Admin"` string.

---

## 🎨 3. Corporate A4 PDF Layout Standards

All payables and receivables statements must adhere to standard corporate styling:

1. **Paper Sizing & Orientation:**
   - Multi-column matrix reports (e.g. AR Aging with multiple bucket columns) should render in **A4 Landscape** for readability.
   - Single-vendor statements and payment lists should render in **A4 Portrait**.
2. **Typography & Formatting:**
   - Font family: Clean system sans-serif (`DejaVu Sans`, `Helvetica`, `Arial`).
   - Base text size: `8pt` to `9.5pt` for data rows; table headers `8.5pt` bold; titles `14pt` to `16pt`.
   - Monetary alignment: All currency amounts must be strictly **right-aligned** and formatted in Danish Krone (`kr. {{ number_format($amount, 2) }}`).
3. **Formal Verification & Signatures:**
   - Statements must include a formal 3-column closing block (Prepared By, Checked By, Authorized By) for accounting audit compliance.
