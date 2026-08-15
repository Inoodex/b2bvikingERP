# Phase 3 — Step 3.5 Implementation Plan: Promotional Coupons & Gift Cards Engine

This plan covers the implementation of a full-featured B2B & B2C **Promotional Coupons & Gift Cards Engine** for discount codes, gift card issuance, balance tracking, and redemption.

---

## User Review Required

> [!IMPORTANT]
> - **Coupons vs Discounts Relationship:** Coupons link to the existing `Discount` model (`coupons.discount_id`), allowing coupons to specify percentage (%) or flat amount discounts, minimum order threshold, and usage limits.
> - **Gift Card Ledger & Transactions:** Every time a Gift Card is issued, redeemed, or refunded, a transaction record is logged in `gift_card_transactions` table to maintain full audit transparency.

---

## Proposed Changes

### Component 1: Promotional Coupons Management

#### [NEW] [CouponDataTable.php](file:///c:/laragon/www/b2bvikingErp/app/DataTables/CouponDataTable.php)
- Server-side Yajra DataTable listing all promotional coupon codes (`code`, `discount_info`, `usage_limit`, `used_count`, `expires_at`, `status`, `action`).

#### [NEW] [CouponController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/CouponController.php)
- CRUD operations for Coupons (Create, Edit, Delete, Toggle Status).
- Validation endpoint `validateCoupon(Request $request)` returning JSON `{ status: 'success', discount_amount: XX.XX, discount_type: '...' }`.

#### [NEW] [index.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/coupons/index.blade.php)
- Coupon listing view with Yajra DataTable.

#### [NEW] [create.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/coupons/create.blade.php)
- Coupon creation form (Auto-generate random codes, assign discount rules, usage limits, expiration date).

#### [NEW] [edit.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/coupons/edit.blade.php)
- Coupon edit form.

---

### Component 2: Gift Cards & Ledger Engine

#### [NEW] [GiftCardDataTable.php](file:///c:/laragon/www/b2bvikingErp/app/DataTables/GiftCardDataTable.php)
- Server-side Yajra DataTable listing Gift Cards (`code`, `initial_value`, `balance`, `currency`, `expires_at`, `status`, `action`).

#### [NEW] [GiftCardController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/GiftCardController.php)
- CRUD operations for Gift Cards (Issuance, Balance Adjustment, View Transaction Ledger).
- Validation endpoint `validateGiftCard(Request $request)` returning JSON `{ status: 'success', balance: XX.XX }`.

#### [NEW] [GiftCardService.php](file:///c:/laragon/www/b2bvikingErp/app/Services/GiftCard/GiftCardService.php)
- Business logic for issuing gift cards, deducting balance on redemption, and recording transactions in `gift_card_transactions`.

#### [NEW] [index.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/gift_cards/index.blade.php)
- Gift Card listing view with Yajra DataTable.

#### [NEW] [create.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/gift_cards/create.blade.php)
- Gift Card issuance form with auto-generated unique card code (e.g. `GC-8849-2910-4821`).

#### [NEW] [show.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/gift_cards/show.blade.php)
- Gift Card detail view showing card balance & complete transaction history ledger.

---

### System Integration & Navigation

#### [MODIFY] [web.php](file:///c:/laragon/www/b2bvikingErp/routes/web.php)
- Register `admin.coupons.*` and `admin.gift-cards.*` resource routes and status toggle endpoints.

#### [MODIFY] [navbar.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/layouts/navbar.blade.php)
- Add `Promotional Coupons` and `Gift Cards` links under Marketing / Discounts menu.

---

## Verification Plan

### Automated Tests
- Syntax verification across all updated and new PHP files via `php -l`.
- Verification script `scratch/test_coupons_and_giftcards.php` testing coupon validation, usage count tracking, and gift card redemption ledger.

### Manual Verification
- Issue a Gift Card with `kr. 1000.00` balance.
- Verify that redeeming `kr. 250.00` logs a transaction and leaves a remaining balance of `kr. 750.00`.
- Create a coupon code `WELCOME2026` with 10% discount and test validation.
