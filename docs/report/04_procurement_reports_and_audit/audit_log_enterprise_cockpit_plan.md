# 🛡️ Enterprise Audit Log & Forensic Compliance Cockpit Plan

**Location:** `docs/report/04_procurement_reports_and_audit/audit_log_enterprise_cockpit_plan.md`  
**Route:** `admin.reports.audit` (`http://b2bvikingerp.test/admin/reports/audit`)  
**Controller:** `App\Http\Controllers\Backend\ReportController.php@auditReport`  
**Blade View:** `resources/views/backend/reports/audit.blade.php`  
**Standards:** Plus Jakarta Sans Typography, 4-Card Executive KPI Grid, Single Dynamic Slide-Over Forensic Drawer (Zero DOM Bloat), Quick Presets & Select2, Async Background Queue PDF & CSV Compliance Exports.

---

## 🎯 1. Executive Summary & Problem Context

The current Audit Log report (`/admin/reports/audit`) was built as an MVP using legacy Stisla/Bootstrap 4 styles:
1. **Severe DOM Bloat & Memory Leaks:** Loops `@foreach($logs as $log)` and renders **30 complete duplicate hidden HTML modal dialogs** on every page reload (`<div class="audit-modal" id="audit-modal-{{ $log->id }}">`), inflating the HTML payload and slowing down rendering.
2. **Outdated Generic Stisla Theme:** Uses legacy `card-statistic-1` blocks with harsh primary/warning/danger colors that clash with the modern executive design system of the ERP dashboard and procurement reports.
3. **Squished 7-Input Form & Full-Page Reloads:** Narrow, crowded raw HTML `<select>` inputs with zero Select2 integration, no quick date range presets (`Today`, `7D`, `30D`), and full-page reloads on every interaction.
4. **Irrelevant "Vendor" Column:** Wastes valuable table width by permanently displaying a Vendor column that shows `N/A` for 80% of non-vendor operations (inventory, accounts, permissions, users).
5. **Hidden Security Metadata:** Stores `ip_address` and `user_agent` in the database, but completely hides them in the table view.
6. **Zero Corporate PDF or CSV Export:** Completely lacks compliance audit exports required for regulatory, tax, or governance reviews.

---

## 🏗️ 2. Target Architecture & Key Decisions

> [!IMPORTANT]
> **5 Core Architectural Pillars:**
> 1. **Single Dynamic Slide-Over Forensic Drawer:** Eradicate all 30 duplicate hidden DOM modals. Replace them with **1 lightweight, reusable Slide-Over Inspector Drawer** that opens smoothly from the right, providing actor details, IP/browser context, visual side-by-side diff (Red Old vs Green New), and a syntax-highlighted JSON viewer with a "Copy JSON" button.
> 2. **Executive Design System Match:** Restructure the page using `Plus Jakarta Sans`, an executive header bar, 4 top-level KPI cards (Total Logs, Today's Activity, Critical Mutations, Active Actors), and soft severity chips (`CREATE`, `UPDATE`, `DELETE`, `SECURITY`).
> 3. **Quick Presets & Select2 Filter Bar:** Add quick 1-click filter buttons (`[All Time]`, `[Today]`, `[Last 7 Days]`, `[Last 30 Days]`, `[Critical Only]`), alongside Select2 searchable dropdowns for Module, Action, and Staff.
> 4. **Resource Deep-Linking:** Automatically make reference numbers clickable (e.g., Purchase Order `INV-483084` opens `/admin/purchase-orders/{id}` in a new tab; Vendor names open `/admin/vendor-ledger/{id}`).
> 5. **Async Background Queue PDF & CSV Engine:** Implement `GenerateAuditReportPdfJob` with ephemeral storage in `storage/app/temp_reports/`, on-page 2-second polling, and corporate compliance PDF template.

---

## 🛠️ 3. Proposed File Modifications & Additions

### A. Frontend Blade & UI/UX Transformation
#### `[MODIFY]` [resources/views/backend/reports/audit.blade.php](file:///home/agent47/Sites/b2bvikingERP/resources/views/backend/reports/audit.blade.php)
- Master Layout & Fonts: Load `Plus Jakarta Sans` and clean executive CSS styles.
- Executive Header Bar: Title with breadcrumb, live date range subtitle, and action buttons:
  - **Export PDF:** Async background queue dispatch button with spinner and 2s polling.
  - **Download PDF:** Green dynamic badge button appearing when file generation completes.
  - **Export CSV:** Instant client-side CSV compliance export.
  - **Reset:** Quick reset button to clear all filters.
- 4-Card Executive KPI Grid:
  1. *Total Audited Events* (Blue `#2563eb` accent, Shield Check icon).
  2. *Today's Activity* (Emerald `#10b981` accent, Clock / Zap icon).
  3. *Critical Mutations* (Crimson `#dc2626` accent, Alert Triangle icon - deletes/voids).
  4. *Active Actors* (Slate `#64748b` accent, Users icon).
- Filter Ribbon with Date Range Presets:
  - Quick preset pills: `All`, `Today`, `Yesterday`, `Last 7 Days`, `Last 30 Days`, `Critical Only`.
  - Select2 dropdowns for Module, Action/Severity, and Staff.
  - Keyword / Reference text search with debounced submit.
- Activity Stream Matrix Table:
  - **Timestamp:** Formatted date/time with relative time subtext (e.g., `2 hours ago`).
  - **Event Severity:** Color-coded chip (Emerald for Created, Indigo for Updated, Crimson for Deleted, Amber for Security).
  - **Module & Reference:** Uppercase module pill + clickable deep link to resource if available.
  - **Actor:** Avatar circle with initials, staff name, and IP address pill (`2a02:aa7...`).
  - **Summary:** Human-readable action description + changed fields count badge.
  - **Inspect Action:** Sleek "Inspect Changes" button with magnifying glass icon.
- Single Reusable Slide-Over Forensic Inspector Drawer:
  - Eliminate the 30 duplicate hidden modals.
  - Smooth offcanvas slide-over drawer with backdrop blur.
  - Section 1: Event Summary & Deep Resource Link.
  - Section 2: Actor, Role, IP Address (with Copy button), and Browser/OS details.
  - Section 3: Visual Field Diff (Old vs New cards with red/green contrast).
  - Section 4: Raw JSON accordion with "Copy JSON" action.

### B. Background Queue Engine & Corporate PDF Template
#### `[NEW]` [app/Jobs/GenerateAuditReportPdfJob.php](file:///home/agent47/Sites/b2bvikingERP/app/Jobs/GenerateAuditReportPdfJob.php)
- Implements `ShouldQueue` (`512MB RAM`, `600s timeout`).
- Applies all active filters (`module`, `action`, `user_id`, `vendor_id`, `reference`, `start_date`, `end_date`).
- Computes executive audit metrics (Total logs, Module breakdown, Critical deletions, Unique staff).
- Purges previous audit PDFs for the user in `storage/app/temp_reports/` to prevent disk bloat.
- Writes PDF file and registers notification cache `user_pdf_notifications_{userId}`.

#### `[NEW]` [resources/views/backend/reports/pdf/audit_report_pdf.blade.php](file:///home/agent47/Sites/b2bvikingERP/resources/views/backend/reports/pdf/audit_report_pdf.blade.php)
- Clean corporate compliance PDF template in A4 landscape.
- Includes verified company details from `GeneralSetting`.
- Executive KPI summary bar.
- Detailed itemized audit log table with timestamps, modules, actions, references, actors, IP addresses, and summary descriptions.

### C. Controller & Routes Modernization
#### `[MODIFY]` [app/Http/Controllers/Backend/ReportController.php](file:///home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Backend/ReportController.php)
- In `auditReport`:
  - Add calculation for `critical_count` (mutations involving `delete`, `void`, `cancel`).
  - Fetch and pass `$latestPdf` for on-page instant download of ready audit PDFs.
- Add `auditReportPdfAsync(Request $request)` endpoint to dispatch `GenerateAuditReportPdfJob`.
- Update `checkReportStatus` to recognize `audit_report` type.

#### `[MODIFY]` [routes/web.php](file:///home/agent47/Sites/b2bvikingERP/routes/web.php)
- Register async audit PDF dispatch, check status, and download routes:
  - `admin.reports.audit.pdf.async`
  - `admin.reports.audit.pdf.download`
  - `admin.reports.audit.check-status`

### D. Automated Feature Verification
#### `[MODIFY]` [tests/Feature/Controllers/Reports/ProcurementReportsAuditTest.php](file:///home/agent47/Sites/b2bvikingERP/tests/Feature/Controllers/Reports/ProcurementReportsAuditTest.php)
- Add test: `audit report page loads with executive kpi cards and no duplicate dom modals`.
- Add test: `audit report async pdf dispatch and queue execution generates pdf file`.
- Assert all 70+ tests across the reporting suite continue to pass with 0 regressions.

---

## 🧪 4. Verification Plan

### Automated Tests:
```bash
php artisan test tests/Feature/Controllers/Reports/ProcurementReportsAuditTest.php
php artisan test tests/Feature/Controllers/Reports/
```

### Manual Verification:
1. Access `http://b2bvikingerp.test/admin/reports/audit`.
2. Verify executive Plus Jakarta Sans layout, 4 KPI cards, and quick preset buttons (`Today`, `Last 7 Days`, `Last 30 Days`).
3. Click "Inspect Changes" on an audit row and confirm the single Slide-Over Forensic Drawer opens smoothly without page jump.
4. Click "Export PDF" and verify non-blocking async dispatch (< 50ms) and dynamic reveal of the green download button.
5. Click "Export CSV" and verify immediate download of filtered audit records.
