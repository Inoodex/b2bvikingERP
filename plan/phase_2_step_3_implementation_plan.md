# Phase 2 Step 3: Shipment, Stock-in-Transit (SIT), GRN & Landed Cost Allocation Engine — Implementation Plan

This technical implementation plan outlines the architecture, database models, services, controllers, data tables, views, and stock ledger integration required for **Phase 2 Step 3: Foreign Purchase PR Configuration, International Shipment Logistics, Stock-in-Transit (SIT), Goods Received Note (GRN) with Quality Control (QC) & Partial Receiving, Weighted Average Landed Cost Allocation Engine, Vendor Returns with Debit Notes, and Background PDF Generation**.

This plan is fully aligned with existing database migrations (`database/migrations/2026_07_22_092200` to `2026_07_22_092900`), **Client Requirements (Module 2.13 - 2.22 & 2.28 - 2.35, 4.1 - 4.4, 4.14)**, **Daily Roadmap (Days 17-22)**, and **Trello Blueprint (Cards 2.13 - 2.22)**.

---

## 1. User Review Required & Design Intent

> [!IMPORTANT]
> **1. Foreign Purchase PR Import Configuration (Client 2.13)**:
> - Adds `purchase_type` (`local` / `foreign`) flag to `product_requests` table.
> - Foreign PRs display additional fields: preferred currency, expected Incoterm, HS Code notes.
> - All downstream POs inherit the `purchase_type` from their source PR.
>
> **2. Multi-Stage Physical & Accounting Lifecycle**:
> - **Shipment & SIT Stage**: Tracks shipping vessel/flight, Container No, BL/AWB No, Port of Loading/Discharge, ETD/ETA. Status transitions: `in_transit` ➔ `arrived` ➔ `cleared`. Supports **BL/Packing List document upload** for enterprise document management.
> - **GRN & QC Stage**: Store receives physical goods. Performs QC verification (`passed`, `partial`, `failed`). Stock is updated in `inventory_stocks` and `stock_ledgers` **ONLY** when GRN QC is passed/partial.
> - **Multiple GRN per PO (Partial Receiving)**: A single PO can have **multiple GRNs** (e.g., PO for 1,000 pcs → 1st shipment delivers 600 pcs → GRN-001, 2nd shipment delivers 400 pcs → GRN-002). PO `milestone_status` transitions: `goods_partial` (when received < ordered) ➔ `goods_received` (when fully received).
> - **Weighted Average Landed Cost Engine**: Automatically allocates total LC Expenses (13 Duty & Freight elements) across purchased items based on Line Subtotal weight, calculating exact **True Landed Unit Cost** per item.
> - **Vendor Return & Debit Note Engine**: Handles rejected QC items by generating formal **Vendor Returns** and issuing **Debit Notes** for vendor accounts payable reconciliation.
>
> **3. Background GRN PDF Engine (Phase 1 Pattern)**:
> - Uses `GenerateGrnPdfJob` with `PdfCacheManager` to generate GRN Receiving Slip PDF asynchronously, identical to the Phase 1 RFQ/PO PDF pattern. Prevents web server timeouts on large GRNs.

---

## 2. Technical Component Breakdown

### Component 1: Add-on Migration

One clean add-on migration to extend existing tables:

**`2026_08_01_120000_add_step3_enterprise_fields.php`**:
- `product_requests` table: Add `purchase_type` column (`enum('local', 'foreign')`, default `'local'`).
- `shipments` table: Add `bl_awb_no` (`string, nullable`) and `document_path` (`string, nullable`) for BL/Packing List upload.
- `goods_receipts` table: Add `remarks` (`text, nullable`) for receiving notes.

> [!NOTE]
> No existing migrations are modified. All changes are additive via a single new migration file.

---

### Component 2: Eloquent Models & Relationships

1. **`App\Models\Shipment.php`**:
   - Belongs to `Purchase` (`purchase_id`).
   - Fields: `vessel_or_flight`, `container_no`, `bl_awb_no` *(new)*, `port_of_loading`, `port_of_discharge`, `etd`, `eta`, `document_path` *(new)*, `status`.
   - Has many `shipmentCostEstimates()`.
   - Status transitions: `in_transit` ➔ `arrived` ➔ `cleared`.

2. **`App\Models\GoodsReceipt.php`**:
   - Belongs to `Purchase`, `Outlet`, `User` (`received_by`).
   - Has many `items()` (`GoodsReceiptItem`).
   - Has one `vendorReturn()`.
   - Fields: `grn_no` (unique), `qc_status`, `remarks` *(new)*.
   - **Key Design**: One PO → Many GRNs (partial receiving). Controller calculates `remaining_qty` = PO ordered qty − sum of all previous GRN accepted+rejected qty.

3. **`App\Models\GoodsReceiptItem.php`**:
   - Belongs to `GoodsReceipt`, `Product`, `ProductVariant`.
   - Fields: `accepted_qty`, `rejected_qty`, `rejection_reason`.

4. **`App\Models\ShipmentCostEstimate.php`**:
   - Belongs to `Shipment`.
   - Fields: `cost_element`, `estimated_amount`, `actual_amount`, `currency_id`.

5. **`App\Models\LandedCostAllocation.php`**:
   - Belongs to `PurchaseDetail`, `LcExpense`.
   - Fields: `allocated_amount`, `landed_unit_cost`.

6. **`App\Models\VendorReturn.php` & `VendorReturnItem.php`**:
   - Tracks returned goods, rejection reasons, approval status (`pending` / `approved` / `rejected`), and generates `debit_note_no`.
   - VendorReturn belongs to `Purchase`, `GoodsReceipt`.
   - VendorReturnItem belongs to `VendorReturn`, `Product`, `ProductVariant`.

---

### Component 3: Core Business Logic Services

- **`App\Services\LandedCostService.php`**:
  - `calculateLandedCosts(Purchase $purchase): array`
  - Formula:
    $$\text{Item Weight Ratio} = \frac{\text{Item Line Foreign Total}}{\text{PO Total Foreign Amount}}$$
    $$\text{Item Allocated Overhead} = \text{Total LC Expenses Base Amount} \times \text{Item Weight Ratio}$$
    $$\text{True Landed Unit Cost} = \frac{(\text{Item Line Base Total} + \text{Item Allocated Overhead})}{\text{Accepted Qty}}$$
  - Writes allocations into `landed_cost_allocations` and updates `purchase_details.landed_cost`.
  - **Enterprise Enhancement**: Uses `Accepted Qty` from **all GRNs combined** (sum across multiple GRNs for the same PO line item).

- **`App\Services\StockReceiveService.php`**:
  - Executes atomic DB transaction on GRN QC approval:
    1. Increments `inventory_stocks` (quantity at designated outlet/warehouse) by `accepted_qty`.
    2. Writes `stock_ledgers` entry with transaction type `purchase_grn`, `reference_no` = GRN No, UOM, and calculated Landed Unit Cost.
    3. Calculates **total received across all GRNs** for this PO. If total received = total ordered → updates PO `milestone_status` to `goods_received`. If total received < total ordered → updates to `goods_partial`.

- **`App\Services\GrnPdfService.php`** *(new — Phase 1 Pattern)*:
  - Works with `GenerateGrnPdfJob` and `PdfCacheManager`.
  - Cache key: `grn_pdf_{grn_id}`.
  - On GRN creation or QC status change → dispatches background job to regenerate PDF.

---

### Component 4: DataTables & Controllers

- **`ShipmentController.php` & `ShipmentDataTable.php`**:
  - `index()`: DataTable listing all shipments with PO ref, vessel, container, BL/AWB, ETD/ETA, status badge.
  - `show($id)`: Shipment timeline view + BL document viewer + cost estimates.
  - `store()`: Create shipment for a PO (with optional document upload).
  - `updateStatus($id)`: Change status (`in_transit` ➔ `arrived` ➔ `cleared`).
  - `uploadDocument($id)`: Upload/replace BL or Packing List file.

- **`GoodsReceiptController.php` & `GrnDataTable.php`**:
  - `index()`: **DataTable listing ALL GRNs** across all POs — filterable by GRN No, PO No, QC Status, Date Range.
  - `create($purchase_id)`: **Receive Goods Form** — auto-loads PO line items with `remaining_qty` (ordered − previously received). Prevents over-receiving.
  - `store()`: Validates accepted+rejected ≤ remaining, creates GRN & GRN Items, dispatches `GenerateGrnPdfJob`.
  - `show($id)`: GRN details + QC status + item-level breakdown + stock ledger verification link.
  - `updateQcStatus($id)`: Mark QC as `passed`/`partial`/`failed`. On `passed`/`partial` → triggers `StockReceiveService`.
  - `streamPdf($id)`: Serve cached GRN PDF (with synchronous fallback if cache miss, identical to PO PDF pattern).

- **`LandedCostController.php`**:
  - `show($purchase_id)`: Visual **Landed Cost Allocation Matrix** UI showing per-SKU breakdown: Base Cost | Import Overhead | Final Landed Unit Cost.
  - `calculate($purchase_id)`: Triggers `LandedCostService::calculateLandedCosts()`.

- **`VendorReturnController.php` & `VendorReturnDataTable.php`**:
  - `index()`: **DataTable listing ALL Vendor Returns** — filterable by Return No, Debit Note No, PO, Status.
  - `create($goods_receipt_id)`: Auto-loads rejected items from GRN for return processing.
  - `store()`: Creates VendorReturn + items, generates `debit_note_no`.
  - `show($id)`: Return details + Debit Note preview.
  - `approve($id)`: Approve return (updates status, triggers AP reconciliation entry).
  - `streamDebitNotePdf($id)`: Generate/serve Debit Note PDF.

---

### Component 5: Queued Jobs

- **`App\Jobs\GenerateGrnPdfJob.php`** *(new)*:
  - Renders `grn/pdf.blade.php` via DomPDF.
  - Stores result in `PdfCacheManager` with key `grn_pdf_{grn_id}`.
  - Identical architecture to existing `GeneratePoPdfJob`.

---

### Component 6: Blade Views (Stisla UI)

1. `resources/views/backend/shipment/index.blade.php` — Shipment DataTable List
2. `resources/views/backend/shipment/show.blade.php` — Shipment Tracking Timeline & BL Document Viewer
3. `resources/views/backend/shipment/create.blade.php` — Create Shipment Form (with document upload)
4. `resources/views/backend/grn/index.blade.php` — **GRN DataTable List** *(was missing)*
5. `resources/views/backend/grn/create.blade.php` — Receive Goods Form (with remaining qty calculation)
6. `resources/views/backend/grn/show.blade.php` — GRN Details, QC Status & Stock Ledger Link
7. `resources/views/backend/grn/pdf.blade.php` — Official GRN Receiving Slip PDF
8. `resources/views/backend/landed_cost/show.blade.php` — Landed Cost Allocation Matrix View
9. `resources/views/backend/vendor_return/index.blade.php` — **Vendor Return DataTable List** *(was missing)*
10. `resources/views/backend/vendor_return/show.blade.php` — Vendor Return Details & Debit Note Preview
11. `resources/views/backend/vendor_return/create.blade.php` — Create Return from GRN Rejected Items

---

## 3. Proposed File Changes

### [NEW] Migration
- `database/migrations/2026_08_01_120000_add_step3_enterprise_fields.php`

### [NEW] Models
- `app/Models/Shipment.php`
- `app/Models/ShipmentCostEstimate.php`
- `app/Models/GoodsReceipt.php`
- `app/Models/GoodsReceiptItem.php`
- `app/Models/LandedCostAllocation.php`
- `app/Models/VendorReturn.php`
- `app/Models/VendorReturnItem.php`

### [NEW] Services, Jobs & DataTables
- `app/Services/LandedCostService.php`
- `app/Services/StockReceiveService.php`
- `app/Services/GrnPdfService.php`
- `app/Jobs/GenerateGrnPdfJob.php`
- `app/DataTables/ShipmentDataTable.php`
- `app/DataTables/GrnDataTable.php`
- `app/DataTables/VendorReturnDataTable.php`

### [NEW] Controllers & Views
- `app/Http/Controllers/Backend/ShipmentController.php`
- `app/Http/Controllers/Backend/GoodsReceiptController.php`
- `app/Http/Controllers/Backend/LandedCostController.php`
- `app/Http/Controllers/Backend/VendorReturnController.php`
- `resources/views/backend/shipment/index.blade.php`
- `resources/views/backend/shipment/show.blade.php`
- `resources/views/backend/shipment/create.blade.php`
- `resources/views/backend/grn/index.blade.php`
- `resources/views/backend/grn/create.blade.php`
- `resources/views/backend/grn/show.blade.php`
- `resources/views/backend/grn/pdf.blade.php`
- `resources/views/backend/landed_cost/show.blade.php`
- `resources/views/backend/vendor_return/index.blade.php`
- `resources/views/backend/vendor_return/show.blade.php`
- `resources/views/backend/vendor_return/create.blade.php`

### [MODIFY] Existing Files
- `routes/web.php` — Register Shipment, GRN, Landed Cost & Vendor Return routes
- `resources/views/backend/layouts/navbar.blade.php` — Add Shipment, GRN & Landed Cost under Procurement menu
- `resources/views/backend/purchase/po_show.blade.php` — Add "Create Shipment", "Create GRN", "View GRN History" buttons when LC is opened

---

## 4. Enterprise Feature Alignment Matrix

| Client Req | Description | Trello Card | Roadmap Day | Plan Component | Status |
|---|---|---|---|---|---|
| 2.13 | Foreign Purchase PR (Import) | Card 2.13 | Day 17 | `purchase_type` on `product_requests` | ✅ |
| 2.18 | Shipment Information | Card 2.18 | Day 17-19 | `Shipment` model + BL upload | ✅ |
| 2.19 | Cost of Shipment (SIT) | Card 2.19 | Day 17-19 | `ShipmentCostEstimate` model | ✅ |
| 2.20 | Store Receive Goods (GRN) | Card 2.20 | Day 17-19 | `GoodsReceipt` + Partial Receive | ✅ |
| 2.21 | Cost Allocation (Weighted Avg) | Card 2.21 | Day 20-22 | `LandedCostService` | ✅ |
| 2.22 | Unit Cost Configuration | Card 2.22 | Day 20-22 | True Landed Unit Cost formula | ✅ |
| 4.1 | Goods Receipt Quality Check | Implied | Day 17-19 | QC `passed/partial/failed` | ✅ |
| 4.3 | GRN Creation | Implied | Day 17-19 | `GoodsReceiptController` | ✅ |
| 4.4 | GRN Printout | Implied | Day 17-19 | `GenerateGrnPdfJob` + Cache | ✅ |
| 4.14 | Goods Return to Vendor | Card | Day 20-22 | `VendorReturn` + Debit Note | ✅ |

---

## 5. Verification & Testing Plan

### Automated Verification
- Run `php -l` on all newly created controllers, models, services, and jobs.
- Execute `php artisan migrate` to verify add-on migration runs cleanly.
- Execute `php artisan route:list` to verify all shipment, GRN, landed cost, and vendor return routes.

### Manual Verification Flow
1. **Foreign PR Test**: Create a PR with `purchase_type = foreign`. Verify downstream PO inherits import flag.
2. **Shipment Test**: Create Shipment for PO (`Vessel: Maersk Mc-Kinney`, `Container: MSKU908234`, `BL: BL-2026-00451`, Upload Packing List PDF). Update status from `in_transit` ➔ `arrived` ➔ `cleared`.
3. **Partial GRN Test (Critical)**: PO has 1,000 pcs ordered.
   - Create GRN-001: Accept 600, Reject 50 → verify `remaining_qty = 350`.
   - Create GRN-002: Accept 350, Reject 0 → verify PO status changes to `goods_received`.
   - Verify both GRNs appear in GRN Index DataTable.
4. **QC & Stock Ledger Test**: Mark GRN-001 QC as `passed` → verify `inventory_stocks` increased by 600 and `stock_ledgers` recorded `purchase_grn` with correct Landed Unit Cost.
5. **Landed Cost Matrix Test**: Verify 13 LC Expenses are proportionally distributed based on Weighted Average. Confirm per-SKU Landed Unit Cost in Matrix UI.
6. **Debit Note Test**: Create Vendor Return from GRN-001 for 50 rejected items. Verify Debit Note PDF generated. Confirm return appears in Vendor Return Index DataTable.
7. **GRN PDF Test**: Verify GRN PDF generates via background job and serves from cache on subsequent views.
