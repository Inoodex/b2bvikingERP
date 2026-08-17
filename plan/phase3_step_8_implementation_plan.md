# 🏢 Phase 3 Step 3.8: Delivery Orders, Packing Slips & Partial Fulfillment Engine
**Target Parity: SAP S/4HANA Outbound Delivery (VL01N) | Odoo 17 Stock Picking & Delivery Slips**

---

## 📌 Goal Description
Implement an enterprise-grade **Delivery Orders, Packing Slips & Partial Fulfillment Engine** for **B2B Viking ERP**. This engine enables warehouse dispatchers to fulfill approved commercial Sales Orders in full or via partial shipments (back-orders), automatically deduct physical stock from `InventoryStock` & log entries in `StockLedger`, generate official Delivery Order sequence numbers (`DO-202608-XXXX`), and stream printable commercial PDF packing slips.

---

## 🏛️ Enterprise Architecture & Features

### 1. Document Sequence & Storage
- **Sequence Format:** `DO-YYYYMM-XXXX` (e.g. `DO-202608-0001`) via `OrderNumberService`.
- **Database Tables:** Existing `delivery_orders` and `delivery_order_items` tables with fields for carrier name, tracking AWB number, shipping method, driver notes, and dispatch status.

### 2. Yajra DataTables Integration (`DeliveryOrderDataTable.php`)
- Standard Yajra DataTable following `CategoryDataTable` reference 100%.
- **Columns:** `Delivery No`, `Order No`, `Customer / Outlet`, `Total Qty`, `Carrier & AWB`, `Status`, `Action`.
- **Filters & Tabs:** `All`, `Pending Pick`, `Dispatched`, `Cancelled`.

### 3. Partial Shipment & Back-Order Engine (`create.blade.php`)
- **Route:** `admin/delivery-orders/create?order_id={id}`
- Displays order item lines with:
  - `Ordered Quantity`
  - `Already Delivered Quantity`
  - `Remaining Deliverable Quantity`
  - `Current Dispatch Quantity` Input (0 to max remaining)
  - `Carrier / Logistics` Dropdown (`DHL Express`, `PostNord`, `DSV Freight`, `FedEx`, `Local Truck`)
  - `Tracking / AWB Number` Input
  - `Shipping Notes / Special Handling Instructions`

### 4. Dispatch Approval & Inventory Deductions (`DeliveryOrderController@dispatch`)
- When a Delivery Order is dispatched:
  - **Stock Deduction:** Deducts dispatched quantity from `InventoryStock` for each item line.
  - **Stock Ledger Audit:** Inserts `StockLedger` entry (`reference_type = 'DeliveryOrder'`, `transaction_type = 'OUT'`).
  - **Order Fulfillment Status:** Updates `Order` fulfillment status (`partially_delivered` or `fully_delivered`).

5. **DomPDF Commercial Packing Slip Generator (`pdf.blade.php`)**
   - Route: `/admin/delivery-orders/{id}/pdf`
   - Formatted commercial packing slip layout with warehouse address, customer shipping address, shipping carrier badge, tracking AWB, items table, and dispatcher signature box.

---

## 📁 Proposed Changes & Component Breakdown

### 1. Backend Controllers & DataTables
- `[NEW]` [DeliveryOrderController.php](file:///c:\laragon\www\b2bvikingErp\app\Http\Controllers\Backend\DeliveryOrderController.php)
  - `index()`: Render Yajra DataTable.
  - `create()`: Render Delivery Order creation form populated with Sales Order items.
  - `store()`: Create Delivery Order (`DO-XXXX`) in pending state.
  - `show()`: Display Delivery Order & Packing Slip details.
  - `dispatch()`: Approve dispatch, deduct inventory stock, log StockLedger, and update Order fulfillment status.
  - `downloadPdf()`: Stream commercial PDF packing slip.
- `[NEW]` [DeliveryOrderDataTable.php](file:///c:\laragon\www\b2bvikingErp\app\DataTables\DeliveryOrderDataTable.php)
  - Server-side Yajra DataTable with latest ID sorting and status badges.

### 2. Blade Views
- `[NEW]` [index.blade.php](file:///c:\laragon\www\b2bvikingErp\resources\views\backend\delivery_orders\index.blade.php)
- `[NEW]` [create.blade.php](file:///c:\laragon\www\b2bvikingErp\resources\views\backend\delivery_orders\create.blade.php) (Centered Select Order / Dynamic Partial Fulfillment Grid)
- `[NEW]` [show.blade.php](file:///c:\laragon\www\b2bvikingErp\resources\views\backend\delivery_orders\show.blade.php) (SweetAlert dispatch confirmation modal & status badges)
- `[NEW]` [delivery_order.blade.php](file:///c:\laragon\www\b2bvikingErp\resources\views\backend\pdf\delivery_order.blade.php) (DomPDF commercial packing slip)

### 3. Routes & Navigation
- `[MODIFY]` [web.php](file:///c:\laragon\www\b2bvikingErp\routes\web.php): Register `/admin/delivery-orders` resource and dispatch/pdf routes.
- `[MODIFY]` [navbar.blade.php](file:///c:\laragon\www\b2bvikingErp\resources\views\backend\layouts\navbar.blade.php): Ensure **Delivery Orders** navigation link is active.

---

## 🧪 Verification & Manual Testing Plan
1. **Create Delivery Order:** Select an approved Sales Order, enter partial dispatch quantities (e.g. 50 out of 100), select carrier DHL, enter AWB tracking, and submit.
2. **Dispatch & Stock Deduction:** Click **Dispatch & Ship Order**. Verify SweetAlert prompt, Toastr notification, inventory deduction in `InventoryStock`, and `OUT` entry in `StockLedger`.
3. **Back-Order Verification:** Create a 2nd Delivery Order for the remaining 50 pcs. Verify `max_deliverable` calculation matches exactly.
4. **PDF Export:** Click **PDF Packing Slip** and verify DomPDF output streams cleanly.
