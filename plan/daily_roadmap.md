# ERP Transformation - Full 60-Day (Working Days) Implementation Roadmap
**Targeting:** 2 Developers (Dev 1, Dev 2) + AI Assistant
**Total Duration:** 60 Active Working Days (equivalent to 80 Calendar Days including weekends).

---

## Phase 1: Foundation & Approval Engine (Days 1 - 10)
* **Days 1 - 2: Database & Master Data Setup [Completed]**
  - Run 69 migrations. Set up CRUDs for `Currency`, `Company`, `Department`, `Outlet`. Update User/Vendor forms.
* **Days 3 - 5: Odoo-Style Polymorphic Approval Engine [Completed]**
  - Create ApprovalWorkflow and ApprovalStep models. Write `ApprovalService` to attach to workflows.
* **Days 6 - 8: Requisition (SR/PR) Upgrades & Approvals [Completed]**
  - Refactor product requests. Implement approval workflow checkpoints.
* **Days 9 - 10: Foundation QA & Client Review (First Feedback Loop) [Completed]**
  - Present the requisition approval flow to the client. Collect and fix initial UI/flow feedback.

---

## Phase 2: Purchase & Import (LC) Module (Days 11 - 25) [Completed]
* **Days 11 - 13: RFQ, Quotations, and Comparison Statements (CS) [Completed]**
  - Vendor RFQ, Vendor Quotations entry, side-by-side CS tables with CS approvals.
* **Days 14 - 16: PO Upgrades, Proforma Invoices, and LC Details [Completed]**
  - Link POs to RFQ/CS. Setup PI details and LC details/amendments.
* **Days 17 - 19: Shipment, SIT, and GRN with Quality Control [Completed]**
  - Shipment details form, Stock-in-Transit (SIT) calculations, GRN items checklist with strict over-receipt guards.
* **Days 20 - 22: Landed Cost Allocation Engine & Vendor Returns [Completed]**
  - Weighted Average landed cost calculations, Vendor Returns (Debit Note) flow with dynamic claim amount accessors.
* **Days 23 - 25: Procurement testing, Reports, & Client Approval Loop [Completed]**
  - Purchase reports and LC Register. Server-side Yajra DataTables, real-time instant AJAX filters, cleaned titles, and pagination controls.

---

## Phase 3: Sales Module Polish (Days 26 - 33) [Completed]
* **Days 26 - 27: Quotations, Pricelists, & Templates [Completed]**
  - Sales quotations, quotation templates, and pricelists for dynamic customer pricing.
* **Days 28 - 29: Coupons, Gift Cards, & Sales Orders [Completed]**
  - Convert Quote to Sales Order. Coupon/Gift card checkout application.
* **Days 30 - 31: Sales Returns & Credit Notes [Completed]**
  - Process customer return, generate Credit Note, and automatically adjust outstanding invoices.
* **Days 32 - 33: Sales Reports & Client UI Polish [Completed]**
  - Salesperson performance, aging receivables report, and client feedback adjustments on Checkout UI.

---

## Phase 4: Inventory & Stock Controls (Days 34 - 40) [Completed]
* **Days 34 - 36: FIFO Stock Batches & Costing Engine [Completed]**
  - Batch tracking with exact Landed Cost and automatic sequential FIFO depletion on delivery.
* **Days 37 - 38: 3-Stage Stock Transfer & Barcode Generator [Completed]**
  - Outlet-to-outlet transfer with Draft -> Dispatched -> Received states, PDF waybill, and bin barcodes.
* **Days 39 - 40: Adjustments, Month-End Snapshots, & Auto-Replenishment [Completed]**
  - Physical stock corrections, Month-End inventory frozen snapshots, and Auto-Replenish cron.

---

## Phase 5: Core Financial Accounting (Days 41 - 50) [Completed]
* **Days 41 - 42: Chart of Accounts & Fiscal Years Configuration [Completed]**
  - Chart of accounts setup (nested accounts tree) and Fiscal Year periods.
* **Days 43 - 45: Automated Journal Posting (Laravel Observers & Services) [Completed]**
  - Configure Observers/Services to auto-post balanced Journal entries on Purchases, Payments, Sales, and Receipts.
* **Days 46 - 47: Bank Accounts, Petty Cash, & Bank Reconciliation [Completed]**
  - Petty Cash expenses ledger, Bank account registries, Fund transfers (Contra), and Bank statement reconciliation.
* **Days 48 - 49: Fixed Assets Register & Depreciation Scheduler [Completed]**
  - Fixed assets registry, straight-line depreciation calculator, and automatic monthly posting.
* **Days 50: Accounting Verification [Completed]**
  - Audit Trial Balance, General Ledger, Profit & Loss, and Balance Sheet using mock data.

---

## Phase 6: PayPal, Advanced Settings & Testing (Days 51 - 54)
* **Day 51: PayPal API Integration & IPN Webhook [Pending]**
  - Integrate PayPal Express Checkout for Sales Orders. Set up Webhook to listen for payments and auto-reconcile invoices.
* **Day 52: Advanced System Settings & Master Feature Toggles [Pending]**
  - **Enterprise Feature Toggles Center** (`plan/enterprise_feature_toggles_plan.md`): Centralized Admin ON/OFF switches for Auto-Replenish, Vendor Emails, Quote Reminders, Credit Lock, and Auto-Journals.
  - Implement "Clear Cache" UI for optimization.
  - Integrate `spatie/laravel-backup` for DB & File Backup downloads from Admin Panel.
  - Setup "Recycle Bin" for Soft Deletes (view/restore/force delete).
* **Days 53 - 54: System-wide End-to-End Testing (Dev QA) [Pending]**
  - Verify accounting ledger values, inventory valuation, and approval locks under stress testing.

---

## Phase 7: Historical Data Migration, Training & Deployment (Days 55 - 60)
* **Days 55 - 56: Historical Data Migration (Crucial Step!) [Pending]**
  - Write custom scripts to migrate existing customers, vendors, products, and historic purchases/orders into the new database tables.
  - Set up opening accounting balances for the Chart of Accounts to match current assets/liabilities.
* **Days 57 - 58: User Training & Documentation [Pending]**
  - Write user guides or record video walkthroughs for:
    1. Warehouse staff on how to receive goods (GRN) and QC.
    2. Accounts staff on how to reconcile bank accounts and post journal entries.
    3. Purchase team on how to manage LCs and Landed Costs.
* **Days 59 - 60: UAT (User Acceptance Testing) & Live Production Deployment [Pending]**
  - Perform the final client UAT walkthrough.
  - Deploy to the live server. Monitor system performance and database integrity during the first days of use.

---
### Why the Extended 60 Days is Essential
This roadmap prevents failure. By allocating 15 days for post-dev QA, client feedback loops, data migration, and training, the system will launch smoothly. Developers write features for 40 days, while the final 20 days ensure those features work in production with historical data.
