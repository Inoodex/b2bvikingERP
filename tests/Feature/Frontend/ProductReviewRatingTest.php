<?php

namespace Tests\Feature\Frontend;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductReviewRatingTest extends TestCase
{
    use DatabaseTransactions;

    protected function getCustomerUser(): User
    {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Outlet User', 'guard_name' => 'web']);
        $user = User::create([
            'name' => 'Outlet Customer ' . uniqid(),
            'email' => 'customer_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'outlet_name' => 'Copenhagen Souvenir Outlet',
        ]);
        $user->assignRole($role);
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
            'name' => 'Frontend Review Product ' . uniqid(),
            'slug' => 'fe-review-prod-' . uniqid(),
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

    public function test_product_reviews_endpoint_returns_rating_breakdown(): void
    {
        $product = $this->createProduct();
        $userA = $this->getCustomerUser();
        $userB = $this->getCustomerUser();

        // Create 5-star review
        Review::create([
            'product_id' => $product->id,
            'user_id' => $userA->id,
            'rating' => 5,
            'comment' => 'Super high quality product',
            'status' => 1,
        ]);

        // Create 4-star review
        Review::create([
            'product_id' => $product->id,
            'user_id' => $userB->id,
            'rating' => 4,
            'comment' => 'Good product for store',
            'status' => 1,
        ]);

        $this->actingAs($userA);
        $response = $this->getJson(route('frontend.reviews.product', $product->id));

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'average_rating' => 4.5,
            'total_reviews' => 2,
        ]);

        $response->assertJsonStructure([
            'breakdown' => [
                '5' => ['star', 'count', 'percentage'],
                '4' => ['star', 'count', 'percentage'],
                '3' => ['star', 'count', 'percentage'],
                '2' => ['star', 'count', 'percentage'],
                '1' => ['star', 'count', 'percentage'],
            ],
            'reviews',
        ]);

        $data = $response->json();
        $this->assertEquals(1, $data['breakdown']['5']['count']);
        $this->assertEquals(50, $data['breakdown']['5']['percentage']);
        $this->assertEquals(1, $data['breakdown']['4']['count']);
        $this->assertEquals(50, $data['breakdown']['4']['percentage']);
    }

    public function test_product_reviews_endpoint_filters_by_rating(): void
    {
        $product = $this->createProduct();
        $userA = $this->getCustomerUser();
        $userB = $this->getCustomerUser();

        Review::create([
            'product_id' => $product->id,
            'user_id' => $userA->id,
            'rating' => 5,
            'comment' => 'UniqueCommentFiveStar',
            'status' => 1,
        ]);

        Review::create([
            'product_id' => $product->id,
            'user_id' => $userB->id,
            'rating' => 2,
            'comment' => 'UniqueCommentTwoStar',
            'status' => 1,
        ]);

        $this->actingAs($userA);
        // Request only 5-star reviews
        $response = $this->getJson(route('frontend.reviews.product', ['productId' => $product->id, 'rating' => 5]));

        $response->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['reviews']);
        $this->assertEquals('UniqueCommentFiveStar', $data['reviews'][0]['comment']);
    }

    public function test_hidden_review_is_not_exposed_on_frontend(): void
    {
        $product = $this->createProduct();
        $user = $this->getCustomerUser();

        // Create a hidden review
        Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 1,
            'comment' => 'Inappropriate hidden comment',
            'status' => 0,
        ]);

        $this->actingAs($user);
        $response = $this->getJson(route('frontend.reviews.product', $product->id));

        $response->assertOk();
        $data = $response->json();
        $this->assertEquals(0, $data['total_reviews']);
        $this->assertCount(0, $data['reviews']);
    }

    public function test_customer_can_submit_and_update_review(): void
    {
        $product = $this->createProduct();
        $customer = $this->getCustomerUser();

        $this->actingAs($customer);

        // 1. Submit review
        $response = $this->postJson(route('frontend.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'First impression is great!',
        ]);

        $response->assertCreated();
        $response->assertJson([
            'status' => 'success',
            'message' => 'Review added successfully',
        ]);

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 5,
            'comment' => 'First impression is great!',
            'status' => 1,
        ]);

        // 2. Update review (same user and product)
        $responseUpdate = $this->postJson(route('frontend.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 4,
            'comment' => 'Updated feedback after wash test',
        ]);

        $responseUpdate->assertOk();
        $responseUpdate->assertJson([
            'status' => 'success',
            'message' => 'Review updated successfully',
        ]);

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 4,
            'comment' => 'Updated feedback after wash test',
        ]);

        $this->assertEquals(1, Review::where('product_id', $product->id)->where('user_id', $customer->id)->count());
    }
}
