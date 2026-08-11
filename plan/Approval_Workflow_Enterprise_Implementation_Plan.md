# Master Implementation Plan: Enterprise Approval Workflow Enforcement (All Modules)

This plan details the comprehensive fixes and security enhancements required to make the Approval Workflow System 100% dynamic, secure, and strictly enforced across **all 6 modules**: `Requisition (SR/PR)`, `Comparison Statement (CS)`, `Purchase Order (PO)`, `Import Letter of Credit (LC)`, `Vendor Return / Debit Note`, and `Stock Transfer`.

---

## 🎯 Root Cause Audit Findings (All Modules)

### 1. Requisition (SR / PR - ProductRequest & Order)
- **Problem A:** `ProductRequestController@store` hardcoded `$productRequest->status = 'approved'` upon creation, bypassing the workflow entirely.
- **Problem B:** `product-request/show.blade.php` contained a manual dropdown `<select name="status">` allowing any user (such as `manager@example.com`) to manually select `Approve` without checking if `canUserApproveCurrentStep()` was satisfied.

### 2. Comparison Statement (CS)
- **Problem A:** `ComparisonStatementController@store` and `approve` had no permission checks.
- **Problem B:** `rfq/show.blade.php` displayed the `Generate PO` button to anyone if CS status was `approved`, without verifying if the user has `Create Purchase Orders` / `Manage Purchases` permission.
- **Problem C:** `PurchaseOrderController@generateFromCs` allowed any user to trigger PO generation without backend permission checks.

### 3. Purchase Order (PO)
- **Problem A:** `po_show.blade.php` did not render the `Approve PO` button or status check.
- **Problem B:** `po_show.blade.php` rendered action buttons (`Shipment`, `Receive Goods`, `Landed Cost`) even when PO `approval_status` was `pending`!
- **Problem C:** Any user could click `Shipment` or `Receive Goods` on a pending PO.

### 4. Internal Stock Transfer (StockTransfer)
- **Problem:** Status transitions from `pending` to `completed` in `StockTransferController` and `stock_transfers/show.blade.php` did not enforce `ApprovalService` step matching.

### 5. Import LC & Vendor Return (Debit Note)
- **Problem:** `LetterOfCreditController@store` and `VendorReturnController@store` did not invoke `submitForApproval()`, allowing them to bypass workflow enforcement.

---

## 📋 Proposed Master Changes (Component by Component)

### Component 1: `app/Services/ApprovalService.php` & Backend Controllers

#### [MODIFY] [ApprovalService.php](file:///c:/laragon/www/b2bvikingErp/app/Services/ApprovalService.php)
- Enforce strict authorization in `approveStep()` and `rejectStep()` checking `canUserApproveCurrentStep()`.
- If no workflow is active, auto-approve the document (`approval_status = 'approved'`).

#### [MODIFY] [ProductRequestController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/ProductRequestController.php)
- Remove hardcoded `status = 'approved'` on creation; call `submitForApproval()`.
- Require `canUserApproveCurrentStep()` check on status update.

#### [MODIFY] [PurchaseOrderController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/PurchaseOrderController.php)
- Guard `generateFromCs` and PO creation with `Auth::user()->can('Create Purchase Orders')` or `Admin` role check.
- Connect PO approval route (`admin.purchase-orders.approve`).

#### [MODIFY] [StockTransferController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/StockTransferController.php)
- Connect `submitForApproval()` and enforce step approval check on transfer completion.

#### [MODIFY] [LetterOfCreditController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/LetterOfCreditController.php)
- Connect `submitForApproval()` on LC creation and enforce step approval checks.

#### [MODIFY] [VendorReturnController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/VendorReturnController.php)
- Connect `submitForApproval()` on Debit Note creation and enforce step approval checks.

---

### Component 2: Blade Views & UI Action Guards

#### [MODIFY] [rfq/show.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/rfq/show.blade.php)
- `Approve CS` button: Visible ONLY to assigned step approver / Admin.
- `Generate PO` button: Visible ONLY when CS is `approved` AND user has `Create Purchase Orders` / `Admin` permission.
- Non-approvers see: `⏳ Waiting for Approval: Step X (Role Name)`.

#### [MODIFY] [purchase/po_show.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/purchase/po_show.blade.php)
- Lock `Shipment`, `Receive Goods`, `Landed Cost` buttons when PO `approval_status !== 'approved'`.
- Render `Approve PO` button ONLY for assigned step approver / Admin.
- Non-approvers see: `⏳ Waiting for Approval: Step X (Role Name)`.

#### [MODIFY] [product-request/show.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/product-request/show.blade.php)
- Replace manual status dropdown with gated `Approve Requisition` button visible ONLY to assigned step approver.

#### [MODIFY] [stock_transfers/show.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/stock_transfers/show.blade.php)
- Lock completion buttons until step approval is satisfied.

---

## 🧪 Comprehensive Verification Plan

### Manual UI Verification Steps

1. **Test Requisition (SR/PR):**
   - Create PR as Manager -> Verify status is `pending` and manual dropdown is removed.
   - Log in as Admin -> Verify `Approve Requisition` button is visible and works.

2. **Test CS & PO Generation:**
   - Create CS -> Verify status is `pending`.
   - Log in as Manager (`manager@example.com`) -> Verify `Approve CS` and `Generate PO` buttons are **hidden**.
   - Log in as Admin -> Approve CS -> Verify `Generate PO` button is visible for Admin.

3. **Test Purchase Order (PO) Locking:**
   - Generate PO -> Status becomes `pending`.
   - Log in as Manager -> Verify `Shipment`, `Receive Goods`, and `Landed Cost` buttons are **LOCKED / DISABLED**.
   - Log in as Admin -> Click `Approve PO` -> Verify status becomes `approved` and action buttons are unlocked.

4. **Test Stock Transfer, LC, Debit Notes:**
   - Create Transfer / LC / Debit Note -> Verify workflow approval chain is enforced.




