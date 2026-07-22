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
            'account',
            'Accountants',
            'Administration',
            'Approve Custom Product Requests',
            'Create Custom Product Requests',
            'Create Product Requests',
            'Delete Custom Product Requests',
            'Edit Custom Product Requests',
            'Manage Brands',
            'Manage Categories',
            'Manage Custom Product Requests',
            'Manage Inventory',
            'Manage Notification',
            'Manage Order Place',
            'Manage Order Receive',
            'Manage Product Requests',
            'Manage Products',
            'Manage Reports',
            'Manage Vendors',
            'Manager',
            'superadmin',
            'View Custom Product Requests',
            'View Product Requests',
            'View Product Stock',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::all());
        }

        $outletRole = Role::where('name', 'Outlet User')->first();
        if ($outletRole) {
            $outletRole->syncPermissions(['Manage Order Place']);
        }
    }
}
