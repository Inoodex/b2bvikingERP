# Client Voice Phase 2: Stock Movement, Sales Velocity & B2B Visibility Control Plan
**Date:** 2026-09-24  
**Source Audio Files:**
1. `WhatsApp Audio 2026-09-24 at 11.31.33 AM.ogg` (Intro & B2B Focus)
2. `WhatsApp Audio 2026-09-24 at 11.31.33 AM (1).ogg` (1-Click Product Inflow / Movement History)
3. `WhatsApp Audio 2026-09-24 at 11.31.33 AM (2).ogg` (Consumption Velocity / Run-Rate % over 3 to 6 Months)
4. `WhatsApp Audio 2026-09-24 at 11.31.34 AM.ogg` (B2B Customer / Outlet-Specific Stock Visibility Override)

---

## 1. Executive Summary & Audio Transcripts (Bangla & English)

### 🎙️ Audio 1 (Intro): Context & Business Importance
> **Transcript:**  
> *"ইব্রাহিম ভাই, আসসালামু আলাইকুম। আমরা বেশ কয়েকটা বিষয়ে একটু কনফিউজড যে আসলে এই অপশন গুলো থাকবে কিনা বা এই অপশন গুলো আছে কিনা এখন। কারণ এইটা আমাদের বি টু বির কাস্টমারের জন্য অনেক বেশি দরকার।"*  
> **Summary:** The client emphasizes that these 3 specific operational features are crucial for managing wholesale buyers, ordering forecasting, and inventory flow.

---

### 🎙️ Audio 2: 1-Click Stock Movement & Inflow Timeline (No Ledger Hassle)
> **Transcript:**  
> *"মনে করেন এখন তো আমি প্রত্যেকটা প্রোডাক্ট চেক করি লেজারে গিয়ে যে আসলে এটা কতবার ইন হইছে কতবার আউট হইছে। তো প্রত্যেকটা প্রোডাক্টের যখন আমরা হিস্ট্রি ওখানে লেজারে গিয়ে চেক করা, এইটার এটা কি হচ্ছে সরাসরি যখন প্রোডাক্টের লিস্টে থাকবে বা হচ্ছে আমি প্রোডাক্টের আপডেট করব একটা প্রোডাক্টের স্পেসিফিক ওখানে কোনো লিংক বা ওখানে কোনো অপশন দেখা যায় কিনা যে এইটার মুভমেন্টটা আমি স্পেসিফিক প্রোডাক্টের মুভমেন্টটা হচ্ছে আমি দেখতে পারব। ওই জায়গায় গিয়েই সরাসরি হচ্ছে জাস্ট ইন হইছে কত পিস কবে এইটা জাস্ট হচ্ছে আমি যেন দেখতে পারি আর কি। ওই লেজারে গিয়ে চেক না করেই আর কি সাথে সাথে।"*

#### Business Problem:
- Right now, checking when a product came in (Stock In date & qty) requires navigating away to `Reports -> Stock Ledger`, searching by product, and sifting through long tables.
- It slows down the daily workflow for purchasing and warehouse managers.

#### Solution & Implementation Plan:
1. **Product List (Quick Action Modal / Drawer):**
   - Add a direct icon/button in the Product List (`admin/products`): **"Stock Movement / Inflow History"** (`fas fa-history`).
   - Clicking opens an instant AJAX modal or off-canvas drawer showing the last 10-20 stock inflow/outflow entries (`date`, `type: Purchase/Adjustment/Return`, `reference: PO# / Invoice#`, `in_qty`, `out_qty`, `current_stock`).
2. **Product Edit Page (`admin/products/{id}/edit`):**
   - Add a dedicated **"Stock Movement Timeline"** card/tab showing recent goods receipts, PO arrivals, and ledger movements.

---

### 🎙️ Audio 3: Consumption Velocity & Run-Rate % (3 Months / 6 Months Sales Ratio)
> **Transcript:**  
> *"এবং যত পিসই ইন হোক না কেন, ওইটার পার্সেন্টেজটা যেন দেখায় যে ৩ মাসে এত পার্সেন্ট গেছে, ৪ মাসে এত পার্সেন্ট গেছে। তাইলে আমি ফরওয়ার্ড চিন্তা করে তারপর হচ্ছে আবার অর্ডার করতে পারব আর কি। Let's say আমি ১০০ আনছি, দেখা গেল এটা ৬ মাসে গেল ২ পার্সেন্ট, ৫ পার্সেন্ট। আরেকটা আনছি ৫০০ কিন্তু এইটা গেছে ২০ পার্সেন্ট। তখন কিন্তু হচ্ছে আমাকে ওই রেশিওতে আবার আনতে হবে বা ওই রেশিওতে অর্ডার করতে হবে।"*

#### Business Problem:
- Quantity sold in isolation doesn't tell the real velocity story if you don't know the percentage sold relative to total inventory received.
- Example: Selling 50 pcs out of 100 received (50% velocity) is high turnover, but selling 50 pcs out of 2,000 received (2.5% velocity) is dead stock.
- The client needs **Sell-Through Run-Rate (%)** across time intervals (30 days, 90 days / 3 months, 180 days / 6 months) to determine precise reorder replenishment ratios.

#### Solution & Implementation Plan:
1. **Dynamic Sell-Through Velocity Metrics:**
   - **Formula:**
     $$\text{Sell-Through Rate (\%)} = \left( \frac{\text{Quantity Sold in Selected Period}}{\text{Total Stock Inflow (or Total Received Qty)}} \right) \times 100$$
2. **Comprehensive & Flexible Time Filtering (Custom Date, Month, Year & Presets):**
   - **Quick Presets (1-Click Shortcuts):**
     - Last 30 Days (1 Month)
     - Last 90 Days (3 Months)
     - Last 180 Days (6 Months)
     - Last 365 Days / This Fiscal Year (Annual)
   - **Specific Month & Year Selector:**
     - Select any individual month & year (e.g., `January 2026`, `December 2025`) for seasonal trend analysis.
   - **Custom Date Range Picker:**
     - Exact `Start Date` to `End Date` (e.g., `2026-01-15` to `2026-04-15`) to analyze promotional campaigns, Eid, Christmas, or summer sales periods.
3. **Placement & Visual Feedback:**
   - Integrated into the **Product Stock Movement Modal**, **Product Edit View**, and the **Best Sellers / Replenishment Analytics** suite.
   - Visual Badges & Velocity Health Indicators:
     - 🔥 **Fast Mover (High Velocity):** > 50% sold in selected timeframe.
     - ⚖️ **Moderate Mover:** 20% - 50% sold.
     - 🐢 **Slow Mover / At Risk (Dead Stock Risk):** < 10% sold.

---

### 🎙️ Audio 4: Customer / Outlet-Specific Stock Availability Override (B2B Selective In/Out Stock)
> **Transcript:**  
> *"আরেকটা বিষয় হচ্ছে ভাই, যেটা আমরা ফেস করি সেটা হচ্ছে এখানে তো মনে করেন বিভিন্ন ভেন্ডর থেকে আমরা প্রোডাক্ট নেই। অনেক ভেন্ডর কি করে আমাদেরকে এটা আউট অফ স্টক দেখায়, কিন্তু অন্যদের ঠিকই এটা হচ্ছে ইন স্টক দেখায়। আবার আমাদেরকে ইন স্টক দেখায়, অন্যদেরকে আউট অফ স্টক দেখায়। তো এরকম কাস্টমাইজ করে রাখার অপশনটাও যেন থাকে। যে আমি চাইলেই কোনো একটা ফোন নাম্বার, কোনো একটা কোম্পানির নাম, কোনো একটা আউটলেটের নাম এটাকে চাইলে হচ্ছে কিছু প্রোডাক্ট ইন স্টক দেখাবো, কিছু প্রোডাক্ট আবার হচ্ছে আউট অফ স্টক দেখাবো।"*

#### Business Problem:
- In wholesale B2B, certain limited items are reserved for VIP outlets/dealers or contract clients.
- If an item is low in stock or allocated to a specific contract, regular buyers should see it as **"Out of Stock"**, while specific accounts (by Company Name, Outlet, or Phone Number/User) see it as **"In Stock"**.
- Conversely, a problematic or restricted product can be hidden or marked "Out of Stock" for specific buyers.

#### Solution & Implementation Plan:
1. **Customer Stock Visibility Configuration Table (`customer_product_visibilities`):**
   - Fields:
     - `product_id`
     - `customer_id` (User / Company / Outlet)
     - `visibility_mode`: `force_in_stock` (Show In Stock even if 0 or restricted) | `force_out_of_stock` (Show Out of Stock to this buyer) | `inherit_global` (Default)
     - `reserved_qty`: (Optional reserved stock limit for this client)
2. **Admin Configuration Interface:**
   - On the **Product Edit Page** or **Customer Profile Page**:
     - Quick selector: Add Customer / Company / Phone Number -> Choose: `Show In-Stock` / `Show Out-of-Stock`.
3. **B2B Storefront & Catalog Enforcement:**
   - When a logged-in B2B customer views products, the system checks their override rule before determining stock availability.

---

## 2. Technical Architecture & Component Matrix

| Module | Feature | Target Files | Database / Schema | Priority |
| :--- | :--- | :--- | :--- | :---: |
| **Module 5** | **1-Click Stock Inflow & Movement History** | `app/Http/Controllers/Backend/ProductController.php`<br>`resources/views/backend/product/index.blade.php`<br>`resources/views/backend/product/partials/stock_movement_modal.blade.php`<br>`app/Models/StockLedger.php` | Reads from existing `stock_ledgers` & `purchase_details` | **P1** |
| **Module 6** | **Dynamic Sell-Through Velocity (%)**<br>(Presets, Custom Date, Month & Year) | `app/Services/InventoryVelocityService.php`<br>`resources/views/backend/product/partials/stock_velocity_card.blade.php`<br>`app/Http/Controllers/Backend/ReportController.php` | Calculates `(sales_qty / total_inflow_qty) * 100` dynamically for selected periods (Presets, Month/Year, Date Range) | **P1** |
| **Module 7** | **B2B Customer/Outlet Stock Visibility Override** | `database/migrations/xxxx_create_customer_product_visibilities_table.php`<br>`app/Models/CustomerProductVisibility.php`<br>`resources/views/backend/product/partials/b2b_visibility_tab.blade.php` | New pivot table `customer_product_visibilities` (`product_id`, `user_id`/`outlet_id`, `status`) | **P2** |

---

## 3. Next Action Steps
1. Review and approve the plan with the development team and user.
2. Step 1: Implement the **1-Click Stock Movement & Inflow Modal** on the Product page so the client never has to visit the general ledger just to inspect a product's history.
3. Step 2: Implement the **Dynamic Sell-Through Velocity Percentage Calculation** with Quick Presets, Month-Year, and Custom Date Range filters.
4. Step 3: Implement the **Customer/Outlet Stock Visibility Override**.
