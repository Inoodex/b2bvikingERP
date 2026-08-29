<?php

namespace Tests\Feature\Inventory;

use App\Models\Company;
use App\Models\Outlet;
use App\Models\User;
use App\Models\WarehouseZone;
use App\Models\WarehouseBin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WarehouseBinControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function getOrCreateUser(): User
    {
        return User::first() ?? User::create([
            'name' => 'Admin User',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);
    }

    protected function getOrCreateZone(): WarehouseZone
    {
        $company = Company::first() ?? Company::create([
            'name' => 'Test Company',
            'code' => 'TC-' . uniqid(),
            'status' => 1,
        ]);

        $outlet = Outlet::create([
            'name' => 'Test Outlet ' . uniqid(),
            'code' => 'OUT-' . uniqid(),
            'company_id' => $company->id,
            'status' => 1,
        ]);
        
        return WarehouseZone::create([
            'outlet_id' => $outlet->id,
            'name' => 'Zone Beta',
            'type' => 'active',
            'status' => 1,
        ]);
    }

    public function test_it_can_create_a_warehouse_bin(): void
    {
        $user = $this->getOrCreateUser();
        $zone = $this->getOrCreateZone();

        $this->actingAs($user);

        $response = $this->post(route('admin.warehouse-bins.store'), [
            'zone_id' => $zone->id,
            'name' => 'Bin Alpha',
            'barcode' => 'B-A-123',
            'status' => 1,
        ]);

        $response->assertRedirect(route('admin.warehouse-bins.index'));
        $this->assertDatabaseHas('warehouse_bins', [
            'zone_id' => $zone->id,
            'name' => 'Bin Alpha',
        ]);
    }

    public function test_it_can_update_a_warehouse_bin(): void
    {
        $user = $this->getOrCreateUser();
        $zone = $this->getOrCreateZone();
        
        $bin = WarehouseBin::create([
            'zone_id' => $zone->id,
            'name' => 'Old Bin',
            'barcode' => 'OLD-123',
            'status' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->put(route('admin.warehouse-bins.update', $bin->id), [
            'zone_id' => $zone->id,
            'name' => 'New Bin Name',
            'barcode' => 'NEW-123',
            'status' => 0,
        ]);

        $response->assertRedirect(route('admin.warehouse-bins.index'));
        
        $this->assertDatabaseHas('warehouse_bins', [
            'id' => $bin->id,
            'name' => 'New Bin Name',
            'status' => 0,
        ]);
    }
}
