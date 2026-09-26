# Client Voice Requirements & Implementation Roadmap (Procurement, Catalog & Analytics)

**Document Reference:** `docs/00_master_plans/client_voice_procurement_and_sales_roadmap.md`  
**Created:** September 21, 2026  
**Source:** Client WhatsApp Voice Notes (September 19, 2026)  
**System:** B2B Viking ERP / Copenhagen Tourist Point  
**Status:** Ready for Execution  

---

## Executive Overview

This document captures the 4 critical business requirements voiced directly by the business owner/client. These requirements focus on closing key operational gaps in:
1. **Sales & Buyer Relations:** Generating tailored product catalogs for prospective B2B buyers.
2. **Procurement Intelligence:** Buying the right proportions of top-selling products using velocity percentages.
3. **Inventory Tracking:** Instantly knowing which products are currently on order, in production, or in transit among thousands of SKUs.
4. **Vendor Negotiation:** Accessing multi-shipment historical purchase costs and inflation trends per product.

---

## Module 1: Buyer Product Catalog & Price List Generator (Audio 1)

### 1.1. Client Voice Transcript (Bangla)
> *"আমাদের কোন বায়ার বা হচ্ছে যে আমাদের থেকে প্রোডাক্ট কিনবে, ও আমাদেরকে বললো যে 'তোমাদের কি কি প্রোডাক্ট আছে সেগুলোর আমাকে লিস্ট দাও।' এরপর কেউ বললো 'তোমাদের শুধু ম্যাগনেটের মধ্যে কি কি আছে সেগুলোর লিস্ট দাও', 'মগের যেগুলা আছে সেই মগের লিস্ট দাও।' তো এই যে প্রোডাক্ট লিস্ট পাঠানো, এটার কোনো রিকোয়ারমেন্ট অলরেডি আমাদের আছে কি না?"*

### 1.2. Gap Analysis
- **Current State:** The ERP has general inventory/stock reports (`reports/current-stock/export`) and DataTable CSV exports, but these are internal warehouse sheets with internal bin codes and system IDs.
- **Missing:** A professional, customer-facing **Buyer Catalog / Price Sheet Generator** that outputs branded PDFs or clean Excels with product images, SKUs, wholesale prices, retail MSRPs, and category filters (e.g. only "Magnets", only "Mugs", or all).

### 1.3. Functional Requirements
1. **Catalog Export Modal / Studio (`admin/products/export-catalog`):**
   - **Scope Selector:** All Products, Specific Category (e.g. Magnets, Mugs, Apparel), or Tagged/Selected items.
   - **Price Column Selector:** Wholesale Price only, Retail/Outlet Price only, or Both (Wholesale + MSRP).
   - **Output Formats:**
     - **PDF Lookbook / Catalog:** High-res thumbnail image, Product Title, SKU, Barcode, Wholesale Price, Case Pack / MOQ.
     - **Excel / CSV Order Sheet:** Standard format with image thumbnail column, SKU, barcode, unit price, and blank "Order Qty" column for buyers to fill out and send back.
2. **Public Shareable Expiring Link (Optional Future Extension):**
   - Generate a temporary public link (e.g. `b2bviking.com/catalog/magnets-2026?token=xyz`) that buyers can browse on their tablet/phone.

---

## Module 2: Best Selling Analytics with Velocity & Ordering Proportions (% Ratio) (Audio 2)

### 2.1. Client Voice Transcript (Bangla)
> *"আমরা আমাদের বেস্ট সেলিং প্রোডাক্টগুলো দেখতে পারি (যেমন পাঁচটা টি-শার্ট এক থেকে পাঁচ নম্বরে আছে)। এখন আমার প্রশ্ন হচ্ছে, পার্সেন্টেজ অনুযায়ী কতটুকু যাচ্ছে? দুই নাম্বারের থেকে এক নাম্বারের টি-শার্টটা কত পারসেন্ট বেশি যাচ্ছে? আমি তো ওই অনুযায়ীই অর্ডার করব। পাঁচটা টি-শার্ট আমি তো একই অ্যামাউন্টের অর্ডার করব না। প্রথমটা সেকেন্ডার থেকে কত পারসেন্ট বেশি অর্ডার করব? এইটার ইনফরমেশন দরকার।"*

### 2.2. Gap Analysis
- **Current State:** `admin/reports/best-sellers` lists ranked products by `times_ordered`, `total_qty`, and `total_value`.
- **Missing:**
  - No product images on the table.
  - **No percentage velocity or sales share.** The manager sees that #1 sold 500 pcs and #2 sold 300 pcs, but has to manually calculate the ratio in their head or on a calculator to plan the next PO.

### 2.3. Functional Requirements
1. **Enhanced Best Sellers Table (`resources/views/backend/reports/best_sellers.blade.php`):**
   - Add **Product Thumbnail Image** column for visual recognition.
   - Add **Category Sales Share (%)** column:  
     $$\text{Share} = \left(\frac{\text{Product Qty Sold}}{\text{Total Category Qty Sold}}\right) \times 100$$
   - Add **Relative Velocity Index (% vs Next Rank)**:  
     $$\text{Velocity} = \left(\frac{\text{Rank 1 Qty} - \text{Rank 2 Qty}}{\text{Rank 2 Qty}}\right) \times 100 \quad (+66.7\% \text{ higher})$$
2. **Procurement Order Ratio Calculator (Interactive Widget):**
   - The user inputs their planned total order volume (e.g. *"I want to order 2,000 total T-shirts"*).
   - The system automatically recommends the exact purchase breakdown based on sales velocity:
     - Rank 1: $40\% \to 800\text{ pcs}$
     - Rank 2: $25\% \to 500\text{ pcs}$
     - Rank 3: $15\% \to 300\text{ pcs}$
     - Rank 4: $12\% \to 240\text{ pcs}$
     - Rank 5: $8\% \to 160\text{ pcs}$
   - 1-Click button: **"Generate Draft Purchase Order with this Ratio"**.

---

## Module 3: Active Purchase Order (PO) & Production Milestone Badging on Products (Audio 3)

### 3.1. Client Voice Transcript (Bangla)
> *"আমার তো হাজার হাজার লাইনের প্রোডাক্ট। আমি তো বুঝতে পারি না যে কোনটা অলরেডি অর্ডার করা হইছে বা কোনটা অর্ডার করা হয়নি। যখন আমি সিস্টেমের মধ্য দিয়ে একটা অর্ডার করব, ওইটার যেন স্ট্যাটাস আমি পরিবর্তন করতে পারি যে—হ্যাঁ এই প্রোডাক্টটা অর্ডার হইছে এত তারিখে, প্রোডাকশন শুরু হইছে এত তারিখে, ডেলিভারি হবে এক্সপেক্টেড ডেলিভারি ডেট এত তারিখে। এইরকম অপশনগুলো যেন আমার কাছে আসে।"*

### 3.2. Gap Analysis
- **Current State:** The Purchase module tracks POs and milestones (`draft`, `approved`, `po_sent`, `pi_attached`, `shipped`, `goods_received`).
- **Missing:** When viewing the master Product List (`admin/products`), there is **zero visibility** into whether a product currently has an active PO in progress. An admin looking at low-stock items cannot immediately tell if 500 units are already ordered and arriving next week, risking double-orders or missed orders.

### 3.3. Functional Requirements
1. **Live PO Milestone Badge on Product Table (`ProductDataTable.php`):**
   - Add an **Order / PO Status** column on the Product Table:
     - If no active PO: `<span class="badge badge-light text-muted">No Active Order</span>`
     - If PO active: `<span class="badge badge-warning" title="PO #1042"><i class="fas fa-industry mr-1"></i>In Production (Exp: 28 Oct)</span>`
2. **Interactive Hover Tooltip / Modal:**
   - Hovering or clicking the badge reveals:
     - **PO Number & Supplier:** `PO-2026-1042 (Nordic Textiles)`
     - **Order Placed Date:** `15 Sep 2026`
     - **Production Started Date:** `20 Sep 2026`
     - **Expected Delivery Date:** `28 Oct 2026`
     - **Quantity on Order:** `500 pcs`
3. **Filter by Order Status:**
   - Filter dropdown at top of Product Table:
     - `All Products`
     - `In Stock Only`
     - `Out of Stock & NOT Ordered (Action Required!)`
     - `Currently on Order / In Production`

---

## Module 4: Multi-Shipment Purchase Price History & Landed Cost Tracking (Audio 4)

### 4.1. Client Voice Transcript (Bangla)
> *"প্রাইস অ্যাডজাস্টমেন্ট। প্রথম শিপমেন্টে নিয়ে আসছি ৫ টাকা, পরের শিপমেন্টে হয়েছে ৬ টাকা। এখন শুধু একটা বাইং প্রাইস দেখা যায়। কিন্তু আমি যখন নতুন পাঁচটা বায়ারের সাথে দাম নিয়ে নেগোসিয়েট করব, তখন এই হিস্ট্রিটা কোথায় পাব যে প্রথম শিপমেন্টে ৫ টাকা ছিল, দ্বিতীয়তে ৬ টাকা ছিল? ডেটসহ শিপমেন্টভিত্তিক প্রাইসের মাল্টিপল হিস্ট্রি থাকা দরকার।"*

### 4.2. Gap Analysis
- **Current State:** The database stores historical purchase details in `purchase_details` (`unit_cost`, `landed_cost`, `qty`, `created_at`) and `purchases` (`invoice_no`, `purchase_date`, `vendor_id`).
- **Missing:** The Product Edit / View page (`resources/views/backend/product/edit.blade.php`) only shows a single static input box: `purchase_price`. There is no visual timeline or history table showing past shipment prices, dates, vendors, or price fluctuations.

### 4.3. Functional Requirements
1. **Purchase Price History Tab / Card on Product View & Edit Page:**
   - Add a **"Purchase Cost & Shipment History"** section:
     | Shipment / PO # | Date Received | Supplier / Vendor | Qty Received | Unit Cost | Landed Cost | Cost Trend |
     | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
     | `PO-2026-001` | 10 Jan 2026 | Nordic Crafts | 500 pcs | 5.00 kr. | 5.40 kr. | Base |
     | `PO-2026-042` | 15 Apr 2026 | Baltic Souvenirs | 300 pcs | 6.00 kr. | 6.50 kr. | <span style="color:red;">▲ +20.0%</span> |
     | `PO-2026-099` | 20 Aug 2026 | Viking Direct | 800 pcs | 5.50 kr. | 5.90 kr. | <span style="color:green;">▼ -8.3%</span> |
2. **Supplier Negotiation Comparison Card:**
   - Display:
     - **Lowest Ever Cost:** `5.00 kr.` (Vendor: Nordic Crafts)
     - **Highest Ever Cost:** `6.00 kr.` (Vendor: Baltic Souvenirs)
     - **Weighted Average Cost:** `5.44 kr.`
     - **Current Replacement Cost:** `5.50 kr.`
   - This empowers the manager to say to a new supplier: *"Last January we bought at 5.00 kr., we cannot accept more than 5.20 kr. for 1,000 units."*

---

## Technical Implementation Plan & File Matrix

| Component | Target Files | Actions | Priority |
| :--- | :--- | :--- | :---: |
| **Buyer Catalog Generator** | `app/Http/Controllers/Backend/ProductController.php`<br>`resources/views/backend/product/catalog_export.blade.php`<br>`routes/web.php` | Add category-filtered PDF lookbook and Excel price sheet export. | **P2** |
| **Best Sellers % Velocity** | `app/Http/Controllers/Backend/ReportController.php`<br>`resources/views/backend/reports/partials/best_sellers_table.blade.php` | Add % category share, comparative velocity ratio, and PO volume distributor. | **P2** |
| **Product PO Milestone Badges** | `app/DataTables/ProductDataTable.php`<br>`app/Models/Product.php`<br>`resources/views/backend/product/index.blade.php` | Add active PO relationship, production dates, and visual milestone status badge. | **P1** |
| **Shipment Price History** | `app/Http/Controllers/Backend/ProductController.php`<br>`resources/views/backend/product/edit.blade.php`<br>`resources/views/backend/product/partials/price_history.blade.php` | Add historical purchase timeline table with vendor comparison and cost trends. | **P1** |

---

## Execution Readiness

All 4 modules leverage existing database tables (`purchases`, `purchase_details`, `products`, `orders`, `order_items`) without requiring disruptive schema alterations. Execution can be performed sequentially in 4 streamlined phases upon client sign-off.
