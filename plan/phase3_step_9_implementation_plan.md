# 🏢 Phase 3 Step 3.9: Commercial B2B Sales Invoicing & Billing Engine
**Target Parity: SAP S/4HANA Billing Document (VF01) | Odoo 17 Customer Invoices**

---

## 📌 Goal Description
Implement an enterprise-grade **Commercial B2B Sales Invoicing & Billing Engine** for **B2B Viking ERP**. This engine enables accounts managers and sales billing staff to convert dispatched Delivery Orders (`DO-XXXX`) or approved Sales Orders (`SO-XXXX`) into official Commercial Sales Invoices (`INV-202608-XXXX`). It calculates itemized VAT tax and discount amounts, tracks payment dues, posts automated double-entry General Ledger journal transactions (`Accounts Receivable` vs `Sales Revenue`), and streams printable DomPDF commercial B2B invoices with bank wire transfer details.

---

## 🏛️ Enterprise Architecture & Features

### 1. Document Sequence & Storage (`sales_invoices` + `sales_invoice_items`)
- **Sequence Format:** `INV-YYYYMM-XXXX` (e.g. `INV-202608-0001`) via `OrderNumberService`.
- **Database Tables:** Existing `sales_invoices` and `sales_invoice_items` tables with fields for `subtotal_amount`, `tax_amount`, `discount_amount`, `total_amount`, `paid_amount`, `due_amount`, `due_date`, `incoterm`, `notes`, `created_by`, `posted_by`.

### 2. Yajra DataTables Integration (`SalesInvoiceDataTable.php`)
- Standard Yajra DataTable following `CategoryDataTable` reference 100%.
- **Columns:** `Invoice No`, `Order No`, `Customer / Outlet`, `Total Amount`, `Paid Amount`, `Due Amount`, `Status`, `Action`.
- **Filters & Tabs:** `All`, `Draft`, `Posted`, `Paid`, `Cancelled`.

### 3. 1-Click Generation & Delivery Order Conversion (`create.blade.php`)
- **Route:** `admin/sales-invoices/create?order_id={id}&delivery_order_id={id}`
- **Header Buttons:** 🧾 **Generate Sales Invoice** button added to Delivery Order details page (`/admin/delivery-orders/{id}`) and Sales Order details page (`/admin/orders/{id}`).
- Pre-loads item lines, prices, tax rates, and remaining uninvoiced quantities.
- Input fields for `Due Date` (Net 30 / Net 15 / Immediate), `Incoterms` (FOB, CIF, EXW), and `Payment Instructions`.

### 4. Financial Posting & General Ledger Integration (`SalesInvoiceController@post`)
- When an invoice is Posted (`Draft` -> `Posted`):
  - **General Ledger Entry:** Posts `JournalEntry` & `JournalEntryLine` records:
    - **Debit:** Accounts Receivable (Customer Balance)
    - **Credit:** Sales Revenue Account
    - **Credit:** Output VAT / Sales Tax Payable Account (if applicable)
  - Updates `Order` payment and billing status.

### 5. DomPDF Commercial B2B Sales Invoice Generator (`pdf.blade.php`)
- Route: `/admin/sales-invoices/{id}/pdf`
- Executive B2B Commercial Sales Invoice layout formatted with:
  - Seller VAT Tax Registration No & Registered Company Address
  - Customer VAT Tax Registration No & Billing/Shipping Address
  - Payment Due Date & Payment Terms (Net 30 / Net 15)
  - Bank Account IBAN / SWIFT details for wire transfer payment
  - Itemized table with Unit Price, Discount, VAT Tax %, and Subtotal
  - Authorized Financial Controller Signature.

---

## 📁 Proposed Changes & Component Breakdown

### 1. Database Migration
- `[NEW]` [2026_08_17_000003_alter_sales_invoices_table_for_delivery_ref.php](file:///c:\laragon\www\b2bvikingErp\database\migrations\2026_08_17_000003_alter_sales_invoices_table_for_delivery_ref.php)
  - Adds `delivery_order_id`, `delivery_no_ref`, `variant_id`, `tax_rate` to `sales_invoices` and `sales_invoice_items` tables.

### 2. Backend Controllers & DataTables
- `[NEW]` [SalesInvoiceController.php](file:///c:\laragon\www\b2bvikingErp\app\Http\Controllers\Backend\SalesInvoiceController.php)
  - `index()`: Render Yajra DataTable.
  - `create()`: Render Commercial Sales Invoice creation form populated from Order/Delivery Order.
  - `store()`: Create Sales Invoice (`INV-XXXX`) in draft or posted state.
  - `show()`: Display Sales Invoice details & financial posting logs.
  - `post()`: Approve posting, record General Ledger journal entries, and update customer balance.
  - `downloadPdf()`: Stream commercial PDF invoice.
- `[NEW]` [SalesInvoiceDataTable.php](file:///c:\laragon\www\b2bvikingErp\app\DataTables\SalesInvoiceDataTable.php)
  - Server-side Yajra DataTable with latest ID sorting and status badges.

### 3. Blade Views
- `[NEW]` [index.blade.php](file:///c:\laragon\www\b2bvikingErp\resources\views\backend\sales_invoices\index.blade.php)
- `[NEW]` [create.blade.php](file:///c:\laragon\www\b2bvikingErp\resources\views\backend\sales_invoices\create.blade.php) (Centered Select Order / Delivery Order Preloaded Form)
- `[NEW]` [show.blade.php](file:///c:\laragon\www\b2bvikingErp\resources\views\backend\sales_invoices\show.blade.php) (SweetAlert post confirmation modal & GL journal badge)
- `[NEW]` [sales_invoice.blade.php](file:///c:\laragon\www\b2bvikingErp\resources\views\backend\pdf\sales_invoice.blade.php) (DomPDF commercial B2B invoice with IBAN bank details)

### 4. Routes & Navigation
- `[MODIFY]` [web.php](file:///c:\laragon\www\b2bvikingErp\routes\web.php): Register `/admin/sales-invoices` resource, post and pdf routes.
- `[MODIFY]` [navbar.blade.php](file:///c:\laragon\www\b2bvikingErp\resources\views\backend\layouts\navbar.blade.php): Add **Sales Invoices** navigation link.

---

## 🧪 Verification & Manual Testing Plan
1. **1-Click Invoice Creation:** Open a dispatched Delivery Order (`/admin/delivery-orders/{id}`) and click 🧾 **Generate Sales Invoice**. Verify items, prices, and totals are preloaded.
2. **Draft vs Posted:** Submit form as `Draft`. Verify status is `Draft` with `Post Invoice` button available.
3. **Financial Posting & GL Journal:** Click **Post Invoice**. Verify SweetAlert prompt, status update to `Posted`, and General Ledger journal entry created.
4. **PDF Invoice Export:** Click **PDF Commercial Invoice** and verify DomPDF export formatted with B2B VAT registration, payment terms, IBAN bank transfer info, and itemized totals.
