<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\User;
use App\Models\Company;
use App\Models\Outlet;
use App\Models\CustomerProductVisibility;
use App\Services\InventoryVelocityService;
use App\Services\B2bProductVisibilityService;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductStockMovementAndVelocityTest extends TestCase
{
    use DatabaseTransactions;

    protected function getOrCreateTestProduct(): Product
    {
        $category = Category::first() ?? Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category-' . uniqid(),
            'status' => 1
        ]);

        return Product::first() ?? Product::create([
            'name' => 'Viking Test Horn Mug',
            'slug' => 'viking-test-horn-mug-' . uniqid(),
            'category_id' => $category->id,
            'thumb_image' => 'uploads/products/default.jpg',
            'qty' => 100,
            'purchase_price' => 50,
            'price' => 100,
            'outlet_price' => 80,
            'status' => 1,
            'is_approved' => 1
        ]);
    }

    public function test_inventory_velocity_service_calculates_presets_and_custom_ranges()
    {
        $service = new InventoryVelocityService();
        $product = $this->getOrCreateTestProduct();

        // 1. Test 90 days default preset
        $result90 = $service->calculateVelocity($product->id, '90_days');
        $this->assertEquals('90_days', $result90['preset']);
        $this->assertArrayHasKey('sell_through_rate', $result90);
        $this->assertArrayHasKey('classification', $result90);
        $this->assertArrayHasKey('badge', $result90['classification']);

        // 2. Test 30 days preset
        $result30 = $service->calculateVelocity($product->id, '30_days');
        $this->assertEquals('30_days', $result30['preset']);

        // 3. Test Month & Year calculation
        $resultMonth = $service->calculateVelocity($product->id, 'custom', null, null, 8, 2026);
        $this->assertEquals('Aug 2026', $resultMonth['period_label']);

        // 4. Test Custom Date Range
        $resultCustom = $service->calculateVelocity($product->id, 'custom', '2026-01-01', '2026-03-31');
        $this->assertStringContainsString('2026', $resultCustom['period_label']);
    }

    public function test_b2b_product_visibility_service_resolves_overrides()
    {
        $product = $this->getOrCreateTestProduct();

        $user = User::first() ?? User::create([
            'name' => 'Nordic Wholesale Customer',
            'email' => 'customer_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'phone' => '+4798765432'
        ]);

        // Case 1: Default real stock
        $defaultStatus = B2bProductVisibilityService::resolveAvailability($product, $user);
        $this->assertFalse($defaultStatus['is_overridden']);

        // Case 2: Force In Stock override
        $override = CustomerProductVisibility::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'visibility_mode' => 'force_in_stock',
            'reserved_qty' => 500,
            'notes' => 'Contract reserve'
        ]);

        $inStockStatus = B2bProductVisibilityService::resolveAvailability($product, $user);
        $this->assertTrue($inStockStatus['is_overridden']);
        $this->assertEquals('in_stock', $inStockStatus['stock_status']);

        // Case 3: Force Out of Stock override
        $override->update(['visibility_mode' => 'force_out_of_stock']);
        $outStatus = B2bProductVisibilityService::resolveAvailability($product, $user);
        $this->assertTrue($outStatus['is_overridden']);
        $this->assertEquals('out_of_stock', $outStatus['stock_status']);

        // Case 4: Hide Product
        $override->update(['visibility_mode' => 'hide_product']);
        $hiddenStatus = B2bProductVisibilityService::resolveAvailability($product, $user);
        $this->assertTrue($hiddenStatus['is_overridden']);
        $this->assertFalse($hiddenStatus['is_visible']);
    }

    public function test_velocity_metrics_ajax_endpoint_returns_json()
    {
        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin = User::first() ?? User::create([
            'name' => 'Admin Test',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password123')
        ]);
        if (!$admin->hasRole('Admin')) {
            $admin->assignRole($role);
        }

        $product = $this->getOrCreateTestProduct();

        $response = $this->actingAs($admin)
            ->getJson(route('admin.products.velocity-metrics', ['id' => $product->id, 'preset' => '30_days']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'metrics' => [
                'product_id',
                'preset',
                'sell_through_rate',
                'classification' => ['badge', 'label', 'advice']
            ]
        ]);
    }

    public function test_stock_movement_modal_endpoint_renders_html()
    {
        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin = User::first() ?? User::create([
            'name' => 'Admin Test',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password123')
        ]);
        if (!$admin->hasRole('Admin')) {
            $admin->assignRole($role);
        }

        $product = $this->getOrCreateTestProduct();

        $response = $this->actingAs($admin, 'web')
            ->get(route('admin.products.stock-movement', ['id' => $product->id]), [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]);

        $response->assertStatus(200);
        $response->assertSee('Lifetime Inflow');
        $response->assertSee('Stock Inflows');
        $response->assertSee('B2B Customer Stock Rules');
        $response->assertSee('Buyer / Phone');
    }

    public function test_save_b2b_visibility_rule_for_registered_user_and_phone()
    {
        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin = User::first() ?? User::create([
            'name' => 'Admin Test',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password123')
        ]);
        if (!$admin->hasRole('Admin')) {
            $admin->assignRole($role);
        }

        $customer = User::create([
            'name' => 'Buyer VIP',
            'email' => 'buyer_' . uniqid() . '@example.com',
            'phone' => '01711223344',
            'password' => bcrypt('secret123')
        ]);

        $product = $this->getOrCreateTestProduct();

        // 1. Save rule for registered user
        $responseUser = $this->actingAs($admin, 'web')->postJson(
            route('admin.products.b2b-visibility.store', ['id' => $product->id]),
            [
                'user_id' => $customer->id,
                'visibility_mode' => 'force_in_stock',
                'notes' => 'VIP Buyer Contract'
            ]
        );

        $responseUser->assertStatus(200);
        $responseUser->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('customer_product_visibilities', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'visibility_mode' => 'force_in_stock'
        ]);

        // 2. Save rule for phone number
        $responsePhone = $this->actingAs($admin, 'web')->postJson(
            route('admin.products.b2b-visibility.store', ['id' => $product->id]),
            [
                'phone_number' => '01899887766',
                'visibility_mode' => 'force_out_of_stock',
                'notes' => 'Restricted for phone'
            ]
        );

        $responsePhone->assertStatus(200);
        $responsePhone->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('customer_product_visibilities', [
            'product_id' => $product->id,
            'phone_number' => '01899887766',
            'visibility_mode' => 'force_out_of_stock'
        ]);
    }
}
