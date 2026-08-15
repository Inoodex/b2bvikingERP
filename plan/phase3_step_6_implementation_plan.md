# Phase 3 — Step 3.6 Implementation Plan: Enterprise Sales Orders & Customer Credit Limit Validation Engine

This plan implements a **Tier-1 Enterprise Sales Order Engine (SAP S/4HANA SD / Odoo 17 Parity)** with built-in **Customer Credit Limit Validation & Credit Hold Workflow**.

---

## User Review Required

> [!IMPORTANT]
> - **Enterprise Credit Exposure Check:**
>   Before an order is saved or processed, the system calculates the customer's total exposure:
>   $$\text{Total Exposure} = \text{Current Outstanding Unpaid Dues} + \text{New Order Total}$$
>   If $\text{Total Exposure} > \text{Customer Credit Limit}$, the order status is automatically assigned to **`credit_hold`**.
> - **Credit Hold Release Authorization:**
>   Orders in `credit_hold` cannot proceed to fulfillment until a Credit Manager / Admin grants an authorized **Credit Override Release** with an audit reason.

---

## Proposed Changes

### Core Engine & Services

#### [NEW] [CreditValidationService.php](file:///c:/laragon/www/b2bvikingErp/app/Services/Credit/CreditValidationService.php)
- Calculates a customer's current credit limit, total unpaid outstanding dues across past orders (`due_amount`), and new order total.
- Determines whether credit limit is exceeded (`isExceeded()`) and returns detailed exposure breakdown `{ credit_limit: XX, current_dues: YY, new_order_total: ZZ, remaining_credit: AA, status: 'approved'|'credit_hold' }`.

#### [NEW] [SalesOrderDataTable.php](file:///c:/laragon/www/b2bvikingErp/app/DataTables/SalesOrderDataTable.php)
- Server-side Yajra DataTable listing all Sales Orders (`order_no`, `customer`, `date`, `total_amount`, `credit_status`, `order_status`, `payment_status`, `actions`).

#### [NEW] [SalesOrderController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/SalesOrderController.php)
- Full enterprise CRUD for Sales Orders (`admin.sales-orders.*`).
- Integrates `CreditValidationService` on creation/update.
- Endpoint `releaseCreditHold(Request $request, Order $order)` to authorize credit hold override.
- Endpoint `checkCustomerCredit(Request $request)` returning live AJAX JSON credit check for frontend & admin order creation forms.

---

### UI Views & Navigation

#### [NEW] [index.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/sales_orders/index.blade.php)
- Sales Order listing view with Yajra DataTable and status filter tabs (`All`, `Draft`, `Credit Hold`, `Pending Approval`, `Approved`, `Processing`, `Delivered`).

#### [NEW] [create.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/sales_orders/create.blade.php)
- Admin Sales Order creation form with live credit exposure widget displaying customer's remaining credit line in real-time.

#### [NEW] [show.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/sales_orders/show.blade.php)
- Enterprise Sales Order detail view with customer credit exposure breakdown, item lines table, financial summary, and Credit Hold Release action modal.

#### [MODIFY] [web.php](file:///c:/laragon/www/b2bvikingErp/routes/web.php)
- Register `admin.sales-orders.*` resource routes, credit release endpoint, and live credit check AJAX route.

#### [MODIFY] [navbar.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/layouts/navbar.blade.php)
- Update `Sales Orders (SO)` navigation link to point to `admin.sales-orders.index`.

---

## Verification Plan

### Automated Tests
- Syntax verification across all updated and new PHP files via `php -l`.
- Verification script `scratch/test_sales_order_credit_validation.php` testing credit limit validation, exposure math, credit hold assignment, and credit release workflow.

### Manual Verification
- Set Customer A's `Credit Limit = kr. 10,000.00`.
- Create a Sales Order for Customer A with total `kr. 15,000.00`.
- Verify that the order is automatically flagged as **`credit_hold`**.
- Log in as Credit Manager / Admin and perform **Release Credit Hold**: verify order transitions to approved state cleanly.
