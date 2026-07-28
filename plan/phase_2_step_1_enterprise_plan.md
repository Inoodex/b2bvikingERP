# Enterprise Plan — Phase 2 Step 1 Complete Perfection (Odoo/SAP Style)

This plan outlines the complete enterprise-grade implementation and design polish for **Phase 2, Step 1** (RFQ, Vendor Quotations, Comparison Statement & Approvals), directly addressing all client requirements, queuing, emailing, and pagination standards.

---

## 🚀 Key Improvements & Requirements

### 1. Separate Dedicated View Page for Quotations (No Modal)
- Replace Modal popups with a dedicated full-page view route: `admin.rfqs.quotations.show` (`/admin/rfqs/{rfq}/quotations/{quotation}`).
- Responsive layout supporting large catalogs (50+ items) using clean pagination & structured tables.
- Includes Vendor info, Currency badges, Exchange rates, Itemized tables with product images/thumbnails, unit prices, sub-totals, and payment terms.

### 2. Product Image & Unit Type Integration
- Add Product Thumbnail images to:
  - RFQ Requested Items table.
  - Vendor Quotation Submission & View pages.
  - Comparison Statement (CS) Matrix.
  - CS PDF Export document.
- Display **Unit Type** (e.g. `Pcs`, `Box`, `Kg`, `Meter`) alongside quantities everywhere (from `product->unit` relationship).

### 3. Fix Single Vendor Award Calculation Bug (`Total Value: 0.00`)
- **Bug Root Cause:** In `ComparisonStatementController@store`, when `award_type === 'single'`, `selected_vendor_quotation_item_id` was left `null`.
- **Fix:** For single-vendor awards, automatically locate and link the corresponding `VendorQuotationItem` of the recommended vendor for every line item, calculating the true total value and passing it to the Approval Engine correctly.

### 4. Asynchronous Queue for PDF & Email Jobs
- **Named Job:** `App\Jobs\SendRfqEmailJob` implementing `ShouldQueue`.
- **Laravel Mailable:** `App\Mail\SendRfqMail` which automatically generates the RFQ PDF attachment and emails it to selected vendors.
- **Mailer Configuration:** Set `MAIL_MAILER=log` for local development so emails log cleanly without breaking if SMTP isn't set up yet.

### 5. Pagination & DataTables Standards
- Ensure all large list tables (RFQs, Vendor Quotation Items) support clean pagination / Yajra DataTables to ensure instant page load speeds.

### 6. Enterprise-Grade Premium UI/UX Redesign
- Redesign RFQ Details (`show.blade.php`), CS Matrix (`cs_matrix.blade.php`), and Quotation Details (`quotation_show.blade.php`) with modern Odoo/Oracle aesthetic:
  - Sleek glassmorphic card headers with crisp typography.
  - Dynamic status badges with micro-animations.
  - Clean KPI metric cards (Total Requested Qty, Lowest Bidder Tag, Exchange Rates).
  - High-contrast table layouts with product thumbnails.

---

## 🛠️ Proposed File Changes

### Models & Services
#### [MODIFY] [VendorQuotationController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/VendorQuotationController.php)
- Add `show($rfqId, $quotationId)` method to render the new dedicated Quotation View page with pagination/tables.

#### [MODIFY] [ComparisonStatementController.php](file:///c:/laragon/www/b2bvikingErp/app/Http/Controllers/Backend/ComparisonStatementController.php)
- Fix Single-Vendor Award logic in `store()` to resolve `selected_vendor_quotation_item_id` and compute non-zero total value.

#### [NEW] [SendRfqEmailJob.php](file:///c:/laragon/www/b2bvikingErp/app/Jobs/SendRfqEmailJob.php)
- Queued background job to handle sending RFQ emails asynchronously.

#### [NEW] [SendRfqMail.php](file:///c:/laragon/www/b2bvikingErp/app/Mail/SendRfqMail.php)
- Mailable class that attaches the generated PDF and sends the email template.

---

### Views & Templates

#### [NEW] [quotation_show.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/rfq/quotation_show.blade.php)
- Create a dedicated, full-page view for Vendor Quotations with product images, unit types, and vendor details.

#### [MODIFY] [show.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/rfq/show.blade.php)
- Update "View Quote" button to redirect to `admin.rfqs.quotations.show`.
- Add product images and unit type column to Requested Items table.
- Redesign CS Card with premium KPIs, badge indicators, and Approval Chain.

#### [MODIFY] [cs_matrix.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/rfq/cs_matrix.blade.php)
- Update CS Matrix to include product thumbnails and unit type labels.

#### [MODIFY] [cs_pdf.blade.php](file:///c:/laragon/www/b2bvikingErp/resources/views/backend/rfq/cs_pdf.blade.php)
- Add product thumbnails and unit types to the PDF layout.

---

### Routes
#### [MODIFY] [web.php](file:///c:/laragon/www/b2bvikingErp/routes/web.php)
- Register `Route::get('rfqs/{rfq}/quotations/{quotation}', [VendorQuotationController::class, 'show'])->name('rfqs.quotations.show');`
- Register `Route::post('rfqs/{rfq}/send-emails', [RfqController::class, 'sendVendorEmails'])->name('rfqs.send-emails');`

---

## 🧪 Verification Plan

### Manual Verification
1. **View Quote Dedicated Page:** Click "View Quote" on RFQ Details page (`/admin/rfqs/2`). Verify it opens `/admin/rfqs/2/quotations/1` displaying product images and unit types (Pcs, Kg, etc.).
2. **Single-Vendor Award Calculation Test:** Generate a CS selecting "Single Vendor". Verify the CS Total Value displays the actual calculated total (e.g. `kr.5,000.00`) instead of `0.00`.
3. **Queue & Email Test:** Trigger "Send Email to Vendors", verify `SendRfqEmailJob` queues in background and `laravel.log` records the sent email with PDF attachment.
4. **PDF Test:** Download CS PDF and verify product images and unit types appear in the table.
