# 2 — Payables & Receivables Statements Async PDF Engine

**Module Directory:** [docs/report/02_payables_receivables_statements/](02_payables_receivables_statements/)  
**Feature Roadmap:** [docs/report/02_payables_receivables_statements/feature.md](02_payables_receivables_statements/feature.md)  
**Architecture & Standards:** [docs/report/02_payables_receivables_statements/SKILL.md](02_payables_receivables_statements/SKILL.md)  
**Main Report Inventory:** [docs/report/reportlist.md](reportlist.md)  

---

## 🎯 Plan Summary

Convert all 5 Payables and Receivables financial reports to an **asynchronous background queue architecture (`ShouldQueue`)** with on-page **"Download PDF (Ready: {time})"** button controls, preventing HTTP 503 server crashes and eliminating all dummy data.

### Scope of Reports:
1. **Report 5: Customer Transaction Ledger** (`admin.accounts.index` via `AccountController@paymentHistoryPdf`)
   - Remove 1000-row hard limit.
   - Dispatch to background queue worker.
   - On-page green ready download button with polling.
2. **Report 6: AR Customer Aging** (`admin.reports.ar-aging` via `SalesReportController@exportArAgingPdf`)
   - 🚨 Critical 503 crash risk resolved via background queue job.
   - Corporate A4 Landscape layout with 0-30, 31-60, 61-90, 90+ days aging buckets.
   - Zero forced popup download.
3. **Report 7: Vendor Payment Ledger** (`admin.accounts.vendor-payments.index` via `AccountController@vendorPaymentHistoryPdf`)
   - Remove 1000-row limit.
   - Non-blocking queue dispatch.
4. **Report 8: AP Vendor Aging** (`admin.vendor-ledger.aging` via `VendorLedgerController@agingReport`)
   - Add new background queue PDF export engine.
   - Corporate A4 Landscape AP aging matrix.
5. **Report 9: Supplier Ledger & Statement** (`admin.vendor-ledger.show` via `VendorLedgerController@exportPdf`)
   - Background queue export with running balance and transaction timeline.

---

## 🛡️ Core Rules & Constraints

1. **Zero HTTP 503 Server Crashes:** Controllers dispatch jobs in `< 50ms`. PDF rendering runs strictly in CLI Queue Workers (`512MB`, `600s`).
2. **Zero Forced Auto-Downloads:** Dedicated green on-page button: `Download PDF (Ready: {time})` appears when polling returns `ready: true`.
3. **Zero Dummy Data:** Complete removal of placeholder VAT numbers (`DK12345678`, `DK-99238419`) and dummy contact emails. Only real database values are rendered.
4. **Pre-generation Auto-Purge:** Deletes previous ephemeral files for the user before storing fresh files in `storage/app/temp_reports/`.
5. **Persistent Notification:** Top navbar bell icon alert is updated with the direct download link.
