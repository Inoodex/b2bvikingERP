<?php

namespace App\Jobs;

use App\Models\GeneralSetting;
use App\Models\Vendor;
use App\Services\PurchaseReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class GenerateTotalPurchaseValuePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $filters;
    public int $userId;
    public int $timeout = 3600;

    public function __construct(array $filters, int $userId)
    {
        $this->filters = $filters;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        /** @var PurchaseReportService $reportService */
        $reportService = app(PurchaseReportService::class);
        $result = $reportService->getTotalPurchaseValue($this->filters);
        $summary = $result['summary'];
        $reportData = $result['reportData'];

        $settings = GeneralSetting::first();
        $currencyIcon = $settings->currency_icon ?? 'Kr.';

        $selectedVendor = null;
        if (!empty($this->filters['vendor_id'])) {
            $selectedVendor = Vendor::with('currency')->find($this->filters['vendor_id']);
        }

        $data = [
            'summary' => $summary,
            'reportData' => $reportData,
            'settings' => $settings,
            'currencyIcon' => $currencyIcon,
            'filters' => $this->filters,
            'selectedVendor' => $selectedVendor,
        ];

        $pdf = Pdf::loadView('backend.purchase_report.pdf.total_value_pdf', $data)
            ->setPaper('a4', 'landscape');

        $tempDir = storage_path('app/temp_reports');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Pre-generation purge: delete previous total_purchase_value_report PDFs for this user
        $pattern = $tempDir . "/total_purchase_value_report_u{$this->userId}_*.pdf";
        foreach (glob($pattern) as $oldFile) {
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        $timestamp = now()->format('Ymd_His');
        $filename = "total_purchase_value_report_u{$this->userId}_{$timestamp}.pdf";
        $filePath = "{$tempDir}/{$filename}";
        $pdf->save($filePath);

        $filterLabel = '';
        if (!empty($this->filters['start_date']) || !empty($this->filters['end_date'])) {
            $filterLabel .= ($this->filters['start_date'] ?? 'Start') . ' → ' . ($this->filters['end_date'] ?? 'End');
        }
        if ($selectedVendor) {
            $filterLabel .= ' ' . ($selectedVendor->shop_name ?? $selectedVendor->name);
        }
        $filterLabel = trim($filterLabel) ?: 'All Periods';

        $this->addCacheNotification($this->userId, [
            'type' => 'pdf_ready',
            'title' => 'Total Purchase Value Report Ready',
            'desc' => "Total Purchase Value Report ({$filterLabel}) is ready.",
            'url' => route('admin.purchase-reports.total-value.pdf.download', ['file' => $filename]),
            'icon' => 'fas fa-file-pdf',
            'class' => 'bg-success',
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
