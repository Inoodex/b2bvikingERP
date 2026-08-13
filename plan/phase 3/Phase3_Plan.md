# 🏢 B2B Viking ERP — Phase 3 Master Implementation Plan
**Sales Management, Dynamic Pricing, Commercial Distribution & Accounts Credit Engine**
*Target Parity: SAP S/4HANA SD (Sales & Distribution) | Oracle Fusion SCM | Odoo 17 Enterprise Sales*

---

## 📌 Executive Architecture & Strategy

Phase 3 implements a **Tier-1 Enterprise Sales & Commercial Distribution Engine** seamlessly integrated into our existing Laravel 11 codebase, polymorphic `ApprovalService` engine, Yajra DataTables, DomPDF generators, and Multi-Currency exchange mechanics.

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                   PHASE 3 ENTERPRISE SALES LIFECYCLE                             │
│                                                                                                  │
│ 1. Pricelist/Coupon ➔ 2. Sales Quotation ➔ 3. Sales Order (SO) ➔ 4. SO Approval Workflow         │
│          │                                                                      │                │
│  8. Credit Note  7. Sales Return (RMA)  6. Sales Invoice  5. Incoterms & Fulfillment           │
└──────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🛠️ Step-by-Step Implementation Breakdown

### 🔹 Step 3.1: Database Migrations & Eloquent Data Models
- **Database Tables:**
  - `customer_pricelists` & `pricelist_items` (B2B Wholesale, Retail, VIP Tier Pricing)
  - `coupons` & `gift_cards` (Promotional discount codes & gift card balances)
  - `sales_quotations` & `sales_quotation_items` (Quotations with currency & validity)
  - `sales_orders` & `sales_order_items` (Orders linked to SO approval workflows & fulfillment)
  - `sales_invoices` & `sales_invoice_items` (3-Way Matching Sales Invoices)
  - `sales_returns` & `credit_notes` (Customer RMA & Accounts Credit Notes)

---

### 🔹 Step 3.2: Sales Quotation Engine & PDF Templates
- **Controller:** `app/Http/Controllers/Backend/SalesQuotationController.php`
- **Features:**
  - Create / Edit / Clone Sales Quotations.
  - Multi-Currency conversion (DKK base vs Customer Currency USD/EUR).
  - One-Click Convert to Sales Order (SO).
  - Professional PDF export (`resources/views/backend/sales_quotation/pdf.blade.php`).

---

### 🔹 Step 3.3: Sales Order (SO) & Polymorphic Approval Workflow
- **Model Target:** `App\Models\SalesOrder`
- **Controller:** `app/Http/Controllers/Backend/SalesOrderController.php`
- **Features:**
  - Integration with `ApprovalService.php` (`submitForApproval`, `canUserApproveCurrentStep`, `approveStep`).
  - Gated Status Progression: `Draft` ➔ `Pending Approval` ➔ `Approved` ➔ `Fulfilled`.
  - Visual Stepper & Status Banner (`⏳ Waiting for Sales Order Approval: Step X (Role)`).
  - Concurrency Lock (`lockForUpdate()`) to prevent duplicate order generation.

---

### 🔹 Step 3.4: Dynamic Pricing Tiers & Pricelists
- **Controller:** `app/Http/Controllers/Backend/CustomerPricelistController.php`
- **Features:**
  - Tier-based pricing rules (Retail, Wholesale, B2B VIP, Distributor).
  - Min Quantity Volume Discounts (e.g. Buy 100+ pcs ➔ 15% discount).
  - Auto-fetch customer-specific price during Quotation & SO creation.

---

### 🔹 Step 3.5: Promotional Coupons, Gift Cards & Discounts
- **Controllers:** `CouponController.php` & `GiftCardController.php`
- **Features:**
  - Percentage & Fixed Amount Coupons (`SAVE10`, `VIP2026`).
  - Gift Card issuance, active balance tracking & checkout redemption.
  - Real-time AJAX validation at checkout/order entry.

---

### 🔹 Step 3.6: Sales Invoicing & Commercial Incoterms
- **Controller:** `app/Http/Controllers/Backend/SalesInvoiceController.php`
- **Features:**
  - Auto-generate Sales Invoices from Approved Sales Orders.
  - Incoterms tracking (FOB, CIF, EXW, DDP, CFR).
  - Tax calculation (VAT, Tax Duty) & multi-currency display (Customer Currency + DKK Base).

---

### 🔹 Step 3.7: Customer Sales Return (RMA) & Credit Notes
- **Controllers:** `SalesReturnController.php` & `CreditNoteController.php`
- **Features:**
  - Process Customer Return (RMA) for damaged/returned goods.
  - Auto-issue Accounts Credit Note (`CN-XXXX`).
  - **3 Settlement Modes:**
    - **Mode A:** Auto-deduct Credit Note amount from outstanding customer invoice.
    - **Mode B (Product Replacement):** Issue replacement stock to customer.
    - **Mode C (Direct Refund):** Issue Refund Voucher (`RCN-XXXX`).

---

### 🔹 Step 3.8: DataTables Reports & Dashboard Widgets
- **Reports:**
  - Salesperson Performance Report (Yajra Server-side DataTables).
  - Customer AP/AR Aging Receivables Report (0-30, 31-60, 61-90, 90+ days).
  - Product Sales Velocity & High-Margin Items Report.
- **Exports:** Real-time AJAX filtering with Excel, CSV, PDF, and Print support.

---

## 📊 Enterprise Parity Matrix (Odoo / SAP vs B2B Viking ERP)

| Feature | SAP S/4HANA SD | Odoo Enterprise | B2B Viking ERP Phase 3 |
| :--- | :---: | :---: | :---: |
| **Sales Quotation & PDF** | ✅ | ✅ | ✅ |
| **Sales Order Approval Workflow** | ✅ | ✅ | ✅ (Polymorphic Approval Engine) |
| **Customer Pricelists & Tiers** | ✅ | ✅ | ✅ |
| **Coupons & Gift Cards** | ✅ | ✅ | ✅ |
| **Commercial Incoterms (FOB/CIF)** | ✅ | ✅ | ✅ |
| **RMA Returns & Credit Notes** | ✅ | ✅ | ✅ |
| **Multi-Currency Base Framing** | ✅ | ✅ | ✅ (DKK kr. Base Engine) |
| **AR Aging & Sales Reports** | ✅ | ✅ | ✅ (Yajra DataTables + Exports) |

---

## 🎯 Verification & Testing Strategy

1. **Automated PHP Syntax Checks:** Run `php -l` across all new Models, Controllers, Requests, and Views.
2. **Approval Engine Integrity:** Verify non-approvers see `⏳ Waiting for Sales Order Approval` banner, and downstream fulfillment remains locked until SO is approved.
3. **Credit Note Accounting Adjustment:** Verify customer credit notes deduct cleanly from total unpaid invoices.
