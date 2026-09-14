<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Category;

use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\SubCategory;
use Tests\Feature\Controllers\ControllerTestCase;

class SubCategoryControllerTest extends ControllerTestCase
{
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Parent Category ' . uniqid(),
            'slug' => 'parent-category-' . uniqid(),
            'status' => 1,
            'frontend_show' => 1,
        ]);
    }

    public function test_index_displays_view(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.sub-category.index'));

        $response->assertOk();
        $response->assertViewIs('backend.sub-category.index');
    }

    public function test_create_displays_form_with_categories(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.sub-category.create'));

        $response->assertOk();
        $response->assertViewIs('backend.sub-category.create');
        $response->assertViewHas('categories');
    }

    public function test_store_creates_subcategory_and_redirects(): void
    {
        $name = 'T-Shirts ' . uniqid();
        $payload = [
            'category' => $this->category->id,
            'name' => $name,
            'status' => 1,
        ];

        $response = $this->actingAs($this->adminUser)->post(route('admin.sub-category.store'), $payload);

        $response->assertRedirect(route('admin.sub-category.index'));
        $this->assertDatabaseHas('sub_categories', [
            'category_id' => $this->category->id,
            'name' => $name,
            'status' => 1,
        ]);
    }

    public function test_store_fails_with_validation_errors(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.sub-category.store'), []);

        $response->assertSessionHasErrors(['category', 'name', 'status']);
    }

    public function test_edit_displays_form_with_subcategory_and_categories(): void
    {
        $subCategory = SubCategory::create([
            'category_id' => $this->category->id,
            'name' => 'Edit SubCat ' . uniqid(),
            'slug' => 'edit-subcat-' . uniqid(),
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.sub-category.edit', $subCategory->id));

        $response->assertOk();
        $response->assertViewIs('backend.sub-category.edit');
        $response->assertViewHas(['subCategory', 'categories']);
    }

    public function test_update_modifies_subcategory_and_redirects(): void
    {
        $subCategory = SubCategory::create([
            'category_id' => $this->category->id,
            'name' => 'Old SubCat ' . uniqid(),
            'slug' => 'old-subcat-' . uniqid(),
            'status' => 1,
        ]);

        $updatedName = 'Updated SubCat ' . uniqid();

        $response = $this->actingAs($this->adminUser)->put(route('admin.sub-category.update', $subCategory->id), [
            'category' => $this->category->id,
            'name' => $updatedName,
            'status' => 1,
        ]);

        $response->assertRedirect(route('admin.sub-category.index'));
        $this->assertDatabaseHas('sub_categories', [
            'id' => $subCategory->id,
            'name' => $updatedName,
        ]);
    }

    public function test_destroy_deletes_subcategory_and_returns_json(): void
    {
        $subCategory = SubCategory::create([
            'category_id' => $this->category->id,
            'name' => 'To Delete ' . uniqid(),
            'slug' => 'to-delete-' . uniqid(),
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.sub-category.destroy', $subCategory->id));

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseMissing('sub_categories', ['id' => $subCategory->id]);
    }

    public function test_destroy_prevents_deletion_if_child_categories_exist(): void
    {
        $subCategory = SubCategory::create([
            'category_id' => $this->category->id,
            'name' => 'Parent SubCat ' . uniqid(),
            'slug' => 'parent-subcat-' . uniqid(),
            'status' => 1,
        ]);

        ChildCategory::create([
            'category_id' => $this->category->id,
            'sub_category_id' => $subCategory->id,
            'name' => 'Child Cat ' . uniqid(),
            'slug' => 'child-cat-' . uniqid(),
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.sub-category.destroy', $subCategory->id));

        $response->assertStatus(422);
        $response->assertJson(['status' => 'error']);
        $this->assertDatabaseHas('sub_categories', ['id' => $subCategory->id]);
    }

    public function test_change_status_toggles_boolean_and_returns_json(): void
    {
        $subCategory = SubCategory::create([
            'category_id' => $this->category->id,
            'name' => 'Status SubCat ' . uniqid(),
            'slug' => 'status-subcat-' . uniqid(),
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.subcategory.change-status'), [
            'id' => $subCategory->id,
            'status' => 'true',
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('sub_categories', [
            'id' => $subCategory->id,
            'status' => 1,
        ]);
    }
}
