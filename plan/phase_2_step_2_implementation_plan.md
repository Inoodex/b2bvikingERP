# Phase 2 Step 2: Enterprise Purchase Order (PO), PI & LC Tracking Engine — Implementation Plan

This technical implementation plan outlines the architecture, database models, controllers, queued jobs, and views required for **Phase 2 Step 2: Purchase Order (PO) Generation, Proforma Invoice (PI), and Letter of Credit (LC) Import Tracking System**, fully synchronized with existing database migrations (`database/migrations/`), **Client Requirements (Module 1.1 & 1.2)**, **Daily Roadmap (Days 14-16)**, and **Trello Blueprint (Cards 2.9 - 2.35)**.

---

## 1. User Review Required & Design Intent

> [!IMPORTANT]
> **1. Split PO Generation Engine**: If a Comparison Statement (CS) awards items to multiple suppliers (e.g. 2 items to Vendor A, 3 items to Vendor B), the system automatically generates **Separate Purchase Orders** (`PO-00001` for Vendor A, `PO-00002` for Vendor B).
> **2. Database Schema Alignment**: Uses exact column names from existing migrations: `purchases` (`lc_id`, `proforma_invoice_id`, `purchase_type`, `foreign_amount`, `exchange_rate_used`, `base_amount`, `approval_status`), `letters_of_credit` (`lc_no`, `issuing_bank`, `margin_percent`, `amount`), `lc_expenses` (`cost_element`, `amount`, `goes_to_unit_cost`), and `lc_amendments` (`amendment_no`, `change_details`, `amended_date`).
> **3. PO Milestone Lifecycle Tracker**: `draft` ➔ `pending_approval` ➔ `approved` ➔ `po_sent` ➔ `pi_attached` ➔ `lc_opened`.
> **4. Background Queued PO PDF Engine**: Uses `GeneratePoPdfJob` with `PdfCacheManager` to prevent web server timeouts.

---

## 2. Database Schema Mapping & Relationship Architecture

### Table Mapping Summary:

1. **`purchases` (Existing Table Repurposed for PO Engine)**:
   - Primary Columns: `id`, `po_no` (via add-on migration), `rfq_id`, `comparison_statement_id`, `vendor_id`, `purchase_type` (`local`/`foreign`), `currency_id`, `foreign_amount`, `exchange_rate_used`, `base_amount`, `proforma_invoice_id`, `lc_id`, `approval_status` (`pending`, `level1_approved`, `approved`, `rejected`), `milestone_status` (via add-on migration).

2. **`proforma_invoices` (Existing Table `2026_07_22_091800`)**:
   - Columns: `id`, `pi_no`, `vendor_id`, `rfq_id`, `currency_id`, `total_amount`, `issue_date`, `attachment_path` (via add-on migration), `status` (`pending`, `confirmed`, `cancelled`).

3. **`letters_of_credit` (Existing Table `2026_07_22_091900`)**:
   - Columns: `id`, `lc_no`, `proforma_invoice_id`, `vendor_id`, `issuing_bank`, `margin_percent`, `amount`, `currency_id`, `issue_date`, `expiry_date`, `status` (`open`, `amended`, `closed`, `cancelled`).

4. **`lc_expenses` (Normalized Expense Table `2026_07_22_092100` & `2026_07_22_100800`)**:
   - Columns: `id`, `lc_id`, `cost_element` (`CD`, `RD`, `SD`, `VAT`, `AIT`, `AT`, `LC Margin`, `Opening Charge`, `Doc Handling`, `Insurance`, `Transport`, `Freight`, `C&F`), `amount`, `currency_id`, `goes_to_unit_cost`, `gl_account_id`.

5. **`lc_amendments` (Existing Table `2026_07_22_092000`)**:
   - Columns: `id`, `lc_id`, `amendment_no`, `change_details`, `amended_date`.

6. **`po_email_logs` (Existing Table `2026_07_22_092700`)**:
   - Columns: `id`, `purchase_id`, `recipient_email`, `status`, `sent_at`.

---

## 3. Technical Component Breakdown

### Component 1: Eloquent Models & Relationships

- **[NEW] [Purchase.php](file:///c:/laragon/www/b2bvikingErp/app/Models/Purchase.php) (Updated)**:
  - Implements `Approvable` interface & `approvals()` polymorphic relation.
  - Relations: `vendor()`, `comparisonStatement()`, `rfq()`, `proformaInvoice()`, `letterOfCredit()`, `items()` (`PurchaseDetail`), `emailLogs()`.
- **[NEW] [ProformaInvoice.php](file:///c:/laragon/www/b2bvikingErp/app/Models/ProformaInvoice.php)**:
  - Relations: `vendor()`, `rfq()`, `currency()`, `purchases()`, `letterOfCredit()`.
- **[NEW] [LetterOfCredit.php](file:///c:/laragon/www/b2bvikingErp/app/Models/LetterOfCredit.php)**:
  - Relations: `proformaInvoice()`, `vendor()`, `currency()`, `expenses()` (`LcExpense`), `amendments()` (`LcAmendment`), `purchases()`.
- **[NEW] [LcExpense.php](file:///c:/laragon/www/b2bvikingErp/app/Models/LcExpense.php)**:
  - Relations: `letterOfCredit()`, `currency()`.
- **[NEW] [LcAmendment.php](file:///c:/laragon/www/b2bvikingErp/app/Models/LcAmendment.php)**:
  - Relations: `letterOfCredit()`.
- **[NEW] [PoEmailLog.php](file:///c:/laragon/www/b2bvikingErp/app/Models/PoEmailLog.php)**:
  - Relations: `purchase()`.

---

### Component 2: Controllers, DataTables & Queued Jobs

- **[NEW] [PurchaseOrderController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/PurchaseOrderController.php)**:
  - `index(PoDataTable $dataTable)`: Renders PO Datatable.
  - `generateFromCs($cs_id)`: Generates Split POs from approved CS per winning vendor.
  - `edit($id)` / `update($id)`: Allows admin to edit PO items while draft/pending.
  - `cancel($id)`: Cancels PO and releases locked CS items.
  - `sendEmail($id)`: Dispatches `SendPoEmailJob`.
- **[NEW] [ProformaInvoiceController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/ProformaInvoiceController.php)**:
  - Handles PI uploading, attachment storage, and PO milestone updates.
- **[NEW] [LetterOfCreditController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/LetterOfCreditController.php)**:
  - `index(LcDataTable $dataTable)`: Renders LC Register Datatable.
  - `store()`: Stores LC master with normalized `lc_expenses` rows.
  - `amend()`: Records LC amendments in `lc_amendments`.
- **[NEW] [GeneratePoPdfJob.php](file:///c:/laragon/www/b2bvikingErp/app/Jobs/GeneratePoPdfJob.php) & [SendPoEmailJob.php](file:///c:/laragon/www/b2bvikingErp/app/Jobs/SendPoEmailJob.php)**:
  - Renders PO PDF asynchronously via `PdfCacheManager` and sends email via `PoNotificationMail`.

---

### Component 3: Blade Views & UI Design

- **[NEW] [po_list.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/purchase/po_list.blade.php)**: Stisla DataTable showing all POs, approval status, and milestone tracker.
- **[NEW] [po_show.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/purchase/po_show.blade.php)**: PO details page with action buttons (**Edit PO**, **Cancel PO**, **Send Email**, **Download PDF**, **Upload PI**, **Create LC**).
- **[NEW] [po_pdf.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/purchase/po_pdf.blade.php)**: Executive domPDF layout for Purchase Orders.
- **[NEW] [lc_register.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/purchase/lc_register.blade.php)**: LC Register DataTable showing Bank, Expiry, Margin %, Expenses Summary, and Amendments.
- **[NEW] [lc_show.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/purchase/lc_show.blade.php)**: LC Details view showing itemized 13 import expenses breakdown and amendment history log.

---

## 4. Add-on Migration Summary

We will create **one clean add-on migration** to add missing helper columns:
- `2026_07_29_180000_add_po_fields_to_purchases_and_pis_tables.php`:
  - Adds `po_no` and `milestone_status` to `purchases` table.
  - Adds `attachment_path` and `remarks` to `proforma_invoices` table.

---

## 5. Verification Plan

### Automated Tests
- Syntax check using `php -l` across all new controllers, models, and jobs.

### Manual Verification
1. **Split PO Test**: Generate POs from an approved CS with multiple vendors.
2. **Approval Test**: Verify multi-level PO approval workflow.
3. **PI Upload Test**: Upload PI attachment and verify status update.
4. **LC & Expenses Test**: Register LC with itemized `lc_expenses` and verify landed cost tracking.
5. **LC Amendment Test**: Add amendment and verify log in LC Details page.
