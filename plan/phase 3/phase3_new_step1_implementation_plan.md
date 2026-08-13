# 🏢 Phase 3 — Step 1 NEW Implementation Plan: Database Migrations & Eloquent Data Models
# (Enterprise Tier-1 Upgraded)

> **Phase:** 3 (Sales Management, Dynamic Pricing, Fulfillment & Credit Engine)
> **Step:** 1 (Database Schema, Polymorphic Models & Enterprise Engine Alignment)
> **Target Parity:** SAP S/4HANA SD Data Schema | Oracle Fusion Sales Data Architecture | Odoo 17 Sales Models
> **Version:** 2.0 (Enterprise Upgraded)

---

## 📌 1. Scope & System Architecture

Step 1 creates **ALL** foundational database tables, relationships, indexes, foreign keys, and Eloquent models for the entire Sales & Distribution module — including the 7 enterprise upgrades that were missing in the previous plan.

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                           PHASE 3 STEP 1 DATA MODEL ARCHITECTURE (ENTERPRISE TIER-1)                    │
│                                                                                                        │
│  🆕 tax_rules                    (Configurable VAT/Tax Engine)                                          │
│  🆕 document_sequences           (Admin-configurable Number Formats)                                    │
│  customer_pricelists ──< pricelist_items                                                                │
│  coupons / gift_cards                                                                                   │
│  sales_quotations    ──< sales_quotation_items                                                          │
│  sales_orders        ──< sales_order_items      ──< (Linked to ApprovalWorkflow model_type)             │
│  🔴 sales_invoices   ──< sales_invoice_items    (আগে মিসিং ছিল — এখন সংযোজিত)                          │
│  🆕 delivery_orders  ──< delivery_order_items   (Partial Delivery & Back Order)                          │
│  🆕 customer_payments                           (Partial & Advance Payment Collection)                   │
│  sales_returns       ──< credit_notes                                                                   │
│  🆕 customers table  ──  ADD credit_limit column                                                        │
└──────────────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🛠️ 2. Database Migrations Breakdown (Total: 10 Migrations)

---

### 2.1 `tax_rules` 🆕 Enterprise Addition
**ডাইনামিক ভ্যাট/ট্যাক্স ইঞ্জিন — হার্ডকোড ভ্যাট নয়**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `name` | string | e.g. "Denmark Moms 25%", "EU Reverse Charge", "Zero-Rated Export" |
| `code` | string, unique | e.g. "DK_MOMS_25", "EU_RC", "ZERO_EXPORT" |
| `rate` | decimal(5,2) | e.g. 25.00, 0.00 |
| `type` | enum | `inclusive`, `exclusive` |
| `country` | string, nullable | e.g. "DK", "EU", null (for global) |
| `is_default` | boolean | default false |
| `status` | enum | `active`, `inactive` |
| `timestamps` | | |

**Seeder Data:**
- Denmark Moms 25% (DK_MOMS_25, rate: 25.00, exclusive, country: DK, is_default: true)
- EU Reverse Charge (EU_RC, rate: 0.00, exclusive, country: EU)
- Zero-Rated Export (ZERO_EXPORT, rate: 0.00, exclusive, country: null)
- Reduced VAT (DK_REDUCED, rate: 0.00, exclusive, country: DK)

---

### 2.2 `document_sequences` 🆕 Enterprise Addition
**Admin Panel থেকে কনফিগারেবল ডকুমেন্ট নাম্বার ফরম্যাট**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `model_type` | string, unique | e.g. "SalesQuotation", "SalesOrder", "SalesInvoice", "DeliveryOrder", "CreditNote" |
| `prefix` | string | e.g. "SQ-", "SO-", "INV-", "DO-", "CN-" |
| `suffix` | string, nullable | optional suffix |
| `padding` | integer | default 4 (e.g. 0001) |
| `next_number` | integer | default 1 |
| `reset_policy` | enum | `yearly`, `monthly`, `never` |
| `include_date` | boolean | default true (e.g. SQ-202608-0001) |
| `date_format` | string | default "Ym" (e.g. 202608) |
| `timestamps` | | |

**Seeder Data:**
- SalesQuotation: prefix "SQ-", padding 4, reset yearly
- SalesOrder: prefix "SO-", padding 4, reset yearly
- SalesInvoice: prefix "INV-", padding 4, reset yearly
- DeliveryOrder: prefix "DO-", padding 4, reset yearly
- CreditNote: prefix "CN-", padding 4, reset yearly
- SalesReturn: prefix "SRET-", padding 4, reset yearly
- CustomerPayment: prefix "CPAY-", padding 4, reset yearly

---

### 2.3 `customer_pricelists` & `pricelist_items`

**Fields for `customer_pricelists`:**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `name` | string | e.g. "B2B VIP Tier", "Wholesale Denmark" |
| `code` | string, unique | e.g. "B2B_VIP", "WHOLESALE_DK" |
| `customer_type` | enum | `retail`, `wholesale`, `b2b_vip`, `distributor` |
| `currency_id` | foreignId | FK → currencies |
| `valid_from` | date, nullable | Pricelist start date |
| `valid_until` | date, nullable | Pricelist end date |
| `status` | enum | `active`, `inactive` |
| `timestamps` | | |

**Fields for `pricelist_items`:**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `customer_pricelist_id` | foreignId | FK → customer_pricelists |
| `product_id` | foreignId | FK → products |
| `variant_id` | foreignId, nullable | FK → product_variants |
| `min_qty` | integer | default 1 |
| `fixed_price` | decimal(15,2) | Customer-specific price |
| `discount_percentage` | decimal(5,2) | default 0.00 |
| `timestamps` | | |

---

### 2.4 `coupons` & `gift_cards`

**Fields for `coupons`:**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `code` | string, unique | e.g. "SAVE10", "VIP2026" |
| `discount_type` | enum | `fixed`, `percentage` |
| `discount_value` | decimal(15,2) | Amount or percentage value |
| `min_order_amount` | decimal(15,2), nullable | Minimum order to apply |
| `max_discount_amount` | decimal(15,2), nullable | Cap for percentage discounts |
| `valid_from` | date | Start date |
| `valid_until` | date | End date |
| `usage_limit` | integer, nullable | Max total uses (null = unlimited) |
| `per_customer_limit` | integer | default 1 |
| `used_count` | integer | default 0 |
| `status` | enum | `active`, `inactive`, `expired` |
| `timestamps` | | |

**Fields for `gift_cards`:**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `card_number` | string, unique | Auto-generated unique code |
| `initial_value` | decimal(15,2) | Original amount |
| `current_balance` | decimal(15,2) | Remaining balance |
| `currency_id` | foreignId | FK → currencies |
| `expiry_date` | date | Card expiry |
| `customer_id` | foreignId, nullable | FK → customers (if assigned) |
| `status` | enum | `active`, `redeemed`, `expired` |
| `timestamps` | | |

---

### 2.5 `sales_quotations` & `sales_quotation_items`

**Fields for `sales_quotations`:**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `quotation_no` | string, unique | Auto from document_sequences (e.g. SQ-202608-0001) |
| `customer_id` | foreignId | FK → customers |
| `currency_id` | foreignId | FK → currencies |
| `exchange_rate` | decimal(12,6) | default 1.000000 |
| `tax_rule_id` | foreignId, nullable | FK → tax_rules 🆕 |
| `pricelist_id` | foreignId, nullable | FK → customer_pricelists |
| `quotation_date` | date | |
| `valid_until` | date | Quotation expiry date |
| `reminder_sent` | boolean | default false 🆕 (Auto-reminder tracking) |
| `subtotal` | decimal(15,2) | Before tax |
| `tax_amount` | decimal(15,2) | Calculated from tax_rule |
| `discount_amount` | decimal(15,2) | Total discount |
| `grand_total` | decimal(15,2) | Final total |
| `notes` | text, nullable | |
| `status` | enum | `draft`, `sent`, `accepted`, `declined`, `converted`, `expired` |
| `created_by` | foreignId | FK → users |
| `timestamps` | | |

**Fields for `sales_quotation_items`:**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `sales_quotation_id` | foreignId | FK → sales_quotations |
| `product_id` | foreignId | FK → products |
| `variant_id` | foreignId, nullable | FK → product_variants |
| `description` | string, nullable | Custom line description |
| `quantity` | decimal(10,2) | |
| `unit_price` | decimal(15,2) | |
| `discount` | decimal(15,2) | default 0.00 |
| `tax_amount` | decimal(15,2) | default 0.00 |
| `subtotal` | decimal(15,2) | (qty × unit_price) - discount + tax |
| `timestamps` | | |

---

### 2.6 `sales_orders` & `sales_order_items`

**Fields for `sales_orders`:**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `order_no` | string, unique | Auto from document_sequences (e.g. SO-202608-0001) |
| `sales_quotation_id` | foreignId, nullable | FK → sales_quotations (if converted) |
| `customer_id` | foreignId | FK → customers |
| `currency_id` | foreignId | FK → currencies |
| `tax_rule_id` | foreignId, nullable | FK → tax_rules 🆕 |
| `exchange_rate` | decimal(12,6) | default 1.000000 |
| `foreign_amount` | decimal(15,2) | Amount in customer currency |
| `base_amount` | decimal(15,2) | Amount in DKK base |
| `order_date` | date | |
| `expected_delivery_date` | date, nullable | |
| `incoterm` | enum, nullable | `FOB`, `CIF`, `EXW`, `DDP`, `CFR` |
| `subtotal` | decimal(15,2) | |
| `tax_amount` | decimal(15,2) | |
| `discount_amount` | decimal(15,2) | |
| `grand_total` | decimal(15,2) | |
| `coupon_id` | foreignId, nullable | FK → coupons |
| `gift_card_id` | foreignId, nullable | FK → gift_cards |
| `payment_status` | enum | `unpaid`, `partial`, `paid` |
| `approval_status` | enum | `draft`, `pending`, `approved`, `rejected` |
| `fulfillment_status` | enum | `unfulfilled`, `partial`, `fulfilled`, `cancelled` 🆕 |
| `notes` | text, nullable | |
| `created_by` | foreignId | FK → users |
| `timestamps` | | |

**Fields for `sales_order_items`:**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `sales_order_id` | foreignId | FK → sales_orders |
| `product_id` | foreignId | FK → products |
| `variant_id` | foreignId, nullable | FK → product_variants |
| `description` | string, nullable | |
| `quantity` | decimal(10,2) | Ordered qty |
| `delivered_qty` | decimal(10,2) | default 0.00 🆕 (Partial delivery tracking) |
| `unit_price` | decimal(15,2) | |
| `discount` | decimal(15,2) | default 0.00 |
| `tax_amount` | decimal(15,2) | default 0.00 |
| `subtotal` | decimal(15,2) | |
| `timestamps` | | |

---

### 2.7 `sales_invoices` & `sales_invoice_items` 🔴 আগে মিসিং ছিল

**Fields for `sales_invoices`:**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `invoice_no` | string, unique | Auto from document_sequences (e.g. INV-202608-0001) |
| `sales_order_id` | foreignId, nullable | FK → sales_orders |
| `customer_id` | foreignId | FK → customers |
| `currency_id` | foreignId | FK → currencies |
| `tax_rule_id` | foreignId, nullable | FK → tax_rules |
| `exchange_rate` | decimal(12,6) | default 1.000000 |
| `invoice_date` | date | |
| `due_date` | date | Payment due date |
| `incoterm` | enum, nullable | `FOB`, `CIF`, `EXW`, `DDP`, `CFR` |
| `subtotal` | decimal(15,2) | |
| `tax_amount` | decimal(15,2) | |
| `discount_amount` | decimal(15,2) | |
| `grand_total` | decimal(15,2) | |
| `paid_amount` | decimal(15,2) | default 0.00 (Updated by customer_payments) |
| `due_amount` | decimal(15,2) | grand_total - paid_amount |
| `payment_status` | enum | `unpaid`, `partial`, `paid`, `overdue` |
| `notes` | text, nullable | |
| `created_by` | foreignId | FK → users |
| `timestamps` | | |

**Fields for `sales_invoice_items`:**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `sales_invoice_id` | foreignId | FK → sales_invoices |
| `product_id` | foreignId | FK → products |
| `variant_id` | foreignId, nullable | FK → product_variants |
| `description` | string, nullable | |
| `quantity` | decimal(10,2) | |
| `unit_price` | decimal(15,2) | |
| `discount` | decimal(15,2) | default 0.00 |
| `tax_amount` | decimal(15,2) | default 0.00 |
| `subtotal` | decimal(15,2) | |
| `timestamps` | | |

---

### 2.8 `delivery_orders` & `delivery_order_items` 🆕 Enterprise Addition

**Fields for `delivery_orders`:**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `delivery_no` | string, unique | Auto from document_sequences (e.g. DO-202608-0001) |
| `sales_order_id` | foreignId | FK → sales_orders |
| `customer_id` | foreignId | FK → customers |
| `delivery_date` | date | Scheduled delivery date |
| `actual_delivery_date` | date, nullable | When actually delivered |
| `delivery_type` | enum | `full`, `partial`, `back_order` |
| `shipping_address` | text, nullable | |
| `tracking_number` | string, nullable | Carrier tracking |
| `carrier_name` | string, nullable | e.g. "PostNord", "DHL", "GLS" |
| `notes` | text, nullable | |
| `status` | enum | `draft`, `confirmed`, `packed`, `shipped`, `delivered`, `cancelled` |
| `created_by` | foreignId | FK → users |
| `timestamps` | | |

**Fields for `delivery_order_items`:**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `delivery_order_id` | foreignId | FK → delivery_orders |
| `sales_order_item_id` | foreignId | FK → sales_order_items |
| `product_id` | foreignId | FK → products |
| `variant_id` | foreignId, nullable | FK → product_variants |
| `ordered_qty` | decimal(10,2) | Original ordered quantity |
| `delivered_qty` | decimal(10,2) | Quantity in this delivery |
| `timestamps` | | |

---

### 2.9 `customer_payments` 🆕 Enterprise Addition

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `payment_no` | string, unique | Auto from document_sequences (e.g. CPAY-202608-0001) |
| `customer_id` | foreignId | FK → customers |
| `sales_invoice_id` | foreignId, nullable | FK → sales_invoices (null for advance) |
| `currency_id` | foreignId | FK → currencies |
| `exchange_rate` | decimal(12,6) | default 1.000000 |
| `amount` | decimal(15,2) | Payment amount |
| `base_amount` | decimal(15,2) | Amount in DKK base |
| `payment_date` | date | |
| `payment_method` | enum | `cash`, `bank_transfer`, `cheque`, `paypal`, `other` |
| `payment_type` | enum | `regular`, `advance`, `refund` |
| `reference` | string, nullable | Bank reference / cheque no |
| `notes` | text, nullable | |
| `status` | enum | `confirmed`, `cancelled` |
| `created_by` | foreignId | FK → users |
| `timestamps` | | |

---

### 2.10 `sales_returns` & `credit_notes`

**Fields for `sales_returns`:**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `return_no` | string, unique | Auto from document_sequences (e.g. SRET-202608-0001) |
| `sales_order_id` | foreignId | FK → sales_orders |
| `sales_invoice_id` | foreignId, nullable | FK → sales_invoices |
| `customer_id` | foreignId | FK → customers |
| `return_date` | date | |
| `reason` | text | Return reason |
| `total_refund_amount` | decimal(15,2) | |
| `return_to_stock` | boolean | default true (return items to inventory) |
| `status` | enum | `draft`, `approved`, `processed`, `cancelled` |
| `created_by` | foreignId | FK → users |
| `timestamps` | | |

**Fields for `sales_return_items`:** (🆕 আগে ছিল না — line item tracking দরকার)

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `sales_return_id` | foreignId | FK → sales_returns |
| `product_id` | foreignId | FK → products |
| `variant_id` | foreignId, nullable | FK → product_variants |
| `quantity` | decimal(10,2) | Returned quantity |
| `unit_price` | decimal(15,2) | Original sale price |
| `refund_amount` | decimal(15,2) | qty × unit_price |
| `condition` | enum | `good`, `damaged`, `defective` |
| `timestamps` | | |

**Fields for `credit_notes`:**

| Column | Type | Details |
|--------|------|---------|
| `id` | bigIncrements | PK |
| `credit_note_no` | string, unique | Auto from document_sequences (e.g. CN-202608-0001) |
| `sales_return_id` | foreignId, nullable | FK → sales_returns |
| `sales_invoice_id` | foreignId, nullable | FK → sales_invoices |
| `customer_id` | foreignId | FK → customers |
| `currency_id` | foreignId | FK → currencies |
| `amount` | decimal(15,2) | Total credit amount |
| `settled_amount` | decimal(15,2) | default 0.00 |
| `remaining_amount` | decimal(15,2) | amount - settled_amount |
| `settlement_mode` | enum | `invoice_deduct`, `product_replace`, `direct_refund` |
| `settlement_status` | enum | `unsettled`, `partial`, `settled` |
| `notes` | text, nullable | |
| `created_by` | foreignId | FK → users |
| `timestamps` | | |

---

### 2.11 `customers` Table — ADD `credit_limit` Column 🆕

| Action | Column | Type | Details |
|--------|--------|------|---------|
| ADD | `credit_limit` | decimal(15,2), nullable | default null (null = unlimited credit) |

---

## 📂 3. File Creation Matrix

### 3A. Migration Files (10 files)

| # | Action | File Path | Purpose |
|---|--------|-----------|---------|
| 1 | `[NEW]` | `database/migrations/xxxx_create_tax_rules_table.php` | 🆕 Tax engine |
| 2 | `[NEW]` | `database/migrations/xxxx_create_document_sequences_table.php` | 🆕 Doc numbering |
| 3 | `[NEW]` | `database/migrations/xxxx_create_customer_pricelists_table.php` | Pricelists & items |
| 4 | `[NEW]` | `database/migrations/xxxx_create_coupons_and_gift_cards_table.php` | Coupons & gift cards |
| 5 | `[NEW]` | `database/migrations/xxxx_create_sales_quotations_table.php` | Quotations & items |
| 6 | `[NEW]` | `database/migrations/xxxx_create_sales_orders_table.php` | Orders & items |
| 7 | `[NEW]` | `database/migrations/xxxx_create_sales_invoices_table.php` | 🔴 Invoices & items |
| 8 | `[NEW]` | `database/migrations/xxxx_create_delivery_orders_table.php` | 🆕 Delivery & items |
| 9 | `[NEW]` | `database/migrations/xxxx_create_customer_payments_table.php` | 🆕 Payments |
| 10 | `[NEW]` | `database/migrations/xxxx_create_sales_returns_and_credit_notes_table.php` | Returns, return items & credit notes |
| 11 | `[NEW]` | `database/migrations/xxxx_add_credit_limit_to_customers_table.php` | 🆕 Credit limit column |

### 3B. Eloquent Model Files (20 files)

| # | Action | File Path | Key Relations |
|---|--------|-----------|---------------|
| 1 | `[NEW]` | `app/Models/TaxRule.php` | 🆕 |
| 2 | `[NEW]` | `app/Models/DocumentSequence.php` | 🆕 + static `generateNumber($modelType)` |
| 3 | `[NEW]` | `app/Models/CustomerPricelist.php` | `items()`, `currency()` |
| 4 | `[NEW]` | `app/Models/PricelistItem.php` | `pricelist()`, `product()` |
| 5 | `[NEW]` | `app/Models/Coupon.php` | `isValid()`, `apply()` |
| 6 | `[NEW]` | `app/Models/GiftCard.php` | `customer()`, `deductBalance()` |
| 7 | `[NEW]` | `app/Models/SalesQuotation.php` | `items()`, `customer()`, `currency()`, `taxRule()` |
| 8 | `[NEW]` | `app/Models/SalesQuotationItem.php` | `quotation()`, `product()` |
| 9 | `[NEW]` | `app/Models/SalesOrder.php` | `items()`, `customer()`, `approvalWorkflow()`, `invoices()`, `deliveries()` |
| 10 | `[NEW]` | `app/Models/SalesOrderItem.php` | `order()`, `product()`, `deliveryItems()` |
| 11 | `[NEW]` | `app/Models/SalesInvoice.php` | 🔴 `items()`, `order()`, `payments()`, `customer()` |
| 12 | `[NEW]` | `app/Models/SalesInvoiceItem.php` | 🔴 `invoice()`, `product()` |
| 13 | `[NEW]` | `app/Models/DeliveryOrder.php` | 🆕 `items()`, `order()`, `customer()` |
| 14 | `[NEW]` | `app/Models/DeliveryOrderItem.php` | 🆕 `delivery()`, `orderItem()`, `product()` |
| 15 | `[NEW]` | `app/Models/CustomerPayment.php` | 🆕 `customer()`, `invoice()`, `currency()` |
| 16 | `[NEW]` | `app/Models/SalesReturn.php` | `items()`, `order()`, `creditNote()` |
| 17 | `[NEW]` | `app/Models/SalesReturnItem.php` | 🆕 `return()`, `product()` |
| 18 | `[NEW]` | `app/Models/CreditNote.php` | `return()`, `customer()`, `invoice()` |

### 3C. Seeder Files (2 files)

| # | Action | File Path | Purpose |
|---|--------|-----------|---------|
| 1 | `[NEW]` | `database/seeders/TaxRuleSeeder.php` | Pre-seed Denmark Moms, EU, Zero-rated |
| 2 | `[NEW]` | `database/seeders/DocumentSequenceSeeder.php` | Pre-seed all document types |

### 3D. Existing Model Update (1 file)

| # | Action | File Path | Change |
|---|--------|-----------|--------|
| 1 | `[MODIFY]` | `app/Models/Customer.php` | Add `credit_limit` to fillable, add `hasAvailableCredit($amount)` method |

---

## 📊 4. Comparison: আগের Step 1 vs নতুন Step 1

| বিষয় | আগের Plan | নতুন Plan (Enterprise) |
|--------|-----------|----------------------|
| **মোট Migration** | ৫টি | **১১টি** |
| **মোট Model** | ১০টি | **১৮টি** |
| **মোট Seeder** | ০ | **২টি** |
| **Tax Engine** | ❌ | ✅ |
| **Document Sequence** | ❌ | ✅ |
| **Sales Invoice Tables** | ❌ মিসিং | ✅ সংযোজিত |
| **Delivery Order Tables** | ❌ | ✅ |
| **Customer Payment Table** | ❌ | ✅ |
| **Sales Return Items** | ❌ | ✅ |
| **Credit Limit Column** | ❌ | ✅ |

---

## 🧪 5. Verification & Testing Strategy

1. **Migration Execution:** Run `php artisan migrate` — verify 0 syntax/foreign key errors
2. **Model Integrity:** Run `php -l` on all 18 new Eloquent models + 2 seeders
3. **Seeder Execution:** Run `php artisan db:seed --class=TaxRuleSeeder` and `DocumentSequenceSeeder`
4. **Approval Engine Alignment:** Register `App\Models\SalesOrder` in ApprovalWorkflow dropdown
5. **Credit Limit Method:** Verify `Customer::hasAvailableCredit($amount)` returns correct boolean
6. **Document Sequence:** Verify `DocumentSequence::generateNumber('SalesQuotation')` returns `SQ-202608-0001`

---

## ⏱️ Estimated Time: ১-২ ঘণ্টা
