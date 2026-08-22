<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Core
            'Manage Dashboard',
            'Administration',
            'superadmin',

            // Catalog
            'Manage Categories',
            'Manage Brands',
            'Manage Products',
            'Manage Product Requests',
            'Manage Custom Product Requests',
            'Create Product Requests',
            'View Product Requests',
            'Create Custom Product Requests',
            'Edit Custom Product Requests',
            'Delete Custom Product Requests',
            'View Custom Product Requests',
            'Approve Custom Product Requests',

            // Inventory & WMS
            'Manage Inventory',
            'View Product Stock',
            'Manage Stock Adjustments',
            'Manage Stock Transfers',
            'Manage Stock Ledger',

            // Sales & Fulfillment
            'Manage Orders',
            'Manage Order Place',
            'Manage Order Receive',
            'Manage Sales Quotations',
            'Manage Delivery Orders',
            'Manage Sales Invoices',
            'Manage Customer Payments',
            'Manage Sales Returns',
            'Manage Pricelists',

            // Procurement & Supply Chain
            'Manage Procurement',
            'Manage Vendors',
            'Manage RFQs',
            'Manage Purchase Orders',
            'Manage Letter of Credits',
            'Manage Shipments',
            'Manage Goods Receipts',
            'Manage Vendor Bills',
            'Manage Vendor Returns',

            // Financial Accounting & Reports
            'Manage Accounts',
            'Accountants',
            'Manage Reports',
            'Manage Vendor Ledger',

            // Enterprise Setup
            'Manage Enterprise Setup',
            'Manage Companies',
            'Manage Outlets',
            'Manage Departments',
            'Manage Currencies',
            'Manage Approval Workflows',

            // System & Settings
            'Manage Users',
            'Manage Roles',
            'Manage Permissions',
            'Manage Settings',
            'Manage Notification',
            'Manage Document Sequences',
            'Manage Taxes',
            'Manage Discounts',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign all permissions to Admin Role
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::all());
        }

        // Outlet User Role default permissions
        $outletRole = Role::firstOrCreate(['name' => 'Outlet User', 'guard_name' => 'web']);
        if ($outletRole) {
            $outletRole->syncPermissions([
                'Manage Dashboard',
                'Manage Order Place',
                'View Product Stock',
            ]);
        }

        // Manager Role default permissions
        $managerRole = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        if ($managerRole) {
            $managerRole->syncPermissions([
                'Manage Dashboard',
                'Manage Products',
                'Manage Categories',
                'Manage Inventory',
                'View Product Stock',
                'Manage Stock Transfers',
                'Manage Orders',
                'Manage Sales Quotations',
                'Manage Delivery Orders',
                'Manage Procurement',
                'Manage Reports',
            ]);
        }

        // Staff Role default permissions
        $staffRole = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
        if ($staffRole) {
            $staffRole->syncPermissions([
                'Manage Dashboard',
                'View Product Stock',
                'Manage Orders',
            ]);
        }
    }
}
