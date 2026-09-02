# Trello Board: B2B Viking ERP — Master Development Tracker
**Timeline: 60 Working Days | 7 Phases | 2 Developers + AI**

---

## 📋 LIST: ✅ DONE (Phase 1, Phase 2, Phase 3 & Phase 4)

---

### 🃏 Module: Database & Master Data Setup (Days 1-2)
*   **Sub-modules & Checklists:**
    *   [x] Run Initial Database Migrations
    *   [x] Setup Currency CRUD
    *   [x] Setup Company CRUD
    *   [x] Setup Department CRUD
    *   [x] Setup Outlet CRUD
    *   [x] Update & Refine User Forms
    *   [x] Update & Refine Vendor Forms

---

### 🃏 Module: Multi-Level Approval Engine (Days 3-5)
*   **Sub-modules & Checklists:**
    *   [x] Create ApprovalWorkflow Model
    *   [x] Create ApprovalStep Model
    *   [x] Develop ApprovalService (submit, approve, reject logic)
    *   [x] Dynamic Multi-level routing based on department hierarchy
    *   [x] Specific User role-filtered strict approval assignment

---

### 🃏 Module: Requisition & Local Purchase Request (Days 6-8)
*   **Sub-modules & Checklists (Client Specs):**
    *   [x] 2.1 Store Requisition (SR) Implementation
    *   [x] 2.2 Purchase Requisition (PR) Implementation
    *   [x] 2.3 PR Approval (Multi-level workflow integration)
    *   [x] 2.4 SR Approval (Multi-level workflow integration)
    *   [x] 2.5 Edit / Cancel / Return PR / SR Functionalities

---

### 🃏 Module: Foundation QA & Client Review (Days 9-10)
*   **Sub-modules & Checklists:**
    *   [x] Present Requisition & Approval flow to client
    *   [x] Collect initial UI/flow feedback
    *   [x] Implement fixes based on feedback

---

### 🃏 Module: RFQ & Quotation System (Days 11-13)
*   **Sub-modules & Checklists (Client Specs):**
    *   [x] 2.6 Issue RFQ to Vendors (Generate from PR)
    *   [x] Auto-Generate PDF RFQ and Attach to Vendor Email
    *   [x] 2.7 Receive Vendor Quotations (System Data Entry)
    *   [x] 2.8 Comparison Statement (CS) Matrix Engine
    *   [x] CS Auto-Currency Conversion for L1 (Lowest Bidder) calculation
    *   [x] CS Approval Workflow Integration

---

### 🃏 Module: Purchase Order (PO) & PI Details (Days 14-16)
*   **Sub-modules & Checklists (Client Specs):**
    *   [x] 2.9 Create Purchase Order (PO) Engine
    *   [x] Split PO Capability (Awarding different items to different vendors)
    *   [x] 2.10 PO Approval (Multi-level workflow integration)
    *   [x] 2.11 Edit / Cancel PO Functionality
    *   [x] 2.12 Email PO Document to Supplier
    *   [x] 2.14 Foreign Purchase — PO (Multi-currency support)
    *   [x] 2.15 Proforma Invoice (PI) Tracking & Document Upload
    *   [x] 2.17 LC Amendment & Tracking Module

---

### 🃏 Module: Shipment, SIT & GRN (Days 17-19)
*   **Sub-modules & Checklists (Client Specs):**
    *   [x] 2.13 Foreign Purchase — PR (Import Configuration)
    *   [x] 2.18 Shipment Information (Track BL/AWB, Arrival, Departure, Port)
    *   [x] 2.19 Cost of Shipment (Stock-in-Transit / SIT Calculations before GRN)
    *   [x] 2.20 Store Receive Goods (GRN creation in local currency)
    *   [x] GRN Quality Control (QC) Checklist & Verification

---

### 🃏 Module: Landed Cost Allocation Engine (Days 20-22)
*   **Sub-modules & Checklists (Client Specs):**
    *   [x] 2.16 Track LC Related Expenses (CD, RD, SD, VAT, AIT, AT, LC Margin, Opening Charge, Doc Handling, Insurance, Transport, Freight, C&F)
    *   [x] 2.21 Cost Allocation Engine (Distribute overheads using Weighted Average)
    *   [x] 2.22 Unit Cost Configuration (Determine final True Cost)
    *   [x] Vendor Returns Workflow
    *   [x] Generate Debit Note for Rejected Goods

---

### 🃏 Module: Purchase Reporting & Client UAT (Days 23-25)
*   **Sub-modules & Checklists (Client Specs):**
    *   [x] 2.23 Supplier-wise Purchase Report
    *   [x] 2.24 Item-wise Purchase Report
    *   [x] 2.25 Total Purchase Value (Periodic Report)
    *   [x] 2.26 Purchase Value – Item wise (Periodic Report)
    *   [x] 2.27 Purchase Value vs Last Year Comparison
    *   [x] 2.28 PR Received List/Count
    *   [x] 2.29 PR Pending List/Count
    *   [x] 2.30 Items Pending in PR List/Count
    *   [x] 2.31 Items Purchased List/Count
    *   [x] 2.32 PO Issued List/Count
    *   [x] 2.33 CS / PR / SR / PO Print Formats validation
    *   [x] 2.35 LC Register Dashboard
    *   [x] Client Mock Purchase & Import Costing UAT

---

### 🃏 Module: Sales Polish, Quotations, Pricing & Fulfillment (Days 26-33)
*   **Sub-modules & Checklists:**
    *   [x] Sales Quotation CRUD Engine & Streamlined UI
    *   [x] Dynamic Quotation Templates (PDF streaming & downloads)
    *   [x] Customer Pricelists (Specialized pricing tiers & overrides)
    *   [x] One-click Quote to Sales Order Conversion
    *   [x] Coupon Code Validation System
    *   [x] Gift Card Generation & Redemption Logic
    *   [x] Customer Checkout Integration
    *   [x] Delivery Order (DO) Dispatch, Logistics & Tracking
    *   [x] Customer Payments & Statement Tracking
    *   [x] Customer Return Process (RMA) & QC Inspection
    *   [x] Credit Note Generation
    *   [x] Auto-adjustment of Outstanding Invoices against Credit Notes
    *   [x] Customer Segmentation (Wholesale, B2B VIP, Distributor, Retail)
    *   [x] Enterprise User Management (Staff vs B2B segregation & credit limits)
    *   [x] Comprehensive Bilingual User Manuals (English & Bengali)
    *   [x] Full Automated Test Coverage (22 Feature Tests passing)

---
---

### 🃏 Module: Inventory & Stock Controls (Days 34-40)
*   **Sub-modules & Checklists:**
    *   [x] Build FIFO (First In First Out) Stock Batch Depletion Engine (`FifoDepletionService.php`)
    *   [x] Build LIFO (Last In First Out) Option
    *   [x] Integrate FIFO/LIFO sequential batch consumption into Delivery Orders & Dispatches
    *   [x] Outlet-to-Outlet Stock Transfer System (Enterprise Master-Variant Matrix Grid)
    *   [x] Stock Transfer Approval & Logistics Workflow Integration (Draft ➔ Dispatch ➔ Receive ➔ Gate Pass PDF)
    *   [x] Minimum Stock Reorder Point Logic & Dynamic Low Stock Reporting
    *   [x] Dashboard Reorder Notifications / Alert Widget & Global Navbar Cart Integration
    *   [x] Physical Stock Correction/Adjustment Module (`StockAdjustment` with Reason Codes)
    *   [x] Month-End Inventory Frozen Snapshot System (`TakeInventorySnapshot` & `MonthEndSnapshotDataTable`)
    *   [x] FIFO Valuation Verification & Audit (`StockLedger` with 11,500+ records)
    *   [x] Comprehensive Bilingual User Manuals (English & Bengali)

---
---

## 📋 LIST: 🔄 NEXT / IN PROGRESS — Phase 5 (Core Financial Accounting)

---

### 🃏 Module: Chart of Accounts & Journals (Days 41-50)
*   **Sub-modules & Checklists:**
    *   [x] Develop Chart of Accounts (CoA) Nested Tree Structure
    *   [x] Fiscal Year Periods Configuration
    *   [x] Implement Laravel Observers for Auto-Journals
    *   [x] Purchase → Auto Journal Posting
    *   [x] Payment → Auto Journal Posting
    *   [x] Sales → Auto Journal Posting
    *   [x] Receipt → Auto Journal Posting
    *   [x] Bank Account Registry Management
    *   [x] Bank Statement Reconciliation Engine
    *   [x] Petty Cash Ledger Module
    *   [x] 2.34 Supplier Ledger (Accounts Payable)
    *   [x] Supplier Outstanding Acknowledgement Report
    *   [x] Fixed Assets Registry
    *   [x] Straight-line Depreciation Calculator
    *   [x] Monthly Depreciation Auto-Posting Scheduler
    *   [x] Generate Trial Balance
    *   [x] Generate General Ledger Report
    *   [x] Generate Profit & Loss (Income Statement)
    *   [x] Generate Balance Sheet
    *   [x] System Accounting Verification with mock data

---
---

## 📋 LIST: 🧊 BACKLOG — Phase 6 (Settings & System Testing)

---

### 🃏 Module: Settings & Testing (Days 51-54)
*   **Sub-modules & Checklists:**
    *   [ ] Integrate PayPal Express Checkout for Sales Orders
    *   [ ] Set up PayPal IPN Webhook listener
    *   [ ] Auto-reconcile invoices upon successful webhook trigger
    *   [ ] Implement "Clear Cache" Optimization UI
    *   [ ] Integrate `spatie/laravel-backup` for Admin DB & File Downloads
    *   [ ] Setup "Recycle Bin" (View, Restore, Force Delete for Soft Deletes)
    *   [ ] Verify Accounting Ledger Integrity under load
    *   [ ] Verify Inventory FIFO Valuation Integrity
    *   [ ] Test Approval Workflow Race Conditions / Locks
    *   [ ] General System Stress Testing

---
---

## 📋 LIST: 🧊 BACKLOG — Phase 7 (Data Migration & Go-Live)

---

### 🃏 Module: Migration & Go-Live (Days 55-60)
*   **Sub-modules & Checklists:**
    *   [ ] Write custom scripts to migrate legacy Customers
    *   [ ] Migrate legacy Vendors
    *   [ ] Migrate legacy Products & Variants
    *   [ ] Migrate historic Purchases & Orders
    *   [ ] Setup Opening Accounting Balances in CoA
    *   [ ] Draft Guide: Warehouse Staff (GRN & QC)
    *   [ ] Draft Guide: Accounts Staff (Bank Recon & Journals)
    *   [ ] Draft Guide: Purchase Team (LCs & Landed Costs)
    *   [ ] Final Client UAT (User Acceptance Testing) Walkthrough
    *   [ ] Move Database and Codebase to Production Server
    *   [ ] Go-Live System Performance Monitoring
    *   [ ] Phase 7 Sign-off & Handover
