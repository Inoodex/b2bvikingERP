# b2bviking.com — ERP Database Design (v4 — Full Scope, First 5 Modules)
### Foundation + Purchase + Sales + Inventory + Accounting
*(B2C, HR & Payroll, API/Integration — এই তিনটা module বাদ, পরবর্তী phase-এ আলাদাভাবে করা হবে)*

এই version আগের v3-কে **সম্পূর্ণভাবে replace করে** — v3-তে যা যা missing ছিল (GRN, Shipment, Fiscal Year, Cost Centre, Assets, Fund Transfer, Petty Cash, Coupons, Pricelist ইত্যাদি), সেগুলো সব এখন যোগ করা হয়েছে, পুরো client requirement doc-এর প্রথম ৫ module অনুযায়ী।

---

## 1. Ground Rules (অপরিবর্তিত)

- কোনো existing table drop/rename হয়নি — শুধু `nullable()` + `after()` দিয়ে column add করা হয়েছে
- যেখানেই existing table (`product_requests`, `purchases`, `orders`, `order_items`, `order_payments`, `issues`, `stock_ledgers`, `vendors`) কাজে লাগে, সেটাই reuse করা হয়েছে
- Migration strictly dependency order অনুযায়ী সাজানো (৬৯টা ফাইল)

---

## 2. এই Round-এ নতুন যা Add হলো (v3 থেকে যা Missing ছিল)

| Category | নতুন Table/Column | কোন Requirement পূরণ করছে |
|---|---|---|
| Foundation | `companies` | Company Master, Multi-Company (5.4) — lightweight, future-proof |
| Foundation | `departments` (formal master) | Department Master (0.3) |
| Foundation | `notifications` | Notification System (0.4) |
| Foundation | `product_requests` alter | Edit/Cancel/Return PR/SR (2.5) — `cancelled` status + SR/PR distinction + department/outlet link |
| Purchase | `shipments`, `shipment_cost_estimates` | Shipment Info (2.18), Cost of Shipment/SIT (2.19) |
| Purchase | `goods_receipts`, `goods_receipt_items` | Store Receive Goods/GRN with QC (2.20, 4.1–4.4) |
| Purchase | `po_email_logs` | Email PO to Supplier (2.12) |
| Purchase | `vendor_returns`, `vendor_return_items` | Goods Return to Vendor (4.14) |
| Purchase | `lc_expenses.goes_to_unit_cost` | Unit Cost Configuration (2.22) |
| Sales | `quotation_templates` | Quotation Template (3.2) |
| Sales | `coupons`, `gift_cards`, `gift_card_transactions` | Coupons & Gift Card (3.9) |
| Sales | `pricelists`, `pricelist_items` | Pricelists / Dynamic Pricing (3.10) |
| Sales | `orders` alter (+incoterm, +salesperson_id, +coupon_id, +approval_status) | Incoterms (3.6), Sales Order Approval (3.4), Salesperson Reports (3.12) |
| Inventory | `stock_reorder_settings` | Min Stock Level / Reorder Point (4.7, 4.8) |
| Inventory | `stock_adjustments`, `stock_adjustment_items` | Stock Adjustment + Approval (4.9) |
| Inventory | `month_end_snapshots` | Month-End Inventory Snapshot (4.13) |
| Accounting | `fiscal_years` | Fiscal Year Configuration (5.3) |
| Accounting | `cost_centres`, `analytic_tags` | Analytical Accounting / Cost Centres, Analytic Tags (5.15, 5.16) |
| Accounting | `party_ledgers` | Customer/Vendor Statements (5.10, 5.14), Supplier Ledger (2.34) |
| Accounting | `petty_cash_transactions`, `fund_transfers` | Petty Cash Management (5.20), Fund Transfers (5.21) |
| Accounting | `assets`, `asset_depreciations`, `asset_disposals` | Asset Register, Depreciation, Disposal (5.23–5.26) |
| Accounting | `budgets` | Budget Variance (5.36) |
| Accounting | `advance_payments`, `payment_allocations` | Advance/Down Payments (5.8, 5.13), Payment Matching (5.11) |

---

## 3. গুরুত্বপূর্ণ Design Decision

### 3.1 Multi-Company — Lightweight, Future-Proof (Overengineer করা হয়নি)
আগের আলোচনায় confirm হয়েছিল Multi-Company **এখনই** দরকার নেই, শুধু architecture-এ room রাখতে হবে। তাই `companies` টেবিল বানানো হয়েছে, আর শুধু মূল financial টেবিলগুলোতে (`fiscal_years`, `chart_of_accounts`, `bank_accounts`, `journal_entries`, `outlets`, `departments`, `assets`) nullable `company_id` যোগ করা হয়েছে — every transactional table-এ company_id ছড়িয়ে দেওয়া হয়নি, কারণ এখন একটাই company, future-এ দরকার হলে সহজেই expand করা যাবে।

### 3.2 `product_requests.status` Enum-এ `'cancelled'` যোগ (Raw SQL দিয়ে)
Existing enum-এ `cancelled` value ছিল না — অথচ "Edit/Cancel/Return PR/SR" (2.5) চাইলে এটা দরকার। Laravel-এর `Blueprint::change()` দিয়ে enum modify করতে গেলে `doctrine/dbal` package লাগে (শুধু এই একটা কারণে নতুন dependency যোগ করা ঠিক না), তাই migration-এ সরাসরি raw SQL (`DB::statement`) ব্যবহার করা হয়েছে — এটাই safer approach।

### 3.3 PR এবং SR — একই `product_requests` Table, `request_type` দিয়ে আলাদা
Client-এর doc-এ SR (Store Requisition) আর PR (Purchase Requisition) দুটো আলাদা concept হিসেবে লেখা থাকলেও, আপনার existing DB-তে একটাই `product_requests` টেবিল আছে। নতুন parallel table না বানিয়ে একটা `request_type` enum (`store_requisition`/`purchase_requisition`) column যোগ করা হয়েছে — ডেটা structure একই থাকায় duplicate logic এড়ানো গেছে।

### 3.4 `approval_status` Column — সব জায়গায় Default `'approved'`
`purchases` ও `orders`-এ নতুন `approval_status` column-এর default value **`'approved'`** রাখা হয়েছে (`'pending'` না) — কারণ আপনার হাজার হাজার existing historical PO/Order ইতিমধ্যে সম্পন্ন। Default `'pending'` রাখলে সবগুলো পুরনো record হঠাৎ "pending approval" দেখাত, যেটা ভুল এবং বিভ্রান্তিকর হতো। নতুন approval workflow শুধু নতুন তৈরি হওয়া PO/Order-এর জন্যই effective হবে (application logic-এ explicitly `'pending'` set করবেন)।

### 3.5 GRN → Vendor Return → Landed Cost — সম্পূর্ণ Chain
```
Purchase (PO) → Shipment (Import হলে) → Goods Receipt (GRN, QC সহ)
    → accepted_qty ভালো হলে stock-এ ঢুকবে (stock_batches-এ নতুন batch তৈরি)
    → rejected_qty থাকলে Vendor Return (Debit Note) তৈরি হবে
LC Expenses → landed_cost_allocations দিয়ে প্রতিটা purchase_detail line-এ allocate হবে
    → GRN-এর সময় সেই landed cost দিয়ে stock_batches.unit_cost সেট হবে
```

### 3.6 Party Ledger — Supplier Ledger + Customer Statement, একই Engine
`party_ledgers` টেবিল `party_type` (vendor/customer) দিয়ে দুটো আলাদা requirement (2.34 Supplier Ledger + 5.10/5.14 Customer/Vendor Statement) একসাথে পূরণ করছে — duplicate ledger logic লাগছে না।

### 3.7 Payment Matching ও Advance Payment — আলাদা কিন্তু Linked
`advance_payments` (আগাম টাকা জমা) আর `payment_allocations` (কোন payment কোন invoice-এর সাথে matched) — দুটো আলাদা টেবিল, কারণ advance payment প্রথমে জমা হয়, পরে নির্দিষ্ট invoice-এর সাথে match (allocate) হয় — এই দুই ধাপ আলাদা টেবিলে থাকাই standard accounting practice।

### 3.8 বাদ দেওয়া হয়েছে এই Round-এ (আগের আলোচনা অনুযায়ী)
- **B2C E-commerce** — সম্পূর্ণ বাদ
- **HR & Payroll** — সম্পূর্ণ বাদ (departments টেবিল শুধু organizational structure-এর জন্য রাখা হয়েছে, employee-specific কিছু নেই)
- **API/Integration (module 6)** — PayPal-সহ সব external integration বাদ; `order_payments`-এর existing generic structure (`transaction_id`, `payment_method`) আপাতত যথেষ্ট, gateway-specific টেবিল পরের phase-এ যোগ হবে

---

## 4. Module-wise Complete Table List (৬৯ Migration File)

| Module | নতুন Table (৫৩টা) | Existing Table-এ Alter (৬টা) |
|---|---|---|
| **0. Foundation** | `currencies`, `companies`, `departments`, `outlets`, `approval_workflows`, `approval_steps`, `approvals`, `notifications` | `vendors`, `users`, `product_requests` |
| **1. Purchase** | `rfqs`, `rfq_items`, `rfq_vendors`, `vendor_quotations`, `vendor_quotation_items`, `comparison_statements`, `comparison_statement_items`, `proforma_invoices`, `letters_of_credit`, `lc_amendments`, `lc_expenses`, `landed_cost_allocations`, `shipments`, `shipment_cost_estimates`, `goods_receipts`, `goods_receipt_items`, `po_email_logs`, `vendor_returns`, `vendor_return_items` | `purchases` |
| **2. Sales** | `quotation_templates`, `sales_quotations`, `sales_quotation_items`, `coupons`, `gift_cards`, `gift_card_transactions`, `pricelists`, `pricelist_items`, `sales_returns`, `sales_return_items` | `orders` |
| **3. Inventory** | `stock_transfers`, `stock_transfer_items`, `stock_batches`, `stock_reorder_settings`, `stock_adjustments`, `stock_adjustment_items`, `month_end_snapshots` | `stock_ledgers` |
| **4. Accounting** | `fiscal_years`, `chart_of_accounts`, `cost_centres`, `analytic_tags`, `journal_entries`, `journal_entry_lines`, `party_ledgers`, `bank_accounts`, `bank_transactions`, `bank_reconciliations`, `petty_cash_transactions`, `fund_transfers`, `assets`, `asset_depreciations`, `asset_disposals`, `budgets`, `advance_payments`, `payment_allocations` | `lc_expenses` (gl_account_id FK wire) |

---

## 5. Reports — কোনো নতুন Table লাগবে না
Purchase Reporting (2.23–2.33), Inventory Reporting (4.19–4.22), Accounting Reporting (5.31–5.39) — এই সবগুলোই existing/নতুন transactional টেবিলের উপর **query/aggregation** দিয়ে বানানো হবে (Eloquent scope বা raw SQL দিয়ে), কোনো আলাদা persisted "report" টেবিল লাগবে না। ব্যতিক্রম শুধু `month_end_snapshots` — কারণ historical month-end data প্রতি মাসে "freeze" করে রাখা দরকার, তাই সেটাই একমাত্র snapshot-ধর্মী টেবিল।

---

## 6. Migration Execution Order (Summary)

```
Step 0  → Foundation (8 tables) + alter vendors, users, product_requests
Step 1  → Purchase (19 tables) + alter purchases
Step 2  → Sales (10 tables) + alter orders
Step 3  → Inventory (7 tables) + alter stock_ledgers
Step 4  → Accounting (18 tables) + alter lc_expenses (gl_account_id FK wire — শেষে, কারণ chart_of_accounts আগে দরকার)
```

সম্পূর্ণ file-by-file order `migrations/` ফোল্ডারে timestamp অনুযায়ী already সঠিকভাবে সাজানো আছে — `php artisan migrate` চালালে automatically ঠিক sequence-এ চলবে।

---

## 7. এরপর যা করবেন

1. **Staging/local copy-তে প্রথমে test করুন**
2. `migrations/` ফোল্ডারের ৬৯টা ফাইল existing migration ফোল্ডারে কপি করুন
3. `php artisan migrate`
4. Seed করুন: অন্তত ১টা `companies` row, কিছু `chart_of_accounts` default entries (Asset/Liability/Equity/Revenue/Expense group accounts), ১টা default `fiscal_year`
5. Phase অনুযায়ী (Purchase → Sales → Inventory → Accounting) Model + Relationship বানানো শুরু — চাইলে এখনই করে দিতে পারি
