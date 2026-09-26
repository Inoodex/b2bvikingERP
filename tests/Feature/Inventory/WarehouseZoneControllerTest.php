<?php

namespace Tests\Feature\Inventory;

use App\Models\Company;
use App\Models\Outlet;
use App\Models\User;
use App\Models\WarehouseZone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WarehouseZoneControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function getOrCreateUser(): User
    {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $user = User::first() ?? User::create([
            'name' => 'Admin User',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);
        if (!$user->hasRole('Admin')) {
            $user->assignRole($role);
        }
        return $user;
    }

    protected function getOrCreateOutlet(): Outlet
    {
        $company = Company::first() ?? Company::create([
            'name' => 'Test Company',
            'code' => 'TC-' . uniqid(),
            'status' => 1,
        ]);

        return Outlet::create([
            'name' => 'Test Outlet ' . uniqid(),
            'code' => 'OUT-' . uniqid(),
            'company_id' => $company->id,
            'status' => 1,
        ]);
    }

    public function test_it_can_create_a_warehouse_zone(): void
    {
        $user = $this->getOrCreateUser();
        $outlet = $this->getOrCreateOutlet();

        $this->actingAs($user);

        $response = $this->post(route('admin.warehouse-zones.store'), [
            'outlet_id' => $outlet->id,
            'name' => 'Zone Alpha',
            'type' => 'active',
            'status' => 1,
        ]);

        $response->assertRedirect(route('admin.warehouse-zones.index'));
        $this->assertDatabaseHas('warehouse_zones', [
            'outlet_id' => $outlet->id,
            'name' => 'Zone Alpha',
            'type' => 'active',
        ]);
    }

    public function test_it_can_update_a_warehouse_zone(): void
    {
        $user = $this->getOrCreateUser();
        $outlet = $this->getOrCreateOutlet();
        
        $zone = WarehouseZone::create([
            'outlet_id' => $outlet->id,
            'name' => 'Old Zone',
            'type' => 'quarantine',
            'status' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->put(route('admin.warehouse-zones.update', $zone->id), [
            'outlet_id' => $outlet->id,
            'name' => 'New Zone Name',
            'type' => 'scrap',
            'status' => 0,
        ]);

        $response->assertRedirect(route('admin.warehouse-zones.index'));
        
        $this->assertDatabaseHas('warehouse_zones', [
            'id' => $zone->id,
            'name' => 'New Zone Name',
            'type' => 'scrap',
            'status' => 0,
        ]);
    }

    public function test_it_can_delete_a_warehouse_zone_via_ajax(): void
    {
        $user = $this->getOrCreateUser();
        $outlet = $this->getOrCreateOutlet();

        $zone = WarehouseZone::create([
            'outlet_id' => $outlet->id,
            'name' => 'Zone to Delete',
            'type' => 'active',
            'status' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson(route('admin.warehouse-zones.destroy', $zone->id));

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'message' => 'Warehouse Zone deleted successfully.',
        ]);

        $this->assertDatabaseMissing('warehouse_zones', [
            'id' => $zone->id,
        ]);
    }

    public function test_it_can_delete_a_warehouse_zone_via_standard_request(): void
    {
        $user = $this->getOrCreateUser();
        $outlet = $this->getOrCreateOutlet();

        $zone = WarehouseZone::create([
            'outlet_id' => $outlet->id,
            'name' => 'Zone to Delete Standard',
            'type' => 'active',
            'status' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('admin.warehouse-zones.destroy', $zone->id));

        $response->assertRedirect(route('admin.warehouse-zones.index'));

        $this->assertDatabaseMissing('warehouse_zones', [
            'id' => $zone->id,
        ]);
    }
}
