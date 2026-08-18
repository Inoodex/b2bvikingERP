# Phase 3 — Step 3.7 Implementation Plan: Customer Sales Return (RMA) & Credit Notes Engine

This plan implements a **Tier-1 Enterprise Customer Sales Return (RMA) & Credit Notes Engine (SAP S/4HANA SD / Odoo 17 Parity)** for B2B Viking ERP.

---

## 📑 User Review Required

> [!IMPORTANT]
> **Complete Codebase & Sidebar Cleanup:**
> - **Remove Legacy Files:** Delete `app/Http/Controllers/Backend/IssueReturnController.php` and `resources/views/backend/issue_return/` directory.
> - **Remove Legacy Routes:** Clean up `issue-returns` routes in `routes/web.php`.
> - **Remove Sidebar Link:** Delete `Inventory ➔ Stock Return` from `navbar.blade.php`.
> - **Single Master Return Hub** under **Orders**:
>   - 📄 `Customer Returns (RMA)` -> `admin.sales-returns.index`
>   - 💳 `Credit Notes` -> `admin.credit-notes.index`

> [!NOTE]
> **3 Enterprise Settlement Modes:**
> 1. **Mode A (Invoice Offset):** Deducts Credit Note amount from customer's unpaid order/invoice balance.
> 2. **Mode B (Product Replacement):** Issues stock replacement for returned items.
> 3. **Mode C (Direct Refund / Refund Voucher):** Issues direct refund voucher (`RCN-202608-0001`).

---

## 🛠️ Proposed Changes

### 1. Legacy Cleanup

#### [DELETE] [IssueReturnController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/IssueReturnController.php)
- Delete unneeded legacy stock return controller.

#### [DELETE] `resources/views/backend/issue_return/`
- Remove legacy issue return view folder.

---

### 2. New Controllers & DataTables

#### [NEW] [SalesReturnController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/SalesReturnController.php)
- Handle listing, creation, pre-populating order items, approval, and detail views for Customer Sales Returns.
- Auto-generate sequential `return_no` (via `DocumentSequenceService`).
- Auto-restock physical inventory into bins (`StockLedger` / `InventoryStock`).
- Auto-issue linked `CreditNote` record on approval.

#### [NEW] [CreditNoteController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/CreditNoteController.php)
- Manage Credit Notes listing, settlement operations (Modes A, B, C), customer credit limit restoration, and PDF exports.

#### [NEW] [SalesReturnDataTable.php](file:///c:/laragon/www/b2bvikingErp/app/DataTables/SalesReturnDataTable.php)
- Server-side DataTable for Sales Returns with status badges, restock indicators, and action buttons.

#### [NEW] [CreditNoteDataTable.php](file:///c:/laragon/www/b2bvikingErp/app/DataTables/CreditNoteDataTable.php)
- Server-side DataTable for Credit Notes with settlement status and action icons.

---

### 3. Routes & Navigation

#### [MODIFY] [routes/web.php](file:///c:/laragon/www/b2bvikingErp/routes/web.php)
- Remove `issue-returns` routes.
- Add resource routes for `sales-returns` and `credit-notes`.
- Add custom action routes (`approve`, `settle`, `pdf`).

#### [MODIFY] [navbar.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/layouts/navbar.blade.php)
- **Remove `Inventory ➔ Stock Return`** link from Inventory menu.
- Add sub-menu items under **Orders** for:
  - `Customer Returns (RMA)` -> `admin.sales-returns.index`
  - `Credit Notes` -> `admin.credit-notes.index`

---

### 4. Blade Views (Stisla Theme Standard)

#### [NEW] [sales_returns/index.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/sales_returns/index.blade.php)
- List returns with Stisla Section Header and status filters.

#### [NEW] [sales_returns/create.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/sales_returns/create.blade.php)
- Return creation interface with dynamic order item pre-population and quantity inputs.

#### [NEW] [sales_returns/show.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/sales_returns/show.blade.php)
- Detailed return view showing returned items, reasons, stock restock status, and linked Credit Note.

#### [NEW] [credit_notes/index.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/credit_notes/index.blade.php)
- Credit Notes list with settlement status filters.

#### [NEW] [credit_notes/show.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/credit_notes/show.blade.php)
- Credit Note detail page with 3-Mode Settlement Modal and audit history.

#### [NEW] [pdf/credit_note.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/pdf/credit_note.blade.php)
- Professional DomPDF layout for downloading Credit Note PDF.

---

## 🧪 Verification Plan

### Automated Verification
- Run PHP syntax checks across all newly created/modified files:
  ```bash
  php -l app/Http/Controllers/Backend/SalesReturnController.php
  php -l app/Http/Controllers/Backend/CreditNoteController.php
  php -l app/DataTables/SalesReturnDataTable.php
  php -l app/DataTables/CreditNoteDataTable.php
  ```

### Manual Verification
1. Create a Customer Sales Return (RMA) for a real order.
2. Verify return approval updates physical stock AND auto-issues Credit Note `CN-202608-XXXX`.
3. Test Credit Note settlement against unpaid order balance (Mode A).
4. Download Credit Note PDF and verify accuracy.
