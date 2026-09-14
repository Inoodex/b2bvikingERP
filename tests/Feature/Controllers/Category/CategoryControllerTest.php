<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Category;

use App\Models\Category;
use App\Models\SubCategory;
use Tests\Feature\Controllers\ControllerTestCase;

class CategoryControllerTest extends ControllerTestCase
{
    public function test_index_displays_view(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.category.index'));

        $response->assertOk();
        $response->assertViewIs('backend.category.index');
    }

    public function test_create_displays_form(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.category.create'));

        $response->assertOk();
        $response->assertViewIs('backend.category.create');
    }

    public function test_store_creates_category_and_redirects(): void
    {
        $name = 'Men Fashion ' . uniqid();
        $payload = [
            'name' => $name,
            'status' => 1,
            'frontend_show' => 1,
        ];

        $response = $this->actingAs($this->adminUser)->post(route('admin.category.store'), $payload);

        $response->assertRedirect(route('admin.category.index'));
        $this->assertDatabaseHas('categories', [
            'name' => $name,
            'status' => 1,
            'frontend_show' => 1,
        ]);
    }

    public function test_store_fails_with_validation_errors(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.category.store'), []);

        $response->assertSessionHasErrors(['name', 'status']);
    }

    public function test_edit_displays_form_with_category(): void
    {
        $category = Category::create([
            'name' => 'Test Cat ' . uniqid(),
            'slug' => 'test-cat-' . uniqid(),
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.category.edit', $category->id));

        $response->assertOk();
        $response->assertViewIs('backend.category.edit');
        $response->assertViewHas('category');
    }

    public function test_update_modifies_category_and_redirects(): void
    {
        $category = Category::create([
            'name' => 'Old Cat ' . uniqid(),
            'slug' => 'old-cat-' . uniqid(),
            'status' => 1,
        ]);

        $updatedName = 'Updated Cat ' . uniqid();

        $response = $this->actingAs($this->adminUser)->put(route('admin.category.update', $category->id), [
            'name' => $updatedName,
            'status' => 1,
            'frontend_show' => 0,
        ]);

        $response->assertRedirect(route('admin.category.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => $updatedName,
        ]);
    }

    public function test_destroy_deletes_category_and_returns_json(): void
    {
        $category = Category::create([
            'name' => 'To Delete ' . uniqid(),
            'slug' => 'to-delete-' . uniqid(),
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.category.destroy', $category->id));

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_destroy_prevents_deletion_if_subcategories_exist(): void
    {
        $category = Category::create([
            'name' => 'Parent Cat ' . uniqid(),
            'slug' => 'parent-cat-' . uniqid(),
            'status' => 1,
        ]);

        SubCategory::create([
            'category_id' => $category->id,
            'name' => 'Sub Cat ' . uniqid(),
            'slug' => 'sub-cat-' . uniqid(),
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.category.destroy', $category->id));

        $response->assertStatus(422);
        $response->assertJson(['status' => 'error']);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_change_status_toggles_boolean_and_returns_json(): void
    {
        $category = Category::create([
            'name' => 'Status Cat ' . uniqid(),
            'slug' => 'status-cat-' . uniqid(),
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.category.change-status'), [
            'id' => $category->id,
            'status' => 'true',
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'status' => 1,
        ]);
    }

    public function test_change_frontend_show_toggles_boolean_and_returns_json(): void
    {
        $category = Category::create([
            'name' => 'Frontend Cat ' . uniqid(),
            'slug' => 'frontend-cat-' . uniqid(),
            'status' => 1,
            'frontend_show' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.category.change-frontend-show'), [
            'id' => $category->id,
            'frontend_show' => 'true',
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'frontend_show' => 1,
        ]);
    }
}
