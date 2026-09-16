# 4 — Procurement Reports & Audit Modernization, Legacy Decoupling & Async PDF Engine

**Module Directory:** [docs/report/04_procurement_reports_and_audit/](./)  
**Feature Roadmap:** [feature.md](feature.md)  
**Architecture & Standards:** [SKILL.md](SKILL.md)  
**Main Report Inventory:** [../reportlist.md](../reportlist.md)  

---

## 🎯 Plan Summary

Modernize the **Procurement Reports & Audit** module (Section 4 of the Reports inventory) into an enterprise-grade reporting engine by:
1. **Decoupling from Legacy Purchases:** Replacing all outdated links pointing to `/admin/purchases/{id}` with the canonical Procurement PO workspace (`/admin/purchase-orders/{id}`).
2. **Eliminating Severe N+1 Database Leaks:** Refactoring `SupplierWisePurchaseDataTable` and `ItemWisePurchaseDataTable` from 200+ row-level SQL queries down to single-pass database aggregates (< 5 queries).
3. **Upgrading to Async Background Queue PDF Engine:** Implementing `GenerateProcurementReportPdfJob` with ephemeral storage (`storage/app/temp_reports/`), pre-purge, on-page green ready buttons, and zero HTTP 503 gateway timeouts.
4. **Expanding Filter Intelligence & YoY Matrix:** Adding missing Vendor, Product, and Date Range filters across all reports, and delivering a full 12-month comparative matrix for Year-over-Year procurement audits.

---

## 📋 Scope of Reports (9 Enterprise Reports in Step 4)

```
PROCUREMENT REPORTS & AUDIT
├── 1. Purchase History              (admin.reports.purchase)                  ── Fix link to PO show, Async PDF
├── 2. Product Tracking              (admin.reports.product-purchase-history)  ── Date & Vendor filters, PDF export
├── 3. Supplier-wise Purchase        (admin.purchase-reports.supplier-wise)    ── Fix 200+ N+1 queries, Corporate PDF
├── 4. Item-wise Purchase            (admin.purchase-reports.item-wise)        ── Fix 100+ N+1 queries, Category filters
├── 5. Total Purchase Value          (admin.purchase-reports.total-value)      ── Vendor filter, Executive PDF
├── 6. Purchase vs Last Year         (admin.purchase-reports.vs-last-year)     ── 12-Month YoY Comparative Matrix, PDF
├── 7. PR Status & Pending           (admin.purchase-reports.pr-status)        ── Department & Status filters, PDF
├── 8. PO Issued & Items             (admin.purchase-reports.po-status)        ── PO Registry styling, Filter sync
└── 9. Audit Log Report              (admin.reports.audit)                     ── Regulatory Compliance Export
```

---

## 🛡️ Core Rules & Constraints

1. **Procurement Consistency:** All Purchase Orders link strictly to `admin.purchase-orders.show`.
2. **Zero N+1 Queries:** Database query count per report execution must be **< 5 queries**.
3. **Zero HTTP 503 Crashes:** All heavy PDF exports run strictly in CLI Queue Workers (`512MB RAM`, `600s timeout`).
4. **Zero Forced Auto-Downloads:** Dedicated green on-page button: `Download PDF (Ready: {time})` appears upon polling completion.
5. **Zero Dummy Data:** Verified company metadata only; zero placeholder CVR/VAT numbers.
