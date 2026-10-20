<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class MenuSearchService
{
    /**
     * Cache TTL in seconds (1 hour).
     */
    protected const CACHE_TTL = 3600;

    /**
     * Retrieve authorized menu registry for the given user, cached for instant retrieval.
     *
     * @param User|null $user
     * @return array
     */
    public function getAuthorizedMenus(?User $user = null): array
    {
        if (!$user) {
            return [];
        }

        $cacheKey = "user_{$user->id}_menu_search_registry";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return $this->buildAuthorizedMenus($user);
        });
    }

    /**
     * Build the filtered array of menu items based on the user's roles and permissions.
     * Integrates configured rich items with dynamic route auto-discovery.
     *
     * @param User $user
     * @return array
     */
    public function buildAuthorizedMenus(User $user): array
    {
        $allConfigMenus = config('menu_search', []);
        $authorized = [];
        $knownRoutes = [];

        $isAdmin = $user->hasRole('Admin') || $user->can('superadmin');

        // 1. Process curated configuration items (Rich Keywords, Custom Icons & Curated Groups)
        foreach ($allConfigMenus as $item) {
            $routeName = $item['route'] ?? '';
            if (!empty($routeName)) {
                $knownRoutes[$routeName] = true;
            }

            // Check authorization
            if (!$this->isUserAuthorizedForItem($user, $item, $isAdmin)) {
                continue;
            }

            // Validate route
            if (empty($routeName) || !Route::has($routeName)) {
                continue;
            }

            try {
                $url = route($routeName);
            } catch (\Throwable $e) {
                continue;
            }

            $authorized[] = [
                'id' => $item['id'],
                'title' => $item['title'],
                'category' => $item['category'] ?? 'mixed',
                'group' => $item['group'] ?? 'General',
                'url' => $url,
                'icon' => $item['icon'] ?? 'fas fa-circle',
                'color' => $item['color'] ?? '#38bdf8',
                'description' => $item['description'] ?? '',
                'keywords' => $item['keywords'] ?? '',
            ];
        }

        // 2. Dynamic Route Auto-Discovery:
        // Automatically discover ANY backend admin routes that were added to Laravel but NOT in config!
        $discoveredItems = $this->discoverDynamicAdminRoutes($user, $knownRoutes, $isAdmin);

        foreach ($discoveredItems as $item) {
            $authorized[] = $item;
        }

        return $authorized;
    }

    /**
     * Check if user is authorized to view a menu item.
     *
     * @param User $user
     * @param array $item
     * @param bool $isAdmin
     * @return bool
     */
    protected function isUserAuthorizedForItem(User $user, array $item, bool $isAdmin): bool
    {
        if ($isAdmin || !empty($item['public'])) {
            return true;
        }

        if (!empty($item['roles']) && is_array($item['roles'])) {
            foreach ($item['roles'] as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }
        }

        if (!empty($item['permissions']) && is_array($item['permissions'])) {
            foreach ($item['permissions'] as $perm) {
                if ($user->can($perm)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Automatically discover and build search items for newly added admin routes.
     * Ensures any newly added sidebar menu or route is immediately searchable with zero manual config edits.
     *
     * @param User $user
     * @param array $knownRoutes
     * @param bool $isAdmin
     * @return array
     */
    protected function discoverDynamicAdminRoutes(User $user, array $knownRoutes, bool $isAdmin): array
    {
        $discovered = [];
        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            $name = $route->getName();

            // Must be a named route starting with 'admin.'
            if (!$name || !str_starts_with($name, 'admin.')) {
                continue;
            }

            // Must respond to GET
            if (!in_array('GET', $route->methods())) {
                continue;
            }

            // Must not require mandatory URL parameters
            if (!empty($route->parameterNames())) {
                continue;
            }

            // Skip routes already registered in config
            if (isset($knownRoutes[$name])) {
                continue;
            }

            // Only take .index routes or standalone root pages (filter out internal actions, ajax, export, create, store, etc.)
            $subPath = substr($name, 6);
            $isIndexOrRoot = str_ends_with($name, '.index') || !str_contains($subPath, '.');

            if (!$isIndexOrRoot) {
                continue;
            }

            // Filter out system, auth, or internal endpoints
            if (str_contains($name, 'get-') ||
                str_contains($name, 'change-status') ||
                str_contains($name, 'change-frontend-show') ||
                str_contains($name, 'login') ||
                str_contains($name, 'logout') ||
                str_contains($name, 'password') ||
                str_contains($name, 'search') ||
                str_contains($name, 'data') ||
                str_contains($name, 'export') ||
                str_contains($name, 'ajax') ||
                str_contains($name, 'cart')) {
                continue;
            }

            // Determine Controller & Action
            $actionName = $route->getActionName();

            // Check permissions for non-admin users
            if (!$isAdmin) {
                $requiredPerms = $this->resolveRoutePermissions($actionName);
                if (!empty($requiredPerms)) {
                    $hasPerm = false;
                    foreach ($requiredPerms as $p) {
                        if ($user->can($p)) {
                            $hasPerm = true;
                            break;
                        }
                    }
                    if (!$hasPerm) {
                        continue;
                    }
                }
            }

            // Format human-friendly title
            $cleanName = preg_replace('/^admin\./', '', $name);
            $cleanName = preg_replace('/\.index$/', '', $cleanName);
            $title = ucwords(str_replace(['-', '_', '.'], ' ', $cleanName));

            // Smart categorizer
            $metadata = $this->inferRouteMetadata($name, $cleanName, $actionName);

            try {
                $url = route($name);
            } catch (\Throwable $e) {
                continue;
            }

            $discovered[] = [
                'id' => str_replace(['.', '_'], '-', $cleanName),
                'title' => $title,
                'category' => $metadata['category'],
                'group' => $metadata['group'],
                'url' => $url,
                'icon' => $metadata['icon'],
                'color' => $metadata['color'],
                'description' => 'Manage and view ' . $title,
                'keywords' => strtolower($title . ' ' . str_replace(['-', '_'], ' ', $cleanName)),
            ];
        }

        return $discovered;
    }

    /**
     * Map controller action to acceptable permissions (matches CheckPermission middleware).
     *
     * @param string $action
     * @return array
     */
    protected function resolveRoutePermissions(string $action): array
    {
        $map = [
            'CategoryController' => ['Manage Categories'],
            'SubCategoryController' => ['Manage Categories'],
            'ChildCategoryController' => ['Manage Categories'],
            'SliderController' => ['Manage Categories'],
            'ProductTypeController' => ['Manage Categories'],
            'ProductController' => ['Manage Products'],
            'BrandController' => ['Manage Brands', 'Manage Products'],
            'UnitController' => ['Manage Products'],
            'ColorController' => ['Manage Products'],
            'SizeController' => ['Manage Products'],
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
            'SalesQuotationController' => ['Manage Sales Quotations', 'Manage Orders'],
            'SalesOrderController' => ['Manage Orders', 'Manage Order Place'],
            'FrontendOrderController' => ['Manage Orders', 'Manage Order Place'],
            'BookingController' => ['Manage Orders', 'Manage Order Place'],
            'DeliveryOrderController' => ['Manage Delivery Orders', 'Manage Orders'],
            'SalesInvoiceController' => ['Manage Sales Invoices', 'Manage Orders', 'Manage Accounts'],
            'SalesReturnController' => ['Manage Sales Returns', 'Manage Orders'],
            'CreditNoteController' => ['Manage Credit Notes', 'Manage Sales Returns', 'Manage Orders'],
            'PricelistController' => ['Manage Pricelists', 'Manage Orders'],
            'PricingRuleController' => ['Manage Pricing Rules', 'Manage Settings', 'Administration'],
            'CouponController' => ['Manage Discounts', 'Administration'],
            'GiftCardController' => ['Manage Discounts', 'Administration'],
            'RfqController' => ['Manage RFQs', 'Manage Procurement'],
            'VendorQuotationController' => ['Manage RFQs', 'Manage Procurement'],
            'ComparisonStatementController' => ['Manage RFQs', 'Manage Procurement'],
            'PurchaseOrderController' => ['Manage Purchase Orders', 'Manage Procurement'],
            'VendorBillController' => ['Manage Vendor Bills', 'Manage Procurement'],
            'LetterOfCreditController' => ['Manage Letter of Credits', 'Manage Procurement'],
            'ShipmentController' => ['Manage Shipments', 'Manage Procurement'],
            'GoodsReceiptController' => ['Manage Goods Receipts', 'Manage Procurement'],
            'VendorReturnController' => ['Manage Vendor Returns', 'Manage Procurement'],
            'PurchaseController' => ['Manage Purchase Orders', 'Manage Procurement'],
            'ReportController' => ['Manage Reports'],
            'FinancialReportController' => ['Manage Reports'],
            'PayablesReceivablesReportController' => ['Manage Reports'],
            'PurchaseReportController' => ['Manage Reports'],
            'SalesReportController' => ['Manage Reports'],
            'ChartOfAccountController' => ['Manage Chart of Accounts'],
            'JournalVoucherController' => ['Manage Journal Vouchers'],
            'FiscalYearController' => ['Manage Fiscal Years'],
            'BankAccountController' => ['Manage Bank Accounts'],
            'BankReconciliationController' => ['Manage Bank Reconciliation', 'Manage Bank Accounts'],
            'PettyCashController' => ['Manage Petty Cash'],
            'FundTransferController' => ['Manage Accounts'],
            'CustomerPaymentController' => ['Manage Customer Payments'],
            'PurchasePaymentController' => ['Manage Vendor Bills'],
            'AssetController' => ['Manage Fixed Assets'],
            'CompanyController' => ['Manage Companies', 'Manage Enterprise Setup'],
            'OutletController' => ['Manage Outlets', 'Manage Enterprise Setup'],
            'DepartmentController' => ['Manage Departments', 'Manage Enterprise Setup'],
            'CurrencyController' => ['Manage Currencies', 'Manage Enterprise Setup'],
            'ApprovalWorkflowController' => ['Manage Approval Workflows', 'Manage Enterprise Setup'],
            'ApprovalInboxController' => ['Manage Approval Workflows', 'Manage Enterprise Setup'],
            'UserController' => ['Manage Users'],
            'RolesController' => ['Manage Roles'],
            'PermissionController' => ['Manage Permissions'],
            'TaxController' => ['Manage Taxes'],
            'DocumentSequenceController' => ['Manage Document Sequences'],
            'SettingController' => ['Manage Settings', 'Administration'],
            'PaymentSettingController' => ['Manage Settings', 'Administration'],
            'FeatureToggleController' => ['Manage Settings', 'Administration'],
            'BackupController' => ['Manage Settings', 'Administration'],
            'RecycleBinController' => ['Manage Settings', 'Administration'],
        ];

        foreach ($map as $ctrl => $perms) {
            if (str_contains($action, $ctrl)) {
                return $perms;
            }
        }

        return [];
    }

    /**
     * Intelligently infer group, category, color, and icon for newly auto-discovered routes.
     *
     * @param string $routeName
     * @param string $cleanName
     * @param string $actionName
     * @return array
     */
    protected function inferRouteMetadata(string $routeName, string $cleanName, string $actionName): array
    {
        $lower = strtolower($routeName . ' ' . $cleanName . ' ' . $actionName);

        if (str_contains($lower, 'stock') || str_contains($lower, 'inventory') || str_contains($lower, 'warehouse') || str_contains($lower, 'bin') || str_contains($lower, 'batch')) {
            return [
                'category' => 'inventory',
                'group' => 'Inventory & Movements',
                'icon' => 'fas fa-boxes',
                'color' => '#10b981',
            ];
        }

        if (str_contains($lower, 'product') || str_contains($lower, 'category') || str_contains($lower, 'brand') || str_contains($lower, 'unit') || str_contains($lower, 'size') || str_contains($lower, 'color')) {
            return [
                'category' => 'inventory',
                'group' => 'Products & Catalog',
                'icon' => 'fas fa-box-open',
                'color' => '#38bdf8',
            ];
        }

        if (str_contains($lower, 'order') || str_contains($lower, 'quotation') || str_contains($lower, 'invoice') || str_contains($lower, 'sale') || str_contains($lower, 'return') || str_contains($lower, 'booking') || str_contains($lower, 'coupon') || str_contains($lower, 'pricelist')) {
            return [
                'category' => 'orders',
                'group' => 'Sales & Orders',
                'icon' => 'fas fa-file-invoice',
                'color' => '#3b82f6',
            ];
        }

        if (str_contains($lower, 'rfq') || str_contains($lower, 'purchase') || str_contains($lower, 'vendor') || str_contains($lower, 'supplier') || str_contains($lower, 'shipment') || str_contains($lower, 'receipt') || str_contains($lower, 'bill') || str_contains($lower, 'credit')) {
            return [
                'category' => 'procurement',
                'group' => 'Procurement & SCM',
                'icon' => 'fas fa-truck-loading',
                'color' => '#f59e0b',
            ];
        }

        if (str_contains($lower, 'report') || str_contains($lower, 'ledger') || str_contains($lower, 'balance') || str_contains($lower, 'account') || str_contains($lower, 'finance') || str_contains($lower, 'tax') || str_contains($lower, 'payment') || str_contains($lower, 'bank') || str_contains($lower, 'cash')) {
            return [
                'category' => 'finance',
                'group' => 'Finance & Accounts',
                'icon' => 'fas fa-calculator',
                'color' => '#06b6d4',
            ];
        }

        if (str_contains($lower, 'user') || str_contains($lower, 'role') || str_contains($lower, 'permission') || str_contains($lower, 'setting') || str_contains($lower, 'company') || str_contains($lower, 'outlet') || str_contains($lower, 'department') || str_contains($lower, 'currency') || str_contains($lower, 'backup') || str_contains($lower, 'recycle')) {
            return [
                'category' => 'settings',
                'group' => 'Enterprise Administration',
                'icon' => 'fas fa-cogs',
                'color' => '#a855f7',
            ];
        }

        return [
            'category' => 'mixed',
            'group' => 'ERP Navigation',
            'icon' => 'fas fa-layer-group',
            'color' => '#6366f1',
        ];
    }

    /**
     * Clear menu search cache for a user or all users.
     *
     * @param User|null $user
     * @return void
     */
    public function clearUserCache(?User $user = null): void
    {
        if ($user) {
            Cache::forget("user_{$user->id}_menu_search_registry");
        }
    }
}
