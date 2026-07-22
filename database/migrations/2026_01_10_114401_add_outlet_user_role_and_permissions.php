<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure Spatie permissions are available
        $permissions = [
            'Manage Product Requests',
            'Create Product Requests',
            'View Product Stock'
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::findOrCreate($permission);
        }

        $outletRole = \Spatie\Permission\Models\Role::findOrCreate('Outlet User');
        $outletRole->syncPermissions(['Create Product Requests', 'View Product Stock']);

        $adminRole = \Spatie\Permission\Models\Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(['Manage Product Requests', 'View Product Stock']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
