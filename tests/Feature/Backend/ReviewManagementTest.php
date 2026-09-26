<?php

namespace Tests\Feature\Backend;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReviewManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected function getAdminUser(): User
    {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $user = User::first() ?? User::create([
            'name' => 'Admin User ' . uniqid(),
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);
        if (!$user->hasRole('Admin')) {
            $user->assignRole($role);
        }
        return $user;
    }

    protected function createProduct(): Product
    {
        $category = Category::first() ?? Category::create([
            'name' => 'Test Cat ' . uniqid(),
            'slug' => 'test-cat-' . uniqid(),
            'status' => 1,
        ]);

        return Product::create([
            'name' => 'Review Test Product ' . uniqid(),
            'slug' => 'review-test-prod-' . uniqid(),
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
    }

    public function test_admin_can_access_reviews_index_page(): void
    {
        $admin = $this->getAdminUser();
        $this->actingAs($admin);

        $response = $this->get(route('admin.reviews.index'));

        $response->assertOk();
        $response->assertSee('Product Reviews & Customer Feedback', false);
        $response->assertSee('Manage Customer Reviews');
    }

    public function test_reviews_datatable_ajax_returns_json(): void
    {
        $admin = $this->getAdminUser();
        $product = $this->createProduct();

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'rating' => 5,
            'comment' => 'Exceptional test product feedback',
            'status' => 1,
        ]);

        $this->actingAs($admin);

        $response = $this->getJson(route('admin.reviews.index'), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'recordsTotal',
            'recordsFiltered',
        ]);

        $content = $response->getContent();
        $this->assertStringContainsString('Exceptional test product feedback', $content);
    }

    public function test_admin_can_inspect_single_review_modal_json(): void
    {
        $admin = $this->getAdminUser();
        $product = $this->createProduct();

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'rating' => 4,
            'comment' => 'Great build quality and fabric',
            'status' => 1,
        ]);

        $this->actingAs($admin);

        $response = $this->getJson(route('admin.reviews.show', $review->id));

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'review' => [
                'id' => $review->id,
                'rating' => 4,
                'comment' => 'Great build quality and fabric',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                ],
            ],
        ]);
    }

    public function test_admin_can_toggle_review_status(): void
    {
        $admin = $this->getAdminUser();
        $product = $this->createProduct();

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'rating' => 3,
            'comment' => 'Average item',
            'status' => 1,
        ]);

        $this->actingAs($admin);

        // Toggle to hidden (status: 0)
        $response = $this->postJson(route('admin.reviews.change-status'), [
            'id' => $review->id,
            'status' => 0,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => 0,
        ]);

        // Toggle back to published (status: 1)
        $response2 = $this->postJson(route('admin.reviews.change-status'), [
            'id' => $review->id,
            'status' => 1,
        ]);

        $response2->assertOk();
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => 1,
        ]);
    }

    public function test_admin_can_delete_review(): void
    {
        $admin = $this->getAdminUser();
        $product = $this->createProduct();

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'rating' => 1,
            'comment' => 'Spam review to delete',
            'status' => 1,
        ]);

        $this->actingAs($admin);

        $response = $this->deleteJson(route('admin.reviews.destroy', $review->id));

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'message' => 'Deleted Successfully!',
        ]);

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_reviews_datatable_filters_by_rating(): void
    {
        $admin = $this->getAdminUser();
        $product = $this->createProduct();

        $review5 = Review::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'rating' => 5,
            'comment' => 'SpecialFiveStarComment_' . uniqid(),
            'status' => 1,
        ]);

        $this->actingAs($admin);

        $response = $this->getJson(route('admin.reviews.index', ['rating' => 5]), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString($review5->comment, $content);
    }

    public function test_reviews_datatable_page_length_and_fallback_image(): void
    {
        $admin = $this->getAdminUser();
        $this->actingAs($admin);

        $dt = app(\App\DataTables\ReviewDataTable::class);
        $scripts = $dt->html()->generateScripts();

        $this->assertStringContainsString('"pageLength":10', $scripts);
        $this->assertStringContainsString('"All"', $scripts);

        $category = Category::first() ?? Category::create([
            'name' => 'Test Cat ' . uniqid(),
            'slug' => 'test-cat-' . uniqid(),
            'status' => 1,
        ]);

        $product = Product::create([
            'name' => 'No Image Product ' . uniqid(),
            'slug' => 'no-img-prod-' . uniqid(),
            'product_number' => 'SKU-' . rand(1000, 9999),
            'sku' => 'SKU-' . rand(1000, 9999),
            'category_id' => $category->id,
            'thumb_image' => 'uploads/products/non_existent_file.jpg',
            'qty' => 10,
            'purchase_price' => 10,
            'price' => 20,
            'outlet_price' => 15,
            'status' => 1,
            'is_approved' => 1,
        ]);

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'rating' => 5,
            'comment' => 'Fallback image test unique ' . uniqid(),
            'status' => 1,
        ]);

        $response = $this->getJson(route('admin.reviews.index'), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('no-image.svg', $response->getContent());
    }

    public function test_reviews_datatable_filters_by_category_and_excludes_id_column(): void
    {
        $admin = $this->getAdminUser();
        $this->actingAs($admin);

        $dt = app(\App\DataTables\ReviewDataTable::class);
        $columnTitles = array_map(fn($col) => $col->title, $dt->getColumns());

        // Assert #ID column is removed
        $this->assertNotContains('#ID', $columnTitles);
        $this->assertNotContains('ID', $columnTitles);
        $this->assertContains('Product', $columnTitles);

        // Test Category filtering
        $catA = Category::create([
            'name' => 'Category Alpha ' . uniqid(),
            'slug' => 'cat-alpha-' . uniqid(),
            'status' => 1,
        ]);
        $prodA = Product::create([
            'name' => 'Prod In Alpha ' . uniqid(),
            'slug' => 'prod-alpha-' . uniqid(),
            'product_number' => 'SKU-' . rand(1000, 9999),
            'sku' => 'SKU-' . rand(1000, 9999),
            'category_id' => $catA->id,
            'thumb_image' => 'uploads/products/default.jpg',
            'qty' => 10,
            'purchase_price' => 10,
            'price' => 20,
            'outlet_price' => 15,
            'status' => 1,
            'is_approved' => 1,
        ]);
        $reviewA = Review::create([
            'product_id' => $prodA->id,
            'user_id' => $admin->id,
            'rating' => 4,
            'comment' => 'UniqueCommentAlphaCat_' . uniqid(),
            'status' => 1,
        ]);

        $response = $this->getJson(route('admin.reviews.index', ['category_id' => $catA->id]), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString($reviewA->comment, $content);
    }
}
