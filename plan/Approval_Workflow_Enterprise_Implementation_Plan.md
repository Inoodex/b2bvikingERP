# Master Enterprise Implementation Plan: Dynamic Approval Workflows (Pure Workflow Engine)

This document outlines the strict, pure **Approval Workflow Integration Plan** for all Phase 2 target modules (`Requisition / PR`, `Comparison Statement / CS`, `Purchase Order / PO`, `Stock Transfer`, `Import LC`, `Vendor Return / Debit Note`).

> [!IMPORTANT]
> **Strict Architecture Principle**: Zero static Spatie permission wrappers. All authorization, document locking, and step progression will be driven **100% dynamically** by the `Approval Workflow` database rules (`approval_workflows`, `approval_steps`, `approvals`).

---

## 🎯 Current Database Workflow Status Audit

| Workflow ID | Module Target | Document Type | Step 1 Approver | Step 2 Approver | Min/Max Amount Rule |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **#4** | `App\Models\Order` | Requisition / Order | `Role #6` (Manager) | `Role #1` (Admin) | Min Amount: 5,000 |
| **#5** | `App\Models\ComparisonStatement` | Comparison Statement (CS) | `Role #1` / `User #1` (Admin) | N/A | Min Amount: 0 (All CS) |
| **#6** | `App\Models\Purchase` | Purchase Order (PO) | `Role #1` / `User #1` (Admin) | N/A | Min Amount: 0 (All POs) |

---

## 📋 Pure Workflow Enforcing Architecture (Component by Component)

### Component 1: Centralized Service & Backend Authorization (`ApprovalService.php`)

#### 1. Strict Step Authorization Check (`canUserApproveCurrentStep`)
- Query active pending step in `approvals` table for `$model`.
- Return `true` **ONLY if** the logged-in user matches `approver_user_id` OR has `approver_role_id` assigned to that specific active step.
- **No hardcoded role overrides** (`Admin` role bypass is strictly commented out).

#### 2. Workflow Submission Fallback (`submitForApproval`)
- If an active `ApprovalWorkflow` rule exists for `$model`:
  - Set `$model->approval_status = 'pending'`.
  - Create initial pending entry in `approvals` table for `Step 1`.
- If NO active `ApprovalWorkflow` exists:
  - Auto-approve the model (`$model->approval_status = 'approved'`).

---

### Component 2: Module-by-Module Integration & UI Locking

#### 1. Requisition (SR / PR - `ProductRequest`)
- **Creation (`ProductRequestController@store`)**: Set initial status `$productRequest->status = 'pending'` and invoke `$approvalService->submitForApproval($productRequest)`.
- **View (`product-request/show.blade.php`)**:
  - If `canUserApproveCurrentStep($productRequest)` is `true`: Display `Approve Requisition` action button.
  - If `false`: Hide approval button and display `⏳ Waiting for Approval: Step X (Role Name)`.
  - Disable manual status change dropdowns.

#### 2. Comparison Statement (CS)
- **Creation (`ComparisonStatementController@store`)**: Automatically submits generated CS to `$approvalService->submitForApproval($cs)`.
- **View (`rfq/show.blade.php`)**:
  - `Approve CS` button: Rendered **ONLY** if `canUserApproveCurrentStep($cs)` returns `true`.
  - `Generate PO` button: Rendered **ONLY** if `$cs->approval_status === 'approved'`. (If pending, button is completely hidden).
  - Non-approvers see: `⏳ Waiting for Approval: Step X (Role Name)`.

#### 3. Purchase Order (PO)
- **Creation (`PurchaseOrderController@generateFromCs`)**: When generated from an approved CS, invoke `$approvalService->submitForApproval($po)`.
- **Approval Route (`PurchaseOrderController@approve`)**: Calls `$approvalService->approveStep($po, Auth::id())`.
- **View (`purchase/po_show.blade.php`)**:
  - `Approve PO` button: Rendered **ONLY** if `canUserApproveCurrentStep($po)` returns `true`.
  - Downstream Actions (`Shipment`, `Receive Goods / GRN`, `Landed Cost`): **LOCKED & DISABLED** until `$po->approval_status === 'approved'`.
  - Non-approvers see: `⏳ Waiting for Approval: Step X (Role Name)`.

#### 4. Import LC, Debit Note & Stock Transfer
- Connect `submitForApproval()` on creation and enforce `canUserApproveCurrentStep()` on status completion.

---

## 🧪 Verification & Live Testing Plan

1. **Requisition Test (SR/PR):**
   - Submit PR -> Verify status is `pending` in DB.
   - Log in as Manager -> Verify status banner shows `⏳ Waiting for Approval`.
   - Log in as Admin (Assigned Approver) -> Click `Approve Requisition` -> Verify status becomes `approved`.

2. **CS & PO Test:**
   - Generate CS -> Verify status is `pending`.
   - Log in as Manager (`manager@example.com`) -> Verify `Approve CS` and `Generate PO` buttons are **hidden**, and banner `⏳ Waiting for Approval: Step 1 (Admin)` is shown.
   - Log in as Admin -> Click `Approve CS` -> Verify CS status becomes `approved` and `Generate PO` button appears.

3. **PO Locking Test:**
   - Generate PO -> Status becomes `pending`.
   - Log in as Manager -> Verify `Shipment`, `Receive Goods`, and `Landed Cost` buttons are **LOCKED / DISABLED**.
   - Log in as Admin -> Click `Approve PO` -> Verify status becomes `approved` and downstream action buttons unlock.
