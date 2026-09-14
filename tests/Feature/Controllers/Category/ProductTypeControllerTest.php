<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Category;

use App\Models\ProductType;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Controllers\ControllerTestCase;

class ProductTypeControllerTest extends ControllerTestCase
{
    public function test_index_displays_view(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.product-types.index'));

        $response->assertOk();
        $response->assertViewIs('backend.product-types.index');
    }

    public function test_create_displays_form(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.product-types.create'));

        $response->assertOk();
        $response->assertViewIs('backend.product-types.create');
    }

    public function test_store_creates_product_type_and_redirects(): void
    {
        $name = 'Viking Helmet ' . uniqid();
        $payload = [
            'name' => $name,
            'status' => 1,
        ];

        $response = $this->actingAs($this->adminUser)->post(route('admin.product-types.store'), $payload);

        $response->assertRedirect(route('admin.product-types.index'));
        $this->assertDatabaseHas('product_types', [
            'name' => $name,
            'status' => 1,
        ]);
    }

    public function test_store_fails_with_validation_errors(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.product-types.store'), []);

        $response->assertSessionHasErrors(['name', 'status']);
    }

    public function test_edit_displays_form_with_product_type(): void
    {
        $productType = ProductType::create([
            'name' => 'Type ' . uniqid(),
            'slug' => 'type-' . uniqid(),
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.product-types.edit', $productType->id));

        $response->assertOk();
        $response->assertViewIs('backend.product-types.edit');
        $response->assertViewHas('productType');
    }

    public function test_update_modifies_product_type_and_redirects(): void
    {
        $productType = ProductType::create([
            'name' => 'Old Type ' . uniqid(),
            'slug' => 'old-type-' . uniqid(),
            'status' => 1,
        ]);

        $updatedName = 'New Type ' . uniqid();

        $response = $this->actingAs($this->adminUser)->put(route('admin.product-types.update', $productType->id), [
            'name' => $updatedName,
            'status' => 1,
        ]);

        $response->assertRedirect(route('admin.product-types.index'));
        $this->assertDatabaseHas('product_types', [
            'id' => $productType->id,
            'name' => $updatedName,
        ]);
    }

    public function test_destroy_prevents_deletion_if_products_exist(): void
    {
        $productType = ProductType::create([
            'name' => 'Protected Type ' . uniqid(),
            'slug' => 'protected-type-' . uniqid(),
            'status' => 1,
        ]);

        // Insert a minimal product row referencing this product type
        DB::table('products')->insert([
            'product_type_id' => $productType->id,
            'name' => 'Test Product ' . uniqid(),
            'slug' => 'test-product-' . uniqid(),
            'status' => 1,
            'price' => 100,
            'discount' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.product-types.destroy', $productType->id));

        $response->assertStatus(422);
        $response->assertJson(['status' => 'error']);
        $this->assertDatabaseHas('product_types', ['id' => $productType->id]);
    }

    public function test_destroy_deletes_product_type_and_returns_json(): void
    {
        $productType = ProductType::create([
            'name' => 'To Delete ' . uniqid(),
            'slug' => 'to-delete-' . uniqid(),
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.product-types.destroy', $productType->id));

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseMissing('product_types', ['id' => $productType->id]);
    }

    public function test_change_status_toggles_boolean_and_returns_json(): void
    {
        $productType = ProductType::create([
            'name' => 'Status Type ' . uniqid(),
            'slug' => 'status-type-' . uniqid(),
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.product-types.change-status'), [
            'id' => $productType->id,
            'status' => 'true',
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('product_types', [
            'id' => $productType->id,
            'status' => 1,
        ]);
    }
}
