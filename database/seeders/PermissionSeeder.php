<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Support\PermissionCatalog;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed all serial-wise permissions defined in PermissionCatalog
        $permissions = PermissionCatalog::getAllPermissions();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Assign all permissions to Admin Role (Super Admin)
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::all());
        }

        // 3. Outlet User Role default permissions
        $outletRole = Role::firstOrCreate(['name' => 'Outlet User', 'guard_name' => 'web']);
        if ($outletRole) {
            $outletRole->syncPermissions([
                'Manage Dashboard',
                'Manage Order Place',
                'View Product Stock',
            ]);
        }

        // 4. Manager Role default permissions
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

        // 5. Staff Role default permissions
        $staffRole = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
        if ($staffRole) {
            $staffRole->syncPermissions([
                'Manage Dashboard',
                'View Product Stock',
                'Manage Orders',
            ]);
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
