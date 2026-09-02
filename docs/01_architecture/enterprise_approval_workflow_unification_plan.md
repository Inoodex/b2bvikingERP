# Enterprise Approval Workflow Unification & Accounts Integration Plan
**Project:** B2B Viking ERP  
**Standard Target:** SAP S/4HANA & Odoo 17 Tiered Governance  
**Scope:** Reconnecting Disconnected Modules, Adding Critical Financial Approvals, and Building a Centralized Approvals Inbox.

---

## 1. Executive Summary & Objective

The B2B Viking ERP possesses a foundational polymorphic multi-step approval engine (`ApprovalWorkflow`, `ApprovalStep`, `Approval`, and `ApprovalService`). However, our comprehensive system-wide audit revealed two major architectural gaps:
1. **Disconnected Existing Modules:** Document types configured in the Admin Workflow Setup (`StockTransfer`, `LetterOfCredit`, `VendorReturn`, and B2B `Order`) are either commented out or not calling `ApprovalService::submitForApproval()`, rendering configured rules inactive.
2. **Missing Financial Governance in Accounts:** Critical corporate financial operations (large Inter-Account Bank Transfers and high-value Vendor Bills) currently post directly to General Ledger without managerial review thresholds.
3. **Absence of a Centralized Approver Inbox:** Currently, approvers have no unified dashboard to view their pending approval tasks across document types.

This plan outlines the systematic implementation required to achieve 100% unified approval governance across Procurement, Inventory, Sales, and Financial Accounting.

---

## 2. Target Architecture: Tiered Multi-Module Governance Matrix

| Domain | Document / Model | Trigger Event | Condition / Threshold | Approver Roles | Outcome Upon Final Approval |
|---|---|---|---|---|---|
| **Procurement** | `ProductRequest` (SR / PR) | Request Submission | Tiered by value or always | Dept Head -> Procurement Head | Order/PO Generation unlocked |
| **Procurement** | `ComparisonStatement` (CS) | CS Generation | Quotation Comparison | Procurement Manager -> Director | Selected Vendor PO can be issued |
| **Procurement** | `Purchase` (PO) | PO Creation | Value Threshold (e.g. > kr. 20,000) | Procurement Head -> Managing Director | PO Dispatched to Supplier |
| **Procurement** | `LetterOfCredit` (LC) | LC Application | Always / Import Limit | Finance Manager -> Managing Director | Bank LC Margin block confirmed |
| **Procurement** | `VendorReturn` (Debit Note)| QC Claim / Rejection | Claim Value > kr. 5,000 | Warehouse Head -> Procurement Manager | Vendor Debit Note settled on AP |
| **Inventory** | `StockTransfer` (WMS) | Transfer Dispatch | Inter-Outlet Dispatch | Central Warehouse Manager | Stock depleted & Dispatched |
| **Sales** | `Order` (B2B Commercial SO) | Order Placement | Exceeds Credit Limit or > kr. 10,000 | Credit Manager / Sales Director | Unlocked for Warehouse Packing |
| **Accounts** | `VendorBill` (AP Invoices) | Non-GRN or High Value (> kr. 50,000)| Exception / High Value | Accounts Manager -> CFO | Bill Cleared for Payment Voucher |
| **Accounts** | `FundTransfer` (Contra) | Bank Transfer Request | Transfer Amount > kr. 10,000 | Finance Controller -> CFO | Bank balances deducted & Contra GL posted |

---

## 3. Phased Implementation Breakdown

### Phase A: Reconnecting Existing Disconnected Modules
1. **Internal Stock Transfer (`StockTransfer`):**
   - In `StockTransferController::store()`, call `ApprovalService::submitForApproval($transfer, $totalTransferValue)`.
   - If workflow exists, set status to `pending_approval` (disallow transit/dispatch until approved).
   - Add approval review buttons (Approve / Reject modal with comments) in `stock_transfers/show.blade.php`.
2. **Vendor Return & Debit Note (`VendorReturn`):**
   - In `VendorReturnController::store()`, integrate `ApprovalService::submitForApproval($return, $totalClaimAmount)`.
   - Update `vendor_return/show.blade.php` to render multi-step approval status and action buttons.
3. **Import Letter of Credit (`LetterOfCredit`):**
   - In `LetterOfCreditController::store()`, call `ApprovalService::submitForApproval($lc, $totalAmount)`.
   - Add approval actions in `letters_of_credit/show.blade.php`.
4. **Commercial Sales Order (`Order`):**
   - In `CartController` & `OrderController`, uncomment the `ApprovalService::submitForApproval($order, $amount)` integration.
   - If customer order exceeds `credit_limit` or matches active high-value workflow, put on `pending` or `credit_hold`.

### Phase B: Accounts & Financial Governance Expansion
1. **Extend Workflow Setup UI (`ApprovalWorkflowController`):**
   - Add `App\Models\VendorBill` and `App\Models\FundTransfer` to `$models` dropdown in Workflow Setup (`create.blade.php` & `edit.blade.php`).
2. **Vendor Bill Approval Integration (`VendorBillController`):**
   - When bill exceeds workflow threshold (e.g. > kr. 50,000 or manual non-GRN bill):
     - Submit for approval via `ApprovalService`.
     - Payment voucher generation is locked while in `pending_approval`.
3. **Inter-Account Bank Transfer Governance (`FundTransferController`):**
   - When fund transfer exceeds configured threshold (e.g. > kr. 10,000):
     - Transfer record created with `status = 'pending_approval'`.
     - Actual bank balance decrement/increment and GL Contra Journal posting are deferred until final approval step is signed off!

### Phase C: Centralized Approvals Hub ("My Pending Approvals")
1. **Approvals Inbox Controller & View (`/admin/approvals`):**
   - Query all pending `Approval` items where step matches current user ID or current user's assigned roles.
   - Tabbed view: `All`, `Procurement (PR/PO/CS)`, `Inventory (Transfers)`, `Sales (Orders)`, `Finance (Bills/Transfers)`.
2. **Topbar Quick Indicator:**
   - Add a dedicated check badge in topbar or dropdown showing count of documents waiting for current user's approval.
3. **Audit Trail & PDF Signatures:**
   - Log timestamp, approver user ID, role, and comment on each approval step.

---

## 4. Verification & Testing Standards
1. **Zero-Imbalance Integrity:** Adding approval holds must not create orphan journal entries; GL entries should only post upon final approved state.
2. **Auto-Approve Fallback:** If no workflow is configured for a document type or amount is below threshold, system must auto-approve with 0ms delay (no operational bottlenecks).
3. **Role & Permission Isolation:** A user without the designated role cannot approve steps assigned to other departments.
