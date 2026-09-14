<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Category;

use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\SubCategory;
use Tests\Feature\Controllers\ControllerTestCase;

class ChildCategoryControllerTest extends ControllerTestCase
{
    protected Category $category;
    protected SubCategory $subCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Category ' . uniqid(),
            'slug' => 'category-' . uniqid(),
            'status' => 1,
            'frontend_show' => 1,
        ]);

        $this->subCategory = SubCategory::create([
            'category_id' => $this->category->id,
            'name' => 'SubCategory ' . uniqid(),
            'slug' => 'subcategory-' . uniqid(),
            'status' => 1,
        ]);
    }

    public function test_index_displays_view(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.child-category.index'));

        $response->assertOk();
        $response->assertViewIs('backend.child-category.index');
    }

    public function test_create_displays_form_with_categories(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.child-category.create'));

        $response->assertOk();
        $response->assertViewIs('backend.child-category.create');
        $response->assertViewHas('categories');
    }

    public function test_get_subcategories_returns_json(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.get-subCategories', ['id' => $this->category->id]));

        $response->assertOk();
        $response->assertJsonFragment(['name' => $this->subCategory->name]);
    }

    public function test_get_child_categories_returns_json(): void
    {
        $child = ChildCategory::create([
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'name' => 'Test Child ' . uniqid(),
            'slug' => 'test-child-' . uniqid(),
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.get-child-categories', ['id' => $this->subCategory->id]));

        $response->assertOk();
        $response->assertJsonFragment(['name' => $child->name]);
    }

    public function test_store_creates_child_category_and_redirects(): void
    {
        $name = 'Polo Shirts ' . uniqid();
        $payload = [
            'category' => $this->category->id,
            'sub_category' => $this->subCategory->id,
            'name' => $name,
            'status' => 1,
        ];

        $response = $this->actingAs($this->adminUser)->post(route('admin.child-category.store'), $payload);

        $response->assertRedirect(route('admin.child-category.index'));
        $this->assertDatabaseHas('child_categories', [
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'name' => $name,
            'status' => 1,
        ]);
    }

    public function test_store_fails_with_validation_errors(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.child-category.store'), []);

        $response->assertSessionHasErrors(['category', 'sub_category', 'name', 'status']);
    }

    public function test_edit_displays_form_with_child_category_and_options(): void
    {
        $child = ChildCategory::create([
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'name' => 'Edit Child ' . uniqid(),
            'slug' => 'edit-child-' . uniqid(),
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.child-category.edit', $child->id));

        $response->assertOk();
        $response->assertViewIs('backend.child-category.edit');
        $response->assertViewHas(['childCategory', 'categories', 'subCategories']);
    }

    public function test_update_modifies_child_category_and_redirects(): void
    {
        $child = ChildCategory::create([
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'name' => 'Old Child ' . uniqid(),
            'slug' => 'old-child-' . uniqid(),
            'status' => 1,
        ]);

        $updatedName = 'Updated Child ' . uniqid();

        $response = $this->actingAs($this->adminUser)->put(route('admin.child-category.update', $child->id), [
            'category' => $this->category->id,
            'sub_category' => $this->subCategory->id,
            'name' => $updatedName,
            'status' => 1,
        ]);

        $response->assertRedirect(route('admin.child-category.index'));
        $this->assertDatabaseHas('child_categories', [
            'id' => $child->id,
            'name' => $updatedName,
        ]);
    }

    public function test_destroy_prevents_deletion_if_products_exist(): void
    {
        $child = ChildCategory::create([
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'name' => 'Protected Child ' . uniqid(),
            'slug' => 'protected-child-' . uniqid(),
            'status' => 1,
        ]);

        Product::create([
            'child_category_id' => $child->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'name' => 'Test Product ' . uniqid(),
            'slug' => 'test-product-' . uniqid(),
            'status' => 1,
            'price' => 100,
            'discount' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.child-category.destroy', $child->id));

        $response->assertStatus(422);
        $response->assertJson(['status' => 'error']);
        $this->assertDatabaseHas('child_categories', ['id' => $child->id]);
    }

    public function test_destroy_deletes_child_category_and_returns_json(): void
    {
        $child = ChildCategory::create([
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'name' => 'To Delete ' . uniqid(),
            'slug' => 'to-delete-' . uniqid(),
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.child-category.destroy', $child->id));

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseMissing('child_categories', ['id' => $child->id]);
    }

    public function test_change_status_toggles_boolean_and_returns_json(): void
    {
        $child = ChildCategory::create([
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'name' => 'Status Child ' . uniqid(),
            'slug' => 'status-child-' . uniqid(),
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.child-category.change-status'), [
            'id' => $child->id,
            'status' => 'true',
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('child_categories', [
            'id' => $child->id,
            'status' => 1,
        ]);
    }
}
