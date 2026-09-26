# Client Voice Technical Implementation Plan

**Reference Document:** `docs/00_master_plans/client_voice_technical_implementation_plan.md`  
**Related Master Plan:** `docs/00_master_plans/client_voice_procurement_and_sales_roadmap.md`  
**Architecture Pattern:** Report Section Asynchronous & Ephemeral Engine (Zero-Crash Guarantee)  
**Status:** Approved for Implementation (Ready for Execution)  
**System:** B2B Viking ERP  

---

## Executive Summary & Architecture Strategy

Following comprehensive codebase analysis, all 4 client voice requirements will be implemented **without creating new database tables or altering core schemas**, using existing ERP tables (`purchases`, `purchase_details`, `shipments`, `products`, `orders`, `order_items`, `sales_quotations`, `sales_quotation_items`).

### Zero-Crash High-Performance PDF & Excel Engine (As per Report Section Standards)
In B2B wholesale, when exporting a catalog or price list of 50 to 200+ products (e.g. all Magnets or Mugs) with product thumbnail images, conventional synchronous DomPDF rendering causes severe server issues: **RAM exhaustion (500 Out of Memory)**, **PHP execution timeouts (504 Gateway Timeout)**, and **Nginx worker crashes**.

To ensure 100% server stability and prevent crashes, we adopt the proven architectural pattern from the ERP's Report Section (`GenerateStockReportPdfJob`, `PdfImageHelper`, `PdfCacheManager`, `HasEphemeralPdfReports`):

1. **Image Optimization via `PdfImageHelper`:**
   - No raw high-res image files will ever be fed directly into DomPDF.
   - All product thumbnails are converted, compressed, and resized (80x80px at 70% quality) to lightweight Base64 data strings using `PdfImageHelper::optimize()`.
   - Direct local filesystem path resolution (`public_path('storage/...')`) eliminates internal HTTP deadlocks.
2. **Dual-Mode PDF Generation (Instant Stream vs Async Queue):**
   - **Direct Fast Stream:** For standard individual quotes ($\le 25$ items), stream directly with memory safety.
   - **Asynchronous Queue Engine (`GenerateBuyerCatalogPdfJob`):** For bulk category catalogs (e.g. 50–200+ items), the task is dispatched to the background queue worker.
   - Memory overrides: `ini_set('memory_limit', '1024M')` and `set_time_limit(0)` within the worker job.
   - Files are written to ephemeral storage: `storage_path('app/temp_reports/catalog_u{userId}_{timestamp}.pdf')`.
   - UI polls status asynchronously using `HasEphemeralPdfReports` and provides instant download when ready—**Zero UI blocking, Zero server freeze**.
3. **Memory-Safe Streaming Excel Exports:**
   - `SalesQuotationExcelExport` uses `Maatwebsite\Excel` streaming (`FromCollection`, `WithMapping`, `ShouldAutoSize`).
   - Uses lightweight array mapping rather than bulky nested Eloquent models to maintain a near-zero memory footprint.

---

## Module 1: Sales Quotation (SQ) based Buyer Catalog & Price List Generator

### Objectives
Enable sales managers to select an entire product category (e.g., all "Magnets" or all "Mugs") in 1 click, generate an image-rich Visual Lookbook PDF for buyers, and download an Excel Order Sheet with a blank "Order Qty" column.

### Files to Modify / Create
1. **[MODIFY] `resources/views/backend/sales_quotation/create.blade.php`**
   - Add a "Bulk Add by Category" toolbar above the quotation items grid.
   - User selects Category (and optional Subcategory) and clicks "Load Category Items".
   - Dynamically appends all active products in that category into the SQ grid with wholesale prices (`outlet_price`) and default quantity 1.
   - Add optional fields for Walk-in / Prospect Buyer (`prospect_name`, `prospect_phone`, `prospect_email`) so unregistered prospective buyers can receive quotes immediately.
2. **[MODIFY] `resources/views/backend/sales_quotation/pdf.blade.php`**
   - Integrate `PdfImageHelper::optimize()` for all product thumbnail images (`thumb_image`).
   - Add product SKU and barcode display under the product title.
   - Support displaying both Wholesale Unit Price and Retail MSRP.
3. **[NEW] `app/Jobs/GenerateBuyerCatalogPdfJob.php`**
   - Asynchronous queue job implementing `ShouldQueue`.
   - Handles memory-safe background rendering of large catalog PDFs using `ini_set('memory_limit', '1024M')` and `PdfImageHelper`.
   - Saves output to `storage/app/temp_reports/catalog_u{userId}_{timestamp}.pdf`.
4. **[NEW] `app/Exports/SalesQuotationExcelExport.php`**
   - Maatwebsite Excel export class for Sales Quotation.
   - Columns: `#`, `Thumbnail Image / Link`, `Product Name`, `SKU / Barcode`, `Wholesale Price`, `MSRP Price`, and empty **`Order Qty`** column.
5. **[MODIFY] `app/Http/Controllers/Backend/SalesQuotationController.php`**
   - Implement `HasEphemeralPdfReports` trait.
   - Add `catalogPdfAsync(Request $request)` to dispatch `GenerateBuyerCatalogPdfJob` for bulk category lookbooks.
   - Add `checkCatalogStatus(Request $request)` and `downloadCatalogPdf($file)` using the ephemeral pattern.
   - Add `excel(SalesQuotation $salesQuotation)` method returning `Excel::download(new SalesQuotationExcelExport($salesQuotation), ...)`.
   - Update `store()` and `update()` validation to allow prospect buyers if `customer_id` is guest/prospect.
6. **[MODIFY] `resources/views/backend/sales_quotation/show.blade.php`**
   - Add "Download Excel Order Sheet" button alongside the existing PDF button.
7. **[MODIFY] `routes/web.php`**
   - Register routes:
     - `admin/sales-quotations/{salesQuotation}/excel`
     - `admin/sales-quotations/{salesQuotation}/catalog-pdf-async`
     - `admin/sales-quotations/catalog-pdf/status`
     - `admin/sales-quotations/catalog-pdf/download/{file}`

---

## Module 2: Best Selling Analytics with Velocity & Ordering Proportions

### Objectives
Empower procurement managers to see sales share within categories, velocity differences between consecutive ranks (e.g. #1 vs #2), simulate purchase volume distributions across ranks, and push calculated quantities to draft PO creation.

### Files to Modify
1. **[MODIFY] `app/Http/Controllers/Backend/ReportController.php`**
   - In `bestSellers(Request $request)`:
     - Eager load / select `products.thumb_image`, `products.category_id`.
     - Calculate Category Total Qty sold for the selected filter to compute Category Sales Share (%):  
       $$\text{Category Share} = \left(\frac{\text{Product Qty Sold}}{\text{Category Qty Sold}}\right) \times 100$$
     - Calculate Relative Velocity Index (% vs Next Rank):  
       $$\text{Velocity} = \left(\frac{\text{Rank } N \text{ Qty} - \text{Rank } N+1 \text{ Qty}}{\text{Rank } N+1 \text{ Qty}}\right) \times 100$$
2. **[MODIFY] `resources/views/backend/reports/partials/best_sellers_table.blade.php`**
   - Add **Image** column with product thumbnail.
   - Add **Category Sales Share (%)** column with visual progress bar.
   - Add **Velocity vs Next Rank** column with trend badge (e.g., `▲ +66.7% vs #2`).
3. **[MODIFY] `resources/views/backend/reports/best_sellers.blade.php`**
   - Add **"Procurement Volume Split Calculator"** widget card at the top.
   - User inputs planned total order units (e.g., `2,000` units).
   - Dynamic JavaScript breakdown table showing exact units per product based on sales share %.
   - Button: **"Generate Draft Purchase Order with this Ratio"** redirecting to `route('admin.purchases.create')` with `ids` and pre-filled order quantities.

---

## Module 3: Active Purchase Order (PO) & Production Milestone Badging

### Objectives
Prevent double-orders and out-of-stock blind spots on the master Product List by showing live order milestones, expected arrival dates, and an action-oriented filter.

### Files to Modify
1. **[MODIFY] `app/Models/Product.php`**
   - Add relationship `activePurchaseDetails()`:
     ```php
     public function activePurchaseDetails()
     {
         return $this->hasMany(PurchaseDetail::class)
             ->whereHas('purchase', function($q) {
                 $q->whereNotIn('milestone_status', ['goods_received', 'cancelled']);
             })
             ->with(['purchase.vendor', 'purchase.shipments']);
     }
     ```
2. **[MODIFY] `app/DataTables/ProductDataTable.php`**
   - Add column `po_status` ("Order / PO Status").
   - Badge rendering:
     - If no active order: `<span class="badge badge-light text-muted">No Active Order</span>`
     - If active PO: Badge styled by milestone (`In Production`, `PO Sent`, `Shipped`, `LC Opened`), including ETA date (`Exp: 28 Oct`) and ordered quantity.
   - In `query(Product $model)`:
     - Eager load `activePurchaseDetails.purchase.vendor`, `activePurchaseDetails.purchase.shipments`.
     - Support filter `po_filter` (`in_stock`, `out_of_stock_not_ordered`, `on_order`).
3. **[MODIFY] `resources/views/backend/product/index.blade.php`**
   - Add filter dropdown for PO Status:
     - `All Products`
     - `In Stock Only`
     - `Out of Stock & NOT Ordered (Action Required!)`
     - `Currently on Order / In Production`
   - Add Bootstrap Popover / Modal to inspect active PO details (PO#, Supplier, Order Date, ETA Date, Qty on Order).

---

## Module 4: Multi-Shipment Purchase Price History & Landed Cost Tracking

### Objectives
Provide negotiation leverage during supplier discussions and buyer pricing by displaying multi-shipment cost history, landed costs, and cost trends directly on the Product Edit & View page.

### Files to Modify / Create
1. **[MODIFY] `app/Http/Controllers/Backend/ProductController.php`**
   - In `edit($id)`:
     - Fetch historical shipments for the product from `purchase_details` joined with `purchases` and `vendors`, ordered by `purchases.date` desc.
     - Calculate negotiation metrics:
       - **Lowest Ever Cost** & vendor name
       - **Highest Ever Cost** & vendor name
       - **Weighted Average Cost:** $\frac{\sum (\text{unit\_cost} \times \text{qty})}{\sum \text{qty}}$
       - **Latest / Replacement Cost**
       - **Shipment-to-Shipment Price Trend (%):** $\left(\frac{\text{New Cost} - \text{Old Cost}}{\text{Old Cost}}\right) \times 100$
     - Pass `$purchaseHistory`, `$negotiationMetrics` to view.
2. **[NEW] `resources/views/backend/product/partials/price_history.blade.php`**
   - Summary Metric Cards: Lowest Ever Cost, Highest Ever Cost, Weighted Average Cost, Latest Cost.
   - Historical Shipment Timeline Table:
     - Shipment / PO #
     - Date Received
     - Vendor / Supplier
     - Quantity Received
     - Unit Cost
     - Landed Cost
     - Price Trend Badge (`▲ +20.0%` red or `▼ -8.3%` green)
     - Link to PO details.
3. **[MODIFY] `resources/views/backend/product/edit.blade.php`**
   - Include `backend.product.partials.price_history` right below the pricing card.

---

## Verification Plan

### Automated / Syntax Verification
- Run syntax linter checks on modified controllers, jobs, and models:
  ```bash
  php -l app/Http/Controllers/Backend/SalesQuotationController.php
  php -l app/Jobs/GenerateBuyerCatalogPdfJob.php
  php -l app/Exports/SalesQuotationExcelExport.php
  php -l app/Http/Controllers/Backend/ReportController.php
  php -l app/Http/Controllers/Backend/ProductController.php
  php -l app/DataTables/ProductDataTable.php
  ```

### Functional & Load Verification
1. **Module 1 (Crash-Proof Catalog PDF & Excel):**
   - Add 50+ items in SQ.
   - Test async PDF job dispatch and verify CPU/RAM stays low without timeout.
   - Verify `PdfImageHelper` generates crisp Base64 thumbnails without memory spikes.
   - Download Excel Order Sheet and verify clean structure.
2. **Module 2 (Best Sellers % Velocity):**
   - Navigate to `/admin/reports/best-sellers`.
   - Verify Category Share % and Velocity Index (% vs Next Rank).
   - Test Volume Split Calculator with test volume (e.g. 2,000 units).
3. **Module 3 (PO Milestone Badges):**
   - Verify `po_status` column displays live milestone badges.
   - Test filter: "Out of Stock & NOT Ordered" and "Currently on Order".
4. **Module 4 (Multi-Shipment Purchase Price History):**
   - Open `/admin/products/{id}/edit` for a product with purchase history.
   - Verify negotiation benchmark card and shipment timeline table with price trend badges.
