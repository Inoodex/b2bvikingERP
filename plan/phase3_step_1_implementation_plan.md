# 🏢 Phase 3 — Step 1 Implementation Plan: Database Migrations & Eloquent Data Models
*(Status: ✅ FULLY COMPLETED)*

> **Phase:** 3 (Sales Management, Dynamic Pricing, Fulfillment & Credit Engine)  
> **Step:** 1 (Database Schema, ALTER/CREATE Migrations, Eloquent Models & Relationships)  
> **Target Parity:** SAP S/4HANA SD Data Schema | Oracle Fusion Sales Data Architecture | Odoo 17 Sales Models  

---

## 📌 1. Scope & Database Architecture Audit

Step 1 creates the foundational database schema, relationships, indexes, foreign keys, and Eloquent models for the entire Sales & Distribution module.

### 🔍 Empirical Database Schema Status:
1. **Existing Database Tables:** `taxes`, `pricelists`, `pricelist_items`, `coupons`, `gift_cards`, `sales_quotations`, `sales_quotation_items`, `sales_invoices`, `sales_invoice_items`, `delivery_orders`, `delivery_order_items`, `sales_returns`, `sales_return_items`, `advance_payments`, `payment_allocations`, `order_sequences`, `orders`, `order_items`.
2. **Missing Database Tables:** `credit_notes`, `document_sequences`.
3. **Missing Customer Table:** Customer = `users` table (`sales_quotations.customer_id` points to `users.id`).
4. **ALTER Migrations Required:** Add enterprise columns (`currency_id`, `exchange_rate`, `tax_id`, `created_by`, `due_date`, `paid_amount`, `due_amount`, `tracking_number`, `carrier_name`, `delivery_type`, `credit_limit` on `users` table).

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                           PHASE 3 STEP 1 DATA MODEL ARCHITECTURE (COMPLETED)                            │
│                                                                                                        │
│  taxes                           (Utilizing existing Tax & CheckoutTaxResolver engine)                 │
│  🆕 document_sequences           (Admin-configurable Number Formats: SQ-, SO-, INV-, DO-, CN-)           │
│  pricelists ──< pricelist_items  (Customer Tiers: Retail, Wholesale, B2B VIP, Distributor)             │
│  coupons / gift_cards                                                                                   │
│  sales_quotations ──< sales_quotation_items                                                             │
│  orders ──< order_items          (Unified B2C/B2B Engine linked to ApprovalWorkflow model_type)          │
│  sales_invoices ──< sales_invoice_items                                                                 │
│  delivery_orders ──< delivery_order_items (Packing Slips, Partial Shipments & Back Orders)             │
│  advance_payments / customer_payments                                                                   │
│  sales_returns ──< sales_return_items ──< 🆕 credit_notes                                               │
│  users table ── ADD credit_limit column                                                                │
└──────────────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🛠️ 2. Database Migrations Breakdown [x]

- [x] 2.1 `ALTER TABLE sales_quotations` (`currency_id`, `exchange_rate`, `tax_id`, `notes`, `created_by`, `reminder_sent`)
- [x] 2.2 `ALTER TABLE sales_invoices` (`subtotal_amount`, `tax_amount`, `discount_amount`, `due_date`, `paid_amount`, `due_amount`, `currency_id`, `exchange_rate`, `incoterm`, `notes`, `created_by`)
- [x] 2.3 `ALTER TABLE sales_invoice_items` (`variant_id`, `discount`, `tax_amount`, `description`)
- [x] 2.4 `ALTER TABLE delivery_orders` (`delivery_type`, `tracking_number`, `carrier_name`, `shipping_address`, `actual_delivery_date`, `notes`, `created_by`)
- [x] 2.5 `ALTER TABLE delivery_order_items` (`variant_id`, `sales_order_item_id`, `ordered_qty`)
- [x] 2.6 `ALTER TABLE sales_returns` (`sales_invoice_id`, `return_to_stock`, `created_by`)
- [x] 2.7 `ALTER TABLE users` (`credit_limit`)
- [x] 2.8 `CREATE TABLE credit_notes` (`credit_notes` table)
- [x] 2.9 `CREATE TABLE document_sequences` (`document_sequences` table)

---

## 📂 3. Eloquent Models Matrix (19 Models Created) [x]

| Action | File Path | Model Class | Status |
| :--- | :--- | :--- | :--- |
| `[NEW]` | `app/Models/SalesQuotation.php` | `SalesQuotation` | ✅ Done |
| `[NEW]` | `app/Models/SalesQuotationItem.php` | `SalesQuotationItem` | ✅ Done |
| `[NEW]` | `app/Models/SalesInvoice.php` | `SalesInvoice` | ✅ Done |
| `[NEW]` | `app/Models/SalesInvoiceItem.php` | `SalesInvoiceItem` | ✅ Done |
| `[NEW]` | `app/Models/DeliveryOrder.php` | `DeliveryOrder` | ✅ Done |
| `[NEW]` | `app/Models/DeliveryOrderItem.php` | `DeliveryOrderItem` | ✅ Done |
| `[NEW]` | `app/Models/SalesReturn.php` | `SalesReturn` | ✅ Done |
| `[NEW]` | `app/Models/SalesReturnItem.php` | `SalesReturnItem` | ✅ Done |
| `[NEW]` | `app/Models/CreditNote.php` | `CreditNote` | ✅ Done |
| `[NEW]` | `app/Models/Coupon.php` | `Coupon` | ✅ Done |
| `[NEW]` | `app/Models/GiftCard.php` | `GiftCard` | ✅ Done |
| `[NEW]` | `app/Models/GiftCardTransaction.php` | `GiftCardTransaction` | ✅ Done |
| `[NEW]` | `app/Models/Pricelist.php` | `Pricelist` | ✅ Done |
| `[NEW]` | `app/Models/PricelistItem.php` | `PricelistItem` | ✅ Done |
| `[NEW]` | `app/Models/CustomerPayment.php` | `CustomerPayment` | ✅ Done |
| `[NEW]` | `app/Models/AdvancePayment.php` | `AdvancePayment` | ✅ Done |
| `[NEW]` | `app/Models/PaymentAllocation.php` | `PaymentAllocation` | ✅ Done |
| `[NEW]` | `app/Models/QuotationTemplate.php` | `QuotationTemplate` | ✅ Done |
| `[NEW]` | `app/Models/DocumentSequence.php` | `DocumentSequence` | ✅ Done |

---

## 🧪 4. Verification & Testing Results

1. **Migration Execution:** Executed `php artisan migrate` with 0 schema conflicts. ✅
2. **Model Syntax Check:** Verified via `php -l` across all 19 Eloquent Models with 0 syntax errors. ✅
