<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Reports;

use App\Jobs\GenerateReportPdfJob;
use App\Jobs\GenerateStockReportPdfJob;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\Feature\Controllers\ControllerTestCase;

class AnalyticsStockReportPdfTest extends ControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        GeneralSetting::firstOrCreate(['site_name' => 'B2B Viking ERP'], ['currency_icon' => 'Kr.']);
        Role::firstOrCreate(['name' => 'Outlet User', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        Cache::forget('user_pdf_notifications_' . $this->adminUser->id);

        $tempDir = storage_path('app/temp_reports');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        $tempDir = storage_path('app/temp_reports');
        if (File::exists($tempDir)) {
            $files = glob("{$tempDir}/*_u{$this->adminUser->id}_*.pdf");
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }

        parent::tearDown();
    }

    public function test_reports_index_redirects_to_orders_report(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.index'));

        $response->assertRedirect(route('admin.reports.orders'));
    }

    public function test_order_report_pdf_route_dispatches_queue_job_instantly(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders.pdf', [
                'month' => '9',
                'year'  => '2026',
            ]));

        $response->assertRedirect();
        Queue::assertPushed(GenerateReportPdfJob::class, function ($job) {
            return $job->userId === $this->adminUser->id
                && (string) ($job->filters['month'] ?? '') === '9'
                && (string) ($job->filters['year'] ?? '') === '2026';
        });
    }

    public function test_order_report_pdf_async_route_dispatches_queue_job(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders.pdf.async', [
                'user_id' => (string) $this->adminUser->id,
            ]));

        $response->assertRedirect();
        Queue::assertPushed(GenerateReportPdfJob::class, function ($job) {
            return $job->userId === $this->adminUser->id
                && (string) ($job->filters['user_id'] ?? '') === (string) $this->adminUser->id;
        });
    }

    public function test_ajax_dispatch_returns_json_with_dispatched_at(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.reports.orders.pdf', ['month' => '9']));

        $response->assertOk();
        $response->assertJson([
            'status'  => 'dispatched',
            'message' => 'Order & Sales Report generation started in the background.',
        ]);
        $response->assertJsonStructure(['status', 'message', 'dispatched_at']);
    }

    public function test_job_execution_generates_pdf_and_registers_bell_notification(): void
    {
        $job = new GenerateReportPdfJob(['month' => 9, 'year' => 2026], $this->adminUser->id);
        $job->handle();

        // 1. Verify notification was cached
        $notifications = Cache::get('user_pdf_notifications_' . $this->adminUser->id, []);
        $this->assertNotEmpty($notifications);
        $this->assertEquals('pdf_ready', $notifications[0]['type']);
        $this->assertStringContainsString('Order & Sales Report', $notifications[0]['title']);

        // 2. Extract generated filename
        $downloadUrl = $notifications[0]['url'];
        $fileName = basename($downloadUrl);

        // 3. Verify file exists in ephemeral directory
        $filePath = storage_path('app/temp_reports/' . $fileName);
        $this->assertFileExists($filePath);

        // 4. Test download route serves file as PDF
        $downloadResponse = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders.pdf.download', ['file' => $fileName]));

        $downloadResponse->assertOk();
        $downloadResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_job_purges_previous_temporary_file_for_same_user_and_report(): void
    {
        $tempDir = storage_path('app/temp_reports');
        $oldFile = "{$tempDir}/order_sales_report_u{$this->adminUser->id}_20260101_000000.pdf";
        file_put_contents($oldFile, '%PDF-1.4 mock old report');
        $this->assertFileExists($oldFile);

        $job = new GenerateReportPdfJob([], $this->adminUser->id);
        $job->handle();

        // Old file must be purged
        $this->assertFileDoesNotExist($oldFile);

        // New file must exist
        $newFiles = glob("{$tempDir}/order_sales_report_u{$this->adminUser->id}_*.pdf");
        $this->assertNotEmpty($newFiles);
    }

    public function test_download_missing_report_redirects_with_error(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders.pdf.download', ['file' => 'non_existent_report.pdf']));

        $response->assertRedirect();
    }

    public function test_check_report_status_returns_ready_with_download_url_when_file_exists(): void
    {
        $tempDir = storage_path('app/temp_reports');
        $filename = "order_sales_report_u{$this->adminUser->id}_" . date('Ymd_His') . ".pdf";
        $filePath = "{$tempDir}/{$filename}";
        file_put_contents($filePath, '%PDF-1.4 test report');

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.reports.orders.check-status', ['type' => 'order_sales_report']));

        $response->assertOk();
        $response->assertJson([
            'ready'    => true,
            'filename' => $filename,
        ]);
        $response->assertJsonStructure(['ready', 'download_url', 'filename', 'time', 'date', 'timestamp']);
    }

    public function test_check_report_status_respects_after_timestamp_filter(): void
    {
        $tempDir = storage_path('app/temp_reports');
        $pastTime = time() - 300;
        $filename = "order_sales_report_u{$this->adminUser->id}_20260101_000000.pdf";
        $filePath = "{$tempDir}/{$filename}";
        file_put_contents($filePath, '%PDF-1.4 test report');
        touch($filePath, $pastTime);

        // Polling with 'after' filter set to current time
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.reports.orders.check-status', [
                'type'  => 'order_sales_report',
                'after' => time(),
            ]));

        $response->assertOk();
        $response->assertJson(['ready' => false]);
    }

    public function test_order_report_view_renders_successfully_with_on_page_action_buttons(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders'));

        $response->assertOk();
        $response->assertSee('btn-generate-pdf', false);
        $response->assertSee('btn-download-pdf', false);
    }

    public function test_low_stock_report_queries_min_inventory_qty_instead_of_hardcoded_100(): void
    {
        $unit = Unit::firstOrCreate(['slug' => 'piece'], ['name' => 'Piece', 'short_name' => 'pc', 'status' => 1]);
        $category = Category::firstOrCreate(['slug' => 'auto-parts'], ['name' => 'Auto Parts', 'status' => 1]);

        // Product A: stock is 50, min_inventory_qty is 10. (50 > 10, so NOT low stock)
        $productHealthy = Product::create([
            'name'              => 'Healthy Stock Brake Disc',
            'slug'              => 'healthy-stock-brake-disc-' . uniqid(),
            'sku'               => 'HS-BD-' . uniqid(),
            'category_id'       => $category->id,
            'unit_id'           => $unit->id,
            'price'             => 250.00,
            'purchase_price'    => 150.00,
            'status'            => 1,
            'min_inventory_qty' => 10,
        ]);

        InventoryStock::create([
            'product_id' => $productHealthy->id,
            'quantity'   => 50,
        ]);

        // Product B: stock is 3, min_inventory_qty is 10. (3 <= 10, so IS low stock)
        $productLow = Product::create([
            'name'              => 'Critically Low Brake Pad',
            'slug'              => 'critically-low-brake-pad-' . uniqid(),
            'sku'               => 'CL-BP-' . uniqid(),
            'category_id'       => $category->id,
            'unit_id'           => $unit->id,
            'price'             => 100.00,
            'purchase_price'    => 60.00,
            'status'            => 1,
            'min_inventory_qty' => 10,
        ]);

        InventoryStock::create([
            'product_id' => $productLow->id,
            'quantity'   => 3,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.low-stock'));

        $response->assertOk();
        // Product with 50 units must NOT show up as low stock
        $response->assertDontSee('Healthy Stock Brake Disc');
        // Product with 3 units must show up as low stock
        $response->assertSee('Critically Low Brake Pad');
    }

    public function test_low_stock_report_renders_category_and_brand_filters_and_clean_table(): void
    {
        $unit = Unit::firstOrCreate(['slug' => 'piece'], ['name' => 'Piece', 'short_name' => 'pc', 'status' => 1]);
        $category = Category::firstOrCreate(['slug' => 'filters'], ['name' => 'Filters', 'status' => 1]);
        $prod = Product::create([
            'name'              => 'Sample Low Stock Product ' . uniqid(),
            'slug'              => 'sample-low-stock-' . uniqid(),
            'sku'               => 'SLS-' . uniqid(),
            'category_id'       => $category->id,
            'unit_id'           => $unit->id,
            'price'             => 50.00,
            'purchase_price'    => 30.00,
            'status'            => 1,
            'min_inventory_qty' => 10,
        ]);
        InventoryStock::create(['product_id' => $prod->id, 'quantity' => 1]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.low-stock'));

        $response->assertOk();
        // Verify redesigned filter card and options matching stock report
        $response->assertSee('sr-filter-card', false);
        $response->assertSee('sr-filter-head', false);
        $response->assertSee('name="category_id"', false);
        $response->assertSee('name="brand_id"', false);
        $response->assertSee('name="vendor_id"', false);
        $response->assertSee('All Categories', false);
        $response->assertSee('All Brands', false);
        $response->assertSee('All Vendors', false);

        // Verify clean table without buggy DataTables that caused shaking
        $response->assertSee('id="low-stock-table"', false);
        $response->assertDontSee('id="table-1"', false);
        $response->assertDontSee('$("#table-1").dataTable', false);
    }

    public function test_low_stock_report_filters_by_category_and_brand(): void
    {
        $unit = Unit::firstOrCreate(['slug' => 'piece'], ['name' => 'Piece', 'short_name' => 'pc', 'status' => 1]);
        $catA = Category::create(['name' => 'Category Alpha ' . uniqid(), 'slug' => 'cat-alpha-' . uniqid(), 'status' => 1]);
        $catB = Category::create(['name' => 'Category Beta ' . uniqid(), 'slug' => 'cat-beta-' . uniqid(), 'status' => 1]);
        $brandA = Brand::create(['name' => 'Brand Apex ' . uniqid(), 'slug' => 'brand-apex-' . uniqid(), 'status' => 1]);
        $brandB = Brand::create(['name' => 'Brand Bosch ' . uniqid(), 'slug' => 'brand-bosch-' . uniqid(), 'status' => 1]);

        $prodA = Product::create([
            'name'              => 'Low Stock Filter Product A',
            'slug'              => 'low-stock-filter-prod-a-' . uniqid(),
            'sku'               => 'LS-FA-' . uniqid(),
            'category_id'       => $catA->id,
            'brand_id'          => $brandA->id,
            'unit_id'           => $unit->id,
            'price'             => 100.00,
            'purchase_price'    => 60.00,
            'status'            => 1,
            'min_inventory_qty' => 10,
        ]);
        InventoryStock::create(['product_id' => $prodA->id, 'quantity' => 2]);

        $prodB = Product::create([
            'name'              => 'Low Stock Filter Product B',
            'slug'              => 'low-stock-filter-prod-b-' . uniqid(),
            'sku'               => 'LS-FB-' . uniqid(),
            'category_id'       => $catB->id,
            'brand_id'          => $brandB->id,
            'unit_id'           => $unit->id,
            'price'             => 120.00,
            'purchase_price'    => 70.00,
            'status'            => 1,
            'min_inventory_qty' => 10,
        ]);
        InventoryStock::create(['product_id' => $prodB->id, 'quantity' => 1]);

        // Filter by Category A
        $resCat = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.low-stock', ['category_id' => $catA->id]));
        $resCat->assertOk();
        $resCat->assertSee('Low Stock Filter Product A');
        $resCat->assertDontSee('Low Stock Filter Product B');

        // Filter by Brand B
        $resBrand = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.low-stock', ['brand_id' => $brandB->id]));
        $resBrand->assertOk();
        $resBrand->assertSee('Low Stock Filter Product B');
        $resBrand->assertDontSee('Low Stock Filter Product A');

        // Test AJAX auto-filter response
        $resAjax = $this->actingAs($this->adminUser)
            ->getJson(route('admin.reports.low-stock', ['category_id' => $catA->id]));
        $resAjax->assertOk();
        $resAjax->assertJson([
            'success' => true,
        ]);
        $this->assertStringContainsString('Low Stock Filter Product A', $resAjax->json('html'));
        $this->assertStringNotContainsString('Low Stock Filter Product B', $resAjax->json('html'));
    }

    public function test_sidebar_has_no_all_analytics_reports_link(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders'));

        $response->assertOk();
        $response->assertDontSee('All Analytics Reports');
    }

    public function test_stock_report_view_renders_redesigned_kpi_cards_and_no_image_fallback(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.stock'));

        $response->assertOk();
        $response->assertSee('sr-stat-stripe', false);
        $response->assertSee('span-total-qty', false);
        $response->assertSee('span-total-value', false);
        $response->assertSee('span-potential-revenue', false);
        $response->assertSee('span-potential-profit', false);
        $response->assertSee('uploads/no-image.svg', false);
        $response->assertDontSee('sr-product-placeholder', false);
        $response->assertSee('btn-generate-pdf', false);
    }

    public function test_stock_report_pdf_route_dispatches_queue_job_instantly(): void
    {
        Queue::fake();

        $startTime = microtime(true);
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.stock.pdf', ['category_id' => 1]));
        $duration = microtime(true) - $startTime;

        $response->assertRedirect();
        $this->assertLessThan(0.5, $duration, 'Controller dispatch took too long');
        Queue::assertPushed(GenerateStockReportPdfJob::class, function ($job) {
            return $job->userId === $this->adminUser->id;
        });
    }

    public function test_stock_report_pdf_without_filter_returns_warning(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.reports.stock.pdf.async'));

        $response->assertStatus(422);
        $response->assertJson([
            'status'  => 'warning',
            'message' => 'Please select a Category or Brand filter before exporting PDF. For full inventory, please use Excel export.',
        ]);
        Queue::assertNotPushed(GenerateStockReportPdfJob::class);
    }

    public function test_stock_report_pdf_async_route_dispatches_queue_job(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.reports.stock.pdf.async', ['category_id' => 1]));

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'dispatched_at',
        ]);
        Queue::assertPushed(GenerateStockReportPdfJob::class);
    }

    public function test_stock_report_job_execution_generates_pdf_and_registers_bell_notification(): void
    {
        $job = new GenerateStockReportPdfJob([], $this->adminUser->id);
        $job->handle();

        $tempDir = storage_path('app/temp_reports');
        $files = glob("{$tempDir}/stock_valuation_report_u{$this->adminUser->id}_*.pdf");
        $this->assertNotEmpty($files, 'Expected generated stock valuation PDF was not found.');

        // Verify notifications in cache
        $cachedNotifications = Cache::get('user_pdf_notifications_' . $this->adminUser->id);
        $this->assertIsArray($cachedNotifications);
        $this->assertNotEmpty($cachedNotifications);
        $this->assertEquals('Stock Valuation Report Ready', $cachedNotifications[0]['title']);
    }

    public function test_stock_report_check_status_and_download_flow(): void
    {
        $job = new GenerateStockReportPdfJob([], $this->adminUser->id);
        $job->handle();

        $tempDir = storage_path('app/temp_reports');
        $files = glob("{$tempDir}/stock_valuation_report_u{$this->adminUser->id}_*.pdf");
        $this->assertNotEmpty($files);
        $filename = basename($files[0]);

        // Status poll
        $pollRes = $this->actingAs($this->adminUser)
            ->getJson(route('admin.reports.stock.check-status', ['type' => 'stock_valuation_report']));

        $pollRes->assertOk();
        $pollRes->assertJson([
            'ready' => true,
        ]);
        $this->assertEquals($filename, $pollRes->json('filename'));

        // Download
        $downloadRes = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.stock.pdf.download', ['file' => $filename]));

        $downloadRes->assertOk();
        $this->assertEquals('application/pdf', $downloadRes->headers->get('content-type'));
    }

    public function test_stock_report_job_purges_previous_temporary_file_for_same_user(): void
    {
        $tempDir = storage_path('app/temp_reports');
        $oldFile = "{$tempDir}/stock_valuation_report_u{$this->adminUser->id}_20260101_000000.pdf";
        File::put($oldFile, '%PDF-1.4 dummy old content');
        $this->assertFileExists($oldFile);

        $job = new GenerateStockReportPdfJob([], $this->adminUser->id);
        $job->handle();

        $this->assertFileDoesNotExist($oldFile, 'Previous temporary stock report file should have been auto-purged.');
    }
}
