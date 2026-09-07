<?php

namespace App\Support;

use Spatie\Permission\Models\Permission;

class PermissionCatalog
{
    /**
     * Complete serial-wise catalog of ERP permissions organized by the 12 navbar modules.
     */
    public static function getModules(): array
    {
        return [
            'dashboard' => [
                'title' => 'Dashboard',
                'icon' => 'fas fa-th-large',
                'badge' => 'Core',
                'permissions' => [
                    'Manage Dashboard',
                ],
            ],
            'categories' => [
                'title' => 'Categories & Catalogs',
                'icon' => 'fas fa-layer-group',
                'badge' => 'Catalog',
                'permissions' => [
                    'Manage Categories',
                ],
            ],
            'products' => [
                'title' => 'Products & Attributes',
                'icon' => 'fas fa-box-open',
                'badge' => 'Catalog',
                'permissions' => [
                    'Manage Products',
                    'View Product Stock',
                    'Manage Product Requests',
                    'Create Product Requests',
                    'View Product Requests',
                    'Manage Custom Product Requests',
                    'Create Custom Product Requests',
                    'Edit Custom Product Requests',
                    'Delete Custom Product Requests',
                    'View Custom Product Requests',
                    'Approve Custom Product Requests',
                ],
            ],
            'inventory' => [
                'title' => 'Inventory & WMS',
                'icon' => 'fas fa-warehouse',
                'badge' => 'Operations',
                'permissions' => [
                    'Manage Inventory',
                    'Manage Stock Adjustments',
                    'Manage Stock Transfers',
                    'Manage Stock Ledger',
                    'Manage Stock Batches',
                    'Manage Warehouse Zones',
                    'Manage Warehouse Bins',
                ],
            ],
            'orders' => [
                'title' => 'Orders & Commercial Sales',
                'icon' => 'fas fa-shopping-bag',
                'badge' => 'Sales',
                'permissions' => [
                    'Manage Orders',
                    'Manage Order Place',
                    'Manage Order Receive',
                    'Manage Sales Quotations',
                    'Manage Delivery Orders',
                    'Manage Sales Invoices',
                    'Manage Sales Returns',
                    'Manage Credit Notes',
                    'Manage Pricelists',
                ],
            ],
            'procurement' => [
                'title' => 'Procurement & Supply Chain',
                'icon' => 'fas fa-file-contract',
                'badge' => 'Supply Chain',
                'permissions' => [
                    'Manage Procurement',
                    'Manage RFQs',
                    'Manage Purchase Orders',
                    'Manage Vendor Bills',
                    'Manage Letter of Credits',
                    'Manage Shipments',
                    'Manage Goods Receipts',
                    'Manage Vendor Returns',
                ],
            ],
            'reports' => [
                'title' => 'Reports & Analytics',
                'icon' => 'fas fa-chart-bar',
                'badge' => 'Analytics',
                'permissions' => [
                    'Manage Reports',
                ],
            ],
            'accounts' => [
                'title' => 'Financial Accounting & Treasury',
                'icon' => 'fas fa-file-invoice-dollar',
                'badge' => 'Finance',
                'permissions' => [
                    'Manage Accounts',
                    'Accountants',
                    'Manage Chart of Accounts',
                    'Manage Journal Vouchers',
                    'Manage Fiscal Years',
                    'Manage Bank Accounts',
                    'Manage Bank Reconciliation',
                    'Manage Petty Cash',
                    'Manage Customer Payments',
                    'Manage Vendor Ledger',
                    'Manage Fixed Assets',
                ],
            ],
            'brands' => [
                'title' => 'Brands',
                'icon' => 'fas fa-tag',
                'badge' => 'Catalog',
                'permissions' => [
                    'Manage Brands',
                ],
            ],
            'vendors' => [
                'title' => 'Vendors & Suppliers',
                'icon' => 'fas fa-truck',
                'badge' => 'Supply Chain',
                'permissions' => [
                    'Manage Vendors',
                ],
            ],
            'enterprise' => [
                'title' => 'Enterprise Setup & Workflows',
                'icon' => 'fas fa-building',
                'badge' => 'Enterprise',
                'permissions' => [
                    'Manage Enterprise Setup',
                    'Manage Companies',
                    'Manage Outlets',
                    'Manage Departments',
                    'Manage Currencies',
                    'Manage Approval Workflows',
                ],
            ],
            'system' => [
                'title' => 'System & Administration',
                'icon' => 'fas fa-cog',
                'badge' => 'System',
                'permissions' => [
                    'Administration',
                    'superadmin',
                    'Manage Users',
                    'Manage Roles',
                    'Manage Permissions',
                    'Manage Pricing Rules',
                    'Manage Taxes',
                    'Manage Discounts',
                    'Manage Document Sequences',
                    'Manage Settings',
                    'Manage Notification',
                ],
            ],
        ];
    }

    /**
     * Get flat array of all registered permission names.
     */
    public static function getAllPermissions(): array
    {
        $all = [];
        foreach (self::getModules() as $module) {
            foreach ($module['permissions'] as $perm) {
                if (!in_array($perm, $all)) {
                    $all[] = $perm;
                }
            }
        }
        return $all;
    }

    /**
     * Retrieve Permission models from database grouped into the 12 modules for UI display.
     */
    public static function getGroupedPermissions(): array
    {
        $dbPermissions = Permission::all()->keyBy('name');
        $modules = self::getModules();
        $grouped = [];
        $allocated = [];

        foreach ($modules as $key => $module) {
            $items = [];
            foreach ($module['permissions'] as $permName) {
                if ($dbPermissions->has($permName)) {
                    $items[] = $dbPermissions->get($permName);
                    $allocated[$permName] = true;
                }
            }
            if (!empty($items)) {
                $grouped[$key] = [
                    'title' => $module['title'],
                    'icon' => $module['icon'],
                    'badge' => $module['badge'],
                    'permissions' => $items,
                ];
            }
        }

        // Catch any orphaned permissions that might exist in DB
        $orphans = [];
        foreach ($dbPermissions as $name => $perm) {
            if (!isset($allocated[$name])) {
                $orphans[] = $perm;
            }
        }

        if (!empty($orphans)) {
            $grouped['other'] = [
                'title' => 'Other Permissions',
                'icon' => 'fas fa-shield-alt',
                'badge' => 'Custom',
                'permissions' => $orphans,
            ];
        }

        return $grouped;
    }
}
