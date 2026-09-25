<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\CustomerProductVisibility;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use App\Services\B2bProductVisibilityService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class B2bProductVisibilityHubTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $this->admin = User::first() ?? User::create([
            'name' => 'Admin Test',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);

        if (!$this->admin->hasRole('Admin')) {
            $this->admin->assignRole($role);
        }
    }

    protected function createProduct(string $name = 'Test Product'): Product
    {
        $category = Category::first() ?? Category::create([
            'name' => 'Test Category',
            'slug' => 'test-cat-' . uniqid(),
            'status' => 1,
        ]);

        return Product::create([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid(),
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

    protected function createCompany(string $name = 'Test Company'): Company
    {
        return Company::create([
            'name' => $name,
            'code' => 'COMP-' . rand(1000, 9999),
            'status' => 1,
        ]);
    }

    public function test_b2b_stock_rules_index_page_loads_successfully()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.b2b-stock-rules.index'));

        $response->assertStatus(200);
        $response->assertSee('Customer Stock Rules');
        $response->assertSee('Master Rules Table');
        $response->assertSee('Customer Matrix');
        $response->assertSee('Total Active Rules');
        $response->assertSee('Priority In-Stock');
        $response->assertSee('Restricted (Out of Stock)');
    }

    public function test_b2b_visibility_datatable_ajax_returns_json()
    {
        $product = $this->createProduct('Datatable Test Item');
        $company = $this->createCompany('Nordic Wholesale AS');

        CustomerProductVisibility::create([
            'product_id' => $product->id,
            'company_id' => $company->id,
            'visibility_mode' => 'force_in_stock',
            'notes' => 'Datatable filter test',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.b2b-stock-rules.index'), [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_ACCEPT' => 'application/json',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'recordsTotal',
            'recordsFiltered',
        ]);
    }

    public function test_store_b2b_visibility_rule_with_company_scope()
    {
        $product = $this->createProduct('Horn Mug Classic');
        $company = $this->createCompany('Fjord Imports');

        $response = $this->actingAs($this->admin, 'web')->postJson(
            route('admin.b2b-stock-rules.store'),
            [
                'product_id' => $product->id,
                'target_scope' => 'company',
                'company_id' => $company->id,
                'visibility_mode' => 'force_in_stock',
                'notes' => 'Contract Reserved Allocation',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('customer_product_visibilities', [
            'product_id' => $product->id,
            'company_id' => $company->id,
            'visibility_mode' => 'force_in_stock',
            'notes' => 'Contract Reserved Allocation',
        ]);
    }

    public function test_store_b2b_visibility_rule_with_outlet_scope()
    {
        $product = $this->createProduct('Viking Battle Axe');
        $outlet = Outlet::where('type', '!=', 'warehouse')->first() ?? Outlet::create([
            'name' => 'Oslo Central Branch',
            'code' => 'OUT-01',
            'type' => 'outlet',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->admin, 'web')->postJson(
            route('admin.b2b-stock-rules.store'),
            [
                'product_id' => $product->id,
                'target_scope' => 'outlet',
                'outlet_id' => $outlet->id,
                'visibility_mode' => 'force_out_of_stock',
                'notes' => 'Regional Restriction',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('customer_product_visibilities', [
            'product_id' => $product->id,
            'outlet_id' => $outlet->id,
            'visibility_mode' => 'force_out_of_stock',
        ]);
    }

    public function test_update_b2b_visibility_rule()
    {
        $product = $this->createProduct('Viking Shield');
        $company = $this->createCompany('Viking Traders Ltd');

        $rule = CustomerProductVisibility::create([
            'product_id' => $product->id,
            'company_id' => $company->id,
            'visibility_mode' => 'force_in_stock',
            'notes' => 'Old note',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin, 'web')->putJson(
            route('admin.b2b-stock-rules.update', $rule->id),
            [
                'visibility_mode' => 'force_out_of_stock',
                'notes' => 'Updated contract restriction',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('customer_product_visibilities', [
            'id' => $rule->id,
            'visibility_mode' => 'force_out_of_stock',
            'notes' => 'Updated contract restriction',
        ]);
    }

    public function test_destroy_b2b_visibility_rule_reverts_to_standard()
    {
        $product = $this->createProduct('Mead Bottle');
        $company = $this->createCompany('Nordic Meadery');

        $rule = CustomerProductVisibility::create([
            'product_id' => $product->id,
            'company_id' => $company->id,
            'visibility_mode' => 'force_in_stock',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin, 'web')->deleteJson(
            route('admin.b2b-stock-rules.destroy', $rule->id)
        );

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('customer_product_visibilities', [
            'id' => $rule->id,
        ]);
    }

    public function test_search_products_returns_clean_select2_json()
    {
        $product = $this->createProduct('Viking Dragon Helmet');

        $response = $this->actingAs($this->admin, 'web')->getJson(
            route('admin.b2b-stock-rules.search-products', ['q' => 'Dragon Helmet'])
        );

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $product->id,
            'name' => $product->name,
        ]);
    }

    public function test_matrix_data_endpoint_renders_html_rows()
    {
        $product = $this->createProduct('Valhalla Pendant');
        $company = $this->createCompany('Silver Guild');

        $response = $this->actingAs($this->admin, 'web')->get(
            route('admin.b2b-stock-rules.matrix-data', [
                'target_scope' => 'company',
                'company_id' => $company->id,
            ])
        );

        $response->assertStatus(200);
        $response->assertSee('Valhalla Pendant');
        $response->assertSee('Force In-Stock');
        $response->assertSee('Force OOS');
        $response->assertSee('Reset Real Stock');
    }

    public function test_matrix_toggle_1_click_availability_and_revert()
    {
        $product = $this->createProduct('Rune Stone');
        $company = $this->createCompany('Rune Crafters');

        // 1. Force In-Stock via 1-click toggle
        $response1 = $this->actingAs($this->admin, 'web')->postJson(
            route('admin.b2b-stock-rules.matrix-toggle'),
            [
                'product_id' => $product->id,
                'target_scope' => 'company',
                'company_id' => $company->id,
                'mode' => 'force_in_stock',
            ]
        );

        $response1->assertStatus(200);
        $response1->assertJson(['status' => 'success', 'mode' => 'force_in_stock']);
        $this->assertDatabaseHas('customer_product_visibilities', [
            'product_id' => $product->id,
            'company_id' => $company->id,
            'visibility_mode' => 'force_in_stock',
        ]);

        // 2. Revert to standard warehouse real stock
        $response2 = $this->actingAs($this->admin, 'web')->postJson(
            route('admin.b2b-stock-rules.matrix-toggle'),
            [
                'product_id' => $product->id,
                'target_scope' => 'company',
                'company_id' => $company->id,
                'mode' => 'standard',
            ]
        );

        $response2->assertStatus(200);
        $response2->assertJson(['status' => 'success', 'mode' => 'standard']);
        $this->assertDatabaseMissing('customer_product_visibilities', [
            'product_id' => $product->id,
            'company_id' => $company->id,
        ]);
    }

    public function test_bulk_store_applies_visibility_rule_to_multiple_products()
    {
        $p1 = $this->createProduct('Bulk Product Alpha');
        $p2 = $this->createProduct('Bulk Product Beta');
        $p3 = $this->createProduct('Bulk Product Gamma');
        $company = $this->createCompany('Consortium B2B');

        $response = $this->actingAs($this->admin, 'web')->postJson(
            route('admin.b2b-stock-rules.bulk-store'),
            [
                'product_ids' => [$p1->id, $p2->id, $p3->id],
                'target_scope' => 'company',
                'company_id' => $company->id,
                'visibility_mode' => 'force_in_stock',
                'notes' => 'Batch VIP contract assignment',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'count' => 3]);

        $this->assertDatabaseHas('customer_product_visibilities', [
            'product_id' => $p1->id,
            'company_id' => $company->id,
            'visibility_mode' => 'force_in_stock',
        ]);
        $this->assertDatabaseHas('customer_product_visibilities', [
            'product_id' => $p2->id,
            'company_id' => $company->id,
            'visibility_mode' => 'force_in_stock',
        ]);
        $this->assertDatabaseHas('customer_product_visibilities', [
            'product_id' => $p3->id,
            'company_id' => $company->id,
            'visibility_mode' => 'force_in_stock',
        ]);
    }

    public function test_bulk_store_reverts_multiple_products_to_standard()
    {
        $p1 = $this->createProduct('Bulk Revert 1');
        $p2 = $this->createProduct('Bulk Revert 2');
        $company = $this->createCompany('Northern Stores');

        CustomerProductVisibility::create([
            'product_id' => $p1->id,
            'company_id' => $company->id,
            'visibility_mode' => 'force_out_of_stock',
            'created_by' => $this->admin->id,
        ]);
        CustomerProductVisibility::create([
            'product_id' => $p2->id,
            'company_id' => $company->id,
            'visibility_mode' => 'force_out_of_stock',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin, 'web')->postJson(
            route('admin.b2b-stock-rules.bulk-store'),
            [
                'product_ids' => [$p1->id, $p2->id],
                'target_scope' => 'company',
                'company_id' => $company->id,
                'visibility_mode' => 'standard',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'count' => 2]);

        $this->assertDatabaseMissing('customer_product_visibilities', [
            'product_id' => $p1->id,
            'company_id' => $company->id,
        ]);
        $this->assertDatabaseMissing('customer_product_visibilities', [
            'product_id' => $p2->id,
            'company_id' => $company->id,
        ]);
    }

    public function test_catalog_page_loads_cleanly_without_bulk_bar_clutter()
    {
        $this->createProduct('Catalog Item Clean');

        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Select All on This Page');
        $response->assertDontSee('catalog-bulk-bar');
        $response->assertDontSee('modal-bulk-b2b-visibility');
        $response->assertSee('stockMovementDrawer');
    }

    public function test_b2b_stock_rules_index_renders_slide_over_drawer_and_no_popup_modal()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.b2b-stock-rules.index'));

        $response->assertStatus(200);
        $response->assertSee('b2bRuleDrawer');
        $response->assertSee('b2bRuleDrawerBackdrop');
        $response->assertSee('b2bRuleDrawerClose');
        $response->assertDontSee('id="modal-manage-rule"', false);
    }

    public function test_matrix_data_loads_catalog_preview_when_no_account_selected()
    {
        $product = $this->createProduct('Preview Helmet');

        $response = $this->actingAs($this->admin, 'web')->get(
            route('admin.b2b-stock-rules.matrix-data')
        );

        $response->assertStatus(200);
        $response->assertSee('Preview Helmet');
        $response->assertSee('Standard Catalog');
    }

    public function test_matrix_data_filters_strictly_by_category()
    {
        $catA = Category::create(['name' => 'Viking Armor ' . uniqid(), 'slug' => 'viking-armor-' . uniqid(), 'status' => 1]);
        $catB = Category::create(['name' => 'Viking Food ' . uniqid(), 'slug' => 'viking-food-' . uniqid(), 'status' => 1]);

        $prodA = Product::create([
            'name' => 'Steel Chainmail ' . uniqid(),
            'slug' => 'steel-chainmail-' . uniqid(),
            'sku' => 'SKU-' . rand(10000, 99999),
            'category_id' => $catA->id,
            'qty' => 10,
            'price' => 100,
            'status' => 1,
            'is_approved' => 1,
        ]);

        $prodB = Product::create([
            'name' => 'Dried Salmon ' . uniqid(),
            'slug' => 'dried-salmon-' . uniqid(),
            'sku' => 'SKU-' . rand(10000, 99999),
            'category_id' => $catB->id,
            'qty' => 10,
            'price' => 20,
            'status' => 1,
            'is_approved' => 1,
        ]);

        $response = $this->actingAs($this->admin, 'web')->get(
            route('admin.b2b-stock-rules.matrix-data', [
                'category_id' => $catA->id,
            ])
        );

        $response->assertStatus(200);
        $response->assertSee($prodA->name);
        $response->assertDontSee($prodB->name);
    }
}

