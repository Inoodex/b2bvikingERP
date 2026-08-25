<?php

namespace Tests\Feature\Inventory;

use App\Models\Company;
use App\Models\Outlet;
use App\Models\User;
use App\Models\WarehouseZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseZoneControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function getOrCreateUser(): User
    {
        return User::first() ?? User::create([
            'name' => 'Admin User',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);
    }

    protected function getOrCreateOutlet(): Outlet
    {
        $company = Company::first() ?? Company::create([
            'name' => 'Test Company',
            'code' => 'TC-' . uniqid(),
            'email' => 'tc@example.com',
            'phone' => '+123456789',
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
}
