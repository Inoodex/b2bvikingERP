# 📖 B2B Viking ERP — Phase 4: Inventory & Enterprise WMS User Manual (English)
**Module:** `04_inventory_and_wms`  
**Plan Document:** `plan/phase 4/Phase4_User_Manual_EN.md`  
**Standard:** Enterprise Operational User Manual & Standard Operating Procedure (SOP)  
**Target Audience:** Warehouse Managers, Store Keepers, Inventory Auditors, Outlet Supervisors, System Administrators  
**Last Updated:** August 2026  

---

## 📑 Table of Contents
1. [Overview & Core Architecture](#1-overview--core-architecture)
2. [WMS Relationship with Purchase & Sales Modules](#2-wms-relationship-with-purchase--sales-modules)
3. [User Roles & Permissions Matrix](#3-user-roles--permissions-matrix)
4. [Warehouse Structure Setup (Zones, Bins & Barcodes)](#4-warehouse-structure-setup-zones-bins--barcodes)
5. [Inbound GRN Receiving & Put-Away Workflow](#5-inbound-grn-receiving--put-away-workflow)
6. [Bin Inventory Inspector (Live Stock Datatables)](#6-bin-inventory-inspector-live-stock-datatables)
7. [Inter-Bin Relocation Engine (Bin-to-Bin Transfers)](#7-inter-bin-relocation-engine-bin-to-bin-transfers)
8. [Current Stock & Inventory Reports](#8-current-stock--inventory-reports)
9. [FIFO Batches & Landed Costing Engine](#9-fifo-batches--landed-costing-engine)
10. [Outbound Sales Depletion & Ledger Audit Trail](#10-outbound-sales-depletion--ledger-audit-trail)
11. [3-Stage Multi-Outlet Stock Transfers](#11-3-stage-multi-outlet-stock-transfers)
12. [Physical Stock Adjustments (Damage & Audits)](#12-physical-stock-adjustments-damage--audits)
13. [Immutable Stock Ledger (Audit Trail)](#13-immutable-stock-ledger-audit-trail)
14. [Barcode & QR Scanner Workflows](#14-barcode--qr-scanner-workflows)
15. [Automated Cron Engines & Month-End Procedures](#15-automated-cron-engines--month-end-procedures)
16. [Troubleshooting & Frequently Asked Questions](#16-troubleshooting--frequently-asked-questions)

---

## 1. Overview & Core Architecture

The **B2B Viking ERP Inventory & Warehouse Management System (WMS)** is an enterprise-grade WMS built according to SAP WM and Odoo WMS standards. It provides 100% audit-compliant stock management, strict FIFO (First-In, First-Out) landed costing, multi-location micro-bin tracking, and seamless inter-branch logistics.

### 🔑 Core Principles:
- **Zero Negative Stock:** The system strictly prevents physical inventory from dropping below zero.
- **Micro-Location Bin Tracking:** Every physical product unit is linked to an exact Outlet, Zone, and Bin (`Outlet ➔ Zone ➔ Bin`).
- **True FIFO Landed Costing:** Every batch is received with its exact landed cost (Purchase Price + Duty + Freight) and depleted in strict chronological order.
- **Immutable Ledger:** Every single movement (GRN in, bin relocation, sales dispatch, transfer) is logged in a permanent double-entry style ledger.
- **Multi-Stage Physical Integrity:** Goods transferred between outlets pass through a formal **In-Transit** state with Delivery Challans.

---

## 2. WMS Relationship with Purchase & Sales Modules

Many users wonder: *"Is WMS just an isolated internal warehouse tool, or does it connect with Procurement and Sales?"*

**Answer:** WMS is the **central heart** that seamlessly bridges Procurement (Inbound Purchase) and Sales (Outbound Fulfillment):

```
┌───────────────────────────┐      ┌───────────────────────────┐      ┌───────────────────────────┐
│   PROCUREMENT (INBOUND)   │ ───► │   WMS WAREHOUSE ENGINE    │ ───► │    SALES & POS (OUTBOUND) │
│ • Purchase Orders (PO)    │      │ • Inbound Gate Receiving  │      │ • Sales Orders & POS      │
│ • Vendor Invoices & Duty  │      │ • Put-Away & Bin Storage  │      │ • Auto FIFO Bin Depletion │
│ • Goods Receipt Note (GRN)│      │ • Inter-Bin Relocation    │      │ • Invoice Stock Ledger    │
└───────────────────────────┘      └───────────────────────────┘      └───────────────────────────┘
```

### 1. Integration with Procurement (Purchase):
- When a Purchase Order (PO) is approved and goods arrive at the gate, the **Goods Receipt Note (GRN)** receives items directly into WMS Bins/Staging Areas.
- GRN creates isolated **FIFO Stock Batches** recording unit purchase price + landed costs (duty, freight) linked directly to `bin_id`.

### 2. Integration with Sales (POS & Sales Orders):
- When a customer places an order via POS or Sales Order, the WMS **FIFO Depletion Engine** automatically scans all Bins in that Outlet to locate the oldest active stock batch.
- It deducts physical inventory from the exact Bin (`bin_id`, `bin_name`, `bin_barcode`) and posts an immutable `delivery_order` / `sales_invoice` record into the Stock Ledger.

### 3. Internal Warehouse Management:
- Inside the warehouse, operators perform Inter-Bin transfers (Move Entire Bin 100% or Single SKU Relocation) and inspect stock counts without altering accounting or purchase order totals.

---

## 3. User Roles & Permissions Matrix

| Role | Zone & Bin Setup | View Stock & Bins | Inter-Bin Relocation | GRN Put-Away | Create Transfers | Approve Adjustments | View Stock Ledger |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **Super Admin / Admin** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Warehouse Manager** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Inventory Operator** | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ |
| **Outlet Manager** | ❌ | ✅ (Branch) | ✅ | ✅ | ✅ | ❌ | ✅ (Branch) |
| **Auditor / Accounts** | 👁️ (Read Only) | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ (All) |

---

## 4. Warehouse Structure Setup (Zones, Bins & Barcodes)

### 4.1 Managing Warehouse Zones
Zones represent logical or physical partitions within an outlet/warehouse (e.g., *Inbound Receiving Dock, Main Storage Racks, Quarantine, Dispatch Bay*).

1. Navigate to: **Sidebar ➔ Warehouse Setup ➔ Warehouse Zones**
2. Click **Create New Zone**
3. Fill in the required details:
   - **Outlet:** Select the target warehouse/outlet.
   - **Zone Name:** e.g., `Inbound Receiving Zone` or `Main Storage Zone`.
   - **Type:**
     - `active` — Available for regular storage, sales, and transfers.
     - `quarantine` — Held for quality checks (QC).
     - `scrap` — Damaged/waste goods pending write-off.
   - **Status:** Active / Inactive.
4. Click **Save Zone**.

---

### 4.2 Managing Warehouse Bins (Racks / Shelves)
Bins define the exact micro-location of items inside a Zone.

1. Navigate to: **Sidebar ➔ Warehouse Setup ➔ Warehouse Bins**
2. Click **Create New Bin**
3. Enter:
   - **Warehouse Zone:** Select the parent Zone.
   - **Bin Name / Identifier:** e.g., `Rack A1 - Shelf A` or `Receiving Dock Bay 1`.
   - **Status:** Active / Inactive.
4. Click **Save Bin**.  
   *Note: The system automatically generates a unique location barcode in format `BIN-{outlet_id}-{zone_id}-{bin_id}` (e.g., `BIN-1-2-5`).*

#### 🖨️ Printing Bin Barcode Labels:
1. On the **Warehouse Bins** list table, click the **Print Barcode** icon next to any Bin.
2. A thermal sticker layout will open.
3. Press `Ctrl + P` to print on standard thermal label printers (e.g., Zebra, Xprinter). Attach the sticker directly onto the physical rack in your warehouse.

---

## 5. Inbound GRN Receiving & Put-Away Workflow

When a shipment arrives from a supplier:

1. Navigate to: **Sidebar ➔ Procurement ➔ Goods Receipts ➔ Create GRN**
2. **Step 1:** Select the approved Purchase Order (PO).
3. **Step 2:** Select the **Receiving Outlet** and **Put-Away Staging Bin** (e.g., `Receiving Dock Bay 1`).
4. **Header "Apply All" Feature:** Click **Apply All** next to the Put-Away Bin selector to instantly set all 100+ line items to that staging bin in 1 click!
5. **Existing Location Auto-Suggest (`★ Existing`):** If a product is already stored in a primary rack (e.g., `Rack A1`), the system automatically pre-selects `Rack A1 (★ Existing)` in the table row so operators do not have to pick dropdowns manually!
6. Click **Submit GRN & Update Inventory**.
7. **Automated Actions:**
   - Increments physical inventory in `InventoryStock` for that Bin.
   - Creates a new FIFO `StockBatch` recording exact Landed Unit Cost (Purchase Price + Freight + Duty).
   - Posts a `purchase_grn` record in `StockLedger`.

---

## 6. Bin Inventory Inspector (Live Stock Datatables)

Navigate to: **Sidebar ➔ Warehouse Bins ➔ Click "Stock" 👁️ button on any Bin**  
(URL: `/admin/warehouse-bins/{id}/stocks`)

### Capabilities:
- **Live Summary KPI Cards:** Shows Stored Products count, Total Stock Quantity (Pcs), Active Batches, and Total Bin Valuation (currency value).
- **Yajra Server-Side DataTables:** Fast AJAX loading for thousands of items without page reloads or memory lagging.
- **Search & Filter Controls:** Standard `Show [10] entries` dropdown and `Search:` box to instantly search by Product Name, Variant, SKU, Color, or Size.
- **Full Variant & Attribute Display:**
  - Displays Variant Name (e.g., `Size XL | Blue Color`).
  - Displays Color Badge 🎨 and Size Badge 📐.
  - Non-variant items show `<span class="badge badge-light">Standard Item</span>`.
- **Tab 1:** Stored Products Inventory.
- **Tab 2:** Active FIFO Batches (Batch Code, Received Date, Landed Unit Cost, Remaining Qty, Total Batch Value).

---

## 7. Inter-Bin Relocation Engine (Bin-to-Bin Transfers)

Used to move items from one rack to another inside the same warehouse (e.g., moving from `Receiving Dock` to `Rack A1`).

Navigate to: **Sidebar ➔ Warehouse Bins ➔ Click "Relocate Stock"**  
(URL: `/admin/bin-transfers/create`)

### 🔄 Supported Modes:

#### 1. Mode A: Move Entire Bin (100% All Stock)
- **Use Case:** Moving all products from a staging bay or clearing out an entire rack into a new rack.
- **How to operate:**
  1. Toggle mode to **"Move Entire Bin (100% All Stock)"**.
  2. Select **Source Bin** (e.g., `Receiving Dock Bay 1`) and **Destination Bin** (e.g., `Rack A1`).
  3. Click **Move Entire Bin Contents (100%)**. 1-click moves 100% of all items and active FIFO batches.

#### 2. Mode B: Single Product / Partial Relocation
- **Use Case:** Moving specific quantities of a single product to another rack.
- **How to operate:**
  1. Keep mode on **"Single Product Item"**.
  2. Select **Source Bin**, **Product**, and enter **Quantity** (or click **Select All Qty** button to pre-fill available stock).
  3. Select **Destination Bin** and click **Confirm & Relocate**.

#### 3. Migrating Unassigned Legacy Stock (`bin_id = NULL`)
- **Use Case:** Moving legacy products that were created before WMS was installed into new Bins.
- **How to operate:**
  1. Leave **Source Bin** as `-- Select Origin Bin (or Unassigned Stock) --`.
  2. System lists all unallocated products. Select the product, quantity, and target Destination Bin, then click **Confirm & Relocate**.

---

## 8. Current Stock & Inventory Reports

Navigate to: **Sidebar ➔ Inventory ➔ Current Stock**

### Capabilities:
- **Multi-Branch Aggregation:** View physical on-hand quantity per outlet, product, color, and size.
- **Stock Alert Indicators:**
  - 🟢 **Healthy Stock:** Quantity above minimum reorder level.
  - 🟡 **Low Stock:** Quantity at or below `minimum_order_qty`.
  - 🔴 **Out of Stock:** 0 on-hand quantity.
- **Exporting Reports:** Click **Export PDF** or **Export Excel** for physical stock count sheets.

---

## 9. FIFO Batches & Landed Costing Engine

Navigate to: **Sidebar ➔ Inventory ➔ Stock Batches**

Every time goods are received via a **Goods Receipt Note (GRN)** or initial opening balance, the system creates an isolated **Stock Batch**:
- **Batch No:** Auto-sequenced unique batch code (e.g., `BCH-20260825-001`).
- **Bin Location Badge:** Displays the exact Bin where this batch is stored (`Bin Name` and `Barcode`).
- **Landed Unit Cost:** The exact cost per unit including invoice cost, freight, customs, and taxes.
- **Quantity Received vs Remaining:** Tracks remaining available lot balance.
- **Status:** `active`, `depleted`, or `quarantined`.

### ⚙️ How FIFO Depletion Works Automatically:
1. When a **Sales Order**, **POS Invoice**, or **Stock Transfer** is dispatched, you **do not** need to manually select batches.
2. The `FifoDepletionService` locks the product rows and automatically consumes units from the **oldest active batch first**.
3. Once a batch reaches `0`, its status automatically transitions to `depleted`.
4. The system calculates true **Cost of Goods Sold (COGS)** based on exact batch landed costs.

---

## 10. Outbound Sales Depletion & Ledger Audit Trail

When a customer buys items at POS or through Sales Orders:

1. Sales order is approved and dispatched.
2. `FifoDepletionService` identifies the oldest active FIFO batches in that outlet.
3. Automatically deducts physical inventory from the respective Bin locations (`bin_id`, `bin_name`, `bin_barcode`).
4. Logs an immutable record in `StockLedger` with:
   - Reference: `sales_invoice #104` or `delivery_order #52`
   - Bin Location: `Rack A1 (BIN-1-2-5)`
   - Movement: `OUT (- Qty)`
   - Updated Balance Qty.

---

## 11. 3-Stage Multi-Outlet Stock Transfers

Transfers between different outlets follow a strict 3-stage lifecycle:

```
[ 1. Draft Request ] ──➔ [ 2. Dispatched / In-Transit ] ──➔ [ 3. Received & Verified ]
```

### Step 1: Create Transfer Request (Draft)
1. Navigate to: **Sidebar ➔ Inventory ➔ Stock Transfers ➔ Create Stock Transfer**
2. Select Source Outlet, Destination Outlet, Driver Info, and items to transfer.
3. Status is `draft` *(No stock deducted yet)*.

### Step 2: Dispatch Goods (Puts Stock In-Transit)
1. Source Warehouse Supervisor clicks **Dispatch Transfer**.
2. Source stock is deducted using FIFO rules; `transfer_out` is logged in Stock Ledger.
3. Status changes to `dispatched` / `in_transit`. Print Delivery Challan (PDF).

### Step 3: Receive & Verify Goods at Destination
1. Destination supervisor opens transfer record and clicks **Receive Transfer**.
2. Verifies physical quantities and clicks **Confirm Receipt**.
3. Destination stock increases; new FIFO batches are created preserving landed cost; `transfer_in` is logged in Stock Ledger.

---

## 12. Physical Stock Adjustments (Damage & Audits)

Used for recording physical stock counts, water damage, expiration, shrinkage, or audit surpluses.

1. Navigate to: **Sidebar ➔ Inventory ➔ Stock Adjustments ➔ Create Stock Adjustment**
2. Select Outlet, Adjustment Type (`decrease` / `increase`), Reason Code, and Items.
3. Click **Save as Draft**.
4. Authorized Manager opens draft and clicks **Approve Adjustment**. Stock and Stock Ledger update permanently.

---

## 13. Immutable Stock Ledger (Audit Trail)

Navigate to: **Sidebar ➔ Inventory ➔ Stock Ledger**

The Stock Ledger is the single source of truth for financial and inventory auditors.

### 🔍 Ledger Columns:
- **Date & Time:** Exact timestamp of movement.
- **Image & Product:** Thumbnail, Product Name.
- **Variant:** Name, Color, Size attributes.
- **Reference:** Document type and ID (e.g., `purchase_grn #14`, `sales_invoice #88`, `transfer_out #12`).
- **Bin Location:** Bin Name and Barcode badge.
- **Type:** 🟢 `IN` (Added) / 🔴 `OUT` (Deducted).
- **Balance:** On-hand balance immediately following the transaction.

---

## 14. Barcode & QR Scanner Workflows

- All input fields across GRN receiving, Bin Relocations, Transfer Dispatches, and Sales Orders support handheld USB / Bluetooth barcode scanners.
- **Bin Barcodes:** `BIN-{OutletID}-{ZoneID}-{BinID}`
- **Product Barcodes:** `PRD-{CategoryID}-{ProductID}-{Hash}`
- **Batch QR Codes:** Encrypted payload containing Batch No, Product ID, Expiry Date, and Landed Cost.

---

## 15. Automated Cron Engines & Month-End Procedures

### 15.1 Auto-Replenishment Engine
- **Command:** `php artisan inventory:auto-replenish`
- **Schedule:** Nightly at 01:00 AM. Automatically generates Draft Purchase Orders (PO) for items below MOQ.

### 15.2 Month-End Frozen Valuation Snapshot
- **Command:** `php artisan inventory:take-snapshot {--period=YYYY-MM}`
- **Schedule:** Last day of each month at 23:59 PM. Freezes closing physical quantity and FIFO valuation amount for GAAP/IFRS balance sheet compliance.

---

## 16. Troubleshooting & Frequently Asked Questions

### Q1: Does WMS affect Purchase and Sales accounting?
> **Answer:** Yes! WMS connects directly with Purchase (GRN receives landed cost batches into bins) and Sales (POS/Sales Orders consume stock from oldest bins via FIFO and log COGS).

### Q2: What happens if a product is stored in 20 different racks?
> **Answer:** System handles multi-bin stocking effortlessly! Each rack maintains its own physical stock count and batch history. When a sale occurs, FIFO automatically depletes from the oldest rack first.

### Q3: Why does Stock Ledger not allow deleting an entry?
> **Answer:** By enterprise design, `stock_ledgers` is immutable. If a mistake was made during a count, create an offsetting **Stock Adjustment** with reason `audit_variance`.

---

**End of Manual** | *B2B Viking ERP System Architecture Documentation*
