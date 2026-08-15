# Phase 3 — Step 3.4 Implementation Plan: User Management Polish & Customer Pricelists Engine

This plan covers two tightly integrated components:
1. **Part A — User Management Polish:** Adding `Credit Limit ($ / kr.)` input/column and `Customer Segment` selector (`Retail`, `Wholesale`, `B2B VIP`, `Distributor`) to User Create/Edit forms and `UsersDataTable.php`.
2. **Part B — Customer Pricelists & Dynamic Pricing Tiers Engine (Step 3.4):** Building full CRUD for Pricelists, Pricelist Items price grid per customer segment, dynamic auto-price resolver service, AJAX price resolution endpoint, and live auto-pricing integration in Sales Quotation & Sales Order creation forms.

---

## User Review Required

> [!IMPORTANT]
> - **Pricelist Hierarchy & Fallback Rule:** If a customer segment (e.g. `Wholesale`) has no custom price defined for a specific product, the system will automatically fall back to the default Product Base MRP price (`products.price`).
> - **User Management Polish Integration:** Commercial fields (`Credit Limit` & `Customer Segment`) will be saved on the `users` table directly to maintain zero-breaking-change integration with all 19 Phase 3 models.

---

## Proposed Changes

### Part A: User Management Polish

#### [MODIFY] [UsersDataTable.php](file:///c:/laragon/www/b2bvikingErp/app/DataTables/UsersDataTable.php)
- Add `Credit Limit` column formatted as `kr. XX,XXX.XX`.
- Add `Customer Segment` column formatted as color badges (`Retail` = Secondary, `Wholesale` = Info, `B2B VIP` = Warning, `Distributor` = Primary).

#### [MODIFY] [UserService.php](file:///c:/laragon/www/b2bvikingErp/app/Services/User/UserService.php)
- Update `createUser()` and `updateUser()` methods to process and save `credit_limit` and `customer_segment`.

#### [MODIFY] [create.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/authorization/users/create.blade.php)
- Add `Credit Limit ($ / kr.)` input field.
- Add `Customer Segment` dropdown select (`Retail`, `Wholesale`, `B2B VIP`, `Distributor`).

#### [MODIFY] [edit.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/authorization/users/edit.blade.php)
- Add `Credit Limit ($ / kr.)` input field pre-filled with `$user->credit_limit`.
- Add `Customer Segment` dropdown select pre-filled with `$user->customer_segment`.

---

### Part B: Customer Pricelists & Dynamic Pricing Tiers (Step 3.4)

#### [NEW] [PricelistDataTable.php](file:///c:/laragon/www/b2bvikingErp/app/DataTables/PricelistDataTable.php)
- Server-side Yajra DataTable for listing Pricelists (`name`, `customer_segment`, `region`, `validity_period`, `status`, `actions`).

#### [NEW] [PricelistController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/PricelistController.php)
- CRUD operations for Pricelists and Pricelist Items.
- AJAX price resolution endpoint `resolvePrice(Request $request)` returning JSON `{ price: XX.XX, pricelist_name: '...' }`.

#### [NEW] [PricelistResolverService.php](file:///c:/laragon/www/b2bvikingErp/app/Services/Pricing/PricelistResolverService.php)
- Service layer resolving effective product price for a specific customer based on customer's `customer_segment` and active validity date.

#### [NEW] [index.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/pricelist/index.blade.php)
- List view with Yajra DataTable for Pricelists.

#### [NEW] [create.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/pricelist/create.blade.php)
- Form to create Pricelist with dynamic product pricing grid.

#### [NEW] [edit.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/pricelist/edit.blade.php)
- Form to edit existing Pricelist items and tier prices.

#### [MODIFY] [web.php](file:///c:/laragon/www/b2bvikingErp/routes/web.php)
- Register `admin.pricelists.*` resource routes and `/admin/pricelists/resolve-price` route.

#### [MODIFY] [navbar.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/layouts/navbar.blade.php)
- Add `Pricelists` navigation link under Orders / Pricing menu.

#### [MODIFY] [create.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/sales_quotation/create.blade.php)
- Integrate live AJAX auto-pricing lookup when Customer & Product are selected.

---

## Verification Plan

### Automated Tests
- Syntax verification across all updated and new PHP files via `php -l`.
- Verification script `scratch/test_pricelist_resolver.php` testing price resolution for `Retail`, `Wholesale`, `B2B VIP`, and `Distributor` customers.

### Manual Verification
- Create a `Wholesale` Pricelist with custom discounted prices for Product A & B.
- Assign a customer `Customer Segment = Wholesale`.
- Create a Sales Quotation for that customer and verify that Product A & B load the wholesale price automatically!
