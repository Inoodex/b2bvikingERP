<?php

namespace App\Jobs;

use App\Models\GeneralSetting;
use App\Models\Purchase;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class GenerateProcurementReportPdfJob implements ShouldQueue
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

        $query = Purchase::with(['vendor.currency', 'user', 'details'])
            ->where('status', 1);

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('date', '>=', $this->filters['start_date']);
        }
        if (!empty($this->filters['end_date'])) {
            $query->whereDate('date', '<=', $this->filters['end_date']);
        }
        if (!empty($this->filters['vendor_id'])) {
            $query->where('vendor_id', $this->filters['vendor_id']);
        }
        if (!empty($this->filters['purchase_type'])) {
            $query->where('purchase_type', $this->filters['purchase_type']);
        }
        if (!empty($this->filters['milestone_status'])) {
            $query->where('milestone_status', $this->filters['milestone_status']);
        }

        $purchases = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        $summary = [
            'total_pos' => $purchases->count(),
            'total_spend' => (float) $purchases->sum('total_amount'),
            'total_paid' => (float) $purchases->sum('paid_amount'),
            'total_due' => (float) $purchases->sum('due_amount'),
            'local_spend' => (float) $purchases->where('purchase_type', 'local')->sum('total_amount'),
            'foreign_spend' => (float) $purchases->where('purchase_type', 'foreign')->sum('total_amount'),
            'goods_received_count' => $purchases->where('milestone_status', 'goods_received')->count(),
            'pending_milestone_count' => $purchases->where('milestone_status', '!=', 'goods_received')->count(),
        ];

        $settings = GeneralSetting::first();
        $currencyIcon = $settings->currency_icon ?? 'Kr.';

        $selectedVendor = null;
        if (!empty($this->filters['vendor_id'])) {
            $selectedVendor = Vendor::with('currency')->find($this->filters['vendor_id']);
        }

        $data = [
            'purchases' => $purchases,
            'summary' => $summary,
            'settings' => $settings,
            'currencyIcon' => $currencyIcon,
            'filters' => $this->filters,
            'selectedVendor' => $selectedVendor,
        ];

        $pdf = Pdf::loadView('backend.reports.pdf.procurement_report_pdf', $data)
            ->setPaper('a4', 'landscape');

        $tempDir = storage_path('app/temp_reports');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Pre-generation purge: delete previous procurement_report PDFs for this user
        $pattern = $tempDir . "/procurement_report_u{$this->userId}_*.pdf";
        foreach (glob($pattern) as $oldFile) {
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        $timestamp = now()->format('Ymd_His');
        $filename = "procurement_report_u{$this->userId}_{$timestamp}.pdf";
        $filePath = "{$tempDir}/{$filename}";
        $pdf->save($filePath);

        $filterLabel = '';
        if (!empty($this->filters['start_date']) || !empty($this->filters['end_date'])) {
            $filterLabel .= ($this->filters['start_date'] ?? 'Start') . '→' . ($this->filters['end_date'] ?? 'End');
        }
        if (!empty($this->filters['purchase_type'])) {
            $filterLabel .= ' ' . ucfirst($this->filters['purchase_type']);
        }
        if (!empty($this->filters['milestone_status'])) {
            $filterLabel .= ' ' . ucfirst($this->filters['milestone_status']);
        }
        if ($selectedVendor) {
            $filterLabel .= ' ' . ($selectedVendor->shop_name ?? $selectedVendor->name);
        }
        $filterLabel = trim($filterLabel) ?: 'All Records';

        $this->addCacheNotification($this->userId, [
            'type' => 'pdf_ready',
            'title' => 'Procurement Report Ready',
            'desc' => "Procurement Report ({$filterLabel}) is ready.",
            'url' => route('admin.reports.procurement.pdf.download', ['file' => $filename]),
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
