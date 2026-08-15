<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "--- ROLES IN DATABASE ---\n";
$roles = Role::all();
foreach ($roles as $role) {
    $count = User::role($role->name)->count();
    echo "- Role: {$role->name} (Users count: {$count})\n";
}

echo "\n--- TOTAL USERS ---\n";
echo "Total Users: " . User::count() . "\n";

echo "\n--- USERS WITHOUT ROLE ---\n";
$noRoleCount = User::doesntHave('roles')->count();
echo "Users without any role assigned: " . $noRoleCount . "\n";

echo "\n--- USER TABLE COLUMNS ---\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
echo implode(', ', $columns) . "\n";
