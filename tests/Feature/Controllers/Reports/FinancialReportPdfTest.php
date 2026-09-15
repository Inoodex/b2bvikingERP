<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Reports;

use App\Jobs\GenerateFinancialReportPdfJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Controllers\ControllerTestCase;

class FinancialReportPdfTest extends ControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('user_pdf_notifications_' . $this->adminUser->id);
    }

    public function test_general_ledger_pdf_route_dispatches_queue_job_instantly(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.general-ledger.pdf', [
                'date_from' => '2026-01-01',
                'date_to'   => '2026-12-31',
            ]));

        $response->assertRedirect();
        Queue::assertPushed(GenerateFinancialReportPdfJob::class, function ($job) {
            return $job->reportType === 'general_ledger'
                && $job->userId === $this->adminUser->id
                && $job->filters['date_from'] === '2026-01-01';
        });
    }

    public function test_trial_balance_pdf_route_dispatches_queue_job_instantly(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.trial-balance.pdf', [
                'date_from' => '2026-01-01',
                'date_to'   => '2026-12-31',
            ]));

        $response->assertRedirect();
        Queue::assertPushed(GenerateFinancialReportPdfJob::class, function ($job) {
            return $job->reportType === 'trial_balance'
                && $job->userId === $this->adminUser->id;
        });
    }

    public function test_profit_and_loss_pdf_route_dispatches_queue_job_instantly(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.profit-loss.pdf', [
                'date_from' => '2026-01-01',
                'date_to'   => '2026-12-31',
            ]));

        $response->assertRedirect();
        Queue::assertPushed(GenerateFinancialReportPdfJob::class, function ($job) {
            return $job->reportType === 'profit_loss'
                && $job->userId === $this->adminUser->id;
        });
    }

    public function test_balance_sheet_pdf_route_dispatches_queue_job_instantly(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.balance-sheet.pdf', [
                'as_of_date' => '2026-09-15',
            ]));

        $response->assertRedirect();
        Queue::assertPushed(GenerateFinancialReportPdfJob::class, function ($job) {
            return $job->reportType === 'balance_sheet'
                && $job->userId === $this->adminUser->id
                && $job->filters['as_of_date'] === '2026-09-15';
        });
    }

    public function test_job_execution_generates_pdf_and_registers_bell_notification(): void
    {
        $job = new GenerateFinancialReportPdfJob('profit_loss', [
            'date_from' => '2026-01-01',
            'date_to'   => '2026-12-31',
        ], $this->adminUser->id);

        $job->handle();

        // 1. Verify notification was added to cache
        $notifications = Cache::get('user_pdf_notifications_' . $this->adminUser->id, []);
        $this->assertNotEmpty($notifications);
        $this->assertEquals('pdf_ready', $notifications[0]['type']);
        $this->assertStringContainsString('Profit & Loss', $notifications[0]['title']);

        // 2. Extract generated filename from download URL
        $downloadUrl = $notifications[0]['url'];
        $parts = explode('/', $downloadUrl);
        $fileName = end($parts);

        // 3. Verify file exists on disk
        $filePath = storage_path('app/temp_reports/' . $fileName);
        $this->assertFileExists($filePath);

        // 4. Test download route serves the file as PDF
        $downloadResponse = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.financial.download', ['file' => $fileName]));

        $downloadResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string)$downloadResponse->headers->get('content-type'));
    }

    public function test_job_purges_previous_temporary_file_for_same_user_and_report(): void
    {
        $tempDir = storage_path('app/temp_reports');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $userPattern = $tempDir . "/profit_loss_u{$this->adminUser->id}_*.pdf";

        // First job execution
        $job1 = new GenerateFinancialReportPdfJob('profit_loss', [], $this->adminUser->id);
        $job1->handle();

        $initialFiles = glob($userPattern);
        $this->assertCount(1, $initialFiles, 'Should have exactly 1 ephemeral file after first job run');

        sleep(1);

        // Second job execution
        $job2 = new GenerateFinancialReportPdfJob('profit_loss', [], $this->adminUser->id);
        $job2->handle();

        $subsequentFiles = glob($userPattern);
        $this->assertCount(1, $subsequentFiles, 'Previous file must be purged, keeping only 1 latest file');
        $this->assertNotEquals($initialFiles[0], $subsequentFiles[0], 'Filename must be updated with fresh timestamp');
    }

    public function test_download_missing_report_redirects_with_error(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.financial.download', ['file' => 'non_existent_statement.pdf']));

        $response->assertRedirect();
    }

    public function test_ajax_dispatch_returns_json_with_dispatched_at(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.reports.general-ledger.pdf'));

        $response->assertOk()
            ->assertJson([
                'status' => 'dispatched',
            ])
            ->assertJsonStructure(['status', 'message', 'dispatched_at']);
    }

    public function test_check_report_status_returns_ready_with_download_url_when_file_exists(): void
    {
        $job = new GenerateFinancialReportPdfJob('general_ledger', [], $this->adminUser->id);
        $job->handle();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.financial.check-status', ['type' => 'general_ledger']));

        $response->assertOk()
            ->assertJson(['ready' => true])
            ->assertJsonStructure(['ready', 'download_url', 'filename', 'time', 'date', 'timestamp']);
    }

    public function test_check_report_status_respects_after_timestamp_filter(): void
    {
        $job = new GenerateFinancialReportPdfJob('trial_balance', [], $this->adminUser->id);
        $job->handle();

        // Querying with an "after" timestamp in the future should return ready: false
        $futureTimestamp = time() + 3600;
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.financial.check-status', [
                'type'  => 'trial_balance',
                'after' => $futureTimestamp,
            ]));

        $response->assertOk()
            ->assertJson(['ready' => false]);
    }

    public function test_report_views_render_successfully_with_on_page_action_buttons(): void
    {
        $job = new GenerateFinancialReportPdfJob('profit_loss', [], $this->adminUser->id);
        $job->handle();

        // 1. Profit & Loss view should show ready download button
        $pnlResponse = $this->actingAs($this->adminUser)->get(route('admin.reports.profit-loss'));
        $pnlResponse->assertOk();
        $pnlResponse->assertSee('id="btn-download-pdf"', false);
        $pnlResponse->assertSee('id="btn-generate-pdf"', false);
        $pnlResponse->assertSee('Download PDF (Ready:', false);

        // 2. Trial Balance view
        $tbResponse = $this->actingAs($this->adminUser)->get(route('admin.reports.trial-balance'));
        $tbResponse->assertOk();
        $tbResponse->assertSee('id="btn-generate-pdf"', false);

        // 3. Balance Sheet view
        $bsResponse = $this->actingAs($this->adminUser)->get(route('admin.reports.balance-sheet'));
        $bsResponse->assertOk();
        $bsResponse->assertSee('id="btn-generate-pdf"', false);
    }

    public function test_pdf_templates_contain_no_cvr_vat_number_or_dummy_data(): void
    {
        $views = [
            'backend.reports.financial.pdf.general_ledger_pdf',
            'backend.reports.financial.pdf.trial_balance_pdf',
            'backend.reports.financial.pdf.profit_loss_pdf',
            'backend.reports.financial.pdf.balance_sheet_pdf',
        ];

        foreach ($views as $viewName) {
            $path = resource_path('views/' . str_replace('.', '/', $viewName) . '.blade.php');
            $content = file_get_contents($path);

            $this->assertStringNotContainsString('CVR / VAT No', $content, "View {$viewName} must not contain CVR / VAT No");
            $this->assertStringNotContainsString('DK12345678', $content, "View {$viewName} must not contain dummy DK12345678");
            $this->assertStringNotContainsString('finance@b2bviking.com', $content, "View {$viewName} must not contain dummy email");
        }
    }
}
