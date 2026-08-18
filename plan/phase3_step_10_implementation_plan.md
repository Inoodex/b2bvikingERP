# Phase 3 — Step 3.10 Implementation Plan: Customer Payment Collection, Receipt Vouchers & Invoice Allocations Engine

This plan implements a **Tier-1 Enterprise Customer Payment Collection & Receipt Voucher Engine (SAP S/4HANA F-28 / Odoo 17 Customer Payments Parity)** for B2B Viking ERP.

---

## 📑 User Review Required

> [!IMPORTANT]
> **Enterprise Dual-Access Payment Architecture:**
> 1. **Accounts Central Hub:** Main management under **Accounts ➔ Customer Payments** (`http://127.0.0.1:8000/admin/customer-payments`).
> 2. **1-Click Contextual Action:** Direct 💳 **Record Payment** buttons on Sales Order details (`orders/show.blade.php`) and Sales Invoice details (`sales_invoices/show.blade.php`).

> [!NOTE]
> **3 Payment Allocation Modes:**
> - **Mode A (Invoice Allocation):** Allocate payment directly against one or multiple unpaid Sales Invoices (`due_amount > 0`). Auto-knocks down invoice due amounts.
> - **Mode B (Advance Customer Deposit):** Record advance payments before billing. Stored as unallocated customer credit balance.
> - **Mode C (Direct Order Payment):** Record payment directly against a Sales Order.

---

## 🛠️ Proposed Changes

### 1. Database Schema & Migration

#### [NEW] [2026_08_17_000004_create_customer_payments_table.php](file:///c:/laragon/www/b2bvikingErp/database/migrations/2026_08_17_000004_create_customer_payments_table.php)
- Columns:
  - `id`, `payment_no` (`REC-YYYYMM-XXXX`)
  - `user_id` (Customer FK)
  - `sales_invoice_id` (Optional FK)
  - `order_id` (Optional FK)
  - `account_id` (Cash/Bank Account FK)
  - `amount` (Decimal 15,2)
  - `payment_method` (`cash`, `bank_transfer`, `cheque`, `card`, `mobile_money`)
  - `reference_no` (Cheque No / Bank Reference / Transaction ID)
  - `payment_date` (Date)
  - `notes` (Text)
  - `created_by` (User FK)
  - `status` (`posted`, `voided`)

---

### 2. Models & DataTables

#### [NEW] [CustomerPayment.php](file:///c:/laragon/www/b2bvikingErp/app/Models/CustomerPayment.php)
- Model with relationships to `User`, `SalesInvoice`, `Order`, `Account`, `Creator`, and polymorphic `JournalEntry`.

#### [NEW] [CustomerPaymentDataTable.php](file:///c:/laragon/www/b2bvikingErp/app/DataTables/CustomerPaymentDataTable.php)
- Yajra DataTable server-side grid with customer filter, payment method badges, amount formatting, and PDF export action.

---

### 3. Controllers & Services

#### [NEW] [CustomerPaymentController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/CustomerPaymentController.php)
- `index()`: Render DataTable list.
- `create()`: Render form with AJAX pre-selected customer, invoice, and bank accounts.
- `store()`: Create payment, execute double-entry GL posting (`Cash/Bank` Dr, `Accounts Receivable` Cr), update invoice `paid_amount` & `due_amount`, restore customer credit limit, and redirect with success notification.
- `show()`: Payment receipt voucher view.
- `pdf()`: Download DomPDF Payment Receipt PDF.

---

### 4. Views & PDF Templates

#### [NEW] [customer_payments/index.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/customer_payments/index.blade.php)
- Header with **+ New Payment Receipt** button and DataTables container.

#### [NEW] [customer_payments/create.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/customer_payments/create.blade.php)
- Dynamic Form with customer select, invoice auto-knockdown calculator, bank account selection, and payment method fields.

#### [NEW] [customer_payments/show.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/customer_payments/show.blade.php)
- Executive Payment Receipt details card with GL journal posting status.

#### [NEW] [pdf/customer_payment.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/pdf/customer_payment.blade.php)
- Printable 1-page B2B Payment Receipt Voucher PDF.

---

### 5. UI Integration

#### [MODIFY] [navbar.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/layouts/navbar.blade.php)
- Add 💳 **Customer Payments** link under Accounts / Finance sidebar section.

#### [MODIFY] [sales_invoices/show.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/sales_invoices/show.blade.php)
- Add 💳 **Record Payment** button on header.

---

## 🧪 Verification Plan

### Automated Verification
- Create test script `scratch/test_customer_payments.php` to verify:
  1. Payment creation & `REC-YYYYMM-XXXX` sequence generation.
  2. Invoice due amount reduction (`due_amount` becomes 0).
  3. General Ledger posting (`Cash/Bank` Dr, `Accounts Receivable` Cr).
  4. Customer credit limit exposure restoration.
  5. DomPDF payment receipt rendering.

### Manual Verification
- Test in browser at `http://127.0.0.1:8000/admin/customer-payments`.
