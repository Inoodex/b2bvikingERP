<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\HandlingUnit;
use App\Models\HandlingUnitItem;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Barcode\Gs1BarcodeService;
use App\Services\Barcode\HandlingUnitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class HandlingUnitAndPackagingHierarchyTest extends TestCase
{
    protected User $admin;
    protected Category $apparelCategory;
    protected PackagingType $boxType;
    protected PackagingType $cartonType;
    protected PackagingType $palletType;
    protected HandlingUnitService $huService;
    protected Gs1BarcodeService $gs1Service;

    protected function setUp(): void
    {
        parent::setUp();

        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_packaging@b2bviking.dk'],
            [
                'name' => 'Packaging Admin',
                'password' => bcrypt('password'),
                'status' => 1,
            ]
        );
        $this->admin->syncRoles([$role]);

        $this->apparelCategory = Category::firstOrCreate(
            ['slug' => 'apparel-test'],
            [
                'name' => 'Apparel & Souvenirs',
                'gpc_code' => '10001363',
                'gpc_title' => 'Clothing - Tops/Shirts/Apparel',
                'status' => 1,
            ]
        );

        $this->boxType = PackagingType::firstOrCreate(
            ['code' => 'BOX'],
            [
                'name' => 'Small Box',
                'level_order' => 1,
                'tare_weight' => 0.200,
                'is_active' => true,
            ]
        );

        $this->cartonType = PackagingType::firstOrCreate(
            ['code' => 'CTN'],
            [
                'name' => 'Master Carton',
                'level_order' => 2,
                'tare_weight' => 0.800,
                'is_active' => true,
            ]
        );

        $this->palletType = PackagingType::firstOrCreate(
            ['code' => 'PLT'],
            [
                'name' => 'Euro Pallet',
                'level_order' => 3,
                'tare_weight' => 20.000,
                'is_active' => true,
            ]
        );

        $this->gs1Service = app(Gs1BarcodeService::class);
        $this->huService = app(HandlingUnitService::class);
    }

    public function test_it_creates_handling_unit_with_gs1_gpc_prefix(): void
    {
        $hu = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->cartonType->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'status' => 'packed',
        ]);

        $this->assertInstanceOf(HandlingUnit::class, $hu);
        $this->assertNotEmpty($hu->hu_code);
        // Formula: CTN-10001363-YEAR-RANDOM
        $this->assertStringStartsWith('CTN-10001363-', $hu->hu_code);
        $this->assertEquals($this->cartonType->id, $hu->packaging_type_id);
    }

    public function test_it_supports_arbitrary_nested_packaging_hierarchy(): void
    {
        // 1. Create Product & Variants
        $product = Product::create([
            'name' => 'Viking Wool Cap',
            'slug' => 'viking-wool-cap',
            'category_id' => $this->apparelCategory->id,
            'sku' => 'VWC-001',
            'price' => 150.00,
            'status' => 1,
            'qty' => 100,
        ]);

        $variantM = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Size M / Grey',
            'color' => 'Grey',
            'size' => 'M',
            'price' => 150.00,
            'qty' => 50,
            'status' => 1,
        ]);

        $variantL = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Size L / Navy',
            'color' => 'Navy',
            'size' => 'L',
            'price' => 150.00,
            'qty' => 50,
            'status' => 1,
        ]);

        // Step 1: Pack 10 pcs of Variant M into Small Box 1
        $box1 = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->boxType->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'items' => [
                ['product_id' => $product->id, 'variant_id' => $variantM->id, 'quantity' => 10],
            ],
        ]);
        $this->assertEquals(10, $box1->total_quantity);

        // Step 2: Pack 15 pcs of Variant L into Small Box 2
        $box2 = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->boxType->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'items' => [
                ['product_id' => $product->id, 'variant_id' => $variantL->id, 'quantity' => 15],
            ],
        ]);
        $this->assertEquals(15, $box2->total_quantity);

        // Step 3: Nest Box 1 and Box 2 into Master Carton (2-tier nesting)
        $masterCarton = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->cartonType->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'child_unit_ids' => [$box1->id, $box2->id],
        ]);

        // Verify Master Carton has total quantity = 10 + 15 = 25
        $this->assertEquals(25, $masterCarton->fresh()->total_quantity);
        $this->assertCount(2, $masterCarton->fresh()->childUnits);

        // Step 4: Nest Master Carton onto Euro Pallet (3-tier nesting)
        $pallet = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->palletType->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'child_unit_ids' => [$masterCarton->id],
        ]);

        // Verify Pallet has total quantity = 25
        $this->assertEquals(25, $pallet->fresh()->total_quantity);

        // Verify lookup by barcode returns full flattened contents
        $lookupPallet = $this->huService->getByHuCode($pallet->hu_code);
        $this->assertNotNull($lookupPallet);
        $flattened = $lookupPallet->getFlattenedContents();
        $this->assertCount(2, $flattened);

        $totalItemsInPallet = array_sum(array_column($flattened, 'quantity'));
        $this->assertEquals(25, $totalItemsInPallet);
    }

    public function test_it_prevents_circular_packaging_hierarchy(): void
    {
        $unitA = $this->huService->createHandlingUnit(['packaging_type_id' => $this->cartonType->id]);
        $unitB = $this->huService->createHandlingUnit(['packaging_type_id' => $this->boxType->id]);

        $this->huService->nestChildUnit($unitA, $unitB);

        $this->expectException(InvalidArgumentException::class);
        // Attempting to nest unitA inside unitB would create a circular loop
        $this->huService->nestChildUnit($unitB, $unitA);
    }

    public function test_it_renders_packaging_units_index_screen(): void
    {
        $hu = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->cartonType->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'status' => 'packed',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.packaging-units.index'));
        $response->assertStatus(200);
        $response->assertSee('Handling Units');
        $response->assertSee($hu->hu_code);
        $response->assertSee('Pack New Handling Unit');
    }

    public function test_it_renders_packaging_units_create_workbench(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.packaging-units.create'));
        $response->assertStatus(200);
        $response->assertSee('Pack New Handling Unit');
        $response->assertSee('GS1 GPC Category Classification');
        $response->assertSee('Pack Products');
        $response->assertSee('Create Container');
    }

    public function test_it_creates_handling_unit_via_post_and_redirects(): void
    {
        $product = Product::create([
            'name' => 'Viking Souvenir Mug',
            'slug' => 'viking-souvenir-mug',
            'category_id' => $this->apparelCategory->id,
            'sku' => 'MUG-VIK-01',
            'price' => 79.00,
            'status' => 1,
            'qty' => 50,
        ]);

        $postData = [
            'packaging_type_id' => $this->cartonType->id,
            'category_id' => $this->apparelCategory->id,
            'batch_no' => 'LOT-MUG-2026',
            'gross_weight' => 8.500,
            'status' => 'packed',
            'items' => json_encode([
                [
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'quantity' => 24,
                ],
            ]),
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.packaging-units.store'), $postData);
        $response->assertStatus(302);

        $created = HandlingUnit::where('batch_no', 'LOT-MUG-2026')->first();
        $this->assertNotNull($created);
        $this->assertEquals(24, $created->total_quantity);
        $this->assertStringStartsWith('CTN-10001363-', $created->hu_code);
    }

    public function test_it_renders_handling_unit_show_screen_with_vector_barcode(): void
    {
        $hu = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->cartonType->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'status' => 'packed',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.packaging-units.show', $hu->id));
        $response->assertStatus(200);
        $response->assertSee($hu->hu_code);
        $response->assertSee('GS1 Logistics Label Preview');
        $response->assertSee('Print 4×6 Logistics Label', false);
        $response->assertSee('<svg', false);
    }

    public function test_it_renders_4x6_inch_logistics_shipping_label(): void
    {
        $product = Product::create([
            'name' => 'Viking Battle Axe Souvenir',
            'slug' => 'viking-battle-axe-souvenir',
            'category_id' => $this->apparelCategory->id,
            'sku' => 'AXE-SOUV-01',
            'price' => 299.00,
            'status' => 1,
            'qty' => 30,
        ]);

        $hu = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->cartonType->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'batch_no' => 'LOT-AXE-001',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 12],
            ],
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.packaging-units.print', $hu->id));
        $response->assertStatus(200);
        $response->assertSee('@page', false);
        $response->assertSee('100mm 150mm');
        $response->assertSee('B2B VIKING LOGISTICS');
        $response->assertSee($hu->hu_code);
        $response->assertSee('12');
        $response->assertSee('PCS');
        $response->assertSee('<svg', false);
    }

    public function test_it_handles_wms_1_scan_receiving_for_handling_unit_and_its_nested_contents(): void
    {
        $product = Product::create([
            'name' => 'Viking Shield Replica',
            'slug' => 'viking-shield-replica',
            'category_id' => $this->apparelCategory->id,
            'sku' => 'SHD-REP-01',
            'price' => 450.00,
            'status' => 1,
            'qty' => 40,
        ]);

        // Create Small Box with 10 pcs
        $box = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->boxType->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'status' => 'packed',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10],
            ],
        ]);

        // Create Master Carton containing the box
        $masterCarton = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->cartonType->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'status' => 'packed',
            'child_unit_ids' => [$box->id],
        ]);

        $this->assertEquals('packed', $masterCarton->status);
        $this->assertEquals('packed', $box->fresh()->status);

        // Perform 1-Scan Receiving on the Master Carton Barcode
        $response = $this->actingAs($this->admin)->postJson(route('admin.packaging-units.scan-receive'), [
            'hu_code' => $masterCarton->hu_code,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'hu_code' => $masterCarton->hu_code,
            'status' => 'received',
            'total_quantity' => 10,
        ]);

        // Assert Master Carton AND nested box are both marked as received
        $this->assertEquals('received', $masterCarton->fresh()->status);
        $this->assertEquals('received', $box->fresh()->status);
    }

    public function test_it_handles_wms_1_scan_dispatch_shipping(): void
    {
        $hu = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->cartonType->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'status' => 'packed',
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.packaging-units.scan-ship'), [
            'hu_code' => $hu->hu_code,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'hu_code' => $hu->hu_code,
            'status' => 'shipped',
        ]);

        $this->assertEquals('shipped', $hu->fresh()->status);
    }

    public function test_it_dynamically_resolves_gpc_code_for_categories_without_saved_database_code(): void
    {
        // 1. Hoodies / Sweaters
        $hoodieCat = Category::create([
            'name' => 'Viking Nordic Hoodies',
            'slug' => 'viking-nordic-hoodies-' . uniqid(),
            'status' => 1,
            'gpc_code' => null,
            'gpc_title' => null,
        ]);
        $this->assertEquals('10001360', $this->gs1Service->resolveGpcCode($hoodieCat));

        // 2. Leather Boots / Footwear
        $bootCat = Category::create([
            'name' => 'Viking Leather Boots',
            'slug' => 'viking-leather-boots-' . uniqid(),
            'status' => 1,
            'gpc_code' => null,
        ]);
        $this->assertEquals('10001344', $this->gs1Service->resolveGpcCode($bootCat));

        // 3. Mugs / Tableware
        $mugCat = Category::create([
            'name' => 'Ceramic Viking Coffee Mugs',
            'slug' => 'ceramic-viking-coffee-mugs-' . uniqid(),
            'status' => 1,
            'gpc_code' => null,
        ]);
        $this->assertEquals('10001452', $this->gs1Service->resolveGpcCode($mugCat));

        // 4. Rubber Ducks / Toys
        $duckCat = Category::create([
            'name' => 'Viking Rubber Ducks',
            'slug' => 'viking-rubber-ducks-' . uniqid(),
            'status' => 1,
            'gpc_code' => null,
        ]);
        $this->assertEquals('10000780', $this->gs1Service->resolveGpcCode($duckCat));

        // 5. Honey / Preserves
        $honeyCat = Category::create([
            'name' => 'Artisanal Viking Honey',
            'slug' => 'artisanal-viking-honey-' . uniqid(),
            'status' => 1,
            'gpc_code' => null,
        ]);
        $this->assertEquals('10000168', $this->gs1Service->resolveGpcCode($honeyCat));

        // 6. Unknown Category falls back to GS1 General Merchandise Standard
        $unknownCat = Category::create([
            'name' => 'Unidentified Ancient Relic XYZ',
            'slug' => 'unidentified-ancient-relic-' . uniqid(),
            'status' => 1,
            'gpc_code' => null,
        ]);
        $this->assertEquals('10000000', $this->gs1Service->resolveGpcCode($unknownCat));

        // Verify product barcode uses dynamically resolved code
        $product = Product::create([
            'name' => 'Odin Graphic Hoodie',
            'slug' => 'odin-graphic-hoodie-' . uniqid(),
            'category_id' => $hoodieCat->id,
            'price' => 49.99,
            'qty' => 50,
            'status' => 1,
        ]);

        $barcode = $this->gs1Service->generateProductBarcode($product);
        $this->assertStringStartsWith('10001360-', $barcode);
    }

    public function test_category_controller_auto_populates_gpc_fields_when_saved_blank(): void
    {
        $uniqueName = 'Premium Viking T-Shirts ' . \Illuminate\Support\Str::random(6);
        $response = $this->actingAs($this->admin)->post(route('admin.category.store'), [
            'name' => $uniqueName,
            'status' => 1,
            'gpc_code' => '',
            'gpc_title' => '',
        ]);

        $response->assertRedirect(route('admin.category.index'));

        $category = Category::where('name', $uniqueName)->first();
        $this->assertNotNull($category);
        $this->assertEquals('10001363', $category->gpc_code);
        $this->assertEquals('Clothing - Tops/Shirts/T-Shirts/Blouses', $category->gpc_title);
    }

    public function test_it_rejects_packing_product_with_zero_stock(): void
    {
        $zeroStockProduct = Product::create([
            'name' => 'Out of Stock Viking Helmet',
            'slug' => 'out-of-stock-viking-helmet-' . uniqid(),
            'category_id' => $this->apparelCategory->id,
            'price' => 89.00,
            'qty' => 0, // 0 Stock!
            'status' => 1,
        ]);

        // 1. Service-level check throws InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot pack out-of-stock item");

        $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->boxType->id,
            'items' => [
                ['product_id' => $zeroStockProduct->id, 'quantity' => 1],
            ],
        ]);
    }

    public function test_it_rejects_packing_zero_stock_via_controller(): void
    {
        $zeroStockProduct = Product::create([
            'name' => 'Out of Stock Viking Shield',
            'slug' => 'out-of-stock-viking-shield-' . uniqid(),
            'category_id' => $this->apparelCategory->id,
            'price' => 120.00,
            'qty' => 0,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.packaging-units.store'), [
            'packaging_type_id' => $this->boxType->id,
            'items' => json_encode([
                ['product_id' => $zeroStockProduct->id, 'quantity' => 1],
            ]),
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_it_rejects_packing_quantity_exceeding_available_stock(): void
    {
        $limitedProduct = Product::create([
            'name' => 'Limited Edition Viking Horn',
            'slug' => 'limited-edition-viking-horn-' . uniqid(),
            'category_id' => $this->apparelCategory->id,
            'price' => 199.00,
            'qty' => 5, // Only 5 available!
            'status' => 1,
        ]);

        // Attempt to pack 15 pcs via controller
        $response = $this->actingAs($this->admin)->post(route('admin.packaging-units.store'), [
            'packaging_type_id' => $this->cartonType->id,
            'items' => json_encode([
                ['product_id' => $limitedProduct->id, 'quantity' => 15],
            ]),
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_it_can_create_empty_master_carton_without_loose_products(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.packaging-units.store'), [
            'packaging_type_id' => $this->cartonType->id,
            'category_id' => $this->apparelCategory->id,
            'batch_no' => 'EMPTY-CARTON-01',
            'status' => 'draft',
            'items' => json_encode([]), // No loose products!
        ]);

        $response->assertRedirect();

        $carton = HandlingUnit::where('batch_no', 'EMPTY-CARTON-01')->first();
        $this->assertNotNull($carton);
        $this->assertEquals(0, $carton->total_quantity);
        $this->assertEquals($this->cartonType->id, $carton->packaging_type_id);
    }

    public function test_small_box_nested_into_big_carton_bubbles_quantity_upward(): void
    {
        // 1. Create Big Carton first (empty container)
        $bigCarton = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->cartonType->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'status' => 'packed',
        ]);
        $this->assertEquals(0, $bigCarton->total_quantity);

        // 2. Create in-stock product
        $product = Product::create([
            'name' => 'Viking Wool Mittens',
            'slug' => 'viking-wool-mittens-' . uniqid(),
            'category_id' => $this->apparelCategory->id,
            'price' => 45.00,
            'qty' => 50,
            'status' => 1,
        ]);

        // 3. Create Small Box 1 with 10 pcs, nested in Big Carton
        $box1 = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->boxType->id,
            'parent_handling_unit_id' => $bigCarton->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10],
            ],
        ]);
        $this->assertEquals(10, $box1->total_quantity);
        $this->assertEquals(10, $bigCarton->fresh()->total_quantity);

        // 4. Create Small Box 2 with 15 pcs, nested in Big Carton
        $box2 = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->boxType->id,
            'parent_handling_unit_id' => $bigCarton->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 15],
            ],
        ]);
        $this->assertEquals(15, $box2->total_quantity);

        // Big Carton now holds 10 + 15 = 25 items from child boxes!
        $this->assertEquals(25, $bigCarton->fresh()->total_quantity);
        $this->assertCount(2, $bigCarton->fresh()->childUnits);
    }

    public function test_master_carton_packs_existing_child_boxes_via_child_unit_ids(): void
    {
        $product = Product::create([
            'name' => 'Nordic Drinking Horn',
            'slug' => 'nordic-drinking-horn-' . uniqid(),
            'category_id' => $this->apparelCategory->id,
            'price' => 65.00,
            'qty' => 100,
            'status' => 1,
        ]);

        // Box A with 8 pcs
        $boxA = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->boxType->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 8],
            ],
        ]);

        // Box B with 12 pcs
        $boxB = $this->huService->createHandlingUnit([
            'packaging_type_id' => $this->boxType->id,
            'gpc_code' => $this->apparelCategory->gpc_code,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 12],
            ],
        ]);

        // Create Master Carton with child_unit_ids and 0 loose products
        $response = $this->actingAs($this->admin)->post(route('admin.packaging-units.store'), [
            'packaging_type_id' => $this->cartonType->id,
            'category_id' => $this->apparelCategory->id,
            'batch_no' => 'MASTER-NESTED-LOT',
            'child_unit_ids' => [$boxA->id, $boxB->id],
            'items' => json_encode([]),
        ]);

        $response->assertRedirect();

        $masterCarton = HandlingUnit::where('batch_no', 'MASTER-NESTED-LOT')->first();
        $this->assertNotNull($masterCarton);
        $this->assertEquals(20, $masterCarton->total_quantity);
        $this->assertCount(2, $masterCarton->childUnits);
    }
}


