# 🏛️ Enterprise Master Plan: B2B Customer & Outlet Stock Availability Matrix

**Module:** B2B Customer & Outlet Stock Visibility Hub (Phase 2 - Module 7 Enterprise Upgrade)  
**Document Code:** `PLAN-B2B-VISIBILITY-001`  
**Status:** Approved for Implementation  
**Target:** Viking ERP Admin & B2B Buyer Portal  
**Architecture:** Server-Side Yajra DataTable, Composite Indexing, Zero-Lag Remote Lookup  

---

## 1. Business Context & Audio 4 Transcript

> **🎙️ Audio 4 Transcript:**  
> *"আরেকটা বিষয় হচ্ছে ভাই, যেটা আমরা ফেস করি সেটা হচ্ছে এখানে তো মনে করেন বিভিন্ন ভেন্ডর থেকে আমরা প্রোডাক্ট নেই। অনেক ভেন্ডর কি করে আমাদেরকে এটা আউট অফ স্টক দেখায়, কিন্তু অন্যদের ঠিকই এটা হচ্ছে ইন স্টক দেখায়। আবার আমাদেরকে ইন স্টক দেখায়, অন্যদেরকে আউট অফ স্টক দেখায়। তো এরকম কাস্টমাইজ করে রাখার অপশনটাও যেন থাকে। যে আমি চাইলেই কোনো একটা ফোন নাম্বার, কোনো একটা কোম্পানির নাম, কোনো একটা আউটলেটের নাম এটাকে চাইলে হচ্ছে কিছু প্রোডাক্ট ইন স্টক দেখাবো, কিছু প্রোডাক্ট আবার হচ্ছে আউট অফ স্টক দেখাবো।"*

### Business Realities in B2B Wholesale:
1. **VIP Priority Allocation:** Certain scarce products are reserved for contract buyers or VIP retail outlets. Even if physical stock is near zero or regular buyers see "Out of Stock", the VIP client must see "In Stock / Available for Booking".
2. **Selective Territory / Dealer Blacklisting:** Certain vendor contracts restrict sales in specific regions or to competing outlets. Viking ERP must selectively flag these products as "Out of Stock" (or hidden) for those specific accounts without disrupting general stock for others.
3. **Credit Limit Protection:** If a buyer has overdue AR balances, high-ticket products can be selectively marked as "Out of Stock" to restrict them from booking further stock.

---

## 2. Navigation & Sidebar Menu Location

In Viking ERP, custom B2B commercial policies live under Commercial Sales.  
The primary entry point is strategically placed right beside **Customer Pricelists**:

### Primary Menu Placement (`navbar.blade.php`):
* **Menu:** `Orders & Sales`  
* **Sub-Header:** `Pricing & Promotions`  
* **Menu Item:** `Customer Stock Rules` (Route: `admin.b2b-stock-rules.index`)  
  *(Sibling to `Customer Pricelists` `admin.pricelists.index`)*

### Secondary Cross-Link (`navbar.blade.php`):
* Under **`Products`** submenu: Quick link `Customer Visibility Rules` for Product Managers who configure catalog permissions while managing products.

---

## 3. High-Performance Architecture (Page Speed & Scale Guarantees)

To guarantee the page **never slows down** even with 50,000 products, 5,000 customers, and 100,000 active rules:

### A. Server-Side Yajra DataTable (`B2bVisibilityDataTable.php`):
- **Zero Client-Side Bloat:** The browser never downloads all records. Only 25 to 50 active rows are transferred per AJAX page request.
- **Eager Loading Optimization:** Single-pass SQL join with `with(['product', 'company', 'outlet', 'user', 'creator'])` to eliminate N+1 database queries.
- **Composite Indexing:** Every database lookup hits pre-built composite indexes:
  - `INDEX idx_prod_company (product_id, company_id)`
  - `INDEX idx_prod_outlet (product_id, outlet_id)`
  - `INDEX idx_prod_user (product_id, user_id)`
  - `INDEX idx_phone (phone_number)`

### B. Remote AJAX Select2 for Customer & Product Search:
- Company, Outlet, and Customer pickers do NOT dump thousands of HTML `<option>` tags.
- They query `admin/b2b-stock-rules/search-accounts` asynchronously with 300ms debounce as the user types.

---

## 4. Multi-Filter Engine (100% Functional Filters)

Every filter is bound to the server-side query builder with instant `table.draw()` without reloading the page:

| Filter Control | UI Type | Backend SQL Query Logic | Performance Index Used |
| :--- | :--- | :--- | :--- |
| **Target Company** | Select2 AJAX Dropdown | `$query->when($req->company_id, fn($q) => $q->where('company_id', $req->company_id))` | `idx_prod_company` |
| **Target Outlet** | Select2 AJAX Dropdown | `$query->when($req->outlet_id, fn($q) => $q->where('outlet_id', $req->outlet_id))` | `idx_prod_outlet` |
| **Availability Rule Mode** | Pill Badges / Select | `$query->when($req->visibility_mode, fn($q) => $q->where('visibility_mode', $req->visibility_mode))` | Indexed Enum |
| **Product Category** | Select2 Dropdown | `$query->when($req->category_id, fn($q) => $q->whereHas('product', fn($p) => $p->where('category_id', $req->category_id)))` | `products.category_id` |
| **Search Bar** | Debounced Text Input | Searches Product Name, SKU, Barcode, Company Name, Outlet Name, Buyer Phone, or Notes | Composite Fulltext & B-Tree |
| **Date Range** | Date Range Picker | Filter rules created within specific time windows | `created_at` Index |

---

## 5. The 3-Tier Enterprise Interfaces

```
                                  ┌──────────────────────────────────────────────────────────┐
                                  │       B2B Stock Availability Master Engine               │
                                  │             (CustomerProductVisibility)                  │
                                  └────────────────────────────┬─────────────────────────────┘
                                                               │
                ┌──────────────────────────────────────────────┼──────────────────────────────────────────────┐
                ▼                                              ▼                                              ▼
┌─────────────────────────────────────────┐  ┌─────────────────────────────────────────┐  ┌─────────────────────────────────────────┐
│     TIER 1: Central Management Hub      │  │      TIER 2: Catalog Bulk Actions       │  │     TIER 3: Contextual Drawer Override  │
│    (admin/b2b-stock-rules)              │  │         (admin/products)                │  │            (Product Drawer)           │
├─────────────────────────────────────────┤  ├─────────────────────────────────────────┤  ├─────────────────────────────────────────┤
│ • Sidebar: Orders > Customer Stock Rules│  │ • Multi-select checkboxes on products   │  │ • Quick 1-product override            │
│ • Mode A: Customer-Centric Matrix       │  │ • Top action: "Set B2B Availability"    │  │ • Segmented Target Scope (Co/Out/Tel)   │
│ • Mode B: Master Rules Yajra DataTable  │  │ • Bulk assign 10-50 items in 1 click    │  │ • Real-time override table            │
│ • 100% Server-Side Search & Filters     │  │ • Instant modal assignment              │  │ • Zero horizontal scroll               │
└─────────────────────────────────────────┘  └─────────────────────────────────────────┘  └─────────────────────────────────────────┘
```

### 🏢 Tier 1: Dedicated Central Hub (`admin/b2b-stock-rules`)
- **Dual-Mode Workspace:**
  1. **Mode A: Customer-First Availability Grid:**
     - Select Company, Outlet, or Buyer Phone.
     - Live matrix displays all catalog items with their physical warehouse stock and effective status for this customer.
     - Instant AJAX Status Toggle:
       - 🟢 `Priority Available (In Stock)`
       - 🔴 `Restricted (Out of Stock)`
       - ⚪ `Standard (Warehouse Real Stock)`
  2. **Mode B: Master Active Overrides Server-Side DataTable:**
     - Server-side paginated ledger showing all active rules across the ERP.
     - Real-time inline deletion and editing with Toastr confirmations.

### 📦 Tier 2: Catalog Multi-Select Bulk Actions (`admin/products`)
- Checkbox selection on product cards / rows in `product_grid.blade.php`.
- Floating bottom toolbar appears upon selecting $\ge 1$ product.
- Modal: Select Target Buyer(s) / Outlets, choose Rule (`Priority In-Stock`, `Restricted OOS`, `Hidden`), and apply in 1 atomic transaction.

### ⚡ Tier 3: Contextual Drawer Tab 3 (Completed in Sprint 1)
- Modernized layout inside `#stockMovementDrawer`.
- Segmented target scope switcher (`Company`, `Outlet`, `Buyer / Phone`).
- Zero horizontal scrollbar, clean pastel badges, and instant row fade on deletion.

---

## 6. Phased Implementation Roadmap

| Phase | Milestone | Deliverables | Target Timeline |
| :---: | :--- | :--- | :---: |
| **Phase 1** | **Tier 3 Drawer Redesign** *(Done)* | Modernized Tab 3 layout, scope switcher, clean badges, zero horizontal scrollbar | Completed |
| **Phase 2** | **Central Hub Controller & Route** | `B2bProductVisibilityController`, routes under `/admin/b2b-stock-rules`, navbar integration in `Orders > Pricing & Promotions` | Sprint 2.1 |
| **Phase 3** | **Yajra Server-Side DataTable & Filters** | `B2bVisibilityDataTable.php` with all 5 working filters (Company, Outlet, Rule, Category, Search) | Sprint 2.2 |
| **Phase 4** | **Customer-Centric Interactive Matrix** | AJAX matrix view by customer with live 1-click status toggles | Sprint 2.3 |
| **Phase 5** | **Catalog Multi-Select Bulk Bar** | Product card checkboxes, floating bulk toolbar, batch store API endpoint | Sprint 2.4 |
| **Phase 6** | **Automated Testing Suite** | Full feature tests verifying single-product, bulk assignment, and storefront resolution | Sprint 2.5 |
