<?php

namespace App\Jobs;

use App\Models\GeneralSetting;
use App\Services\PurchaseReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class GeneratePurchaseVsLastYearPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $year;
    public ?int $benchmarkYear;
    public int $userId;
    public int $timeout = 3600;

    public function __construct(int $year, int $userId, ?int $benchmarkYear = null)
    {
        $this->year = $year;
        $this->userId = $userId;
        $this->benchmarkYear = $benchmarkYear;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        /** @var PurchaseReportService $reportService */
        $reportService = app(PurchaseReportService::class);
        $comparison = $reportService->getPurchaseVsLastYear($this->year, $this->benchmarkYear);

        $settings = GeneralSetting::first();
        $currencyIcon = $settings->currency_icon ?? 'Kr.';

        $data = [
            'comparison'   => $comparison,
            'year'         => $this->year,
            'benchmarkYear'=> $comparison['benchmark_year'] ?? ($this->year - 1),
            'settings'     => $settings,
            'currencyIcon' => $currencyIcon,
        ];

        $pdf = Pdf::loadView('backend.purchase_report.pdf.vs_last_year_pdf', $data)
            ->setPaper('a4', 'landscape');

        $tempDir = storage_path('app/temp_reports');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Pre-generation purge: delete previous purchase_vs_last_year_report PDFs for this user
        $pattern = $tempDir . "/purchase_vs_last_year_report_u{$this->userId}_*.pdf";
        foreach (glob($pattern) as $oldFile) {
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        $timestamp = now()->format('Ymd_His');
        $filename = "purchase_vs_last_year_report_u{$this->userId}_{$timestamp}.pdf";
        $filePath = "{$tempDir}/{$filename}";
        $pdf->save($filePath);

        $bYear = $comparison['benchmark_year'] ?? ($this->year - 1);
        $this->addCacheNotification($this->userId, [
            'type'      => 'pdf_ready',
            'title'     => 'Purchase Comparison Report Ready',
            'desc'      => "Purchase Comparison Report (Year {$this->year} vs Year {$bYear}) is ready.",
            'url'       => route('admin.purchase-reports.vs-last-year.pdf.download', ['file' => $filename]),
            'icon'      => 'fas fa-file-pdf',
            'class'     => 'bg-success',
            'timestamp' => now()->timestamp,
        ]);
    }

    private function addCacheNotification(int $userId, array $data): void
    {
        $key = 'user_pdf_notifications_' . $userId;
        $notifications = Cache::get($key, []);
        $data['time'] = now()->diffForHumans();
        $data['is_unread'] = true;
        $data['is_out_of_stock'] = false;
        $notifications[] = $data;
        $notifications = array_slice($notifications, -20);
        Cache::put($key, $notifications, now()->addDays(7));
    }
}
