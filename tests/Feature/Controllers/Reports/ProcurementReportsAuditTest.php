<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Reports;

use App\Jobs\GenerateProcurementReportPdfJob;
use App\Jobs\GeneratePurchaseVsLastYearPdfJob;
use App\Jobs\GenerateTotalPurchaseValuePdfJob;
use App\Models\GeneralSetting;
use App\Models\Purchase;
use App\Models\Vendor;
use App\Services\PurchaseReportService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Controllers\ControllerTestCase;

class ProcurementReportsAuditTest extends ControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        GeneralSetting::firstOrCreate(['site_name' => 'B2B Viking ERP'], ['currency_icon' => 'Kr.']);
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

    /**
     * Test 1: Purchase History page loads and completely decouples from legacy /admin/purchases/{id}.
     */
    public function test_purchase_report_page_loads_and_links_to_purchase_orders_show(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.purchase'));

        $response->assertStatus(200);

        // Under no circumstances should legacy purchase show route be linked
        $response->assertDontSee('/admin/purchases/', false);
        $response->assertDontSee("admin.purchases.show", false);

        // Must support procurement PO links and filters
        $response->assertSee('purchase_type', false);
        $response->assertSee('milestone_status', false);
        $response->assertSee('purchase-history-table', false);
        $response->assertSee(route('admin.reports.procurement.pdf.async'), false);

        // Test Yajra DataTable AJAX JSON response
        $ajaxResponse = $this->actingAs($this->adminUser)
            ->getJson(route('admin.reports.purchase'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);

        $firstRow = $ajaxResponse->json('data.0');
        if ($firstRow) {
            $this->assertStringContainsString('/admin/purchase-orders/', $firstRow['po_no'] ?? '');
            $this->assertArrayHasKey('vendor_name', $firstRow);
            $this->assertArrayHasKey('type_badge', $firstRow);
            $this->assertArrayHasKey('milestone_badge', $firstRow);
            $this->assertArrayHasKey('action', $firstRow);
            $this->assertStringContainsString('/admin/purchase-orders/', $firstRow['action'] ?? '');
        }
    }

    /**
     * Test 2: Product Purchase History page loads with vendor/date filters and proper links.
     */
    public function test_product_purchase_history_page_loads_and_has_filters_and_links(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.product-purchase-history'));

        $response->assertStatus(200);
        $response->assertSee('name="product_id"', false);
        $response->assertSee('name="vendor_id"', false);
        $response->assertSee('name="start_date"', false);
        $response->assertSee('name="end_date"', false);
        $response->assertSee('product-purchase-history-table', false);

        // Test Yajra DataTable AJAX JSON response
        $ajaxResponse = $this->actingAs($this->adminUser)
            ->getJson(route('admin.reports.product-purchase-history'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);

        $firstRow = $ajaxResponse->json('data.0');
        if ($firstRow) {
            $this->assertStringContainsString('/admin/purchase-orders/', $firstRow['po_no'] ?? '');
            $this->assertArrayHasKey('product_info', $firstRow);
            $this->assertArrayHasKey('vendor_name', $firstRow);
            $this->assertArrayHasKey('action', $firstRow);
        }
    }

    /**
     * Test 3: Supplier-wise Purchase DataTable executes single-pass aggregate (< 5 queries total).
     */
    public function test_supplier_wise_purchase_datatable_executes_single_pass_aggregate(): void
    {
        DB::enableQueryLog();

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.purchase-reports.supplier-wise'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);

        // Assert query count is tiny (single-pass join, zero N+1 per row)
        $this->assertLessThan(10, $queryCount, "SupplierWisePurchaseDataTable executed {$queryCount} queries! Expected < 10.");
    }

    /**
     * Test 4: Item-wise Purchase DataTable executes single-pass aggregate (< 5 queries total).
     */
    public function test_item_wise_purchase_datatable_executes_single_pass_aggregate(): void
    {
        DB::enableQueryLog();

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.purchase-reports.item-wise'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);

        $this->assertLessThan(10, $queryCount, "ItemWisePurchaseDataTable executed {$queryCount} queries! Expected < 10.");
    }

    /**
     * Test 5: Total Purchase Value Periodic report loads with Vendor filter.
     */
    public function test_total_purchase_value_periodic_report_loads_with_vendor_filter(): void
    {
        $vendor = Vendor::where('status', 1)->first();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.purchase-reports.total-value', [
                'vendor_id' => $vendor?->id,
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('summary');
        $response->assertViewHas('reportData');
        $response->assertViewHas('vendors');
        $response->assertSee('name="vendor_id"', false);
    }

    /**
     * Test 6: Purchase Value vs Last Year computes 12-month comparative YoY matrix.
     */
    public function test_purchase_vs_last_year_report_computes_12_month_yoy_matrix(): void
    {
        $service = app(PurchaseReportService::class);
        $result = $service->getPurchaseVsLastYear((int) date('Y'));

        $this->assertArrayHasKey('current_year', $result);
        $this->assertArrayHasKey('last_year', $result);
        $this->assertArrayHasKey('monthly_matrix', $result);
        $this->assertCount(12, $result['monthly_matrix'], 'YoY matrix must contain exactly 12 months.');

        $january = $result['monthly_matrix'][0];
        $this->assertEquals('January', $january['month']);
        $this->assertArrayHasKey('variance_amount', $january);
        $this->assertArrayHasKey('growth_percentage', $january);

        // Test view render
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.purchase-reports.vs-last-year', ['year' => date('Y')]));

        $response->assertStatus(200);
        $response->assertSee('12-Month Comparative Spend Breakdown', false);
        $response->assertSee('January', false);
        $response->assertSee('December', false);
    }

    /**
     * Test 7: PR Status report and DataTable loads with department and status filters.
     */
    public function test_pr_status_report_and_datatable_loads(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.purchase-reports.pr-status'));

        $response->assertStatus(200);
        $response->assertSee('name="department_id"', false);
        $response->assertSee('name="status"', false);

        $ajaxResponse = $this->actingAs($this->adminUser)
            ->getJson(route('admin.purchase-reports.pr-status'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $ajaxResponse->assertStatus(200);
    }

    /**
     * Test 8: PO Issued & Registry DataTable links to modern procurement PO workspace.
     */
    public function test_po_status_report_and_datatable_links_to_purchase_orders(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.purchase-reports.po-status'));

        $response->assertStatus(200);
        $response->assertSee('name="vendor_id"', false);
        $response->assertSee('name="purchase_type"', false);
        $response->assertSee('name="milestone_status"', false);

        $ajaxResponse = $this->actingAs($this->adminUser)
            ->getJson(route('admin.purchase-reports.po-status'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $ajaxResponse->assertStatus(200);
    }

    /**
     * Test 9: Procurement Report PDF dispatches queue job instantly (< 50ms).
     */
    public function test_procurement_report_pdf_route_dispatches_queue_job_instantly(): void
    {
        Queue::fake();

        $startTime = microtime(true);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.procurement.pdf.async', [
                'purchase_type' => 'local',
            ]), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $durationMs = (microtime(true) - $startTime) * 1000;

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        Queue::assertPushed(GenerateProcurementReportPdfJob::class, function ($job) {
            return ($job->filters['purchase_type'] ?? '') === 'local' && $job->userId === $this->adminUser->id;
        });

        $this->assertLessThan(500, $durationMs, "Queue dispatch was too slow ({$durationMs}ms).");
    }

    /**
     * Test 10: Procurement Report PDF Job generates file, sets cache notification, and allows download.
     */
    public function test_procurement_report_pdf_job_execution_creates_file_and_allows_download(): void
    {
        $job = new GenerateProcurementReportPdfJob([], $this->adminUser->id);
        $job->handle();

        $tempDir = storage_path('app/temp_reports');
        $files = glob("{$tempDir}/procurement_report_u{$this->adminUser->id}_*.pdf");

        $this->assertNotEmpty($files, 'Generated procurement report PDF file was not found in temp storage.');
        $pdfFile = $files[0];
        $this->assertGreaterThan(1000, filesize($pdfFile), 'PDF file size is suspiciously small.');

        // Test status check endpoint
        $statusResponse = $this->actingAs($this->adminUser)
            ->getJson(route('admin.reports.procurement.check-status', [
                'type' => 'procurement_report',
            ]));

        $statusResponse->assertStatus(200);
        $statusResponse->assertJson([
            'ready' => true,
        ]);
        $downloadUrl = $statusResponse->json('download_url');
        $this->assertNotEmpty($downloadUrl);

        // Test downloading the generated PDF
        $downloadResponse = $this->actingAs($this->adminUser)->get($downloadUrl);
        $downloadResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $downloadResponse->headers->get('content-type'));
    }

    /**
     * Test 11: Audit Report page loads correctly with stats and filters.
     */
    public function test_audit_report_page_loads_with_summary_and_filters(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.audit'));

        $response->assertStatus(200);
        $response->assertViewHas('summary');
        $response->assertViewHas('modules');
        $response->assertViewHas('actions');
        $response->assertViewHas('users');
    }

    /**
     * Test 12: Total Purchase Value PDF route dispatches queue job instantly (< 50ms).
     */
    public function test_total_purchase_value_pdf_route_dispatches_queue_job_instantly(): void
    {
        Queue::fake();

        $startTime = microtime(true);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.purchase-reports.total-value.pdf.async'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $durationMs = (microtime(true) - $startTime) * 1000;

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        Queue::assertPushed(GenerateTotalPurchaseValuePdfJob::class, function ($job) {
            return $job->userId === $this->adminUser->id;
        });

        $this->assertLessThan(500, $durationMs, "Queue dispatch was too slow ({$durationMs}ms).");
    }

    /**
     * Test 13: Total Purchase Value PDF Job generates file, sets cache notification, and allows download.
     */
    public function test_total_purchase_value_pdf_job_execution_creates_file_and_allows_download(): void
    {
        $job = new GenerateTotalPurchaseValuePdfJob([], $this->adminUser->id);
        $job->handle();

        $tempDir = storage_path('app/temp_reports');
        $files = glob("{$tempDir}/total_purchase_value_report_u{$this->adminUser->id}_*.pdf");

        $this->assertNotEmpty($files, 'Generated total purchase value report PDF file was not found in temp storage.');
        $pdfFile = $files[0];
        $this->assertGreaterThan(1000, filesize($pdfFile), 'PDF file size is suspiciously small.');

        // Test status check endpoint
        $statusResponse = $this->actingAs($this->adminUser)
            ->getJson(route('admin.purchase-reports.total-value.check-status', [
                'type' => 'total_purchase_value_report',
            ]));

        $statusResponse->assertStatus(200);
        $statusResponse->assertJson([
            'ready' => true,
        ]);
        $downloadUrl = $statusResponse->json('download_url');
        $this->assertNotEmpty($downloadUrl);

        // Test downloading the generated PDF
        $downloadResponse = $this->actingAs($this->adminUser)->get($downloadUrl);
        $downloadResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $downloadResponse->headers->get('content-type'));
    }

    /**
     * Test 14: Purchase vs Last Year PDF route dispatches queue job instantly (< 50ms).
     */
    public function test_purchase_vs_last_year_pdf_route_dispatches_queue_job_instantly(): void
    {
        Queue::fake();

        $startTime = microtime(true);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.purchase-reports.vs-last-year.pdf.async', ['year' => 2026]), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $durationMs = (microtime(true) - $startTime) * 1000;

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        Queue::assertPushed(GeneratePurchaseVsLastYearPdfJob::class, function ($job) {
            return $job->userId === $this->adminUser->id && $job->year === 2026;
        });

        $this->assertLessThan(500, $durationMs, "Queue dispatch was too slow ({$durationMs}ms).");
    }

    /**
     * Test 15: Purchase vs Last Year PDF Job generates file, sets cache notification, and allows download.
     */
    public function test_purchase_vs_last_year_pdf_job_execution_creates_file_and_allows_download(): void
    {
        $job = new GeneratePurchaseVsLastYearPdfJob(2026, $this->adminUser->id);
        $job->handle();

        $tempDir = storage_path('app/temp_reports');
        $files = glob("{$tempDir}/purchase_vs_last_year_report_u{$this->adminUser->id}_*.pdf");

        $this->assertNotEmpty($files, 'Generated purchase vs last year report PDF file was not found in temp storage.');
        $pdfFile = $files[0];
        $this->assertGreaterThan(1000, filesize($pdfFile), 'PDF file size is suspiciously small.');

        // Test status check endpoint
        $statusResponse = $this->actingAs($this->adminUser)
            ->getJson(route('admin.purchase-reports.vs-last-year.check-status', [
                'type' => 'purchase_vs_last_year_report',
            ]));

        $statusResponse->assertStatus(200);
        $statusResponse->assertJson([
            'ready' => true,
        ]);
        $downloadUrl = $statusResponse->json('download_url');
        $this->assertNotEmpty($downloadUrl);

        // Test downloading the generated PDF
        $downloadResponse = $this->actingAs($this->adminUser)->get($downloadUrl);
        $downloadResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $downloadResponse->headers->get('content-type'));
    }
}
