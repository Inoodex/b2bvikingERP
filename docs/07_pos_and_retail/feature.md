# 🏬 Spec: Point of Sale (POS) & Retail Outlet Management Module
**Module:** `07_pos_and_retail`  
**Phase:** Phase 7 (POS & Multi-Outlet Retail Commerce)  
**Status:** Approved Specification  
**Document Standard:** Spec-Driven Development (SDD) Specification

---

## 1. 📌 Executive Summary & Business Objective

Copenhagen Tourist Point operates a Central Distribution Warehouse alongside multiple physical retail tourist outlets (e.g., Nyhavn, Tivoli, Airport Kiosks). 

This module bridges **Back-Office Supply Chain** with **Front-Line Retail Counter Sales**:
1. **Dual Operations for Outlets**:
   - **Internal Procurement**: Outlets order stock from the Central Warehouse at wholesale internal transfer pricing (`outlet_price`) using the existing Frontend Account Portal (`/account?panel=order-form`).
   - **Retail Counter Sales (POS)**: Cashiers scan barcodes and sell products to walk-in tourists at standard retail pricing (`price`) via a dedicated, distraction-free **POS Terminal**.
2. **Security & Role Isolation**:
   - Outlets and Cashiers remain **100% blocked from the `/admin` dashboard**.
   - Store operations (Live Store Stock, Challan Receive, POS Launch, Shift Reports) live cleanly inside the **Frontend Account Portal (`/account`)**.
3. **Automated Real-Time Inventory & Accounting**:
   - Counter sales instantly deplete the specific outlet's inventory (`InventoryStock` where `outlet_id = $cashier->outlet_id`) via FIFO batches.
   - Background General Ledger (Phase 5 COA) double-entry posting recognizes cash/card in hand, sales revenue, VAT, and COGS.

---

## 2. 🏛️ System Topology & User Experience

```
┌────────────────────────────────────────────────────────────────────────┐
│                        B2B VIKING ARCHITECTURE                         │
├──────────────────────────────────┬─────────────────────────────────────┤
│ 1. HEAD OFFICE ERP (/admin)      │ 2. OUTLET ACCOUNT PORTAL (/account) │
│    - Global Inventory Management │    - Outlet Manager & Cashier       │
│    - Purchase & LC Imports       │    - My Store Live Inventory        │
│    - Financial Accounting & P&L  │    - Warehouse Requisitions (DS-)   │
│    - Multi-Branch Monitoring     │    - Receive Inward Challans        │
│    - Consolidated Z-Reports      │    - Shift Reports & Cash Drop      │
└──────────────────────────────────┴──────────────────┬──────────────────┘
                                                      │
                                           ┌──────────▼──────────┐
                                           │ 3. POS TERMINAL UI  │
                                           │    (/outlet/pos)    │
                                           ├─────────────────────┤
                                           │ • Barcode Scanner   │
                                           │ • Touch Product Grid│
                                           │ • Cash/Card/Mobile  │
                                           │ • 80mm Thermal Slip │
                                           │ • Manager PIN Modal │
                                           └─────────────────────┘
```

---

## 3. 🗄️ Database Schema & Data Modeling

### 3.1 `pos_registers` (Physical Cash Registers per Outlet)
Tracks physical terminal hardware at each outlet:
```sql
CREATE TABLE pos_registers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    outlet_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL, -- e.g. 'Counter 1', 'Kiosk 2'
    code VARCHAR(50) UNIQUE NOT NULL, -- 'REG-NYH-01'
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id) ON DELETE CASCADE
);
```

### 3.2 `pos_shifts` (Cash Register Sessions & Daily Z-Reports)
Tracks opening float, sales volume, and cash reconciliation per cashier:
```sql
CREATE TABLE pos_shifts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    register_id BIGINT UNSIGNED NOT NULL,
    outlet_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL, -- Cashier
    opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL,
    opening_cash DECIMAL(12,2) DEFAULT 0.00,
    closing_cash_expected DECIMAL(12,2) DEFAULT 0.00,
    closing_cash_counted DECIMAL(12,2) DEFAULT 0.00,
    cash_discrepancy DECIMAL(12,2) DEFAULT 0.00, -- counted - expected
    total_sales_amount DECIMAL(12,2) DEFAULT 0.00,
    total_cash_amount DECIMAL(12,2) DEFAULT 0.00,
    total_card_amount DECIMAL(12,2) DEFAULT 0.00,
    total_mobile_amount DECIMAL(12,2) DEFAULT 0.00,
    status ENUM('open', 'closed') DEFAULT 'open',
    z_report_no VARCHAR(50) UNIQUE NULL,
    closing_notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (register_id) REFERENCES pos_registers(id),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### 3.3 `pos_sales` & `pos_sale_items` (Counter Retail Transactions)
Stores individual receipts issued at the counter:
```sql
CREATE TABLE pos_sales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_no VARCHAR(50) UNIQUE NOT NULL, -- 'POS-NYH-2026-00001'
    outlet_id BIGINT UNSIGNED NOT NULL,
    shift_id BIGINT UNSIGNED NOT NULL,
    cashier_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NULL, -- Walk-in customer or loyalty member
    customer_phone VARCHAR(30) NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(12,2) DEFAULT 0.00,
    tax_amount DECIMAL(12,2) DEFAULT 0.00,
    total_amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'mobilepay', 'mixed') NOT NULL,
    cash_received DECIMAL(12,2) DEFAULT 0.00,
    change_returned DECIMAL(12,2) DEFAULT 0.00,
    card_auth_code VARCHAR(100) NULL,
    status ENUM('completed', 'voided', 'refunded') DEFAULT 'completed',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (shift_id) REFERENCES pos_shifts(id),
    FOREIGN KEY (cashier_id) REFERENCES users(id)
);

CREATE TABLE pos_sale_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pos_sale_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    variant_id BIGINT UNSIGNED NULL,
    qty DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL, -- Retail price
    unit_cost DECIMAL(12,2) NOT NULL,  -- Recorded FIFO cost for COGS
    subtotal DECIMAL(12,2) NOT NULL,
    discount DECIMAL(12,2) DEFAULT 0.00,
    tax DECIMAL(12,2) DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (pos_sale_id) REFERENCES pos_sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(id)
);
```

---

## 4. 🔄 End-to-End Operational Workflow

### Step 1: Shift Opening (সকালের রেজিস্টার ওপেন)
1. Cashier opens the POS Terminal (`/outlet/pos`).
2. If no shift is active on that terminal, system displays the **"Start Shift"** modal:
   - Cashier enters opening drawer float (e.g., `kr. 500.00`).
   - Registers session in `pos_shifts` (`status = 'open'`).

### Step 2: High-Speed Counter Checkout (কাউন্টারে দ্রুত বিলিং)
1. **Barcode / SKU Input**: Cashier scans barcode via standard USB/Bluetooth scanner.
2. **Instant Cart Append**: 
   - Item added to client-side cart.
   - Price taken from `products.price` (retail price).
   - Real-time stock verified against `InventoryStock` where `outlet_id = current_outlet`.
3. **Payment**:
   - Quick hotkeys: `[F1] Cash`, `[F2] Card (Dankort/Visa)`, `[F3] MobilePay`.
   - Cashier enters amount received -> Instant calculation of change to return.
4. **Complete Sale & Print**:
   - System prints 80mm thermal receipt via browser direct print / ESC/POS.
   - Background execution:
     - Deducts stock from `InventoryStock` and `StockBatch` for that `outlet_id` using `FifoDepletionService`.
     - Records entry in `StockLedger` with `reference_type = 'pos_sale'`.
     - Triggers automated balanced Journal Entry via `JournalEntryService`.

### Step 3: Automated Accounting Entry (Phase 5 GL Flow)
Every POS sale triggers an automated balanced double-entry:
- **Cash Sale**:
  $$\text{DR } 1010\text{-XX (Cash in Hand - Outlet)} \quad / \quad \text{CR } 4010\text{ (Retail Sales Revenue)}$$
  $$\text{CR } 2020\text{ (VAT Payable - 25\%)}$$
- **Card / MobilePay Sale**:
  $$\text{DR } 1020\text{-XX (Card/MobilePay Clearing)} \quad / \quad \text{CR } 4010\text{ (Retail Sales Revenue)}$$
- **Inventory & Cost of Goods Sold (COGS)**:
  $$\text{DR } 5010\text{ (COGS - Retail)} \quad / \quad \text{CR } 1040\text{-XX (Inventory - Outlet)}$$

### Step 4: Shift Closing & Day-End Z-Report (রাত ৯টায় দোকান বন্ধ)
1. Cashier clicks **"Close Shift"**.
2. Counts physical bills/coins in the drawer and enters:
   - *Physical Cash Counted*: e.g., `kr. 6,850.00`.
3. System calculates discrepancy (`expected - counted`).
4. Generates printed **Z-Report Slip** detailing:
   - Total Gross Sales, Net Sales, Total VAT (25%).
   - Cash, Card, MobilePay breakdown.
   - Over/Shortage amount.
5. Sends automated notification to Head Office Executive Dashboard.

---

## 5. 🎛️ Frontend Account Portal UI Integration (`/account`)

In `resources/views/frontend/pages/account/index.blade.php`, the left navigation sidebar for `Outlet User` is upgraded:

```blade
@if(auth()->user()->hasRole('Outlet User') || auth()->user()->hasRole('Outlet'))
    {{-- Prominent POS Launcher Button --}}
    <div class="px-4 py-3 bg-gradient-to-r from-amber-500 to-amber-600 rounded-sm mb-2 shadow-sm text-center">
        <a href="{{ route('outlet.pos.index') }}" target="_blank" class="block text-xs font-black text-slate-900 tracking-wider uppercase">
            <i class="fas fa-cash-register mr-1.5"></i> Launch POS Terminal
        </a>
    </div>

    {{-- Outlet Operational Tabs --}}
    <a href="{{ route('account.index', ['panel' => 'store-inventory']) }}" class="{{ $menuBase }}">
        <i class="fas fa-boxes mr-2 text-slate-400"></i> My Store Inventory
    </a>
    <a href="{{ route('account.index', ['panel' => 'receive-transfers']) }}" class="{{ $menuBase }}">
        <i class="fas fa-truck-loading mr-2 text-slate-400"></i> Receive Shipments
    </a>
    <a href="{{ route('account.index', ['panel' => 'shifts']) }}" class="{{ $menuBase }}">
        <i class="fas fa-calculator mr-2 text-slate-400"></i> Shifts & Z-Reports
    </a>
@endif
```

---

## 6. 🚀 Implementation Milestones

| Milestone | Deliverables | Target Timeline |
|---|---|---|
| **M1: Database & Models** | `pos_registers`, `pos_shifts`, `pos_sales`, `pos_sale_items` migrations and Eloquent models. | 2 Days |
| **M2: Store Portal in Account Section** | Add `Store Inventory`, `Receive Shipments`, and `Shifts` panels to `/account`. | 2 Days |
| **M3: Fullscreen POS UI** | Fast barcode scanner, touch product grid, drawer cart, multi-pay modal, thermal slip layout. | 3 Days |
| **M4: FIFO & GL Integration** | Connect POS checkout to `FifoDepletionService` and `JournalEntryService`. | 2 Days |
| **M5: Z-Report & Audit** | Cash reconciliation, day-end Z-Report printout, and manager PIN override modal. | 2 Days |
| **M6: Testing & QA** | End-to-end simulation: Stock Transfer from HQ -> Outlet Receive -> POS Sale -> Z-Report. | 2 Days |
