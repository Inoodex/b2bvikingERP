<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CustomProductRequestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Define Permissions
        $permissions = [
            'Manage Custom Product Requests', // Access the menu and manage all requests
            'View Custom Product Requests',   // View the list
            'Create Custom Product Requests', // Create new requests
            'Edit Custom Product Requests',   // Edit requests (if applicable)
            'Delete Custom Product Requests', // Delete requests
            'Approve Custom Product Requests', // Admin approval
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Ensure 'Outlet User' Role Exists
        $outletRole = Role::firstOrCreate(['name' => 'Outlet User']);

        // 3. Assign Permissions to Outlet User
        $outletRole->givePermissionTo([
            'Create Custom Product Requests'
        ]);

        // 4. Assign Permissions to Admin (but NOT Create permission for Outlet User requests)
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            // Admin gets all permissions EXCEPT Create Custom Product Requests
            // Admin can still manage/approve requests but won't create outlet requests
            $adminPermissions = [
                'Manage Custom Product Requests',
                'View Custom Product Requests',
                'Edit Custom Product Requests',
                'Delete Custom Product Requests',
                'Approve Custom Product Requests',
            ];
            $adminRole->givePermissionTo($adminPermissions);
        }
    }
}
