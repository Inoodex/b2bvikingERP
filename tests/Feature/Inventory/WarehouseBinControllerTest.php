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

    public function test_it_can_delete_a_warehouse_bin_via_ajax(): void
    {
        $user = $this->getOrCreateUser();
        $zone = $this->getOrCreateZone();

        $bin = WarehouseBin::create([
            'zone_id' => $zone->id,
            'name' => 'Bin to Delete',
            'barcode' => 'DEL-123',
            'status' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson(route('admin.warehouse-bins.destroy', $bin->id));

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'message' => 'Warehouse Bin deleted successfully.',
        ]);

        $this->assertDatabaseMissing('warehouse_bins', [
            'id' => $bin->id,
        ]);
    }

    public function test_it_can_delete_a_warehouse_bin_via_standard_request(): void
    {
        $user = $this->getOrCreateUser();
        $zone = $this->getOrCreateZone();

        $bin = WarehouseBin::create([
            'zone_id' => $zone->id,
            'name' => 'Bin to Delete Standard',
            'barcode' => 'DEL-STD-123',
            'status' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('admin.warehouse-bins.destroy', $bin->id));

        $response->assertRedirect(route('admin.warehouse-bins.index'));

        $this->assertDatabaseMissing('warehouse_bins', [
            'id' => $bin->id,
        ]);
    }

    public function test_it_cannot_delete_a_warehouse_bin_with_active_inventory(): void
    {
        $user = $this->getOrCreateUser();
        $zone = $this->getOrCreateZone();

        $bin = WarehouseBin::create([
            'zone_id' => $zone->id,
            'name' => 'Bin With Stock',
            'barcode' => 'BIN-STOCK-1',
            'status' => 1,
        ]);

        $category = \App\Models\Category::first() ?? \App\Models\Category::create([
            'name' => 'Test Cat ' . uniqid(),
            'slug' => 'test-cat-' . uniqid(),
            'status' => 1,
        ]);

        $product = \App\Models\Product::first() ?? \App\Models\Product::create([
            'name' => 'Test Product ' . uniqid(),
            'slug' => 'test-prod-' . uniqid(),
            'product_number' => 'SKU-' . rand(1000, 9999),
            'sku' => 'SKU-' . rand(1000, 9999),
            'category_id' => $category->id,
            'thumb_image' => 'uploads/products/default.jpg',
            'qty' => 50,
            'purchase_price' => 20,
            'price' => 50,
            'outlet_price' => 40,
            'status' => 1,
            'is_approved' => 1,
        ]);

        \App\Models\InventoryStock::create([
            'product_id' => $product->id,
            'variant_id' => null,
            'outlet_id' => $zone->outlet_id,
            'bin_id' => $bin->id,
            'quantity' => 15,
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson(route('admin.warehouse-bins.destroy', $bin->id));

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Cannot delete bin: Active inventory stock exists in this bin location. Please transfer stock first.',
        ]);

        $this->assertDatabaseHas('warehouse_bins', [
            'id' => $bin->id,
        ]);
    }
}
