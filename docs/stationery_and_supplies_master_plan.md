# 📦 Master Technical Plan: Stationery & Supplies Management Module
**Project:** B2B Viking ERP  
**Module Name:** `Stationery & Supplies`  
**Classification:** Internal Warehouse & Office Consumables Management  
**Location:** `docs/stationery_and_supplies_master_plan.md` (Referenced as `docs/Stationery & Supplies.md`)  
**Status:** Ready for Review & Implementation  

---

## 1. Executive Summary & Problem Statement

### The Problem:
In the daily operations of B2B Viking ERP, warehouse staff and office personnel continuously consume operating materials:
- **Warehouse Packaging & Shipping Materials:** Shipping cartons, packaging tapes, bubble wrap, stretch film, security seals, thermal barcode roll stickers, invoice plastic pouches.
- **Office & Administrative Supplies:** A4 printer paper, invoice books, toner cartridges, pens, markers, file folders.

Currently, these items are bought without centralized tracking. There is no automated record of how many cartons or tapes remain in stock, when to reorder, which department consumes them, or what the monthly operating packaging expense actually is. This poses a major operational risk: **running out of shipping cartons or barcode labels halts warehouse shipments.**

### The Solution:
A dedicated, enterprise-grade **`Stationery & Supplies`** module designed specifically for **internal operational consumables**.
- **100% Isolated from Commercial Catalog:** Will never leak into customer B2B catalogs, price lists, or web portal orders.
- **Automated Weighted Average Cost (WAC):** Calculates precise average purchase cost and live inventory valuation according to standard accounting principles.
- **Departmental Cost Allocation:** Tracks consumption directly to Cost Centers (e.g., Warehouse & Inventory Packing: 75%, Shipping & Logistics: 15%, Accounts & Admin: 10%).
- **Negative-Stock Prevention Guard:** Blocks requests exceeding physical stock.
- **Automated Reorder Badges:** Real-time visual alerts (🔴 Out of Stock, 🟡 Low Stock Alert, 🟢 Healthy).
- **Future-Ready Commercial Switch:** An `allow_sale` flag is baked into the architecture, enabling any supply item to be sold to commercial customers in the future with zero refactoring.

---

## 2. System Architecture & Information Flow

```mermaid
flowchart TD
    Vendor["Suppliers & Vendors (Packing / Stationery)"] -->|Purchase Order & Inward| PurchaseModule["Stationery Purchases (Stock In)"]
    
    subgraph Engine ["Automated Calculations & Inventory Engine"]
        PurchaseModule -->|Increments Stock & Calculates WAC Cost| Stock["Stationery Stock Master"]
        Stock -->|Live Valuation = Stock × Unit Cost| Valuation["Inventory Valuation KPI"]
        Stock -->|Evaluates Stock <= Min Alert Qty| HealthCheck["Health Status Badges (🔴 / 🟡 / 🟢)"]
        HealthCheck --> ReorderNotification["Low Stock Reorder Alert"]
    end
    
    Stock -->|Department Issue Voucher (Stock Out)| ConsumptionModule["Department Consumption (Stock Out)"]
    
    subgraph Departments ["Consuming Cost Centers"]
        D1["Warehouse & Inventory Packing"]
        D2["Shipping & Logistics"]
        D3["Finance & Accounts"]
        D4["Administration & Office"]
    end
    
    ConsumptionModule --> Departments
    ConsumptionModule -->|Deducts Physical Stock| Stock
    ConsumptionModule -->|Logs Audit Trail (Before/After)| AuditLedger["Stationery Stock Ledger"]
    ConsumptionModule -->|Issue Value = Qty × Unit Cost| ExpenseReporting["Monthly Department Expense Accounting"]
```

---

## 3. Mathematical Calculations Engine

### A. Weighted Average Cost (WAC) on Inward Purchases
Whenever a new batch of stationery or cartons is purchased at a new unit price:
$$\text{New Unit Cost} = \frac{(\text{Current Stock} \times \text{Current Unit Cost}) + (\text{New Inward Qty} \times \text{Purchase Unit Price})}{\text{Current Stock} + \text{New Inward Qty}}$$

*Example:*
- Current Stock: 50 rolls at Kr. 10.00 = Kr. 500.00
- New Purchase: 100 rolls at Kr. 13.00 = Kr. 1,300.00
- Total Qty: 150 rolls
- New WAC Unit Cost: $\frac{500 + 1300}{150} = \text{Kr. } 12.00$ per roll.

### B. Stock Deduction & Department Expense Valuation
When stationery is issued to a department:
$$\text{Department Issue Expense} = \text{Issued Quantity} \times \text{Current Unit Cost}$$
$$\text{New Remaining Stock} = \text{Previous Stock} - \text{Issued Quantity}$$

*Safety Guard:*
$$\text{If } \text{Issued Quantity} > \text{Current Stock} \implies \text{BLOCK TRANSACTION (Throw Exception)}$$

### C. Live Stock Health Status
For any item $i$:
- 🔴 **Out of Stock (Urgent):** $\text{Current Stock} = 0$
- 🟡 **Low Stock (Reorder Needed):** $0 < \text{Current Stock} \le \text{Min Alert Qty}$
- 🟢 **Adequate / Healthy:** $\text{Current Stock} > \text{Min Alert Qty}$

### D. Inventory Valuation & Department Share
- **Total In-Stock Valuation:** $\sum_{i=1}^{n} (\text{Current Stock}_i \times \text{Unit Cost}_i)$
- **Monthly Consumption Expense:** $\sum (\text{All Issue Values within calendar month})$
- **Department Expense Ratio (%):** $\frac{\text{Department Monthly Expense}}{\text{Total Monthly Expense}} \times 100$

---

## 4. Database Schema Specification

### 1. Table: `stationery_items`
| Column | Type | Nullable | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK AI | No | Primary Key |
| `item_code` | VARCHAR(50) | No | Unique Code (e.g. `ST-0001`, `ST-TAPE-01`) |
| `name` | VARCHAR(255) | No | Item Name (e.g. "Brown Packaging Tape 48mm x 66m") |
| `category` | VARCHAR(50) | No | `packaging`, `office_supplies`, `printing_labels`, `shipping_materials`, `warehouse_tools`, `other` |
| `unit_id` | BIGINT FK | Yes | Foreign Key to `units` table |
| `unit_name` | VARCHAR(50) | No | Fallback string e.g. "Roll", "Carton", "Pcs", "Ream" |
| `current_stock` | DECIMAL(10,2) | No | Default `0.00`. Real-time physical on-hand quantity |
| `unit_cost` | DECIMAL(10,2) | No | Default `0.00`. Weighted average cost in base currency (Kr.) |
| `min_alert_qty` | DECIMAL(10,2) | No | Default `10.00`. Threshold triggering Low Stock Alert |
| `allow_sale` | BOOLEAN | No | Default `false`. If toggled `true`, available for commercial sale |
| `status` | BOOLEAN | No | Default `true` (Active / Inactive) |
| `notes` | TEXT | Yes | Specification, carton dimensions, supplier ref notes |
| `timestamps` | TIMESTAMP | Yes | `created_at`, `updated_at`, `deleted_at` (SoftDeletes) |

---

### 2. Table: `stationery_purchases` & `stationery_purchase_items`
#### Header: `stationery_purchases`
- `id` (PK)
- `purchase_no` (Unique, e.g. `ST-PO-2026-0001`)
- `vendor_id` (FK to `vendors`, nullable for local market cash purchases)
- `vendor_name_custom` (String, nullable fallback vendor/shop name)
- `purchase_date` (Date)
- `invoice_no` (String, nullable vendor bill/memo number)
- `total_amount` (Decimal 12,2)
- `payment_status` (`paid`, `partial`, `unpaid`)
- `paid_amount` (Decimal 12,2, default 0)
- `created_by` (FK to `users`)
- `notes` (Text, nullable)
- `timestamps`, SoftDeletes

#### Lines: `stationery_purchase_items`
- `id` (PK)
- `stationery_purchase_id` (FK)
- `stationery_item_id` (FK to `stationery_items`)
- `quantity` (Decimal 10,2)
- `unit_price` (Decimal 10,2)
- `subtotal` (Decimal 12,2)
- `timestamps`

---

### 3. Table: `stationery_consumptions` & `stationery_consumption_items`
#### Header: `stationery_consumptions`
- `id` (PK)
- `issue_no` (Unique, e.g. `ST-ISS-2026-0001`)
- `department_id` (FK to existing `departments` table, nullable)
- `department_name` (String fallback: "Warehouse & Inventory Packing", "Finance & Accounts", etc.)
- `issued_to` (String, receiver name / employee)
- `issue_date` (Date)
- `total_cost` (Decimal 12,2)
- `created_by` (FK to `users`)
- `purpose_notes` (Text, nullable)
- `timestamps`, SoftDeletes

#### Lines: `stationery_consumption_items`
- `id` (PK)
- `stationery_consumption_id` (FK)
- `stationery_item_id` (FK to `stationery_items`)
- `quantity` (Decimal 10,2)
- `unit_cost` (Decimal 10,2 - captured snapshot of WAC at issue time)
- `subtotal` (Decimal 12,2)
- `timestamps`

---

### 4. Table: `stationery_stock_ledgers`
- `id` (PK)
- `stationery_item_id` (FK)
- `transaction_type` (Enum: `purchase_in`, `consumption_out`, `manual_adjustment_in`, `manual_adjustment_out`)
- `reference_id` (Nullable ID)
- `reference_no` (String: PO Number or Issue Voucher Number)
- `date` (Date)
- `stock_before` (Decimal 10,2)
- `qty_in` (Decimal 10,2, default 0)
- `qty_out` (Decimal 10,2, default 0)
- `stock_after` (Decimal 10,2)
- `unit_cost` (Decimal 10,2)
- `total_value` (Decimal 12,2)
- `created_by` (FK to `users`)
- `notes` (Text, nullable)
- `timestamps`

---

## 5. UI / UX Design & Navigation Structure

### Sidebar Navigation Integration
Placed prominently in the admin sidebar under a dedicated parent menu:
```
📁 Stationery & Supplies [Icon: fas fa-pencil-ruler]
  ├── 📋 Items Master & Stock (Current stock, valuation & health alerts)
  ├── 📥 Purchases / Stock In (Purchase vouchers & receipt history)
  ├── 📤 Department Issues (Consumption logging & stock out)
  └── 📊 Expense & Audit Ledger (Monthly cost breakdown & stock movement audit)
```

### Dashboard Top KPI Cards:
1. **Total Stationery Items:** Total registered active items.
2. **Current Stock Valuation:** Total monetary value of supplies currently sitting in the warehouse (Kr.).
3. **Low Stock & Depleted Alerts:** Badge count of items below `min_alert_qty` or at 0 stock.
4. **This Month's Consumption Expense:** Total Kr. issued/consumed across departments this calendar month.

---

## 6. Implementation Steps & File Structure

### Phase 1: Database Migrations & Eloquent Models
1. `database/migrations/2026_11_08_000001_create_stationery_items_table.php`
2. `database/migrations/2026_11_08_000002_create_stationery_purchases_table.php`
3. `database/migrations/2026_11_08_000003_create_stationery_consumptions_table.php`
4. `database/migrations/2026_11_08_000004_create_stationery_stock_ledgers_table.php`
5. Models in `app/Models/`:
   - `StationeryItem.php`
   - `StationeryPurchase.php` & `StationeryPurchaseItem.php`
   - `StationeryConsumption.php` & `StationeryConsumptionItem.php`
   - `StationeryStockLedger.php`

### Phase 2: Core Calculation Service
- `app/Services/StationeryInventoryService.php`
  - Encapsulates Weighted Average Cost calculation, atomic database transactions, stock deduction validation, and ledger logging.

### Phase 3: Backend Controller & Routes
- `app/Http/Controllers/Backend/StationeryController.php`
- Web routes in `routes/web.php` under prefix `admin/stationery-supplies`.

### Phase 4: Blade Views & Sidebar
- `resources/views/backend/layouts/navbar.blade.php` (Sidebar item with active state highlighting)
- `resources/views/backend/stationery/index.blade.php` (Master list & KPI cards)
- `resources/views/backend/stationery/purchases/index.blade.php` & `create.blade.php`
- `resources/views/backend/stationery/consumptions/index.blade.php` & `create.blade.php`
- `resources/views/backend/stationery/ledger.blade.php` (Audit movement history)

### Phase 5: Verification & Automated Tests
- Artisan tinker test script verifying:
  - Stock increment and WAC cost calculation
  - Stock deduction upon department issue
  - Negative stock block exception
  - Health alert badge status transitions
  - UI rendering and permission check

---

## 7. Approval & Sign-Off
Once approved by user, execution proceeds according to this specification.

