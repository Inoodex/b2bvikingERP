# Phase 1: The Missing Links (Approval Workflow Integration)
**Status:** Planning

This plan outlines the steps required to properly connect the `ApprovalService` (Multi-Level Approval Engine) with the `Order` system (which acts as the Store/Purchase Requisition). This will complete Phase 1 fully as per the User Manual.

## Goal
To ensure that whenever a Frontend User, Outlet, or Admin creates an `Order`, it doesn't instantly become "Approved". Instead, it must pass through the dynamic rules created in `Master Setup > Approval Workflows`.

## Open Questions for You
> [!WARNING]
> 1. Currently, in `OrderController`, the order `status` is set to `pending`. We also have an `approval_status` column. During the multi-level approval process, should the main `status` remain `pending` until it is fully approved, or should we track everything purely via `approval_status`?
> 2. Should we apply this approval engine only to specific Order Types (e.g., only "B2B/Outlet Orders") or to all Orders universally?

---
## Proposed Changes

### 1. Model Implementations

#### [MODIFY] `app/Models/Order.php`
- Add the `Approvable` Interface to the class.
- Add `public function approvals()` relationship (morphMany `Approval::class`).
- Add helper method `public function isFullyApproved()`.

#### [MODIFY] `app/Models/CustomProductRequest.php` (If applicable)
- Add the `Approvable` Interface and `approvals()` relationship.

---
### 2. Controller & Service Connections

#### [MODIFY] `app/Http/Controllers/Frontend/OrderController.php` & `CartController.php`
- After `Order::create(...)` is called, inject the `ApprovalService`.
- Call `$approvalService->submitForApproval($order, $order->total_amount)`.
- If the workflow rule matches, this will set the `approval_status` to `pending` and generate the first step in the `approvals` table.

#### [MODIFY] `app/Http/Controllers/Backend/ProductRequestController.php` (Admin Side Order Creation)
- Admin-created orders should also pass through `$approvalService->submitForApproval()`.

---
### 3. Backend UI for Approvers

#### [MODIFY] `resources/views/backend/orders/show.blade.php`
- Add an "Approval Chain" UI box showing the current status of approvals (e.g., "Pending Manager Approval").
- If the currently logged-in user matches the role required for the *pending* step, show two big buttons: **[Approve]** and **[Reject]**.

#### [NEW] `app/Http/Controllers/Backend/OrderApprovalController.php` (or add to `OrderController`)
- `approve(Request $request, $id)`: Validates user permissions, calls `$approvalService->approveStep($order, Auth::id())`.
- `reject(Request $request, $id)`: Validates user permissions, calls `$approvalService->rejectStep($order, Auth::id(), $reason)`.

---
### 4. Edit / Cancel / Return Functionality (2.5)

#### [MODIFY] Order Cancellation Logic
- Allow the creator (User/Outlet) to cancel the order **only if** the `approval_status` is still `pending`.
- If an order is Rejected by a manager, auto-change the main `status` to `cancelled/rejected`.

## Verification Plan
1. Create a dynamic Workflow Rule: > 5000 DKK requires 'Manager' approval.
2. Place an order from the frontend.
3. Verify that the order is blocked in `pending` state.
4. Login as a Manager and approve it.
5. Verify the order proceeds to processing/approved state.
