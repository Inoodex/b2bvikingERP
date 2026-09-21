<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BarcodeLabelPrintingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected Outlet $outlet;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_barcode@b2bviking.dk'],
            [
                'name' => 'Barcode Admin',
                'password' => bcrypt('password'),
                'status' => 1,
            ]
        );
        $this->admin->syncRoles([$role]);

        $this->outlet = Outlet::create([
            'name' => 'Central Copenhagen Distribution Hub',
            'code' => 'OUT-CPH-01',
            'address' => 'Vesterbrogade 12, Copenhagen',
            'status' => 1,
            'type' => 'warehouse',
        ]);

        $this->category = Category::create([
            'name' => 'Viking Apparel',
            'slug' => 'viking-apparel',
            'status' => 1,
        ]);
    }

    public function test_it_loads_barcode_labels_hub_screen(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.barcode-labels.index'));

        $response->assertStatus(200);
        $response->assertSee('Barcode Labels Hub');
        $response->assertSee('50mm × 30mm (Thermal Roll)');
        $response->assertSee('38mm × 25mm (Compact Roll)');
        $response->assertSee('A4 Sheet: 3 × 8 (24 Labels per Page)');
        $response->assertSee('Generate Barcodes & Preview Labels', false);
        $response->assertSee('Automatically save newly generated barcodes to Product Master Catalog');
        $response->assertDontSee('id="btnHeaderPrint"', false);
        $response->assertSee('id="btnFooterPrint"', false);
        $response->assertDontSee('id="btnSaveToCatalogOnly"', false);
    }

    public function test_it_searches_products_without_mutating_missing_barcode(): void
    {
        $product = Product::create([
            'name' => 'Nordic Wool Winter Jacket',
            'slug' => 'nordic-wool-winter-jacket',
            'category_id' => $this->category->id,
            'sku' => 'JKT-NORDIC-01',
            'barcode' => null, // Intentionally null
            'price' => 799.00,
            'purchase_price' => 350.00,
            'status' => 1,
            'qty' => 45,
        ]);

        $this->assertNull($product->fresh()->barcode);

        $response = $this->actingAs($this->admin)->getJson(route('admin.barcode-labels.search-products', [
            'q' => 'Nordic Wool',
        ]));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals('Nordic Wool Winter Jacket', $data[0]['name']);
        $this->assertNull($data[0]['barcode']);
        $this->assertFalse($data[0]['has_barcode']);

        // Assert database was NOT mutated on search
        $this->assertNull($product->fresh()->barcode);
    }

    public function test_it_can_generate_barcode_on_demand_and_persist_to_database(): void
    {
        $product = Product::create([
            'name' => 'Viking Horn Souvenir',
            'slug' => 'viking-horn-souvenir',
            'category_id' => $this->category->id,
            'sku' => 'HRN-01',
            'barcode' => null,
            'price' => 249.00,
            'purchase_price' => 100.00,
            'status' => 1,
            'qty' => 20,
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.barcode-labels.generate-barcode'), [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $barcode = $response->json('barcode');

        $this->assertNotEmpty($barcode);
        $this->assertStringStartsWith('PRD-', $barcode);

        // Assert permanently saved in database
        $this->assertEquals($barcode, $product->fresh()->barcode);
    }

    public function test_it_can_render_preview_html_with_vector_svg_and_centered_layout(): void
    {
        $product = Product::create([
            'name' => 'Copenhagen Souvenir Cap',
            'slug' => 'copenhagen-souvenir-cap',
            'category_id' => $this->category->id,
            'sku' => 'CAP-CPH-RED',
            'barcode' => 'CAP-123456',
            'price' => 149.00,
            'purchase_price' => 50.00,
            'status' => 1,
            'qty' => 10,
        ]);

        $postData = [
            'preset' => 'thermal_50x30',
            'show_price' => 1,
            'show_name' => 1,
            'show_sku' => 1,
            'show_brand' => 1,
            'show_barcode_text' => 1,
            'items' => [
                [
                    'id' => $product->id,
                    'qty' => 2,
                    'use_stock' => false,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.barcode-labels.preview'), $postData);

        $response->assertStatus(200);
        $response->assertSee('Copenhagen Souvenir Cap');
        $response->assertSee('CAP-CPH-RED');
        $response->assertSee('CAP-123456');
        $response->assertSee('kr. 149.00');
        $response->assertSee('sticker-centered-layout');
        $response->assertSee('<svg', false);
        $response->assertSee('viewBox=', false);
    }

    public function test_it_can_render_print_view_with_media_print_styling_and_centered_page(): void
    {
        $product = Product::create([
            'name' => 'Viking T-Shirt Grey M',
            'slug' => 'viking-t-shirt-grey-m',
            'category_id' => $this->category->id,
            'sku' => 'TSH-GRY-M',
            'barcode' => 'TSH-778899',
            'price' => 199.00,
            'purchase_price' => 70.00,
            'status' => 1,
            'qty' => 5,
        ]);

        $postData = [
            'preset' => 'thermal_50x30',
            'show_price' => 1,
            'show_name' => 1,
            'show_sku' => 1,
            'show_brand' => 1,
            'show_barcode_text' => 1,
            'items' => json_encode([
                [
                    'id' => $product->id,
                    'qty' => 3,
                    'use_stock' => false,
                ],
            ]),
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.barcode-labels.print'), $postData);

        $response->assertStatus(200);
        $response->assertSee('@media print', false);
        $response->assertSee('50mm 30mm', false);
        $response->assertSee('thermal-page');
        $response->assertSee('TSH-778899');
        $response->assertSee('kr. 199.00');
    }

    public function test_it_can_filter_products_by_category(): void
    {
        $categoryB = Category::create([
            'name' => 'Souvenirs & Gifts',
            'slug' => 'souvenirs-gifts',
            'status' => 1,
        ]);

        $prodA = Product::create([
            'name' => 'Viking Hoodie Navy',
            'slug' => 'viking-hoodie-navy',
            'category_id' => $this->category->id,
            'sku' => 'HOD-NVY-01',
            'price' => 399.00,
            'purchase_price' => 150.00,
            'status' => 1,
            'qty' => 15,
        ]);

        $prodB = Product::create([
            'name' => 'Viking Shield Magnet',
            'slug' => 'viking-shield-magnet',
            'category_id' => $categoryB->id,
            'sku' => 'MAG-SHD-01',
            'price' => 39.00,
            'purchase_price' => 10.00,
            'status' => 1,
            'qty' => 50,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.barcode-labels.search-products', [
            'category_id' => $categoryB->id,
        ]));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals('Viking Shield Magnet', $data[0]['name']);
        $this->assertEquals($categoryB->id, $prodB->category_id);
    }

    public function test_it_returns_all_products_without_hardcoded_limit_and_preserves_barcodes(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            Product::create([
                'name' => "Batch Test Product {$i}",
                'slug' => "batch-test-product-{$i}",
                'category_id' => $this->category->id,
                'sku' => "BTP-{$i}",
                'barcode' => null,
                'price' => 10.00 * $i,
                'purchase_price' => 5.00 * $i,
                'status' => 1,
                'qty' => 10,
            ]);
        }

        $response = $this->actingAs($this->admin)->getJson(route('admin.barcode-labels.search-products'));

        $response->assertStatus(200);
        $data = $response->json('data');

        // Total products returned must be at least 30 (exceeding the old limit of 25)
        $this->assertGreaterThanOrEqual(30, count($data));

        // Ensure corrupted brackets never exist in any item
        foreach ($data as $item) {
            if (! empty($item['barcode'])) {
                $this->assertStringNotContainsString('{catId}', $item['barcode']);
                $this->assertStringNotContainsString('{', $item['barcode']);
            }
        }
    }

    public function test_it_renders_simulated_vector_preview_without_mutating_db_for_unassigned_products(): void
    {
        $product = Product::create([
            'name' => 'Blank Viking Cap',
            'slug' => 'blank-viking-cap',
            'category_id' => $this->category->id,
            'sku' => 'CAP-BLANK',
            'barcode' => null,
            'price' => 99.00,
            'purchase_price' => 40.00,
            'status' => 1,
            'qty' => 15,
        ]);

        $postData = [
            'preset' => 'thermal_50x30',
            'show_price' => 1,
            'show_name' => 1,
            'show_sku' => 1,
            'show_brand' => 1,
            'show_barcode_text' => 1,
            'items' => [
                [
                    'id' => $product->id,
                    'qty' => 1,
                    'use_stock' => false,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.barcode-labels.preview'), $postData);

        $response->assertStatus(200);
        // Only items with ready/saved barcodes are rendered in preview. Unassigned items are excluded from fake barcode rendering!
        $response->assertSee('No Ready Barcodes in Queue');
        $response->assertDontSee('Auto-Generate on Print');
        $response->assertDontSee('<svg', false);

        // Database must remain completely clean (barcode is still null during preview)
        $this->assertNull($product->fresh()->barcode);

        // Printing does NOT mutate or auto-generate barcodes for unassigned products into database
        $printResponse = $this->actingAs($this->admin)->post(route('admin.barcode-labels.print'), $postData);
        $printResponse->assertStatus(200);
        $this->assertNull($product->fresh()->barcode);
    }


    public function test_it_searches_and_returns_product_variants_cleanly(): void
    {
        $product = Product::create([
            'name' => 'Viking Hoodie with Fur',
            'slug' => 'viking-hoodie-with-fur',
            'category_id' => $this->category->id,
            'sku' => 'HD-FUR-01',
            'barcode' => null,
            'price' => 599.00,
            'purchase_price' => 250.00,
            'status' => 1,
            'qty' => 50,
        ]);

        $v1 = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Small',
            'color' => 'Navy',
            'size' => 'S',
            'qty' => 20,
            'price' => 599.00,
            'status' => 1,
            'barcode' => null,
        ]);

        $v2 = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Large',
            'color' => 'Black',
            'size' => 'L',
            'qty' => 30,
            'price' => 599.00,
            'status' => 1,
            'barcode' => 'VAR-HD-L-BLK',
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.barcode-labels.search-products', [
            'q' => 'Viking Hoodie',
        ]));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);

        $item = $data[0];
        $this->assertTrue($item['has_variants']);
        $this->assertEquals(2, $item['variant_count']);
        $this->assertCount(2, $item['variants']);

        $var1 = $item['variants'][0];
        $this->assertEquals($v1->id, $var1['id']);
        $this->assertFalse($var1['has_barcode']);

        $var2 = $item['variants'][1];
        $this->assertEquals($v2->id, $var2['id']);
        $this->assertTrue($var2['has_barcode']);
        $this->assertEquals('VAR-HD-L-BLK', $var2['barcode']);
    }

    public function test_it_can_generate_barcode_for_variant_and_persist_to_database(): void
    {
        $product = Product::create([
            'name' => 'Winter Gloves',
            'slug' => 'winter-gloves',
            'category_id' => $this->category->id,
            'sku' => 'GLV-01',
            'price' => 89.00,
            'purchase_price' => 30.00,
            'status' => 1,
            'qty' => 10,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Medium',
            'color' => 'Black',
            'size' => 'M',
            'qty' => 10,
            'price' => 89.00,
            'status' => 1,
            'barcode' => null,
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.barcode-labels.generate-barcode'), [
            'target_type' => 'variant',
            'variant_id' => $variant->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $barcode = $response->json('barcode');

        $this->assertNotEmpty($barcode);
        $this->assertStringContainsString("-V{$variant->id}-", $barcode);

        // Assert permanently saved in product_variants database table
        $this->assertEquals($barcode, $variant->fresh()->barcode);
    }

    public function test_it_can_bulk_generate_all_missing_barcodes(): void
    {
        $product = Product::create([
            'name' => 'Souvenir Keyring',
            'slug' => 'souvenir-keyring',
            'category_id' => $this->category->id,
            'sku' => 'KEY-01',
            'barcode' => null,
            'price' => 29.00,
            'purchase_price' => 10.00,
            'status' => 1,
            'qty' => 100,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Silver Shield',
            'color' => 'Silver',
            'qty' => 50,
            'price' => 29.00,
            'status' => 1,
            'barcode' => null,
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.barcode-labels.generate-all-missing'), [
            'items' => [
                ['target_type' => 'product', 'id' => $product->id],
                ['target_type' => 'variant', 'id' => $variant->id],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'count' => 2]);

        $this->assertNotNull($product->fresh()->barcode);
        $this->assertNotNull($variant->fresh()->barcode);
    }

    public function test_it_can_render_multiple_copies_cleanly(): void
    {
        $product = Product::create([
            'name' => 'Nordic Viking Sweaters',
            'slug' => 'nordic-viking-sweaters',
            'category_id' => $this->category->id,
            'sku' => 'SWT-01',
            'barcode' => 'PRD-SWT-1234',
            'price' => 699.00,
            'purchase_price' => 300.00,
            'status' => 1,
            'qty' => 100,
        ]);

        $postData = [
            'preset' => 'thermal_50x30',
            'show_price' => 1,
            'show_name' => 1,
            'show_sku' => 1,
            'show_brand' => 1,
            'show_barcode_text' => 1,
            'items' => [
                [
                    'target_type' => 'product',
                    'id' => $product->id,
                    'qty' => 3,
                    'use_stock' => false,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.barcode-labels.preview'), $postData);

        $response->assertStatus(200);
        $response->assertSee('Nordic Viking Sweaters');
        $response->assertSee('PRD-SWT-1234');
        $response->assertSee('Copies: 3 pcs');
        $response->assertSee('<svg', false);
    }
}


