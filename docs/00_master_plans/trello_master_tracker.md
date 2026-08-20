# Trello Board: B2B Viking ERP — Master Development Tracker
**Timeline: 60 Working Days | 7 Phases | 2 Developers + AI**

---

## 📋 LIST: ✅ DONE (Phase 1 & Phase 2 - Step 1)

---

### 🃏 Module: Database & Master Data Setup (Days 1-2)
*   **Sub-modules & Checklists:**
    *   [x] Run 69 Initial Database Migrations
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
---

## 📋 LIST: 🔄 IN PROGRESS (Phase 2 - Step 2)

---

### 🃏 Module: Purchase Order (PO) & PI Details (Days 14-16)
*   **Sub-modules & Checklists (Client Specs):**
    *   [ ] 2.9 Create Purchase Order (PO) Engine
    *   [ ] Split PO Capability (Awarding different items to different vendors)
    *   [ ] 2.10 PO Approval (Multi-level workflow integration)
    *   [ ] 2.11 Edit / Cancel PO Functionality
    *   [ ] 2.12 Email PO Document to Supplier
    *   [ ] 2.14 Foreign Purchase — PO (Multi-currency support)
    *   [ ] 2.15 Proforma Invoice (PI) Tracking & Document Upload
    *   [ ] 2.17 LC Amendment & Tracking Module

---
---

## 📋 LIST: 📝 TO DO — Phase 2 (Import, Shipping & Costing)

---

### 🃏 Module: Shipment, SIT & GRN (Days 17-19)
*   **Sub-modules & Checklists (Client Specs):**
    *   [ ] 2.13 Foreign Purchase — PR (Import Configuration)
    *   [ ] 2.18 Shipment Information (Track BL/AWB, Arrival, Departure, Port)
    *   [ ] 2.19 Cost of Shipment (Stock-in-Transit / SIT Calculations before GRN)
    *   [ ] 2.20 Store Receive Goods (GRN creation in local currency)
    *   [ ] GRN Quality Control (QC) Checklist & Verification

---

### 🃏 Module: Landed Cost Allocation Engine (Days 20-22)
*   **Sub-modules & Checklists (Client Specs):**
    *   [ ] 2.16 Track LC Related Expenses (CD, RD, SD, VAT, AIT, AT, LC Margin, Opening Charge, Doc Handling, Insurance, Transport, Freight, C&F)
    *   [ ] 2.21 Cost Allocation Engine (Distribute overheads using Weighted Average)
    *   [ ] 2.22 Unit Cost Configuration (Determine final True Cost)
    *   [ ] Vendor Returns Workflow
    *   [ ] Generate Debit Note for Rejected Goods

---

### 🃏 Module: Purchase Reporting & Client UAT (Days 23-25)
*   **Sub-modules & Checklists (Client Specs):**
    *   [ ] 2.23 Supplier-wise Purchase Report
    *   [ ] 2.24 Item-wise Purchase Report
    *   [ ] 2.25 Total Purchase Value (Periodic Report)
    *   [ ] 2.26 Purchase Value – Item wise (Periodic Report)
    *   [ ] 2.27 Purchase Value vs Last Year Comparison
    *   [ ] 2.28 PR Received List/Count
    *   [ ] 2.29 PR Pending List/Count
    *   [ ] 2.30 Items Pending in PR List/Count
    *   [ ] 2.31 Items Purchased List/Count
    *   [ ] 2.32 PO Issued List/Count
    *   [ ] 2.33 CS / PR / SR / PO Print Formats validation
    *   [ ] 2.35 LC Register Dashboard
    *   [ ] Client Mock Purchase & Import Costing UAT

---
---

## 📋 LIST: 🧊 BACKLOG — Phase 3 (Sales Module Polish)

---

### 🃏 Module: Quotations & Customer Pricing (Days 26-33)
*   **Sub-modules & Checklists:**
    *   [ ] Sales Quotation CRUD Engine
    *   [ ] Dynamic Quotation Templates (PDF output)
    *   [ ] Customer Pricelists (Specialized pricing tiers)
    *   [ ] One-click Quote to Sales Order Conversion
    *   [ ] Coupon Code Validation System
    *   [ ] Gift Card Generation & Redemption Logic
    *   [ ] Final Checkout Integration
    *   [ ] Customer Return Process (RMA)
    *   [ ] Credit Note Generation
    *   [ ] Auto-adjustment of Outstanding Invoices against Credit Notes
    *   [ ] Salesperson Performance Tracking Report
    *   [ ] Aging Receivables Report
    *   [ ] Polish Checkout UI based on initial client feedback

---
---

## 📋 LIST: 🧊 BACKLOG — Phase 4 (Inventory & Stock Controls)

---

### 🃏 Module: Stock Batches & Reordering (Days 34-40)
*   **Sub-modules & Checklists:**
    *   [ ] Create `stock_batches` Model & Migration
    *   [ ] Build FIFO (First In First Out) Depletion Engine
    *   [ ] Build LIFO (Last In First Out) Option
    *   [ ] Refactor `IssueController` to automatically decrement stock sequentially
    *   [ ] Outlet-to-Outlet Stock Transfer System
    *   [ ] Stock Transfer Approval Workflow Integration
    *   [ ] Minimum Stock Reorder Point Logic
    *   [ ] Dashboard Reorder Notifications / Alert Widget
    *   [ ] Physical Stock Correction/Adjustment Module
    *   [ ] Month-End Inventory Frozen Snapshot System
    *   [ ] FIFO Valuation Verification & Audit

---
---

## 📋 LIST: 🧊 BACKLOG — Phase 5 (Core Financial Accounting)

---

### 🃏 Module: Chart of Accounts & Journals (Days 41-50)
*   **Sub-modules & Checklists:**
    *   [ ] Develop Chart of Accounts (CoA) Nested Tree Structure
    *   [ ] Fiscal Year Periods Configuration
    *   [ ] Implement Laravel Observers for Auto-Journals
    *   [ ] Purchase → Auto Journal Posting
    *   [ ] Payment → Auto Journal Posting
    *   [ ] Sales → Auto Journal Posting
    *   [ ] Receipt → Auto Journal Posting
    *   [ ] Bank Account Registry Management
    *   [ ] Bank Statement Reconciliation Engine
    *   [ ] Petty Cash Ledger Module
    *   [ ] 2.34 Supplier Ledger (Accounts Payable)
    *   [ ] Supplier Outstanding Acknowledgement Report
    *   [ ] Fixed Assets Registry
    *   [ ] Straight-line Depreciation Calculator
    *   [ ] Monthly Depreciation Auto-Posting Scheduler
    *   [ ] Generate Trial Balance
    *   [ ] Generate General Ledger Report
    *   [ ] Generate Profit & Loss (Income Statement)
    *   [ ] Generate Balance Sheet
    *   [ ] System Accounting Verification with mock data

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
