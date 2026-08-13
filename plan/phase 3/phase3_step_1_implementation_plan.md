# 🏢 Phase 3 — Step 1 Implementation Plan: Database Migrations & Eloquent Data Models

> **Phase:** 3 (Sales Management, Dynamic Pricing & Credit Engine)  
> **Step:** 1 (Database Schema, Polymorphic Models & Master Engine Alignment)  
> **Target Parity:** SAP S/4HANA SD Data Schema | Oracle Fusion Sales Data Architecture | Odoo 17 Sales Models  

---

## 📌 1. Scope & System Architecture

Step 1 creates the foundational database tables, relationships, indexes, foreign keys, and Eloquent models for the entire Sales & Distribution module.

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                   PHASE 3 STEP 1 DATA MODEL ARCHITECTURE                         │
│                                                                                                  │
│  customer_pricelists ──< pricelist_items                                                         │
│  coupons / gift_cards                                                                            │
│  sales_quotations    ──< sales_quotation_items                                                   │
│  sales_orders        ──< sales_order_items      ──< (Linked to ApprovalWorkflow model_type)      │
│  sales_returns       ──< credit_notes           ──< debit_note_settlements (Cross-module)        │
└──────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🛠️ 2. Database Migrations Breakdown

### 2.1 `customer_pricelists` & `pricelist_items`
- **Fields for `customer_pricelists`:**
  - `id`, `name`, `code`, `customer_type` (retail, wholesale, b2b_vip, distributor), `currency_id`, `status` (active/inactive), `timestamps`
- **Fields for `pricelist_items`:**
  - `id`, `customer_pricelist_id`, `product_id`, `variant_id` (nullable), `min_qty` (default 1), `fixed_price` (decimal 15,2), `discount_percentage` (decimal 5,2), `timestamps`

### 2.2 `coupons` & `gift_cards`
- **Fields for `coupons`:**
  - `id`, `code` (unique string), `discount_type` (fixed, percentage), `discount_value`, `min_order_amount`, `valid_from`, `valid_until`, `usage_limit`, `used_count`, `status`
- **Fields for `gift_cards`:**
  - `id`, `card_number` (unique), `initial_value`, `current_balance`, `expiry_date`, `customer_id` (nullable), `status` (active, redeemed, expired)

### 2.3 `sales_quotations` & `sales_quotation_items`
- **Fields for `sales_quotations`:**
  - `id`, `quotation_no` (unique string, e.g. `SQ-202608-0001`), `customer_id`, `currency_id`, `exchange_rate` (default 1.0), `quotation_date`, `valid_until`, `subtotal`, `tax_amount`, `discount_amount`, `grand_total`, `notes`, `status` (draft, sent, accepted, declined, converted), `created_by`, `timestamps`
- **Fields for `sales_quotation_items`:**
  - `id`, `sales_quotation_id`, `product_id`, `variant_id` (nullable), `quantity`, `unit_price`, `discount`, `tax_amount`, `subtotal`, `timestamps`

### 2.4 `sales_orders` & `sales_order_items`
- **Fields for `sales_orders`:**
  - `id`, `order_no` (unique string, e.g. `SO-202608-0001`), `sales_quotation_id` (nullable), `customer_id`, `currency_id`, `foreign_amount`, `exchange_rate_used`, `base_amount`, `order_date`, `delivery_date`, `incoterm` (FOB, CIF, EXW, DDP, CFR), `payment_status` (unpaid, partial, paid), `approval_status` (pending, approved, rejected), `milestone_status` (draft, approved, packing, shipped, delivered, cancelled), `created_by`, `timestamps`
- **Fields for `sales_order_items`:**
  - `id`, `sales_order_id`, `product_id`, `variant_id` (nullable), `quantity`, `unit_price`, `subtotal`, `timestamps`

### 2.5 `sales_returns` & `credit_notes`
- **Fields for `sales_returns`:**
  - `id`, `return_no` (unique string, e.g. `SRET-202608-0001`), `sales_order_id`, `customer_id`, `return_date`, `reason`, `total_refund_amount`, `status`, `created_by`, `timestamps`
- **Fields for `credit_notes`:**
  - `id`, `credit_note_no` (unique string, e.g. `CN-202608-0001`), `sales_return_id`, `customer_id`, `currency_id`, `amount`, `settled_amount`, `remaining_amount`, `settlement_status` (unsettled, partial, settled), `timestamps`

---

## 📂 3. File Creation Matrix

| Action | File Path | Purpose |
| :--- | :--- | :--- |
| `[NEW]` | `database/migrations/2026_08_13_000001_create_customer_pricelists_table.php` | Migration for pricelists & items |
| `[NEW]` | `database/migrations/2026_08_13_000002_create_coupons_and_gift_cards_table.php` | Migration for coupons & gift cards |
| `[NEW]` | `database/migrations/2026_08_13_000003_create_sales_quotations_table.php` | Migration for quotations & items |
| `[NEW]` | `database/migrations/2026_08_13_000004_create_sales_orders_table.php` | Migration for sales orders & items |
| `[NEW]` | `database/migrations/2026_08_13_000005_create_sales_returns_and_credit_notes_table.php` | Migration for returns & credit notes |
| `[NEW]` | `app/Models/CustomerPricelist.php` | Eloquent model with `items()` relation |
| `[NEW]` | `app/Models/PricelistItem.php` | Eloquent model for pricelist items |
| `[NEW]` | `app/Models/Coupon.php` | Eloquent model for coupons |
| `[NEW]` | `app/Models/GiftCard.php` | Eloquent model for gift cards |
| `[NEW]` | `app/Models/SalesQuotation.php` | Eloquent model with `items()`, `customer()`, `currency()` relations |
| `[NEW]` | `app/Models/SalesQuotationItem.php` | Eloquent model for quotation items |
| `[NEW]` | `app/Models/SalesOrder.php` | Eloquent model with polymorphic approval relation |
| `[NEW]` | `app/Models/SalesOrderItem.php` | Eloquent model for order items |
| `[NEW]` | `app/Models/SalesReturn.php` | Eloquent model for RMA returns |
| `[NEW]` | `app/Models/CreditNote.php` | Eloquent model for accounting credit notes |

---

## 🧪 4. Verification & Testing Strategy

1. **Migration Execution:** Run `php artisan migrate` and verify 0 syntax/foreign key errors.
2. **Model Integrity:** Run `php -l` on all 10 new Eloquent models.
3. **Approval Engine Alignment:** Register `App\Models\SalesOrder` in `ApprovalWorkflow` dropdown options.
