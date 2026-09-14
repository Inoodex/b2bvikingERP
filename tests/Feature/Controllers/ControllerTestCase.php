<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

abstract class ControllerTestCase extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $this->adminUser = User::first() ?? User::factory()->create();
        if (!$this->adminUser->hasRole('Admin')) {
            $this->adminUser->assignRole($role);
        }
    }
}
