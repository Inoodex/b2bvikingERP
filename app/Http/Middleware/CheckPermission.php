<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('admin.login');
        }

        // Check if user is trying to access /admin routes
        if ($request->is('admin') || $request->is('admin/*')) {
            // Prevent standalone 'Outlet User' and 'User' from accessing /admin routes unless they hold administrative roles
            if ($user->hasAnyRole(['Outlet User', 'User']) && !$user->hasAnyRole(['Admin', 'Manager', 'Staff', 'Admin- Order Receive and Despatch'])) {
                abort(403, 'Access Denied. Your account does not have permission to access the backend dashboard.');
            }
        }

        // Full bypass for Super Admin / Admin role
        if ($user->hasRole('Admin') || $user->can('superadmin')) {
            return $next($request);
        }

        $requiredPermissions = $this->getRequiredPermissions($request);

        if (!empty($requiredPermissions)) {
            $hasPermission = false;
            foreach ($requiredPermissions as $permission) {
                if ($user->can($permission)) {
                    $hasPermission = true;
                    break;
                }
            }

            if (!$hasPermission) {
                abort(403, 'Access Denied. You do not have the required permission to access this page.');
            }
        }

        return $next($request);
    }

    /**
     * Determine acceptable permissions for the requested route/action.
     * Returns an array of acceptable permission names (any one will grant access),
     * or null if the route is open to authenticated backend users or handles self-governed checks.
     */
    private function getRequiredPermissions(Request $request): ?array
    {
        $action = $request->route()?->getActionName() ?? '';

        // Specific case for Product list view (allows View Product Stock)
        if (str_contains($action, 'ProductController@index')) {
            return ['Manage Products', 'View Product Stock'];
        }

        // Announcement actions managed under Settings / Administration
        if (str_contains($action, 'ProductController@announcementIndex') || str_contains($action, 'ProductController@sendAnnouncement')) {
            return ['Manage Settings', 'Administration'];
        }

        // ReviewController requires Product Request or Product Management permissions
        if (str_contains($action, 'ReviewController')) {
            return ['Create Product Requests', 'Manage Product Requests', 'Manage Products'];
        }

        // Controllers that manage their own internal granular permissions
        if (str_contains($action, 'ProductRequestController') || str_contains($action, 'CustomProductRequestController') || str_contains($action, 'ProfileController')) {
            return null;
        }

        // Mapping controllers to acceptable permissions
        $map = [
            // Categories & Catalogs
            'CategoryController' => ['Manage Categories'],
            'SubCategoryController' => ['Manage Categories'],
            'ChildCategoryController' => ['Manage Categories'],
            'SliderController' => ['Manage Categories'],
            'ProductTypeController' => ['Manage Categories'],

            // Products & Attributes
            'ProductController' => ['Manage Products'],
            'BrandController' => ['Manage Brands', 'Manage Products'],
            'UnitController' => ['Manage Products'],
            'ColorController' => ['Manage Products'],
            'SizeController' => ['Manage Products'],

            // Inventory & Warehousing
            'StockAdjustmentController' => ['Manage Stock Adjustments'],
            'StockTransferController' => ['Manage Stock Transfers'],
            'StockLedgerController' => ['Manage Stock Ledger'],
            'StockBatchController' => ['Manage Stock Batches'],
            'MonthEndSnapshotController' => ['Manage Stock Ledger', 'Manage Inventory'],
            'WarehouseZoneController' => ['Manage Warehouse Zones'],
            'WarehouseBinController' => ['Manage Warehouse Bins'],
            'BinTransferController' => ['Manage Warehouse Bins', 'Manage Inventory'],
            'InventoryReportController' => ['Manage Inventory', 'View Product Stock'],
            'IssueController' => ['Manage Inventory'],

            // Orders, Sales & Quotations
            'SalesQuotationController' => ['Manage Sales Quotations', 'Manage Orders'],
            'SalesOrderController' => ['Manage Orders', 'Manage Order Place'],
            'FrontendOrderController' => ['Manage Orders', 'Manage Order Place'],
            'BookingController' => ['Manage Orders', 'Manage Order Place'],
            'DeliveryOrderController' => ['Manage Delivery Orders', 'Manage Orders'],
            'SalesInvoiceController' => ['Manage Sales Invoices', 'Manage Orders', 'Manage Accounts', 'Accountants'],
            'SalesReturnController' => ['Manage Sales Returns', 'Manage Orders'],
            'CreditNoteController' => ['Manage Credit Notes', 'Manage Sales Returns', 'Manage Orders', 'Manage Accounts', 'Accountants'],
            'PricelistController' => ['Manage Pricelists', 'Manage Orders'],
            'PricingRuleController' => ['Manage Pricing Rules', 'Manage Settings', 'Administration', 'Manage Orders'],
            'CouponController' => ['Manage Discounts', 'Administration', 'Manage Orders'],
            'GiftCardController' => ['Manage Discounts', 'Administration', 'Manage Orders'],

            // Procurement & Suppliers
            'RfqController' => ['Manage RFQs', 'Manage Procurement'],
            'VendorQuotationController' => ['Manage RFQs', 'Manage Procurement'],
            'ComparisonStatementController' => ['Manage RFQs', 'Manage Procurement'],
            'PurchaseOrderController' => ['Manage Purchase Orders', 'Manage Procurement'],
            'VendorBillController' => ['Manage Vendor Bills', 'Manage Procurement', 'Manage Accounts', 'Accountants'],
            'LetterOfCreditController' => ['Manage Letter of Credits', 'Manage Procurement'],
            'ShipmentController' => ['Manage Shipments', 'Manage Procurement'],
            'LandedCostController' => ['Manage Shipments', 'Manage Procurement'],
            'GoodsReceiptController' => ['Manage Goods Receipts', 'Manage Procurement'],
            'VendorReturnController' => ['Manage Vendor Returns', 'Manage Procurement'],
            'PurchaseController' => ['Manage Order Receive', 'Manage Procurement'],
            'VendorController' => ['Manage Vendors'],

            // Financial Accounting & Banking
            'ChartOfAccountController' => ['Manage Chart of Accounts', 'Manage Accounts', 'Accountants'],
            'BankAccountController' => ['Manage Bank Accounts', 'Manage Accounts', 'Accountants'],
            'JournalVoucherController' => ['Manage Journal Vouchers', 'Manage Accounts', 'Accountants'],
            'FiscalYearController' => ['Manage Fiscal Years', 'Manage Accounts', 'Accountants'],
            'BankReconciliationController' => ['Manage Bank Reconciliation', 'Manage Bank Accounts', 'Manage Accounts', 'Accountants'],
            'PettyCashController' => ['Manage Petty Cash', 'Manage Accounts', 'Accountants'],
            'FundTransferController' => ['Manage Bank Accounts', 'Manage Accounts', 'Accountants'],
            'AssetController' => ['Manage Fixed Assets', 'Manage Accounts', 'Accountants'],
            'AccountController' => ['Manage Accounts', 'Accountants', 'Manage Reports'],
            'CustomerPaymentController' => ['Manage Customer Payments', 'Manage Accounts', 'Accountants'],
            'PurchasePaymentController' => ['Manage Vendor Bills', 'Manage Accounts', 'Accountants', 'Manage Procurement'],
            'VendorLedgerController' => ['Manage Vendor Ledger', 'Manage Accounts', 'Accountants', 'Manage Procurement', 'Manage Reports'],
            'FinancialReportController' => ['Manage Reports', 'Manage Accounts', 'Accountants'],

            // Reports
            'ReportController' => ['Manage Reports'],
            'PurchaseReportController' => ['Manage Reports', 'Manage Procurement'],
            'SalesReportController' => ['Manage Reports', 'Manage Orders'],

            // Enterprise Setup
            'CompanyController' => ['Manage Companies', 'Manage Enterprise Setup'],
            'OutletController' => ['Manage Outlets', 'Manage Enterprise Setup'],
            'DepartmentController' => ['Manage Departments', 'Manage Enterprise Setup'],
            'CurrencyController' => ['Manage Currencies', 'Manage Enterprise Setup'],
            'ApprovalWorkflowController' => ['Manage Approval Workflows', 'Manage Enterprise Setup'],
            'ApprovalInboxController' => ['Manage Approval Workflows', 'Manage Enterprise Setup', 'Manage Orders', 'Manage Procurement', 'Manage Accounts'],
            'OrderApprovalController' => ['Manage Approval Workflows', 'Manage Enterprise Setup', 'Manage Orders'],

            // System Administration
            'UserController' => ['Manage Users', 'Administration'],
            'RolesController' => ['Manage Roles', 'Administration'],
            'PermissionController' => ['Manage Permissions', 'Administration'],
            'SettingController' => ['Manage Settings', 'Administration'],
            'TaxController' => ['Manage Taxes', 'Administration'],
            'DiscountController' => ['Manage Discounts', 'Administration'],
            'DocumentSequenceController' => ['Manage Document Sequences', 'Manage Settings', 'Administration'],
            'NotificationController' => ['Manage Notification', 'Administration'],

            // Dashboard
            'DashboardController' => ['Manage Dashboard'],
        ];

        foreach ($map as $controller => $permissions) {
            if (str_contains($action, $controller)) {
                return $permissions;
            }
        }

        return null;
    }
}
