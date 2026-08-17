<?php

use App\Http\Controllers\Backend\BookingController;
use App\Http\Controllers\Backend\BrandController;
// use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Backend\AccountController as BackendAccountController;
use App\Http\Controllers\Backend\CartController as BackendCartController;
use App\Http\Controllers\Backend\CreditNoteController;
use App\Http\Controllers\Backend\DeliveryOrderController;
use App\Http\Controllers\Backend\SalesReturnController;
use App\Http\Controllers\Backend\SubCategoryController;
use App\Http\Controllers\Backend\TaxController;
use App\Http\Controllers\Backend\ChildCategoryController;
use App\Http\Controllers\Backend\ColorController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DiscountController;
use App\Http\Controllers\Backend\FrontendOrderController;
use App\Http\Controllers\Backend\InventoryReportController;
use App\Http\Controllers\Backend\IssueController;
use App\Http\Controllers\Backend\IssueReturnController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\ProductRequestController;
use App\Http\Controllers\Backend\ProfileController;
use App\Http\Controllers\Backend\PurchaseController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\RolesController;
use App\Http\Controllers\Backend\PricingRuleController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\SizeController;
use App\Http\Controllers\Backend\SliderController;
use App\Http\Controllers\Backend\StockLedgerController;
use App\Http\Controllers\Backend\UnitController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\VendorController;
use App\Http\Controllers\Backend\ProductTypeController;
use App\Http\Controllers\Backend\ReviewController;
use App\Http\Controllers\Backend\CustomProductRequestController;
use App\Http\Controllers\Backend\CurrencyController;
use App\Http\Controllers\Backend\CompanyController;
use App\Http\Controllers\Backend\DepartmentController;
use App\Http\Controllers\Backend\OutletController;
use App\Http\Controllers\Backend\ApprovalWorkflowController;
use App\Http\Controllers\Backend\RfqController;
use App\Http\Controllers\Backend\VendorQuotationController;
use App\Http\Controllers\Backend\VendorBillController;
use App\Http\Controllers\Backend\PurchasePaymentController;
use App\Http\Controllers\Backend\DocumentSequenceController;
use App\Http\Controllers\Backend\CouponController;
use App\Http\Controllers\Backend\GiftCardController;
use App\Http\Controllers\Backend\PricelistController;
use App\Http\Controllers\Backend\SalesOrderController;
use App\Http\Controllers\Backend\SalesQuotationController;
use App\Http\Controllers\Backend\VendorLedgerController;
use App\Http\Controllers\Backend\PurchaseReportController;
use App\Http\Controllers\Backend\VendorReturnController;
use App\Http\Controllers\Frontend\AccountController as FrontendAccountController;
use App\Http\Controllers\Frontend\CartController as FrontendCartController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\OrderController as FrontendCustomerOrderController;
use App\Http\Controllers\Frontend\ProductRequestController as FrontendProductRequestController;
use App\Http\Controllers\Frontend\WishlistController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::middleware('auth')->group(function () {

// Sample file download route (public)
Route::get('/sample/{filename}', function ($filename) {
    $path = 'sample/' . $filename;
    if (Storage::disk('public')->exists($path)) {
        $file = Storage::disk('public')->path($path);
        return response()->download($file, $filename);
    }
    abort(404);
})->name('admin.products.sample.download');

Route::controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/shop', 'shop')->name('shop');
    Route::get('/about', 'about')->name('about');
    Route::get('/b2b-policy', 'b2bPolicy')->name('b2b.policy');
    Route::get('/terms-conditions', 'termsConditions')->name('terms.conditions');
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'submitContact')->name('contact.submit');
    Route::get('/product/{slug}', 'productDetails')->name('product.details');
    Route::get('/products/live-search', 'liveSearch')->name('frontend.products.live-search');
});
Route::get('/frontend/reviews/product/{productId}', [ReviewController::class, 'getProductReviews'])->name('frontend.reviews.product');
// Frontend cart page
Route::get('/cart', [FrontendCartController::class, 'index'])->name('cart.index');

if (app()->environment('local')) {
    Route::get('/_preview/error/{code}', function (int $code) {
        abort($code);
    })->whereNumber('code')->name('error.preview');
}

// ── Frontend Cart API (DB-backed, auth users only) ──────────────────────────
Route::middleware(['auth', 'role:Outlet User|User'])->group(function () {
    Route::controller(FrontendAccountController::class)->group(function () {
        Route::get('/my-account', 'index')->name('account.index');
        Route::post('/my-account/profile', 'updateProfile')->name('account.profile.update');
        Route::post('/my-account/password', 'updatePassword')->name('account.password.update');
        Route::post('/my-account/order-form/add-to-cart', 'addOrderFormToCart')->name('account.order-form.add-to-cart');
        Route::post('/my-account/order-form/save', 'saveOrderForm')->name('account.order-form.save');
        Route::post('/my-account/saved-forms/{savedRequest}/checkout', 'checkoutSavedForm')->name('account.saved-forms.checkout');
        Route::delete('/my-account/saved-forms/{savedRequest}', 'deleteSavedForm')->name('account.saved-forms.delete');
        Route::post('/my-account/custom-product-requests', 'storeCustomProductRequest')->name('account.custom-product-requests.store');
        Route::get('/my-account/custom-product-requests/{customProductRequest}', 'showCustomProductRequest')->name('account.custom-product-requests.show');
        Route::get('/my-account/custom-product-requests/{customProductRequest}/images/{index}', 'showCustomProductRequestImage')
            ->whereNumber('index')
            ->name('account.custom-product-requests.images.show');
        Route::post('/my-account/custom-product-requests/{customProductRequest}/reorder', 'reorderCustomProductRequest')->name('account.custom-product-requests.reorder');
    });

    Route::controller(FrontendCartController::class)->group(function () {
        Route::get('/checkout', 'checkout')->name('checkout.index');
        Route::post('/checkout/place-order', 'placeOrder')->name('checkout.place-order');
        Route::get('/frontend/cart/items', 'items')->name('frontend.cart.items');
        Route::post('/frontend/cart/add', 'add')->name('frontend.cart.add');
        Route::post('/frontend/cart/remove', 'remove')->name('frontend.cart.remove');
        Route::post('/frontend/cart/update-qty', 'updateQuantity')->name('frontend.cart.update-qty');
        Route::post('/frontend/cart/clear', 'clear')->name('frontend.cart.clear');
    });

    Route::controller(FrontendCustomerOrderController::class)->group(function () {
        Route::get('/my-orders', 'index')->name('orders.index');
        Route::get('/my-orders/{order}', 'show')->name('orders.show');
        Route::get('/my-orders/{order}/pi-invoice', 'piInvoice')->name('orders.pi-invoice');
        Route::get('/my-orders/{order}/pi-invoice/download', 'downloadPiInvoice')->name('orders.pi-invoice.download');
        Route::post('/my-orders/{order}/reorder', 'reorder')->name('orders.reorder');
    });

    Route::controller(FrontendProductRequestController::class)->group(function () {
        Route::get('/my-product-requests/{productRequest}', 'show')->name('product-requests.show');
        Route::get('/my-product-requests/{productRequest}/pi-invoice', 'piInvoice')->name('product-requests.pi-invoice');
        Route::get('/my-product-requests/{productRequest}/pi-invoice/download', 'downloadPiInvoice')->name('product-requests.pi-invoice.download');
    });

    // ── Wishlist ─────────────────────────────────────────────────────────────
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/wishlist/ids', [WishlistController::class, 'getIds'])->name('wishlist.ids');
    Route::post('/wishlist/clear', [WishlistController::class, 'clearAll'])->name('wishlist.clear');
    Route::get('/frontend/reviews/user-product/{productId}', [ReviewController::class, 'getUserProductReview'])->name('frontend.reviews.user-product');
    Route::post('/frontend/reviews/store', [ReviewController::class, 'store'])->name('frontend.reviews.store');
    Route::delete('/frontend/reviews/{reviewId}', [ReviewController::class, 'destroy'])->name('frontend.reviews.destroy');
});

// Route::get('/dashboard', function () {
//     return view('backend.dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

/** Backend Routes */
Route::group(['middleware' => ['auth', 'check.permission'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::put('users/change-status', [UserController::class, 'changeStatus'])->name('users.change-status');
    Route::resource('users', UserController::class);
    Route::resource('role', RolesController::class);
    Route::resource('permission', PermissionController::class);

    /** category routes */
    Route::put('category/change-status', [CategoryController::class, 'changeStatus'])->name('category.change-status');
    Route::put('category/change-frontend-show', [CategoryController::class, 'changeFrontendShow'])->name('category.change-frontend-show');
    Route::resource('category', CategoryController::class);

    /** slider routes */
    Route::put('slider/change-status', [SliderController::class, 'changeStatus'])->name('slider.change-status');
    Route::resource('slider', SliderController::class);

    /** subcategory routes */
    Route::put('subcategory/change-status', [SubCategoryController::class, 'changeStatus'])->name('subcategory.change-status');
    Route::resource('sub-category', SubCategoryController::class);

    /** child category routes */
    Route::controller(ChildCategoryController::class)->group(function () {
        Route::put('child-category/change-status', 'changeStatus')->name('child-category.change-status');
        Route::get('get-subcategories', 'getSubCategories')->name('get-subCategories');
        Route::get('get-child-categories', 'getChildCategories')->name('get-child-categories');
    });
    Route::resource('child-category', ChildCategoryController::class);
    /* brand controller */
    Route::put('brand/change-status', [BrandController::class, 'changeStatus'])->name('brand.change-status');
    Route::resource('brand', BrandController::class);


    /** vendor */
    Route::controller(VendorController::class)->group(function () {
        Route::get('vendor/get-details', 'getVendorDetails')->name('vendor.get-details');
        Route::put('vendor/change-status', 'changeStatus')->name('vendor.change-status');
    });
    Route::resource('vendor', VendorController::class);

    /** Unit Routes */
    Route::put('units/change-status', [UnitController::class, 'changeStatus'])->name('units.change-status');
    Route::resource('units', UnitController::class);

    /** Color Routes */
    Route::put('colors/change-status', [ColorController::class, 'changeStatus'])->name('colors.change-status');
    Route::resource('colors', ColorController::class);

    /** Document Sequence Routes */
    Route::get('document-sequences', [DocumentSequenceController::class, 'index'])->name('document-sequences.index');
    Route::put('document-sequences/{documentSequence}', [DocumentSequenceController::class, 'update'])->name('document-sequences.update');

    /** Sales Quotation Routes */
    Route::post('sales-quotations/{salesQuotation}/clone', [SalesQuotationController::class, 'clone'])->name('sales-quotations.clone');
    Route::post('sales-quotations/{salesQuotation}/convert-to-order', [SalesQuotationController::class, 'convertToOrder'])->name('sales-quotations.convert-to-order');
    Route::get('sales-quotations/{salesQuotation}/pdf', [SalesQuotationController::class, 'pdf'])->name('sales-quotations.pdf');
    Route::resource('sales-quotations', SalesQuotationController::class);

    /** Sales Order Routes */
    Route::post('orders/{order}/release-credit', [FrontendOrderController::class, 'releaseCreditHold'])->name('orders.release-credit');
    Route::post('sales-orders/{order}/release-credit', [SalesOrderController::class, 'releaseCreditHold'])->name('sales-orders.release-credit');
    Route::get('sales-orders/check-credit', [SalesOrderController::class, 'checkCustomerCredit'])->name('sales-orders.check-credit');
    Route::resource('sales-orders', SalesOrderController::class);

    /** Pricelist Routes */
    Route::put('pricelists/change-status', [PricelistController::class, 'changeStatus'])->name('pricelists.change-status');
    Route::get('pricelists/resolve-price', [PricelistController::class, 'resolvePrice'])->name('pricelists.resolve-price');
    Route::resource('pricelists', PricelistController::class);

    /** Coupon Routes */
    Route::put('coupons/change-status', [CouponController::class, 'changeStatus'])->name('coupons.change-status');
    Route::get('coupons/validate', [CouponController::class, 'validateCoupon'])->name('coupons.validate');
    Route::resource('coupons', CouponController::class);

    /** Gift Card Routes */
    Route::put('gift-cards/change-status', [GiftCardController::class, 'changeStatus'])->name('gift-cards.change-status');
    Route::post('gift-cards/{giftCard}/adjust-balance', [GiftCardController::class, 'adjustBalance'])->name('gift-cards.adjust-balance');
    Route::get('gift-cards/validate', [GiftCardController::class, 'validateGiftCard'])->name('gift-cards.validate');
    Route::resource('gift-cards', GiftCardController::class);

    /** Size Routes */
    Route::put('sizes/change-status', [SizeController::class, 'changeStatus'])->name('sizes.change-status');
    Route::resource('sizes', SizeController::class);

    /** Product Routes */
    Route::controller(ProductController::class)->group(function () {
        Route::put('products/change-status', 'changeStatus')->name('products.change-status');
        Route::get('products/import', 'importView')->name('products.import.view');
        Route::post('products/import/preview', 'importPreview')->name('products.import.preview');
        Route::post('products/import', 'importStore')->name('products.import.store');
        Route::get('products/announcement', 'announcementIndex')->name('products.announcement.index');
        Route::post('products/announcement/send', 'sendAnnouncement')->name('products.announcement.send');
    });
    Route::resource('products', ProductController::class);

    /** Product Type Routes */
    Route::put('product-types/change-status', [ProductTypeController::class, 'changeStatus'])->name('product-types.change-status');
    Route::resource('product-types', ProductTypeController::class);

    /** Procurement (Phase 2) Routes */
    // Comparison Statement Routes
    Route::get('rfqs/{rfq}/cs', [\App\Http\Controllers\Backend\ComparisonStatementController::class, 'create'])->name('rfqs.cs.create');
    Route::post('rfqs/{rfq}/cs', [\App\Http\Controllers\Backend\ComparisonStatementController::class, 'store'])->name('rfqs.cs.store');
    Route::post('rfqs/cs/{cs}/approve', [\App\Http\Controllers\Backend\ComparisonStatementController::class, 'approve'])->name('rfqs.cs.approve');
    Route::post('rfqs/cs/{cs}/reject', [\App\Http\Controllers\Backend\ComparisonStatementController::class, 'reject'])->name('rfqs.cs.reject');
    Route::get('rfqs/{rfq}/cs/{cs}/pdf', [\App\Http\Controllers\Backend\ComparisonStatementController::class, 'downloadPdf'])->name('rfqs.cs.pdf');
    Route::get('rfqs/{rfq}/cs/{cs}/pdf/view', [\App\Http\Controllers\Backend\ComparisonStatementController::class, 'streamPdf'])->name('rfqs.cs.pdf.view');

    Route::controller(RfqController::class)->group(function () {
        Route::put('rfqs/{id}/close', 'close')->name('rfqs.close');
        Route::get('rfqs/fetch-source-items', 'fetchSourceItems')->name('rfqs.fetch-source-items');
        Route::get('rfqs/{id}/pdf/view', 'streamPdf')->name('rfqs.pdf.view');
        Route::get('rfqs/{id}/pdf/download', 'downloadPdf')->name('rfqs.pdf.download');
        Route::post('rfqs/{rfq}/send-emails', 'sendVendorEmails')->name('rfqs.send-emails');
    });
    Route::resource('rfqs', RfqController::class);

    Route::controller(VendorQuotationController::class)->group(function () {
        Route::get('rfqs/{rfq}/vendors/{vendor}/quotation', 'create')->name('rfqs.quotations.create');
        Route::post('rfqs/quotations', 'store')->name('rfqs.quotations.store');
        Route::get('rfqs/{rfq}/quotations/{quotation}', 'show')->name('rfqs.quotations.show');
    });

    /** Phase 2 Step 2: PO, PI & LC Routes */
    Route::controller(\App\Http\Controllers\Backend\PurchaseOrderController::class)->group(function () {
        Route::get('purchase-orders', 'index')->name('purchase-orders.index');
        Route::post('purchase-orders/generate-from-cs/{cs}', 'generateFromCs')->name('purchase-orders.generate-from-cs');
        Route::get('purchase-orders/{id}', 'show')->name('purchase-orders.show');
        Route::post('purchase-orders/{id}/approve', 'approve')->name('purchase-orders.approve');
        Route::post('purchase-orders/{id}/cancel', 'cancel')->name('purchase-orders.cancel');
        Route::post('purchase-orders/{id}/send-email', 'sendEmail')->name('purchase-orders.send-email');
        Route::get('purchase-orders/{id}/pdf/download', 'downloadPdf')->name('purchase-orders.pdf.download');
        Route::get('purchase-orders/{id}/pdf/view', 'streamPdf')->name('purchase-orders.pdf.view');
    });

    Route::post('proforma-invoices/store', [\App\Http\Controllers\Backend\ProformaInvoiceController::class, 'store'])->name('proforma-invoices.store');

    Route::controller(\App\Http\Controllers\Backend\LetterOfCreditController::class)->group(function () {
        Route::get('letters-of-credit', 'index')->name('letters-of-credit.index');
        Route::post('letters-of-credit', 'store')->name('letters-of-credit.store');
        Route::get('letters-of-credit/{id}', 'show')->name('letters-of-credit.show');
        Route::post('letters-of-credit/{id}/amendments', 'addAmendment')->name('letters-of-credit.amendments.store');
    });

    /** Phase 2 Step 3: Shipment, SIT, GRN, Landed Cost & Vendor Return Routes */
    Route::controller(\App\Http\Controllers\Backend\ShipmentController::class)->group(function () {
        Route::get('shipments', 'index')->name('shipments.index');
        Route::get('shipments/create', 'create')->name('shipments.create');
        Route::post('shipments', 'store')->name('shipments.store');
        Route::get('shipments/{id}', 'show')->name('shipments.show');
        Route::get('shipments/{id}/edit', 'edit')->name('shipments.edit');
        Route::put('shipments/{id}', 'update')->name('shipments.update');
        Route::patch('shipments/{id}/status', 'updateStatus')->name('shipments.update-status');
        Route::delete('shipments/{id}', 'destroy')->name('shipments.destroy');
    });

    Route::controller(\App\Http\Controllers\Backend\GoodsReceiptController::class)->group(function () {
        Route::get('goods-receipts', 'index')->name('goods-receipts.index');
        Route::get('goods-receipts/create', 'create')->name('goods-receipts.create');
        Route::post('goods-receipts', 'store')->name('goods-receipts.store');
        Route::get('goods-receipts/{id}', 'show')->name('goods-receipts.show');
        Route::get('goods-receipts/{id}/pdf', 'streamPdf')->name('goods-receipts.pdf');
    });

    Route::controller(\App\Http\Controllers\Backend\LandedCostController::class)->group(function () {
        Route::get('landed-cost/{purchase_id}', 'show')->name('landed-cost.show');
        Route::get('landed-cost/{purchase_id}/recalculate', 'recalculate')->name('landed-cost.recalculate');
    });

    Route::controller(\App\Http\Controllers\Backend\VendorReturnController::class)->group(function () {
        Route::get('vendor-returns', 'index')->name('vendor-returns.index');
        Route::get('vendor-returns/create', 'create')->name('vendor-returns.create');
        Route::post('vendor-returns', 'store')->name('vendor-returns.store');
        Route::get('vendor-returns/{id}', 'show')->name('vendor-returns.show');
        Route::post('vendor-returns/settle-replacement', 'settleReplacement')->name('vendor-returns.settle-replacement');
        Route::post('vendor-returns/settle-refund', 'settleRefund')->name('vendor-returns.settle-refund');
    });

    /** Booking Routes */
    Route::controller(BookingController::class)->group(function () {
        Route::get('bookings/get-subcategories', 'getSubCategories')->name('bookings.get-subcategories');
        Route::get('bookings/get-childcategories', 'getChildCategories')->name('bookings.get-childcategories');
        Route::get('bookings/view-invoice/{id}', 'viewInvoice')->name('bookings.view-invoice');
        Route::get('bookings/download-pdf/{id}', 'downloadPdf')->name('bookings.download-pdf');
        Route::get('bookings/download-excel/{id}', 'downloadExcel')->name('bookings.download-excel');
        Route::get('bookings/export-excel', 'exportExcel')->name('bookings.export-excel');
    });
    // New route (Primary)
    Route::controller(BookingController::class)->group(function () {
        Route::put('bookings/status-update', 'changeStatus')->name('bookings.status-update');
        // Legacy route (Fallback to prevent RouteNotFoundException)
        Route::put('bookings/change-status', 'changeStatus')->name('bookings.change-status');
    });

    Route::resource('bookings', BookingController::class);

    /** Purchase Routes */
    Route::controller(PurchaseController::class)->group(function () {
        Route::get('purchases/get-booking-details', 'getBookingDetails')->name('purchases.get-booking-details');
        Route::get('purchases/{id}/invoice', 'viewInvoice')->name('purchases.view-invoice');
        Route::get('purchases/{id}/download-pdf', 'downloadPdf')->name('purchases.download-pdf');
        Route::get('purchases/{id}/download-excel', 'downloadExcel')->name('purchases.download-excel');
        Route::get('purchases/export-excel', 'exportExcel')->name('purchases.export-excel');
        Route::get('purchases/{id}/attachment/download', 'downloadLegacyAttachment')->name('purchases.download-legacy-attachment');
        Route::get('purchases/{id}/attachments/{attachmentId}/download', 'downloadAttachment')->name('purchases.download-attachment');
        Route::post('purchases/{id}/attachments', 'uploadAttachments')->name('purchases.upload-attachments');
        Route::delete('purchases/{id}/attachments/{attachmentId}', 'deleteAttachment')->name('purchases.delete-attachment');
    });
    Route::resource('purchases', PurchaseController::class);

    /** Frontend Orders (Customer Orders) */
    Route::controller(FrontendOrderController::class)->group(function () {
        Route::get('orders', 'index')->name('orders.index');
        Route::post('orders/{order}/pi-info', 'savePiInfo')->name('orders.pi-info.save');
        Route::get('orders/{order}/pi-invoice', 'piInvoice')->name('orders.pi-invoice');
        Route::get('orders/{order}/pi-invoice/download', 'downloadPiInvoice')->name('orders.pi-invoice.download');
        Route::get('orders/{order}/view-invoice', 'viewInvoice')->name('orders.view-invoice');
        Route::get('orders/{order}/download-invoice', 'downloadInvoice')->name('orders.download-invoice');
        Route::get('orders/{order}/download-customer-invoice', 'downloadCustomerInvoice')->name('orders.download-customer-invoice');
        Route::get('orders/{order}', 'show')->name('orders.show');
        Route::put('orders/{order}/status', 'updateStatus')->name('orders.update-status');
        Route::post('orders/{order}/approve', [\App\Http\Controllers\Backend\OrderApprovalController::class, 'approve'])->name('orders.approve');
        Route::post('orders/{order}/reject', [\App\Http\Controllers\Backend\OrderApprovalController::class, 'reject'])->name('orders.reject');
        Route::delete('orders/{order}', 'destroy')->name('orders.destroy');
    });

    /** Pricing Rules (Multipliers) */
    Route::resource('pricing-rules', PricingRuleController::class);

    /** Tax / VAT Rules */
    Route::put('taxes/change-status', [TaxController::class, 'changeStatus'])->name('taxes.change-status');
    Route::put('taxes/set-default', [TaxController::class, 'setDefault'])->name('taxes.set-default');
    Route::resource('taxes', TaxController::class);

    /** Discount Rules */
    Route::put('discounts/change-status', [DiscountController::class, 'changeStatus'])->name('discounts.change-status');
    Route::put('discounts/set-default', [DiscountController::class, 'setDefault'])->name('discounts.set-default');
    Route::resource('discounts', DiscountController::class);

    /** Report Routes */
    Route::controller(ReportController::class)->group(function () {
        Route::get('reports', 'index')->name('reports.index');
        Route::get('reports/stock', 'stockReport')->name('reports.stock');
        Route::get('reports/purchase', 'purchaseReport')->name('reports.purchase');
        Route::get('reports/product-purchase-history', 'productPurchaseHistory')->name('reports.product-purchase-history');
        Route::get('reports/low-stock', 'lowStockReport')->name('reports.low-stock');
        Route::get('reports/profit-loss', 'profitLossReport')->name('reports.profit-loss');
        Route::get('reports/audit', 'auditReport')->name('reports.audit');
        Route::get('reports/best-sellers', 'bestSellers')->name('reports.best-sellers');
        Route::get('reports/top-customers', 'topCustomers')->name('reports.top-customers');
        Route::get('reports/orders', 'orderReport')->name('reports.orders');
        Route::get('reports/orders/pdf', 'orderReportPdf')->name('reports.orders.pdf');
        Route::get('reports/orders/pdf/async', 'orderReportPdfAsync')->name('reports.orders.pdf.async');
        Route::get('reports/orders/pdf/download/{file}', 'downloadReportPdf')->name('reports.orders.pdf.download');
        Route::get('low-stock-check', 'lowStockCheck')->name('low-stock-check'); // AJAX endpoint
        Route::post('low-stock-mark-read', 'markNotificationsRead')->name('low-stock-mark-read');
        Route::get('notifications/all', 'allNotifications')->name('notifications.all');
        Route::get('reports/current-stock', 'currentStockReport')->name('reports.current-stock');
        Route::get('reports/current-stock/export', 'exportCurrentStockReport')->name('reports.current-stock.export');
    });


    /** Product Request Routes */
    Route::controller(ProductRequestController::class)->group(function () {
        Route::post('product-requests/{id}/pi-info', 'savePiInfo')->name('product-requests.pi-info.save');
        Route::get('product-requests/{id}/pi-invoice', 'piInvoice')->name('product-requests.pi-invoice');
        Route::get('product-requests/{id}/pi-invoice/download', 'downloadPiInvoice')->name('product-requests.pi-invoice.download');
        Route::get('product-requests/{id}/view-invoice', 'viewInvoice')->name('product-requests.view-invoice');
        Route::get('product-requests/{id}/invoice', 'printPdf')->name('product-requests.download-invoice');
        Route::get('product-requests/{id}/download-customer-invoice', 'downloadCustomerInvoice')->name('product-requests.download-customer-invoice');
        Route::put('product-requests/update-status/{id}', 'updateStatus')->name('product-requests.update-status');
    });
    Route::resource('product-requests', ProductRequestController::class);

    /** Custom Product Request Routes */
    Route::controller(CustomProductRequestController::class)->group(function () {
        Route::get('custom-product-requests/{customProductRequest}/images/{index}', 'showImage')
            ->whereNumber('index')
            ->name('custom-product-requests.images.show');
        Route::put('custom-product-requests/update-status/{id}', 'updateStatus')->name('custom-product-requests.update-status');
    });
    Route::resource('custom-product-requests', CustomProductRequestController::class);

    // Settings
    Route::controller(SettingController::class)->group(function () {
        Route::get('settings', 'index')->name('settings.index');
        Route::get('settings/general', 'general')->name('settings.general');
        Route::put('settings/general', 'updateGeneral')->name('settings.general.update');
        Route::get('settings/email', 'email')->name('settings.email');
        Route::put('settings/email', 'updateEmail')->name('settings.email.update');
        Route::post('settings/email/test', 'sendTestEmail')->name('settings.email.test');
    });

    /** Inventory Plane Routes */
    Route::controller(IssueController::class)->group(function () {
        Route::get('issues/get-request-items', 'getRequestItems')->name('issues.get-request-items');
        Route::get('issues/{id}/view-invoice', 'viewInvoice')->name('issues.view-invoice');
        Route::get('issues/{id}/invoice', 'downloadInvoice')->name('issues.download-invoice');
    });
    Route::resource('issues', IssueController::class);



    Route::get('stock-ledger', [StockLedgerController::class, 'index'])->name('stock-ledger.index');
    Route::controller(InventoryReportController::class)->group(function () {
        Route::get('inventory-reports/export-pdf', 'exportPdf')->name('inventory-reports.export-pdf');
        Route::get('inventory-reports', 'index')->name('inventory-reports.index');
    });

    /** profile routes */
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('profile');
        Route::post('/profile/update', 'updateProfile')->name('profile.update');
        Route::post('/profile/update/password', 'updatePassword')->name('password.update');
    });

    /** Review Routes */
    Route::controller(ReviewController::class)->group(function () {
        Route::get('reviews/user-product/{productId}', 'getUserProductReview')->name('reviews.user-product');
        Route::post('reviews/store', 'store')->name('reviews.store');
        Route::get('reviews/product/{productId}', 'getProductReviews')->name('reviews.product');
        Route::delete('reviews/{reviewId}', 'destroy')->name('reviews.destroy');
        Route::get('reviews', 'index')->name('reviews.index');
    });

    /** Cart Routes (Database Cart System) */
    Route::controller(BackendCartController::class)->group(function () {
        Route::get('cart/count', 'getCount')->name('cart.count');
        Route::get('cart/items', 'getItems')->name('cart.items');
        Route::get('cart/product-ids', 'getProductIds')->name('cart.product-ids');
        Route::get('cart/vendor', 'getVendor')->name('cart.vendor');
        Route::post('cart/add', 'add')->name('cart.add');
        Route::post('cart/remove', 'remove')->name('cart.remove');
        Route::post('cart/clear', 'clear')->name('cart.clear');
    });
    /** Accounts & Payments */
Route::controller(BackendAccountController::class)->group(function () {
    Route::get('accounts', 'index')->name('accounts.index');
    Route::get('accounts/record-payment', 'create')->name('accounts.record-payment');
    Route::get('accounts/search-order', 'searchOrder')->name('accounts.search-order');
    Route::get('accounts/due-orders', 'dueOrders')->name('accounts.due-orders');
    Route::get('accounts/vendor-payments', 'vendorPaymentIndex')->name('accounts.vendor-payments.index');
    Route::get('accounts/vendor-payments/record-payment', 'createVendorPayment')->name('accounts.vendor-payments.record-payment');
    Route::get('accounts/vendor-payments/search-purchase', 'searchPurchase')->name('accounts.vendor-payments.search-purchase');
    Route::get('accounts/vendor-payments/due-purchases', 'vendorDuePurchases')->name('accounts.vendor-payments.due-purchases');
    Route::get('accounts/vendor-payments/pdf', 'vendorPaymentHistoryPdf')->name('accounts.vendor-payments.pdf');
    Route::get('accounts/vendor-payments/pdf/view', 'vendorPaymentHistoryView')->name('accounts.vendor-payments.pdf.view');
    Route::get('accounts/vendor-payments/{payment}/pdf', 'vendorPaymentSinglePdf')->name('accounts.vendor-payments.single.pdf');
    Route::get('accounts/vendor-payments/{payment}/view', 'vendorPaymentSingleView')->name('accounts.vendor-payments.single.view');
    Route::get('accounts/purchases/{purchase}/payments/pdf', 'vendorPurchasePaymentsPdf')->name('accounts.vendor-purchases.payments.pdf');
    Route::get('accounts/purchases/{purchase}/payments/view', 'vendorPurchasePaymentsView')->name('accounts.vendor-purchases.payments.view');
    Route::get('accounts/payments/pdf', 'paymentHistoryPdf')->name('accounts.payments.pdf');
    Route::get('accounts/payments/pdf/view', 'paymentHistoryPdfView')->name('accounts.payments.pdf.view');
    Route::get('accounts/payments/{payment}/pdf', 'paymentSinglePdf')->name('accounts.payments.single.pdf');
    Route::get('accounts/payments/{payment}/view', 'paymentSingleView')->name('accounts.payments.single.view');
    Route::get('accounts/orders/{order}/payments/pdf', 'paymentOrderPdf')->name('accounts.orders.payments.pdf');
    Route::get('accounts/orders/{order}/payments/view', 'paymentOrderView')->name('accounts.orders.payments.view');
    Route::post('accounts/orders/{order}/payment', 'storePayment')->name('accounts.store-payment');
    Route::post('accounts/purchases/{purchase}/payment', 'storePurchasePayment')->name('accounts.vendor-payments.store');
    Route::get('accounts/payments/receipts/{receipt}/download', 'downloadReceipt')->name('accounts.receipts.download');
    Route::delete('accounts/payments/receipts/{receipt}', 'destroyReceipt')->name('accounts.receipts.destroy');
    Route::get('accounts/vendor-payments/receipts/{receipt}/download', 'downloadPurchaseReceipt')->name('accounts.vendor-payments.receipts.download');
    Route::delete('accounts/vendor-payments/receipts/{receipt}', 'destroyPurchaseReceipt')->name('accounts.vendor-payments.receipts.destroy');
});

    /** Enterprise Master Data Routes */
    Route::group(['prefix' => 'master', 'as' => 'master.'], function () {
        Route::resource('currencies', CurrencyController::class);
        Route::resource('companies', CompanyController::class);
        Route::resource('departments', DepartmentController::class);
        Route::resource('outlets', OutletController::class);
        Route::resource('approval-workflows', ApprovalWorkflowController::class);
    });

    /** Phase 2 Step 4: Vendor Bills, Payments, Ledger, Returns & Reports */
    Route::resource('vendor-bills', VendorBillController::class)->only(['index', 'create', 'store', 'show']);
    
    Route::get('purchase-payments/{purchase_payment}/pdf', [PurchasePaymentController::class, 'streamPdf'])->name('purchase-payments.pdf');
    Route::resource('purchase-payments', PurchasePaymentController::class)->only(['index', 'create', 'store', 'show']);



    Route::controller(VendorLedgerController::class)->prefix('vendor-ledger')->name('vendor-ledger.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/aging', 'agingReport')->name('aging');
        Route::get('/{vendor_id}', 'show')->name('show');
        Route::get('/{vendor_id}/pdf', 'exportPdf')->name('pdf');
    });

    Route::controller(PurchaseReportController::class)->prefix('purchase-reports')->name('purchase-reports.')->group(function () {
        Route::get('/supplier-wise', 'supplierWise')->name('supplier-wise');
        Route::get('/item-wise', 'itemWise')->name('item-wise');
        Route::get('/total-value', 'totalValue')->name('total-value');
        Route::get('/vs-last-year', 'vsLastYear')->name('vs-last-year');
        Route::get('/pr-status', 'prStatus')->name('pr-status');
        Route::get('/po-status', 'poStatus')->name('po-status');
    });

    /** Phase 3 Step 3.7: Customer Sales Return (RMA) & Credit Notes Engine */
    Route::controller(SalesReturnController::class)->group(function () {
        Route::get('sales-returns/get-order-items', 'getOrderItems')->name('sales-returns.get-order-items');
        Route::post('sales-returns/{salesReturn}/approve', 'approve')->name('sales-returns.approve');
    });
    Route::resource('sales-returns', SalesReturnController::class);

    Route::controller(CreditNoteController::class)->group(function () {
        Route::post('credit-notes/{creditNote}/settle', 'settle')->name('credit-notes.settle');
        Route::get('credit-notes/{creditNote}/pdf', 'downloadPdf')->name('credit-notes.pdf');
    });
    Route::resource('credit-notes', CreditNoteController::class);

    /** Phase 3 Step 3.8: Delivery Orders, Packing Slips & Partial Fulfillment Engine */
    Route::controller(DeliveryOrderController::class)->group(function () {
        Route::get('delivery-orders/get-order-items', 'getOrderItems')->name('delivery-orders.get-order-items');
        Route::post('delivery-orders/{deliveryOrder}/dispatch', 'dispatch')->name('delivery-orders.dispatch');
        Route::get('delivery-orders/{deliveryOrder}/pdf', 'downloadPdf')->name('delivery-orders.pdf');
    });
    Route::resource('delivery-orders', DeliveryOrderController::class);

});
});

require __DIR__ . '/auth.php';
