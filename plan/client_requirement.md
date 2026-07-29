# Copenhagen Tourist Point (b2bviking.com) — FINAL Laravel Development Plan

---

## 0. Foundation / Core System Module

0.1 User & Role / Permission Management (RBAC) 0.2 Approval Hierarchy Configuration (dynamic multi-level, per company) 0.3 Master Data Management

- Customer Master
- Vendor Master
- Product / Item Master
- Warehouse / Location Master
- UOM (Unit of Measure) Master
- Department Master
- Currency Master
- Company Master 0.4 Notification System (Email/In-app alerts for approvals, stock, etc.) 
- 0.5 Audit Log / Activity Trail 
- 0.6 Dashboard & Analytics Overview

---

## 1. Purchase Module

### Local Purchase

2.1 Store Requisition (SR) 
2.2 Purchase Requisition (PR) 
2.3 PR Approval (Multi-level workflow) 
2.4 SR Approval (Multi-level workflow)
2.5 Edit / Cancel / Return PR / SR 
2.6 Issue RFQ to Vendors 
2.7 Receive Vendor Quotations 
2.8 Comparison Statement (CS) + CS Approval 
2.9 Create Purchase Order (PO) 
2.10 PO Approval (Multi-level workflow) 
2.11 Edit / Cancel PO 
2.12 Email PO to Supplier

### Foreign Purchase / Import

2.13 Foreign Purchase — PR (Import) 
2.14 Foreign Purchase — PO (Multi-currency) 
2.15 Proforma Invoice (PI) 
2.16 LC Related Expenses (CD, RD, SD, VAT, AIT, AT, LC Margin, LC Opening Charge, LC Doc Handling, Insurance, Transport, Freight, C&F) 
2.17 LC Amendment 
2.18 Shipment Information (Ship Info, Arrival, Departure, Port) 
2.19 Cost of Shipment (SIT, before GRN) 
2.20 Store Receive Goods (GRN – local currency, landed cost) 
2.21 Cost Allocation (Weighted Avg) 
2.22 Unit Cost Configuration

### Purchase Reporting

2.23 Supplier-wise Purchase Report 
2.24 Item-wise Purchase Report 
2.25 Total Purchase Value (Periodic) 
2.26 Purchase Value – Item wise (Periodic) 
2.27 Purchase Value vs Last Year 
2.28 PR Received List/Count 
2.29 PR Pending List/Count 
2.30 Items Pending in PR List/Count 
2.31 Items Purchased List/Count 
2.32 PO Issued List/Count 
2.33 CS / PR / SR / PO Print Format

### Additional

2.34 Supplier Ledger + Outstanding Acknowledgement 
2.35 LC Register

---

## 2. Sales Module

3.1 Create Sales Quotation 
3.2 Quotation Template 
3.3 Sales Orders (Quote → Order) 
3.4 Sales Order Approval (Multi-level) 
3.5 Invoicing from Sales Orders 
3.6 Sales Terms / Incoterms 
3.7 Product Variants 
3.8 Discounts 
3.9 Coupons & Gift Card 
3.10 Pricelists (Dynamic Pricing) 
3.11 Sales Return / Credit Note **[Added — client confirmation দরকার]** 
3.12 Sales Reports (Salesperson/Product/Customer-wise)

---

## 3. Inventory Module

4.1 Goods Receipt (Quality Check) 
4.2 Goods Receipt Entry 
4.3 GRN Creation 
4.4 GRN Printout 
4.5 Goods Issue (against SR) 
4.6 Internal Transfer + Approval 
4.7 Minimum Stock Level Setup 
4.8 Reorder Point Setup 
4.9 Stock Adjustment Entry + Approval
4.10 Stock Ledger (Record) 
4.11 Stock Ledger Report 
4.12 Stock Valuation (Actual Price) 
4.13 Month-End Inventory Snapshot 
4.14 Goods Return to Vendor 
4.15 Goods Return to Store 
4.16 Goods Issue by FIFO 
4.17 Goods Issue by LIFO 
4.18 Goods Issue by Batch 
4.19 Consumption Report (Item/Dept/Total) 
4.20 Goods Receiving Report (List/Summary) 
4.21 Goods Issue Report (List/Summary) 
4.22 Goods Transfer Report (List/Summary)

---

## 5. Accounting Module

### Core Setup

5.1 Chart of Accounts 
5.2 Account Types 
5.3 Fiscal Year Configuration 
5.4 Multi-Company Support

### Invoicing & Billing

5.5 Customer Invoicing 
5.6 Multi-Mode Payment 
5.7 Payment Tracking 
5.8 Advance / Down Payments

### Payments & Collections

5.9 Partial Payments 
5.10 Customer Statements 
5.11 Payment Matching 
5.12 Supplier Bills 
5.13 Advance Payments (Supplier) 
5.14 Vendor Statements

### Project & Cost Accounting

5.15 Analytical Accounting / Cost Centres 
5.16 Analytic Tags

### Banking

5.17 Bank Reconciliation 
5.18 Multi-Bank Accounts 
5.19 PayPal Auto-Reconcile 
5.20 Petty Cash Management 
5.21 Fund Transfers 
5.22 Bank Feeds

### Assets

5.23 Asset Register 
5.24 Depreciation Management 
5.25 Asset Tracking 
5.26 Disposal of Assets

### Tax & Currency

5.27 VAT & Tax Handling 
5.28 Multi-Currency Handling
5.29 Exchange Rates 
5.30 Consolidation

### Accounting Reporting

5.31 Financial Statements (P&L, Balance Sheet, Cash Flow) 
5.32 General Ledger
5.33 Unit-wise Sales Reports 
5.34 Cost Center Reports 
5.35 Aged Receivables/Payables 
5.36 Budget Variance 
5.37 Analytic Reports 
5.38 Consolidated Reports 
5.39 Bank Payment Integration

### .6 Reports & Analytics

- Headcount & Turnover Reports
- Payroll Cost Analysis & Forecasting
- Absence & Sickness Rate Analytics
- Gender Pay Gap Reporting (Ligelønsrapport)
- Demographic & Diversity Reports
- Export to Excel/PDF/CSV
- Executive HR Dashboard
- Scheduled Report Delivery


---

## 6. API / Integration Module

6.1 Payment API Integration 
6.2 Denmark Government Site API 
6.3 Courier API Integration 
6.4 E-Commerce Integration — Configuration 
6.5 Product Sync 
6.6 Account Sync 
6.7 Order Sync 
6.8 Invoice Sync 
6.9 Category Sync 
6.10 Stock Sync 
6.11 Price Sync


## 7. B2C E-Commerce / Custom Apparel Module

1.1 Product Catalog **[Added]** 
1.2 Custom Apparel Product Design (Live Preview) 
1.3 Product Variants (Size, Color) **[Added]** 
1.4 Cart & Checkout **[Added]** 
1.5 Order Management **[Added]** 
1.6 Payment Gateway Integration (1x) 
1.7 Shipping Provider Integration (1x) 
1.8 Return / Refund / Cancellation


## 8. HR & Payroll Management Module

### 8.1 Employee Management

- CPR Number Storage (Encrypted)
- Contract Management (Fixed, Flex, Project)
- Department & Org Chart
- Salary History & Grades
- Job Titles & Classifications
- Employment Type
- Work Permit & Residence Tracking
- Audit Trail & Change Log
- Emergency Contacts
- Document Storage (Contracts, Diplomas)

### 8.2 Recruitment & Onboarding

- Job Posting
- Application Form Builder
- Applicant Tracking & Pipeline
- Interview Scheduling
- Email Templates & Auto-responses
- Onboarding Task Checklists
- IT Provisioning Workflow
- Buddy/Mentor Assignment

### 8.3 Absence & Leave

- Leave Request & Approval Workflow
- Absence Calendar & Team View
- Absence Pattern Alerts

### 8.4 Time & Attendance

- Clock-in/out (Web, Mobile, NFC)
- Shift Scheduling & Rota
- Overtime Tracking
- Flex Time Accumulation
- Mobile App Check-in
- GPS/Location-based Attendance
- Integration with Access Control
- Timesheet Approval Workflow

### 8.5 Payroll Processing

- Monthly & Bi-weekly Payroll Runs
- Payslip Generation (PDF/Portal)
- Gross-to-Net Calculation Engine
- AM-bidrag (8%) Auto-deduction
- Bank Transfer via Nets/Betalingsservice
- 13th Month & Bonus Processing
- Retroactive Pay Adjustments
- Multi-Currency Support (DKK primary)
- Payroll Lock & Approval Flow
- Historical Payroll Archive