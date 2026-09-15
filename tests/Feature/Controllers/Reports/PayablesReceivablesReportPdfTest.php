<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Reports;

use App\Jobs\GeneratePayablesReceivablesReportPdfJob;
use App\Models\Purchase;
use App\Models\Vendor;
use App\Models\VendorBill;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Controllers\ControllerTestCase;

class PayablesReceivablesReportPdfTest extends ControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('user_pdf_notifications_' . $this->adminUser->id);
    }

    public function test_customer_ar_aging_pdf_route_dispatches_queue_job_instantly(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.ar-aging.pdf', [
                'customer_id' => 1,
            ]));

        $response->assertRedirect();
        Queue::assertPushed(GeneratePayablesReceivablesReportPdfJob::class, function ($job) {
            return $job->reportType === 'ar_aging'
                && $job->userId === $this->adminUser->id
                && (int) ($job->filters['customer_id'] ?? 0) === 1;
        });
    }

    public function test_customer_payment_ledger_pdf_route_dispatches_queue_job_instantly(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.accounts.payments.pdf', [
                'method' => 'cash',
            ]));

        $response->assertRedirect();
        Queue::assertPushed(GeneratePayablesReceivablesReportPdfJob::class, function ($job) {
            return $job->reportType === 'customer_ledger'
                && $job->userId === $this->adminUser->id
                && ($job->filters['method'] ?? '') === 'cash';
        });
    }

    public function test_vendor_payment_ledger_pdf_route_dispatches_queue_job_instantly(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.accounts.vendor-payments.pdf', [
                'vendor_id' => 1,
            ]));

        $response->assertRedirect();
        Queue::assertPushed(GeneratePayablesReceivablesReportPdfJob::class, function ($job) {
            return $job->reportType === 'vendor_payments'
                && $job->userId === $this->adminUser->id
                && (int) ($job->filters['vendor_id'] ?? 0) === 1;
        });
    }

    public function test_ap_vendor_aging_pdf_route_dispatches_queue_job_instantly(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.vendor-ledger.aging.pdf'));

        $response->assertRedirect();
        Queue::assertPushed(GeneratePayablesReceivablesReportPdfJob::class, function ($job) {
            return $job->reportType === 'ap_aging'
                && $job->userId === $this->adminUser->id;
        });
    }

    public function test_supplier_statement_pdf_route_dispatches_queue_job_instantly(): void
    {
        Queue::fake();

        $vendor = Vendor::firstOrCreate(['shop_name' => 'Nordic Parts Supply A/S'], [
            'address' => 'Industrivej 12, 2600 Glostrup',
            'country' => 'Denmark',
            'phone'   => '+45 88990011',
            'email'   => 'sales@nordicparts.test',
            'status'  => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.vendor-ledger.pdf', [
                'vendor_id' => $vendor->id,
                'from_date' => '2026-01-01',
                'to_date'   => '2026-12-31',
            ]));

        $response->assertRedirect();
        Queue::assertPushed(GeneratePayablesReceivablesReportPdfJob::class, function ($job) use ($vendor) {
            return $job->reportType === 'supplier_statement'
                && $job->userId === $this->adminUser->id
                && (int) ($job->filters['vendor_id'] ?? 0) === $vendor->id;
        });
    }

    public function test_job_execution_generates_pdf_and_registers_bell_notification(): void
    {
        $job = new GeneratePayablesReceivablesReportPdfJob('ar_aging', [], $this->adminUser->id);
        $job->handle();

        // 1. Verify notification was added to cache
        $notifications = Cache::get('user_pdf_notifications_' . $this->adminUser->id, []);
        $this->assertNotEmpty($notifications);
        $this->assertEquals('pdf_ready', $notifications[0]['type']);
        $this->assertStringContainsString('AR Aging', $notifications[0]['title']);

        // 2. Extract generated filename from download URL
        $downloadUrl = $notifications[0]['url'];
        $parts = explode('/', $downloadUrl);
        $fileName = end($parts);

        // 3. Verify file exists on disk
        $filePath = storage_path('app/temp_reports/' . $fileName);
        $this->assertFileExists($filePath);

        // 4. Test download route serves the file as PDF
        $downloadResponse = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.payables-receivables.download', ['file' => $fileName]));

        $downloadResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string)$downloadResponse->headers->get('content-type'));
    }

    public function test_job_purges_previous_temporary_file_for_same_user_and_report(): void
    {
        $tempDir = storage_path('app/temp_reports');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $userPattern = $tempDir . "/ar_aging_u{$this->adminUser->id}_*.pdf";

        // First job execution
        $job1 = new GeneratePayablesReceivablesReportPdfJob('ar_aging', [], $this->adminUser->id);
        $job1->handle();

        $initialFiles = glob($userPattern);
        $this->assertCount(1, $initialFiles, 'Should have exactly 1 ephemeral file after first job run');

        sleep(1);

        // Second job execution
        $job2 = new GeneratePayablesReceivablesReportPdfJob('ar_aging', [], $this->adminUser->id);
        $job2->handle();

        $subsequentFiles = glob($userPattern);
        $this->assertCount(1, $subsequentFiles, 'Previous file must be purged, keeping only 1 latest file');
        $this->assertNotEquals($initialFiles[0], $subsequentFiles[0], 'Filename must be updated with fresh timestamp');
    }

    public function test_download_missing_report_redirects_with_error(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.payables-receivables.download', ['file' => 'non_existent_payables.pdf']));

        $response->assertRedirect();
    }

    public function test_ajax_dispatch_returns_json_with_dispatched_at(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.reports.ar-aging.pdf'));

        $response->assertOk()
            ->assertJson([
                'status' => 'dispatched',
            ])
            ->assertJsonStructure(['status', 'message', 'dispatched_at']);
    }

    public function test_check_report_status_returns_ready_with_download_url_when_file_exists(): void
    {
        $job = new GeneratePayablesReceivablesReportPdfJob('ar_aging', [], $this->adminUser->id);
        $job->handle();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.payables-receivables.check-status', ['type' => 'ar_aging']));

        $response->assertOk()
            ->assertJson(['ready' => true])
            ->assertJsonStructure(['ready', 'download_url', 'filename', 'time', 'date', 'timestamp']);
    }

    public function test_check_report_status_respects_after_timestamp_filter(): void
    {
        $job = new GeneratePayablesReceivablesReportPdfJob('ap_aging', [], $this->adminUser->id);
        $job->handle();

        // Querying with an "after" timestamp in the future should return ready: false
        $futureTimestamp = time() + 3600;
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.payables-receivables.check-status', [
                'type'  => 'ap_aging',
                'after' => $futureTimestamp,
            ]));

        $response->assertOk()
            ->assertJson(['ready' => false]);
    }

    public function test_check_report_status_supports_dynamic_supplier_statement_prefix(): void
    {
        $vendor = Vendor::firstOrCreate(['shop_name' => 'Statement Test Vendor A/S'], [
            'address' => 'Havnegade 44, 1058 Copenhagen',
            'country' => 'Denmark',
            'phone'   => '+45 55667788',
            'email'   => 'contact@statementvendor.test',
            'status'  => 1,
        ]);

        $job = new GeneratePayablesReceivablesReportPdfJob('supplier_statement', [
            'vendor_id' => $vendor->id,
        ], $this->adminUser->id);
        $job->handle();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.payables-receivables.check-status', [
                'type' => "supplier_statement_{$vendor->id}",
            ]));

        $response->assertOk()
            ->assertJson(['ready' => true])
            ->assertJsonStructure(['ready', 'download_url', 'filename', 'time', 'date', 'timestamp']);
    }

    public function test_report_views_render_successfully_with_on_page_action_buttons(): void
    {
        $job = new GeneratePayablesReceivablesReportPdfJob('ar_aging', [], $this->adminUser->id);
        $job->handle();

        // 1. Customer AR Aging view
        $arResponse = $this->actingAs($this->adminUser)->get(route('admin.reports.ar-aging'));
        $arResponse->assertOk();
        $arResponse->assertSee('id="btn-download-pdf"', false);
        $arResponse->assertSee('id="btn-generate-pdf"', false);
        $arResponse->assertSee('Download PDF (Ready:', false);

        // 2. AP Vendor Aging view
        $apResponse = $this->actingAs($this->adminUser)->get(route('admin.vendor-ledger.aging'));
        $apResponse->assertOk();
        $apResponse->assertSee('id="btn-generate-pdf"', false);

        // 3. Customer Ledger (Payments) view
        $paymentsResponse = $this->actingAs($this->adminUser)->get(route('admin.accounts.index'));
        $paymentsResponse->assertOk();
        $paymentsResponse->assertSee('id="btn-generate-pdf"', false);

        // 4. Vendor Payments view
        $vendorPaymentsResponse = $this->actingAs($this->adminUser)->get(route('admin.accounts.vendor-payments.index'));
        $vendorPaymentsResponse->assertOk();
        $vendorPaymentsResponse->assertSee('id="btn-generate-pdf"', false);
    }

    public function test_pdf_templates_contain_no_cvr_vat_number_or_dummy_data(): void
    {
        $views = [
            'backend.pdf.ar_aging',
            'backend.accounts.payment_history_pdf',
            'backend.accounts.vendor_payment_history_pdf',
            'backend.vendor_ledger.statement_pdf',
            'backend.vendor_ledger.aging_pdf',
        ];

        foreach ($views as $viewName) {
            $path = resource_path('views/' . str_replace('.', '/', $viewName) . '.blade.php');
            $content = file_get_contents($path);

            $this->assertStringNotContainsString('CVR / VAT No', $content, "View {$viewName} must not contain CVR / VAT No");
            $this->assertStringNotContainsString('DK12345678', $content, "View {$viewName} must not contain dummy DK12345678");
            $this->assertStringNotContainsString('DK-99238419', $content, "View {$viewName} must not contain dummy DK-99238419");
            $this->assertStringNotContainsString('finance@b2bviking.com', $content, "View {$viewName} must not contain dummy email");
        }
    }

    public function test_vendor_payments_datatable_ajax_returns_valid_json_response(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.accounts.vendor-payments.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk()
            ->assertJsonStructure(['data', 'draw', 'recordsTotal', 'recordsFiltered']);
    }

    public function test_vendor_ledger_index_renders_with_kpi_cards_and_yajra_datatable(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.vendor-ledger.index'));

        $response->assertOk();
        $response->assertSee('Total Outstanding Payables');
        $response->assertSee('Suppliers with Dues');
        $response->assertSee('Fully Settled Suppliers');
        $response->assertSee('id="balance_filter"', false);
        $response->assertSee('vendor-ledger-table');
    }

    public function test_vendor_ledger_datatable_ajax_returns_valid_json_with_supplier_code(): void
    {
        $vendor = $this->createVendor([
            'shop_name' => 'Nordic Test Supplier',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.vendor-ledger.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk()
            ->assertJsonStructure(['data', 'draw', 'recordsTotal', 'recordsFiltered']);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertStringContainsString('V-', $data[0]['code']);
    }

    public function test_vendor_ledger_datatable_ajax_filters_by_balance_status(): void
    {
        $vendorWithDue = $this->createVendor([
            'shop_name' => 'Debtor Supplier',
        ]);

        $vendorSettled = $this->createVendor([
            'shop_name' => 'Settled Supplier',
        ]);

        $purchase = $this->createPurchase($vendorWithDue->id);

        VendorBill::create([
            'bill_no'         => 'BILL-TEST-999',
            'purchase_id'     => $purchase->id,
            'vendor_id'       => $vendorWithDue->id,
            'bill_date'       => now(),
            'due_date'        => now()->subDays(5),
            'subtotal'        => 500,
            'grand_total'     => 500,
            'paid_amount'     => 0,
            'due_amount'      => 500,
            'payment_status'  => 'unpaid',
            'approval_status' => 'approved',
        ]);

        // 1. Has Due filter
        $responseDue = $this->actingAs($this->adminUser)
            ->getJson(route('admin.vendor-ledger.index', ['balance_filter' => 'has_due']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);
        $responseDue->assertOk();
        $totalDueCount = $responseDue->json('recordsTotal');

        // 2. Settled filter
        $responseSettled = $this->actingAs($this->adminUser)
            ->getJson(route('admin.vendor-ledger.index', ['balance_filter' => 'settled']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);
        $responseSettled->assertOk();
        $totalSettledCount = $responseSettled->json('recordsTotal');

        $this->assertGreaterThan(0, $totalDueCount);
        $this->assertGreaterThan(0, $totalSettledCount);
    }

    public function test_ap_vendor_aging_displays_valid_supplier_codes(): void
    {
        $vendor = $this->createVendor([
            'shop_name' => 'Aging Supplier Test',
        ]);

        $purchase = $this->createPurchase($vendor->id);

        VendorBill::create([
            'bill_no'         => 'BILL-AGING-001',
            'purchase_id'     => $purchase->id,
            'vendor_id'       => $vendor->id,
            'bill_date'       => now()->subDays(45),
            'due_date'        => now()->subDays(15),
            'subtotal'        => 1200,
            'grand_total'     => 1200,
            'paid_amount'     => 0,
            'due_amount'      => 1200,
            'payment_status'  => 'unpaid',
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.vendor-ledger.aging'));

        $response->assertOk();
        $response->assertSee('badge-dark font-monospace', false);
        $response->assertSee('V-');
    }

    private function createVendor(array $attributes = []): Vendor
    {
        return Vendor::create(array_merge([
            'shop_name'   => 'Nordic Test Supplier',
            'phone'       => '12345678',
            'email'       => 'supplier@nordic.com',
            'address'     => 'Copenhagen, Denmark',
            'country'     => 'Denmark',
            'currency_id' => null,
            'status'      => 1,
        ], $attributes));
    }

    private function createPurchase(int $vendorId): Purchase
    {
        return Purchase::create([
            'po_no'        => 'PO-' . uniqid(),
            'invoice_no'   => 'INV-' . uniqid(),
            'vendor_id'    => $vendorId,
            'user_id'      => $this->adminUser->id,
            'date'         => now(),
            'total_amount' => 1000,
            'status'       => 1,
        ]);
    }
}

