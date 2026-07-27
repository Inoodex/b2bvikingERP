# Phase 2 Step 2: PO, PI & LC Tracking — Implementation Plan

This plan outlines the technical steps required to build the Purchase Order (PO) Generation, Proforma Invoice (PI), and Letter of Credit (LC) modules as per Phase 2 Step 2.

## User Review Required
> [!IMPORTANT]
> Please review the core logic of **Split PO Generation**. If a Comparison Statement (CS) has 5 items, and 2 items are awarded to Vendor A, and 3 items are awarded to Vendor B, the system will automatically generate **Two Separate Purchase Orders** (one for Vendor A, one for Vendor B). Is this automatic split behavior correct for your business workflow?

## Open Questions
> [!WARNING]
> 1. In `purchases` (PO) table, the default status is `1` (Active/Completed). Since POs now need approval (`approval_status`), should we treat `status = 1` as "Active" and wait for `approval_status` to reach `approved` before the PO can be sent to the vendor?
> 2. Should the Vendor receive an automatic email the moment the PO is `approved`, or should there be a manual "Send Email" button?

## Proposed Changes

---
### 1. Purchase Order (PO) Engine & Approval
We will repurpose the existing `purchases` table as the main Purchase Order table since all the necessary foreign keys (rfq_id, comparison_statement_id, proforma_invoice_id, lc_id, approval_status) were added via migrations.

#### [MODIFY] `app/Models/Purchase.php`
- Add `Approvable` interface implementation.
- Setup `approvals()` polymorphic relationship.
- Setup relationships for `Vendor`, `ComparisonStatement`, `ProformaInvoice`, `LetterOfCredit`, and `PurchaseDetail`.

#### [NEW] `app/Http/Controllers/Backend/PurchaseOrderController.php`
- `generateFromCs($cs_id)`: 
  - Validates if CS is `approved`.
  - Groups the `ComparisonStatementItem` records by `awarded_vendor_id`.
  - Creates a new `Purchase` record for each vendor (Split PO logic).
  - Copies items into `purchase_details`.
  - Submits the new PO to the `ApprovalService` (status becomes `pending`).
- `approve/reject`: Multi-level approval endpoints.

#### [NEW] `app/Mail/PoNotificationMail.php`
- Generates a PDF version of the PO on-the-fly and emails it directly to the vendor's email address.

---
### 2. Proforma Invoice (PI) Tracking

#### [NEW] `app/Models/ProformaInvoice.php`
- Setup relationships: `vendor()`, `rfq()`, `purchases()`.

#### [NEW] `app/Http/Controllers/Backend/ProformaInvoiceController.php`
- Controller to handle uploading Vendor PIs.
- Methods: `index`, `create`, `store`. When storing, it will link the PI to the respective `Purchase` (PO).

---
### 3. Letter of Credit (LC) Module

#### [NEW] `app/Models/LetterOfCredit.php`
- Setup relationships: `proformaInvoice()`, `vendor()`.

#### [NEW] `app/Http/Controllers/Backend/LetterOfCreditController.php`
- Handles the tracking of LC details (Issuing Bank, Margin Percent, Amount, Issue/Expiry Dates).

---
### 4. UI & Views

#### [NEW] `resources/views/backend/purchase/po_list.blade.php`
- Data table showing all generated POs, their Approval Status, and PI/LC link status.

#### [NEW] `resources/views/backend/purchase/po_show.blade.php`
- Detailed view of a specific PO.
- Will contain Action buttons:
  - **"Approve/Reject"** (if pending).
  - **"Email to Vendor"** (if approved).
  - **"Upload PI"** (redirects to PI creation).
  - **"Add LC Details"** (redirects to LC creation).

## Verification Plan

### Automated Tests
- No automated tests required for this step.

### Manual Verification
1. I will generate a PO from the Comparison Statement we tested earlier.
2. I will verify if multiple POs are created when different vendors win different items (Split PO).
3. I will test the PO Approval Workflow using `ApprovalService`.
4. I will test adding a PI and an LC to an approved PO.
