<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProductRequestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Define Permissions
        $permissions = [
            'Manage Product Requests', // Access the menu
            'View Product Requests',   // View the list
            'Create Product Requests', // Create new requests
            'Edit Product Requests',   // Edit requests (if applicable)
            'Delete Product Requests', // Delete requests
            'Approve Product Requests', // Admin approval
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Ensure 'Outlet User' Role Exists
        $outletRole = Role::firstOrCreate(['name' => 'Outlet User']);

        // 3. Assign Permissions to Outlet User
        // They need 'Manage' to see the dropdown, and 'Create' to see the link
        $outletRole->givePermissionTo([
            'Manage Product Requests', 
            'View Product Requests',
            'Create Product Requests'
        ]);

        // 4. Assign All to Admin
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }
    }
}
