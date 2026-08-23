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

## Phase 3: Sales Module Polish, B2B Pricing & Fulfillment (Days 26 - 33) [Completed]
* **Days 26 - 27: Quotations, Pricelists, & Templates [Completed]**
  - Sales quotations CRUD, dynamic PDF templates/streaming, and pricelists for dynamic customer pricing & tier rules.
* **Days 28 - 29: Coupons, Gift Cards, B2B Tiers & Sales Orders [Completed]**
  - One-click Quote to Sales Order conversion, customer segment pricing (Wholesale, B2B VIP, Distributor, Retail), Credit Limits, coupon/gift card validation.
* **Days 30 - 31: Delivery Orders, Sales Returns & Credit Notes [Completed]**
  - Delivery order dispatch & stock deduction, Sales Returns (RMA), Credit Note generation with automatic customer balance and invoice adjustments.
* **Days 32 - 33: User Management, Comprehensive Manuals & Client UI Polish [Completed]**
  - Enterprise User Management refactoring (Internal Staff vs B2B Customers), approval workflow role-filtered assignments, complete Bengali & English user manuals ([Phase3_User_Manual.md](file:///c:/laragon/www/b2bvikingErp/plan/phase%203/Phase3_User_Manual.md)), and 100% automated test coverage.

---

## Phase 4: Inventory & Stock Controls (Days 34 - 40) [Current Sprint / Next Step]
* **Days 34 - 36: FIFO/LIFO Stock Batches Depletion Engine [Next Step]**
  - Integrate sequential FIFO/LIFO stock batch consumption into `DeliveryOrderService` (Sales Dispatch), `StockTransferService`, and `StockAdjustmentService`.
* **Days 37 - 38: Stock Transfer & Reorder Point Notifications [Pending]**
  - Outlet-to-outlet stock transfers with approval workflows. Real-time min stock dashboard notifications & alert widgets.
* **Days 39 - 40: Adjustments, Month-End Snapshots, & Inventory Audit [Pending]**
  - Physical stock corrections, Month-End inventory frozen snapshots, and FIFO valuation verification.

---

## Phase 5: Core Financial Accounting (Days 41 - 50)
* **Days 41 - 42: Chart of Accounts & Fiscal Years Configuration [Pending]**
  - Chart of accounts setup (nested accounts tree) and Fiscal Year periods.
* **Days 43 - 45: Automated Journal Posting (Laravel Observers) [Pending]**
  - Configure Observers to auto-post Journal entries on Purchases, Payments, Sales, and Receipts.
* **Days 46 - 47: Bank Accounts, Petty Cash, & Bank Reconciliation [Pending]**
  - Petty Cash expenses ledger, Bank account registries, and Bank statement reconciliation.
* **Days 48 - 49: Fixed Assets Register & Depreciation Scheduler [Pending]**
  - Fixed assets registry, straight-line depreciation calculator, and automatic monthly posting.
* **Days 50: Accounting Verification [Pending]**
  - Audit Trial Balance, General Ledger, Profit & Loss, and Balance Sheet using mock data.

---

## Phase 6: PayPal, Advanced Settings & Testing (Days 51 - 54)
* **Day 51: PayPal API Integration & IPN Webhook [Pending]**
  - Integrate PayPal Express Checkout for Sales Orders. Set up Webhook to listen for payments and auto-reconcile invoices.
* **Day 52: Advanced System Settings (Enterprise Level) [Pending]**
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
