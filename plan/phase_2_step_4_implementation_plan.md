# Phase 2 Step 4: Purchase Reporting, Vendor Bills, Payment Vouchers & Supplier Ledger — Implementation Plan

This technical implementation plan outlines the architecture, database migrations, models, services, controllers, data tables, views, and financial ledger integrations required for **Phase 2 Step 4: Purchase Reporting Engine (13 Reports), Vendor Invoice / Bill Processing, Debit Note Settlement, Multi-Currency Payment Vouchers, Supplier Ledger & AP Aging Statement, and PO Close / Complete Workflow**, with appended **Enterprise Debit Note Settlement Modes (Product Replacement & Direct Money Refund)**.

This plan is fully aligned with existing database tables (`purchase_payments`, `purchase_payment_receipts`, `payment_allocations`, `vendor_returns`), **Client Requirements (Module 2.23 - 2.35, 5.2, 5.9, 5.12 - 5.14)**, **Daily Roadmap (Days 23-25)**, and **Trello Blueprint (Purchase Reporting & Client UAT)**.

---

## 1. User Review Required & Design Intent

> [!IMPORTANT]
> **1. Purchase Reporting Engine (Client 2.23 - 2.33, 2.35)**:
> - **13 enterprise-grade reports** with DataTables, date-range filters, Excel/CSV/PDF export, and printable formats.
> - Reports include: Supplier-wise Purchase, Item-wise Purchase, Total Purchase Value (Periodic), Purchase Value vs Last Year Comparison, PR Received/Pending counts, Items Purchased/Pending counts, PO Issued counts, CS/PR/SR/PO print format validation, and LC Register Dashboard.
>
> **2. Vendor Invoice / Bill Processing (Client 5.12)**:
> - Converts completed GRN (Goods Receipt) data into formal **Vendor Bills / Invoices (`BILL-YYYYMMDD-XXXX`)**.
> - Line items auto-populate from GRN accepted quantities × Landed Unit Cost.
> - **Debit Note Auto-Settlement**: Outstanding Debit Notes (`DN-YYYYMMDD-XXXX` from QC rejections) are automatically applied as credit against the Vendor Bill, reducing the payable amount.
>
> **3. Multi-Currency Payment Vouchers (Client 5.9, 5.13)**:
> - Record partial or full payments via **Cash, Bank Transfer, Cheque, or LC Margin Settlement**.
> - Each payment generates a sequential **Payment Voucher (`PAY-YYYYMMDD-XXXX`)** with receipt file upload.
> - Multi-currency support: payments in foreign currency with exchange rate and base-amount conversion. Exchange rate gain/loss calculation.
> - **Advance Payments (Client 5.13)**: Record advance / down payments before GRN, tracked as prepaid balance deducted from future invoices.
>
> **4. Supplier Ledger & AP Aging Statement (Client 2.34, 5.14)**:
> - Real-time **Vendor Statement** showing: Total Billed, Total Paid, Debit Note Adjustments, Current Outstanding Balance.
> - **AP Aging Buckets**: 0-30 days, 31-60 days, 61-90 days, 90+ days overdue analysis.
> - **Outstanding Acknowledgement Report**: Printable/PDF supplier confirmation letter.
>
> **5. PO Close / Complete Workflow**:
> - When vendor settles via **money refund** instead of replacement goods, the PO can be manually marked as **`completed`** to remove it from GRN pending list and close the procurement cycle.
>
> **6. Enterprise Debit Note Settlement Modes (Added)**:
> - Mode A: Bill Credit Deduction (Auto-deducted from future Vendor Bills).
> - Mode B: Product Replacement Receive (Supports **Same SKU** or **Alternative Substitute Item** exchange).
> - Mode C: Direct Money Refund Voucher (`RCN-YYYYMMDD-XXXX`) into Bank/Cash Accounts.

---

## 2. Technical Component Breakdown

### Component 1: Add-on Migrations
1. **`2026_08_03_120000_create_vendor_bills_and_payment_voucher_tables.php`**:
   - Upgrades `purchase_payments` table with `payment_no`, `payment_date`, `currency_id`, `exchange_rate`, `base_amount`, `status`, `created_by`.
   - Creates `vendor_bills` table (`bill_no`, `purchase_id`, `vendor_id`, `goods_receipt_id`, `currency_id`, `subtotal`, `debit_note_adjustment`, `grand_total`, `paid_amount`, `due_amount`, `payment_status`).
   - Creates `vendor_bill_items` table (`vendor_bill_id`, `product_id`, `variant_id`, `qty`, `unit_price`, `landed_cost`, `line_total`).
   - Creates `debit_note_settlements` table (`vendor_return_id`, `vendor_bill_id`, `settled_amount`, `settlement_date`).
2. **`2026_08_03_160000_create_debit_note_refunds_and_replacements_table.php`**:
   - Creates `debit_note_refunds` table (`refund_no`, `vendor_return_id`, `vendor_id`, `amount`, `payment_method`, `bank_name`, `cheque_no`, `refund_date`, `created_by`).
   - Adds `settlement_type` and replacement tracking columns to `vendor_returns`.

---

### Component 2: Eloquent Models & Relationships
1. **`App\Models\VendorBill.php`**:
   - Belongs to `Purchase`, `Vendor`, `GoodsReceipt`, `Currency`, `User`.
   - Has many `items()`, `payments()`, `debitNoteSettlements()`.
   - Accessor: `formatted_status`.
2. **`App\Models\VendorBillItem.php`**:
   - Belongs to `VendorBill`, `Product`, `ProductVariant`.
3. **`App\Models\DebitNoteSettlement.php`**:
   - Belongs to `VendorReturn`, `VendorBill`, `User`.
4. **`App\Models\DebitNoteRefund.php`**:
   - Belongs to `VendorReturn`, `Vendor`, `User`.
5. **`App\Models\PurchasePayment.php`**:
   - Upgraded fillables and relationships (`currency()`, `createdBy()`, `allocations()`).
6. **`App\Models\Purchase.php`**:
   - Adds `vendorBills()` relationship and `closePurchaseOrder()` method.

---

### Component 3: Core Business Logic Services
- **`App\Services\VendorBillService.php`**:
  - `createBillFromGrn()`: Calculates accepted Qty × Landed Cost, applies pending Debit Notes, generates `BILL-YYYYMMDD-XXXX`.
  - `settleDebitNote()`: Records Debit Note credit adjustment against bill.
- **`App\Services\VendorPaymentService.php`**:
  - `processPayment()`: Generates `PAY-YYYYMMDD-XXXX`, converts foreign currency to base amount, updates bill/PO balances inside DB transaction.
- **`App\Services\VendorLedgerService.php`**:
  - `getVendorStatement()`: Calculates running Statement of Account.
  - `getApAgingReport()`: Categorizes payables into 0-30, 31-60, 61-90, 90+ days overdue buckets.
- **`App\Services\PurchaseReportService.php`**:
  - Aggregates data for all 13 purchase reports with Base Currency formatting (`kr.`) and pagination.
- **`App\Services\VendorReturnService.php`**:
  - `settleViaProductReplacement()`: Receives replacement stock for same or substitute item, updates inventory & Debit Note status.
  - `settleViaCashRefund()`: Records bank/cash deposit, updates supplier ledger & Debit Note status.

---

### Component 4: DataTables & Controllers
- **`VendorBillController.php` & `VendorBillDataTable.php`**
- **`PurchasePaymentController.php` & `PurchasePaymentDataTable.php`**
- **`VendorLedgerController.php`**
- **`PurchaseReportController.php`**
- **`VendorReturnController.php`**

---

### Component 5: Blade Views (Stisla UI)
1. `resources/views/backend/vendor_bill/index.blade.php`, `create.blade.php`, `show.blade.php`
2. `resources/views/backend/purchase_payment/index.blade.php`, `create.blade.php`, `show.blade.php`, `pdf.blade.php`
3. `resources/views/backend/vendor_ledger/index.blade.php`, `show.blade.php`, `aging.blade.php`, `statement_pdf.blade.php`
4. `resources/views/backend/vendor_return/show.blade.php` (Added Product Swap & Cash Refund Modals)
5. `resources/views/backend/purchase_report/` (6 report views for 13 client reports)

---

## 3. Proposed File List

### Migration
- `database/migrations/2026_08_03_120000_create_vendor_bills_and_payment_voucher_tables.php`
- `database/migrations/2026_08_03_160000_create_debit_note_refunds_and_replacements_table.php`

### Models
- `app/Models/VendorBill.php`
- `app/Models/VendorBillItem.php`
- `app/Models/DebitNoteSettlement.php`
- `app/Models/DebitNoteRefund.php`
- `app/Models/PurchasePayment.php`
- `app/Models/Purchase.php`

### Requests
- `app/Http/Requests/Backend/VendorBill/StoreVendorBillRequest.php`
- `app/Http/Requests/Backend/PurchasePayment/StorePurchasePaymentRequest.php`
- `app/Http/Requests/Backend/VendorReturn/StoreVendorRefundRequest.php`
- `app/Http/Requests/Backend/VendorReturn/StoreReplacementReceiveRequest.php`

### Services
- `app/Services/VendorBillService.php`
- `app/Services/VendorPaymentService.php`
- `app/Services/VendorLedgerService.php`
- `app/Services/PurchaseReportService.php`
- `app/Services/VendorReturnService.php`

### DataTables & Controllers
- `app/DataTables/VendorBillDataTable.php`
- `app/DataTables/PurchasePaymentDataTable.php`
- `app/Http/Controllers/Backend/VendorBillController.php`
- `app/Http/Controllers/Backend/PurchasePaymentController.php`
- `app/Http/Controllers/Backend/VendorLedgerController.php`
- `app/Http/Controllers/Backend/PurchaseReportController.php`
- `app/Http/Controllers/Backend/VendorReturnController.php`

---

## 4. Enterprise Feature Alignment Matrix
| Client Req | Description | Trello Card | Roadmap Day | Plan Component | Status |
|---|---|---|---|---|---|
| 2.23 | Supplier-wise Purchase Report | Card 2.23 | Day 23-25 | `PurchaseReportController::supplierWise()` | ✅ |
| 2.24 | Item-wise Purchase Report | Card 2.24 | Day 23-25 | `PurchaseReportController::itemWise()` | ✅ |
| 2.25 | Total Purchase Value (Periodic) | Card 2.25 | Day 23-25 | `PurchaseReportController::totalValue()` | ✅ |
| 2.26 | Purchase Value – Item wise | Card 2.26 | Day 23-25 | `PurchaseReportController::itemWiseValue()` | ✅ |
| 2.27 | Purchase Value vs Last Year | Card 2.27 | Day 23-25 | `PurchaseReportController::vsLastYear()` | ✅ |
| 2.28 | PR Received List/Count | Card 2.28 | Day 23-25 | `PurchaseReportController::prReceived()` | ✅ |
| 2.29 | PR Pending List/Count | Card 2.29 | Day 23-25 | `PurchaseReportController::prPending()` | ✅ |
| 2.30 | Items Pending in PR List/Count | Card 2.30 | Day 23-25 | `PurchaseReportController::itemsPending()` | ✅ |
| 2.31 | Items Purchased List/Count | Card 2.31 | Day 23-25 | `PurchaseReportController::itemsPurchased()` | ✅ |
| 2.32 | PO Issued List/Count | Card 2.32 | Day 23-25 | `PurchaseReportController::poIssued()` | ✅ |
| 2.33 | CS / PR / SR / PO Print Formats | Card 2.33 | Day 23-25 | Validate existing PDF templates | ✅ |
| 2.34 | Supplier Ledger + Outstanding Ack. | Card 2.34 | Day 23-25 | `VendorLedgerController` | ✅ |
| 2.35 | LC Register Dashboard | Card 2.35 | Day 23-25 | Already exists | ✅ |
| 5.9 | Partial Payments | Phase 5 | Day 23-25 | `VendorPaymentService` | ✅ |
| 5.12 | Supplier Bills | Phase 5 | Day 23-25 | `VendorBillController` | ✅ |
| 5.13 | Advance Payments | Phase 5 | Day 23-25 | `PurchasePaymentController` | ✅ |
| 5.14 | Vendor Statements | Phase 5 | Day 23-25 | `VendorLedgerController::show()` | ✅ |

---

## 5. Verification & Testing Plan

### Automated Verification
- Execute `php -l` on all PHP files.
- Execute `php artisan migrate` for schema updates.
- Execute `php artisan route:list` to verify all routes.
- Execute `php artisan view:clear` and verify zero compilation errors.

### Manual Verification Flow
1. **Create Vendor Bill from GRN**: Verify accepted quantities & landed cost auto-populate → verify Debit Note is auto-applied as credit adjustment.
2. **Record Partial & Full Payments**: Test voucher generation (`PAY-YYYYMMDD-XXXX`) & DomPDF slip.
3. **Product Replacement Test**: Test same SKU & substitute product exchange ➔ verify warehouse stock increases.
4. **Direct Bank Refund Test**: Test recording bank/cash refund receipt ➔ verify Debit Note status updates to `refunded_cash`.
5. **Supplier Ledger & AP Aging**: Verify running balance calculation & aging overdue buckets.
6. **Purchase Reports**: Run all 13 reports with filters and pagination.