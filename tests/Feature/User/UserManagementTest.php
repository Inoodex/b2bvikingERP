<?php

namespace Tests\Feature\User;

use App\Models\Company;
use App\Models\Department;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Role $adminRole;
    protected Role $userRole;
    protected Role $managerRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::findOrCreate('Admin');
        $this->userRole = Role::findOrCreate('User');
        $this->managerRole = Role::findOrCreate('Manager');

        $permission = Permission::findOrCreate('Administration');
        $this->adminRole->givePermissionTo($permission);
        $this->managerRole->givePermissionTo($permission);

        $this->admin = User::factory()->create([
            'role_id' => $this->adminRole->id,
            'name' => 'Super Admin ' . uniqid(),
            'email' => 'admin_' . uniqid() . '@erp.test',
        ]);
        $this->admin->assignRole('Admin');
    }

    public function test_admin_can_create_customer_user_with_segment_and_credit_limit(): void
    {
        $email = 'buyer_' . uniqid() . '@nordic.dk';
        $payload = [
            'name' => 'Nordic Wholesale ApS',
            'email' => $email,
            'phone' => '+45 12 34 56 78',
            'password' => 'secret1234',
            'user_role' => $this->userRole->id,
            'status' => 1,
            'customer_segment' => 'wholesale',
            'credit_limit' => 75000.00,
            'discount_type' => 'percent',
            'discount_value' => 15.00,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $payload);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $created = User::where('email', $email)->first();
        $this->assertNotNull($created);
        $this->assertEquals('Nordic Wholesale ApS', $created->name);
        $this->assertEquals('wholesale', $created->customer_segment);
        $this->assertEquals(75000.00, (float)$created->credit_limit);
        $this->assertEquals('percent', $created->discount_type);
        $this->assertEquals(15.00, (float)$created->discount_value);
        $this->assertTrue($created->isCustomer());
        $this->assertFalse($created->isStaff());
    }

    public function test_admin_can_create_internal_staff_with_enterprise_organization(): void
    {
        $uid = uniqid();
        $company = Company::create(['name' => 'Viking HQ ' . $uid, 'code' => 'VHQ_' . $uid, 'status' => 1]);
        $department = Department::create(['name' => 'Finance Dept ' . $uid, 'code' => 'FIN_' . $uid, 'status' => 1]);
        $outlet = Outlet::create(['name' => 'Strøget Outlet ' . $uid, 'code' => 'STR_' . $uid, 'type' => 'store', 'status' => 1]);

        $email = 'john_' . $uid . '@viking.dk';
        $payload = [
            'name' => 'Finance Officer John',
            'email' => $email,
            'phone' => '+45 87 65 43 21',
            'password' => 'secret1234',
            'user_role' => $this->managerRole->id,
            'status' => 1,
            'company_id' => $company->id,
            'department_id' => $department->id,
            'outlet_id' => $outlet->id,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $payload);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $staff = User::where('email', $email)->first();
        $this->assertNotNull($staff);
        $this->assertEquals($company->id, $staff->company_id);
        $this->assertEquals($department->id, $staff->department_id);
        $this->assertEquals($outlet->id, $staff->outlet_id);
        $this->assertTrue($staff->isStaff());
        $this->assertFalse($staff->isCustomer());
    }

    public function test_admin_can_update_user_enterprise_and_customer_details(): void
    {
        $user = User::factory()->create([
            'role_id' => $this->userRole->id,
            'customer_segment' => 'retail',
            'credit_limit' => 1000.00,
        ]);
        $user->assignRole('User');

        $updatePayload = [
            'name' => 'Updated VIP Customer',
            'email' => $user->email,
            'phone' => '+45 99 88 77 66',
            'user_role' => $this->userRole->id,
            'status' => 1,
            'customer_segment' => 'b2b_vip',
            'credit_limit' => 150000.00,
            'discount_type' => 'flat',
            'discount_value' => 500.00,
            'min_order_amount' => 5000.00,
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $user->id), $updatePayload);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('Updated VIP Customer', $user->name);
        $this->assertEquals('b2b_vip', $user->customer_segment);
        $this->assertEquals(150000.00, (float)$user->credit_limit);
        $this->assertEquals('flat', $user->discount_type);
        $this->assertEquals(500.00, (float)$user->discount_value);
        $this->assertEquals(5000.00, (float)$user->min_order_amount);
    }

    public function test_user_scopes_staff_and_customers(): void
    {
        // 1 Customer
        $customer = User::factory()->create([
            'role_id' => $this->userRole->id,
            'customer_segment' => 'wholesale',
        ]);
        $customer->assignRole('User');

        // 1 Staff
        $staff = User::factory()->create([
            'role_id' => $this->managerRole->id,
            'customer_segment' => 'retail',
        ]);
        $staff->assignRole('Manager');

        $staffUsers = User::staff()->pluck('id')->toArray();
        $customerUsers = User::customers()->pluck('id')->toArray();

        $this->assertContains($this->admin->id, $staffUsers);
        $this->assertContains($staff->id, $staffUsers);
        $this->assertNotContains($customer->id, $staffUsers);

        $this->assertContains($customer->id, $customerUsers);
        $this->assertNotContains($staff->id, $customerUsers);
        $this->assertNotContains($this->admin->id, $customerUsers);
    }
}
