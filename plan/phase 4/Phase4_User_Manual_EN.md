# 📖 B2B Viking ERP — Phase 4: Inventory & WMS User Manual (English)
**Module:** `04_inventory_and_wms`  
**Plan Document:** `plan/phase 4/Phase4_User_Manual_EN.md`  
**Standard:** Enterprise Operational User Manual & SOP  
**Target Audience:** Warehouse Managers, Store Keepers, Inventory Auditors, Outlet Supervisors, System Administrators  
**Last Updated:** August 2026  

---

## 📑 Table of Contents
1. [Overview & Key Concepts](#1-overview--key-concepts)
2. [User Roles & Permissions Matrix](#2-user-roles--permissions-matrix)
3. [Warehouse Structure Setup (Zones & Bins)](#3-warehouse-structure-setup-zones--bins)
4. [Current Stock & Inventory Reports](#4-current-stock--inventory-reports)
5. [FIFO Batches & Costing Engine](#5-fifo-batches--costing-engine)
6. [3-Stage Multi-Outlet Stock Transfers](#6-3-stage-multi-outlet-stock-transfers)
7. [Physical Stock Adjustments (Damage & Audits)](#7-physical-stock-adjustments-damage--audits)
8. [Immutable Stock Ledger (Audit Trail)](#8-immutable-stock-ledger-audit-trail)
9. [Barcode & QR Scanner Workflows](#9-barcode--qr-scanner-workflows)
10. [Automated Cron Engines & Month-End Procedures](#10-automated-cron-engines--month-end-procedures)
11. [Troubleshooting & Frequently Asked Questions](#11-troubleshooting--frequently-asked-questions)

---

## 1. Overview & Key Concepts

The **B2B Viking ERP Inventory & Warehouse Management System (WMS)** is built to provide 100% audit-compliant stock management, strict FIFO (First-In, First-Out) cost depletion, multi-location micro-tracking, and seamless inter-branch logistics.

### 🔑 Core Principles:
- **No Negative Stock:** The system strictly prevents physical inventory from dropping below zero.
- **True FIFO Landed Costing:** Every batch is received with its exact landed cost (Purchase + Duty + Freight) and depleted in chronological order.
- **Immutable Ledger:** Every single movement is logged in a permanent, un-editable double-entry style ledger.
- **Multi-Stage Physical Integrity:** Goods transferred between outlets never "teleport"; they pass through a formal **In-Transit** state with Delivery Challans.

---

## 2. User Roles & Permissions Matrix

| Role | Zone & Bin Setup | View Stock & Batches | Create Transfers | Dispatch Transfers | Receive Transfers | Create Adjustments | Approve Adjustments | View Stock Ledger |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **Super Admin / Admin** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Warehouse Manager** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Inventory Operator** | ❌ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| **Outlet Manager** | ❌ | ✅ (Branch) | ✅ | ❌ | ✅ | ✅ | ❌ | ✅ (Branch) |
| **Auditor / Accounts** | 👁️ (Read Only) | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ (All) |

---

## 3. Warehouse Structure Setup (Zones & Bins)

### 3.1 Managing Warehouse Zones
Zones represent logical or physical partitions within an outlet/warehouse (e.g., *Active Storage, QC Quarantine, Damaged Goods, Fast-Moving Electronics*).

1. Navigate to: **Sidebar ➔ Inventory ➔ Warehouse Zones**
2. Click **Create New Zone**
3. Fill in the required details:
   - **Outlet:** Select the physical warehouse/branch.
   - **Zone Name:** e.g., `Zone A - Main Racks` or `Quarantine Area`.
   - **Type:**
     - `active` — Available for regular sales and transfers.
     - `quarantine` — Held for quality checks (QC).
     - `scrap` — Damaged/waste goods pending write-off.
   - **Status:** Active / Inactive.
4. Click **Save Zone**.

---

### 3.2 Managing Warehouse Bins (Racks / Shelves)
Bins define the exact micro-location of items inside a Zone.

1. Navigate to: **Sidebar ➔ Inventory ➔ Warehouse Bins**
2. Click **Create New Bin**
3. Enter:
   - **Warehouse Zone:** Select the parent Zone.
   - **Bin Name / Identifier:** e.g., `Rack-01-Shelf-B`.
   - **Capacity / Note:** (Optional descriptive notes).
4. Click **Save Bin**.  
   *Note: The system automatically generates a unique Barcode in the format `BIN-{outlet_id}-{zone_id}-{bin_id}`.*

#### 🖨️ Printing Bin Barcode Labels:
1. On the **Warehouse Bins** list table, click the **Barcode / Print** icon next to any Bin.
2. A thermal sticker layout will open.
3. Press `Ctrl + P` to print on standard thermal label printers (e.g., Zebra, Xprinter).

---

## 4. Current Stock & Inventory Reports

Navigate to: **Sidebar ➔ Inventory ➔ Current Stock**

### Capabilities:
- **Multi-Branch Aggregation:** View physical on-hand quantity per outlet, product, color, and size.
- **Stock Alert Indicators:**
  - 🟢 **Healthy Stock:** Quantity above minimum reorder level.
  - 🟡 **Low Stock:** Quantity at or below `minimum_order_qty`.
  - 🔴 **Out of Stock:** 0 on-hand quantity.
- **Search & Filters:** Filter by Category, Brand, Outlet, or Product SKU.
- **Exporting Reports:** Click **Export PDF** or **Export Excel** for physical stock count sheets.

---

## 5. FIFO Batches & Costing Engine

Navigate to: **Sidebar ➔ Inventory ➔ Stock Batches**

Every time goods are received via a **Goods Receipt Note (GRN)** or initial opening balance, the system creates an isolated **Stock Batch**:
- **Batch No:** Auto-sequenced unique batch code (e.g., `BCH-20260825-001`).
- **Landed Unit Cost:** The exact cost per unit including invoice cost, freight, customs, and taxes.
- **Quantity Received vs Remaining:** Tracks remaining available lot balance.
- **Status:** `active`, `depleted`, or `quarantined`.

### ⚙️ How FIFO Depletion Works Automatically:
1. When a **Delivery Order (DO)** or **Stock Transfer** is dispatched, you **do not** need to manually select batches.
2. The `FifoDepletionService` locks the product rows and automatically consumes units from the **oldest active batch first**.
3. Once a batch reaches `0`, its status automatically transitions to `depleted`.
4. The system calculates the true **Cost of Goods Sold (COGS)** based on exact batch prices.

---

## 6. 3-Stage Multi-Outlet Stock Transfers

To maintain zero stock discrepancies between physical locations, transfers follow a strict 3-stage lifecycle:

```
[ 1. Draft Request ] ──➔ [ 2. Dispatched / In-Transit ] ──➔ [ 3. Received & Verified ]
```

### Step 1: Create Transfer Request (Draft)
1. Navigate to: **Sidebar ➔ Inventory ➔ Stock Transfers**
2. Click **Create Stock Transfer**
3. Fill in:
   - **Source Outlet:** Warehouse sending goods.
   - **Destination Outlet:** Warehouse receiving goods.
   - **Transfer Date & Notes:** Driver Name, Vehicle Number, Challan reference.
   - **Items Table:** Select Product, Variant (Color/Size), and quantity to transfer.
4. Click **Save Transfer**. Status is now `draft` *(No stock deducted yet)*.

### Step 2: Dispatch Goods (Puts Stock In-Transit)
1. Open the transfer record.
2. The Warehouse Supervisor clicks **Dispatch Transfer**.
3. **Automated Actions:**
   - Source Warehouse stock is immediately deducted using FIFO rules.
   - An immutable `transfer_out` entry is posted in the **Stock Ledger**.
   - Status changes to `dispatched` / `in_transit`.
4. Click **Download Challan (PDF)** to print the official Transport Waybill for the driver.

### Step 3: Receive & Verify Goods at Destination
1. When the truck arrives at the receiving outlet, the destination supervisor opens the transfer record and clicks **Receive Transfer**.
2. **Verification Table:**
   - Enter the actual physical quantity received for each line item.
   - If full shipment arrived, click **Receive Full**.
3. Click **Confirm Receipt**.
4. **Automated Actions:**
   - Destination Warehouse stock increases immediately.
   - System replicates new FIFO batches at the destination outlet preserving the original unit landed cost.
   - An immutable `transfer_in` entry is posted in the **Stock Ledger**.
   - Status transitions to `received`.

---

## 7. Physical Stock Adjustments (Damage & Audits)

Used for recording physical stock counts, water damage, expiration, shrinkage, or audit surpluses.

### Step 1: Create Stock Adjustment Entry
1. Navigate to: **Sidebar ➔ Inventory ➔ Stock Adjustments**
2. Click **Create Stock Adjustment**
3. Select:
   - **Outlet:** The location where variance occurred.
   - **Adjustment Type:** `decrease` (Damage / Shrinkage) or `increase` (Surplus found).
   - **Reason Code:** `physical_count`, `damage`, `expired`, `theft`, `audit_variance`.
   - **Items:** Choose Product, Variant, and input Counted vs System quantity.
4. Click **Save as Draft**.

### Step 2: Management Approval & Stock Write-Off
1. An authorized Manager opens the draft adjustment.
2. Review the calculated variance cost (Total Financial Loss/Gain).
3. Click **Approve Adjustment**.
4. **Automated Actions:**
   - Physical stock (`InventoryStock`) is updated immediately.
   - Batch balances are depleted / augmented.
   - A permanent `stock_adjustment` record is posted to the Stock Ledger.
   - Status changes to `approved` (Locked).

---

## 8. Immutable Stock Ledger (Audit Trail)

Navigate to: **Sidebar ➔ Inventory ➔ Stock Ledger**

The Stock Ledger is the single source of truth for financial and inventory auditors.

### 🔍 Ledger Column Breakdown:
- **Date & Time:** Exact timestamp of movement.
- **Product & Variant:** Thumbnail, SKU, Color/Size.
- **Reference:** Document type and number (e.g., `purchase_grn #14`, `delivery_order #88`, `transfer_out #12`).
- **Outlet:** Physical location.
- **Movement Type:** 
  - 🟢 **IN:** Goods added (`+ Qty`).
  - 🔴 **OUT:** Goods removed (`- Qty`).
- **Balance:** Exact calculated on-hand balance immediately following the transaction.

### Filter Options:
- Filter by specific Date Range, Product, Variant, Outlet, Reference Type, or Movement Direction.

---

## 9. Barcode & QR Scanner Workflows

### 9.1 Handheld USB / Bluetooth Scanner Usage:
- All input fields across GRN receiving, Transfer Dispatches, and Sales Orders support standard HID barcode scanners.
- Simply focus on the search/barcode input and scan the item or bin label.

### 9.2 Label Formats:
- **Warehouse Bins:** `BIN-{OutletID}-{ZoneID}-{BinID}`
- **Product Master:** `PRD-{CategoryID}-{ProductID}-{Hash}`
- **Stock Batches:** Base64 encrypted QR payload containing Batch No, Product ID, Expiry Date, and Landed Cost.

---

## 10. Automated Cron Engines & Month-End Procedures

### 10.1 Auto-Replenishment Engine
- **Command:** `php artisan inventory:auto-replenish`
- **Schedule:** Runs nightly at 01:00 AM.
- **Behavior:** Scans all products where `quantity <= minimum_order_qty`. Identifies the primary or cheapest supplier from `product_vendors` and automatically generates a **Draft Purchase Order (PO)** with pending approval alerts.

### 10.2 Month-End Frozen Valuation Snapshot
- **Command:** `php artisan inventory:take-snapshot {--period=YYYY-MM}`
- **Schedule:** Runs on the last day of each month at 23:59 PM.
- **Behavior:** Freezes the closing physical quantity and exact FIFO valuation amount for GAAP/IFRS balance sheet compliance.
- **Viewing Snapshots:** Navigate to **Sidebar ➔ Inventory ➔ Month-End Valuation**.

---

## 11. Troubleshooting & Frequently Asked Questions

### Q1: Why did a transfer dispatch fail with "Insufficient stock"?
> **Cause:** The requested transfer quantity exceeds the available physical stock or active batch balance at the source outlet.  
> **Solution:** Check the **Current Stock** and **Stock Batches** screen for that outlet to verify available batch quantities.

### Q2: Can a received stock transfer be edited or deleted?
> **Answer:** No. Once a transfer is marked as `received`, the stock has entered the destination ledgers and batches. To reverse it, you must create a new reverse transfer from Destination ➔ Source.

### Q3: Why does Stock Ledger not allow deleting an entry?
> **Answer:** By enterprise design, the `stock_ledgers` table is immutable. If a mistake was made during a manual count, create an offsetting **Stock Adjustment** with reason `audit_variance`.

---

**End of Manual** | *B2B Viking ERP System Architecture Documentation*
