# ERP Transformation - Developer Task Division (Git Safe Split)
To avoid code conflicts in Git and ensure parallel development, we divide the project by **Inflow/Stock** and **Outflow/Accounting**.

---

## 🛠️ Developer 1 (You) - Procurement & Inventory
**Focus:** Buy Side, Import Logistics, and Warehousing.

* **Phase 1: Foundation & Requisitions (Days 1 - 10)**
  - Database Migration run.
  - Setup CRUDs for `Company`, `Department`, `Outlet`.
  - Requisitions (SR/PR) forms and validations.
* **Phase 2: Local & Foreign Purchase (Days 11 - 25)**
  - RFQ and Vendor Quotation Entry.
  - Comparison Statement (CS) side-by-side view.
  - Purchase Orders (PO) - Local & Foreign.
  - Proforma Invoice (PI) & Letters of Credit (LC) tracker.
  - Shipment Info, C&F Agent, SIT calculations.
  - Goods Receipt Note (GRN) & Quality Check (QC).
  - Landed Cost Weighted Average Calculation script.
  - Return to Vendor (Debit Note).
* **Phase 4: Inventory & Stock Controls (Days 34 - 40)**
  - FIFO/LIFO/Batch Issue depletion code.
  - Stock Transfer (Outlet to Outlet) with approval.
  - Minimum stock levels config & reorder notifications.
  - Stock Adjustments & Month-End Snapshots.

---

## 🛠️ Developer 2 - Sales, Billing & Core Accounting
**Focus:** Sell Side, Pricing Rules, Customer Ledger, and General Ledger.

* **Phase 1: Foundation & Approvals (Days 1 - 10)**
  - Setup CRUDs for `Currency`.
  - Approval Workflow Setup UI (Dynamic Steps config).
  - Notification mail templates and Listener.
* **Phase 3: Sales Module Polish (Days 26 - 33)**
  - Sales Quotations & Quotation Templates.
  - Convert Quote to Sales Order.
  - Coupons, Gift Cards, and Pricelists (Dynamic Pricing).
  - Customer Returns (Credit Note).
* **Phase 5: Core Financial Accounting (Days 41 - 50)**
  - Chart of Accounts (COA) Tree UI.
  - Automated Journal Posting Observers (Sales/Receipts/Bills/Payments).
  - Petty Cash expenses ledger, Bank Account Register.
  - Bank Statement Reconciliation tool.
  - Fixed Assets Register & Depreciation scheduler.
* **Phase 6: Integrations & Financial Reports (Days 51 - 54)**
  - PayPal Webhook Integration.
  - Trial Balance, Profit & Loss, Balance Sheet PDFs/Excel.
  - General Ledger and Cost Center Reports.

---

## 🤖 AI Assistant (Me) - Backend Core Logic Developer
**Focus:** Writing the complex mathematical calculations and backend services for both developers.

* Write `LandedCostService.php` for Dev 1.
* Write `StockDepletionService.php` (FIFO/LIFO algorithms) for Dev 1.
* Write `ApprovalService.php` and polymorphic traits for both Dev 1 & Dev 2.
* Write accounting `JournalPostingService.php` and Observers for Dev 2.
* Update `daily_roadmap.md` progress markers daily.
