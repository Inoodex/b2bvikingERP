# 🏢 B2B Viking ERP — Phase 3 NEW Enterprise Master Plan
**Sales Management, Dynamic Pricing, Commercial Distribution, Fulfillment & Accounts Credit Engine**
*Target Parity: SAP S/4HANA SD | Oracle Fusion SCM | Odoo 17 Enterprise Sales*
*Document Version: 2.0 (Enterprise Upgraded — Tier-1 Parity)*

---

## 📌 Executive Architecture & Strategy

Phase 3 implements a **Tier-1 Enterprise Sales & Commercial Distribution Engine** seamlessly integrated into our existing Laravel 11 codebase, polymorphic `ApprovalService` engine, Yajra DataTables, DomPDF generators, and Multi-Currency exchange mechanics.

**🔴 কেন এই আপডেটেড প্ল্যান তৈরি হলো:**
আগের Phase3_Plan.md তে ৮টি Step ছিল যেটা Mid-Enterprise Level ছিল। True Tier-1 Enterprise (SAP/Odoo/Oracle সমতুল্য) বানাতে **৭টি গুরুত্বপূর্ণ GAP** চিহ্নিত হয়েছে এবং এই নতুন প্ল্যানে সেগুলো সম্পূর্ণ সংযোজিত হয়েছে।

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                           PHASE 3 ENTERPRISE SALES LIFECYCLE (TIER-1 UPGRADED)                          │
│                                                                                                        │
│  1. Tax Config ➔ 2. Doc Sequence ➔ 3. Pricelist ➔ 4. Quotation (+ Auto Reminder)                      │
│        │                                                                                               │
│  5. Coupon/Gift ➔ 6. Sales Order (+ Credit Limit + Approval) ➔ 7. Sales Invoice (+ Tax Engine)        │
│        │                                                                                               │
│  8. Delivery Order (Partial/Back Order) ➔ 9. Customer Payment (Partial/Advance)                        │
│        │                                                                                               │
│  10. Sales Return (RMA) ➔ 11. Credit Note (3 Settlement Modes) ➔ 12. Reports & Dashboard              │
└──────────────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 📅 Timeline Estimate (সময়সীমা)

| হিসাব | সময় |
|--------|------|
| **মোট Step** | ১২টি |
| **AI + Developer একসাথে** | **৫-৬ কর্মদিবস** |
| **শুধু Developer (Manual)** | **১৩-১৫ কর্মদিবস** |

---

## 🛠️ Step-by-Step Implementation Breakdown (১২ Steps)

---

### 🔹 Step 3.1: Database Migrations & Eloquent Data Models
**সময়: ১-২ ঘণ্টা | অগ্রাধিকার: 🔴 জরুরি**

সকল ডাটাবেস টেবিল ও Eloquent মডেল তৈরি — মোট **২০+ টেবিল, ২০+ মডেল**

- **Database Tables:**
  - `tax_rules` (Configurable VAT/Tax Engine — Denmark 25% Moms, EU VAT, Zero-rated)
  - `document_sequences` (Admin-configurable SO/SQ/INV number format)
  - `customer_pricelists` & `pricelist_items` (B2B Wholesale, Retail, VIP Tier Pricing)
  - `coupons` & `gift_cards` (Promotional discount codes & gift card balances)
  - `sales_quotations` & `sales_quotation_items` (Quotations with currency & validity)
  - `sales_orders` & `sales_order_items` (Orders linked to Approval Workflow & Credit Limit)
  - `sales_invoices` & `sales_invoice_items` (🔴 আগে মিসিং ছিল — এখন সংযোজিত)
  - `delivery_orders` & `delivery_order_items` (🔴 নতুন — Partial Delivery & Back Order)
  - `customer_payments` (🔴 নতুন — Partial & Advance Payment Collection)
  - `sales_returns` & `credit_notes` (Customer RMA & Accounts Credit Notes)
- **Customer Table Update:**
  - `customers` টেবিলে `credit_limit` কলাম যোগ (🔴 নতুন — Credit Limit Validation)

**📄 Separate Implementation Plan:** `phase3_new_step1_implementation_plan.md`

---

### 🔹 Step 3.2: Tax Configuration & Document Sequence Engine
**সময়: ১-২ ঘণ্টা | অগ্রাধিকার: 🔴 জরুরি | 🆕 Enterprise Addition**

- **Controller:** `TaxRuleController.php` & `DocumentSequenceController.php`
- **Features:**
  - Admin Panel থেকে Tax Rule CRUD (Name, Rate %, Type: inclusive/exclusive, Country/Region)
  - Pre-seeded: Denmark 25% Moms, EU Reverse Charge, Zero-Rated Export
  - Document Sequence CRUD: Prefix, Suffix, Padding, Reset Policy (yearly/monthly/never)
  - Auto-generate: `SQ-202608-0001`, `SO-202608-0001`, `INV-202608-0001`, `DO-202608-0001`

**📄 Separate Implementation Plan:** `phase3_new_step2_implementation_plan.md`

---

### 🔹 Step 3.3: Sales Quotation Engine & PDF Templates
**সময়: ২-৩ ঘণ্টা | অগ্রাধিকার: 🔴 জরুরি**

- **Controller:** `SalesQuotationController.php`
- **Features:**
  - Create / Edit / Clone Sales Quotations
  - Multi-Currency conversion (DKK base vs Customer Currency USD/EUR)
  - Tax Rule auto-apply (from `tax_rules` engine)
  - Document Sequence auto-numbering (from `document_sequences`)
  - One-Click Convert to Sales Order (SO)
  - Professional PDF export (DomPDF)

**📄 Separate Implementation Plan:** `phase3_new_step3_implementation_plan.md`

---

### 🔹 Step 3.4: Customer Pricelist & Dynamic Pricing Tiers
**সময়: ১-২ ঘণ্টা | অগ্রাধিকার: 🟡 গুরুত্বপূর্ণ**

- **Controller:** `CustomerPricelistController.php`
- **Features:**
  - Tier-based pricing rules (Retail, Wholesale, B2B VIP, Distributor)
  - Min Quantity Volume Discounts (e.g. Buy 100+ pcs ➔ 15% discount)
  - Auto-fetch customer-specific price during Quotation & SO creation
  - Pricelist validity date range support

**📄 Separate Implementation Plan:** `phase3_new_step4_implementation_plan.md`

---

### 🔹 Step 3.5: Promotional Coupons & Gift Cards
**সময়: ১-২ ঘণ্টা | অগ্রাধিকার: 🟡 গুরুত্বপূর্ণ**

- **Controllers:** `CouponController.php` & `GiftCardController.php`
- **Features:**
  - Percentage & Fixed Amount Coupons (`SAVE10`, `VIP2026`)
  - Gift Card issuance, active balance tracking & checkout redemption
  - Real-time AJAX validation at checkout/order entry
  - Usage limit & expiry enforcement

**📄 Separate Implementation Plan:** `phase3_new_step5_implementation_plan.md`

---

### 🔹 Step 3.6: Sales Order (SO) + Approval Workflow + Credit Limit
**সময়: ২-৩ ঘণ্টা | অগ্রাধিকার: 🔴 জরুরি**

- **Controller:** `SalesOrderController.php`
- **Features:**
  - Integration with `ApprovalService.php` (submitForApproval, canUserApproveCurrentStep, approveStep)
  - Gated Status Progression: `Draft` ➔ `Pending Approval` ➔ `Approved` ➔ `Invoiced` ➔ `Fulfilled`
  - Visual Stepper & Status Banner (`⏳ Waiting for Sales Order Approval: Step X (Role)`)
  - Concurrency Lock (`lockForUpdate()`) to prevent duplicate order generation
  - 🔴 **Customer Credit Limit Check:** SO তৈরির সময় Customer-এর বকেয়া + নতুন SO amount মিলে `credit_limit` ক্রস করলে Warning/Block
  - Tax Rule auto-apply & Document Sequence auto-numbering

**📄 Separate Implementation Plan:** `phase3_new_step6_implementation_plan.md`

---

### 🔹 Step 3.7: Sales Invoice + Tax Calculation Engine
**সময়: ২-৩ ঘণ্টা | অগ্রাধিকার: 🔴 জরুরি | 🔴 আগে মিসিং ছিল**

- **Controller:** `SalesInvoiceController.php`
- **Features:**
  - Auto-generate Sales Invoice from Approved Sales Order
  - Manual Invoice creation (without SO link)
  - Tax auto-calculation from `tax_rules` engine (VAT inclusive/exclusive)
  - Incoterms tracking (FOB, CIF, EXW, DDP, CFR)
  - Multi-currency display (Customer Currency + DKK Base)
  - Invoice PDF export with tax breakdown
  - Payment status tracking (Unpaid → Partial → Paid)

**📄 Separate Implementation Plan:** `phase3_new_step7_implementation_plan.md`

---

### 🔹 Step 3.8: Delivery Order + Partial Delivery + Back Order
**সময়: ২-৩ ঘণ্টা | অগ্রাধিকার: 🔴 জরুরি | 🆕 Enterprise Addition**

- **Controller:** `DeliveryOrderController.php`
- **Features:**
  - Auto-generate Delivery Order from Approved Sales Order
  - **Partial Delivery:** ১০০ পিস অর্ডারে ৫০ পিস আগে পাঠানো, বাকি ৫০ পরে
  - **Back Order:** স্টকে যতটুকু আছে ততটুকু ডেলিভার, বাকিটুকু অটো Back Order তৈরি
  - Packing Slip / Delivery Note PDF
  - Status Tracking: `Pending` ➔ `Packed` ➔ `Shipped` ➔ `Delivered`
  - SO থেকে ডেলিভারি percentage ট্র্যাকিং (e.g., 50% delivered)

**📄 Separate Implementation Plan:** `phase3_new_step8_implementation_plan.md`

---

### 🔹 Step 3.9: Customer Payment Collection (Partial + Advance)
**সময়: ২-৩ ঘণ্টা | অগ্রাধিকার: 🔴 জরুরি | 🆕 Enterprise Addition**

- **Controller:** `CustomerPaymentController.php`
- **Features:**
  - Record Customer Payments against Sales Invoices
  - **Partial Payment:** Invoice-র একটা অংশ পেমেন্ট নেওয়া
  - **Advance Payment:** Invoice তৈরির আগেই Advance নেওয়া
  - Multi-mode: Cash, Bank Transfer, Cheque, PayPal
  - Auto-update Invoice payment_status (Unpaid → Partial → Paid)
  - Payment Receipt PDF generation
  - Payment Matching: একটি Payment দিয়ে একাধিক Invoice adjust

**📄 Separate Implementation Plan:** `phase3_new_step9_implementation_plan.md`

---

### 🔹 Step 3.10: Sales Return (RMA) & Credit Note
**সময়: ২-৩ ঘণ্টা | অগ্রাধিকার: 🔴 জরুরি**

- **Controllers:** `SalesReturnController.php` & `CreditNoteController.php`
- **Features:**
  - Process Customer Return (RMA) for damaged/returned goods
  - Auto-issue Accounts Credit Note (`CN-XXXX`)
  - **3 Settlement Modes:**
    - **Mode A:** Auto-deduct Credit Note amount from outstanding customer invoice
    - **Mode B (Product Replacement):** Issue replacement stock to customer
    - **Mode C (Direct Refund):** Issue Refund Voucher (`RCN-XXXX`)
  - Return stock back to inventory (optional based on condition)

**📄 Separate Implementation Plan:** `phase3_new_step10_implementation_plan.md`

---

### 🔹 Step 3.11: Quotation Expiry Auto-Reminder & Notifications
**সময়: ১ ঘণ্টা | অগ্রাধিকার: 🟡 গুরুত্বপূর্ণ | 🆕 Enterprise Addition**

- **Features:**
  - Laravel Scheduler: Quotation মেয়াদ শেষ হওয়ার ৩ দিন আগে Email Reminder
  - মেয়াদ শেষ হওয়ার ১ দিন আগে ২য় Reminder
  - মেয়াদ শেষ হলে Status auto-update to `Expired`
  - In-app notification to Salesperson
  - Customer Credit Limit Warning notification (SO তৈরির সময়)

**📄 Separate Implementation Plan:** `phase3_new_step11_implementation_plan.md`

---

### 🔹 Step 3.12: DataTables Reports & Dashboard Widgets
**সময়: ২-৩ ঘণ্টা | অগ্রাধিকার: 🟡 গুরুত্বপূর্ণ**

- **Reports:**
  - Salesperson Performance Report (Yajra Server-side DataTables)
  - Customer AP/AR Aging Receivables Report (0-30, 31-60, 61-90, 90+ days)
  - Product Sales Velocity & High-Margin Items Report
  - Delivery Fulfillment Rate Report
  - Customer Payment Collection Report
  - Tax Collection Summary Report
- **Dashboard Widgets:**
  - Today's Sales Overview
  - Pending Quotations Count
  - Overdue Invoices Alert
  - Credit Limit Exceeded Customers
- **Exports:** Real-time AJAX filtering with Excel, CSV, PDF, and Print support

**📄 Separate Implementation Plan:** `phase3_new_step12_implementation_plan.md`

---

## 📊 Enterprise Parity Matrix (Odoo / SAP vs B2B Viking ERP Phase 3 NEW)

| Feature | SAP S/4HANA SD | Odoo Enterprise | B2B Viking Phase 3 NEW |
| :--- | :---: | :---: | :---: |
| **Sales Quotation & PDF** | ✅ | ✅ | ✅ |
| **Quotation Expiry Auto-Reminder** | ✅ | ✅ | ✅ 🆕 |
| **Sales Order Approval Workflow** | ✅ | ✅ | ✅ (Polymorphic Approval Engine) |
| **Customer Credit Limit Check** | ✅ | ✅ | ✅ 🆕 |
| **Customer Pricelists & Tiers** | ✅ | ✅ | ✅ |
| **Coupons & Gift Cards** | ✅ | ✅ | ✅ |
| **Configurable Tax/VAT Engine** | ✅ | ✅ | ✅ 🆕 |
| **Sales Invoice (Separate Module)** | ✅ | ✅ | ✅ 🔴 Fixed |
| **Commercial Incoterms (FOB/CIF)** | ✅ | ✅ | ✅ |
| **Delivery Order & Partial Delivery** | ✅ | ✅ | ✅ 🆕 |
| **Back Order Management** | ✅ | ✅ | ✅ 🆕 |
| **Customer Payment (Partial/Advance)** | ✅ | ✅ | ✅ 🆕 |
| **RMA Returns & Credit Notes** | ✅ | ✅ | ✅ |
| **Multi-Currency Base Framing** | ✅ | ✅ | ✅ (DKK kr. Base Engine) |
| **Document Sequence Configuration** | ✅ | ✅ | ✅ 🆕 |
| **AR Aging & Sales Reports** | ✅ | ✅ | ✅ (Yajra DataTables + Exports) |

**Parity Score: ১৬/১৬ = ✅ 100% Enterprise Tier-1 Parity**

---

## 🧭 Execution Strategy (কাজের পদ্ধতি)

1. প্রতিটি Step-এর জন্য আলাদা `phase3_new_stepX_implementation_plan.md` ফাইল তৈরি হবে
2. প্রতিটি Step এর Implementation Plan approve হলে সেই Step-এর কোড লেখা হবে
3. Step শেষ হলে পরবর্তী Step-এর Plan তৈরি হবে
4. **ক্রম:** Step 1 → 2 → 3 → ... → 12 (সিকোয়েনশিয়াল)

---

## 🎯 Verification & Testing Strategy

1. **প্রতি Step শেষে:** `php -l` syntax check + `php artisan migrate` validation
2. **Approval Engine Integrity:** Non-approvers see `⏳ Waiting` banner, downstream locked
3. **Credit Limit Enforcement:** SO creation blocked/warned when limit exceeded
4. **Partial Delivery Math:** Total delivered qty never exceeds SO ordered qty
5. **Payment Reconciliation:** Invoice paid amount = sum of all linked payments
6. **Credit Note Accounting:** Customer credit notes deduct cleanly from unpaid invoices
