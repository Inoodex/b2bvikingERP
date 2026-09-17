<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\GeneralSetting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class GenerateAuditReportPdfJob implements ShouldQueue
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

        $query = AuditLog::with(['user', 'vendor'])->latest();

        if (!empty($this->filters['module'])) {
            $query->where('module', $this->filters['module']);
        }

        if (!empty($this->filters['action'])) {
            $query->where('action', $this->filters['action']);
        }

        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', (int) $this->filters['user_id']);
        }

        if (!empty($this->filters['vendor_id'])) {
            $query->where('vendor_id', (int) $this->filters['vendor_id']);
        }

        if (!empty($this->filters['reference'])) {
            $reference = trim((string) $this->filters['reference']);
            $query->where('reference_no', 'like', '%' . $reference . '%');
        }

        if (!empty($this->filters['start_date'])) {
            $query->where('created_at', '>=', Carbon::parse($this->filters['start_date'])->startOfDay());
        }

        if (!empty($this->filters['end_date'])) {
            $query->where('created_at', '<=', Carbon::parse($this->filters['end_date'])->endOfDay());
        }

        if (!empty($this->filters['critical_only'])) {
            $query->where(function ($q) {
                $q->where('action', 'like', '%delete%')
                  ->orWhere('action', 'like', '%void%')
                  ->orWhere('action', 'like', '%cancel%');
            });
        }

        // Fetch up to 1000 records for PDF to ensure high performance
        $logs = $query->limit(1000)->get();

        $summary = [
            'total_logs'     => $logs->count(),
            'modules_count'  => $logs->pluck('module')->unique()->count(),
            'staff_count'    => $logs->pluck('user_id')->filter()->unique()->count(),
            'critical_count' => $logs->filter(function ($log) {
                $act = strtolower($log->action ?? '');
                return str_contains($act, 'delete') || str_contains($act, 'void') || str_contains($act, 'cancel');
            })->count(),
        ];

        $settings = GeneralSetting::first();

        $selectedUser = null;
        if (!empty($this->filters['user_id'])) {
            $selectedUser = User::find($this->filters['user_id']);
        }

        $data = [
            'logs'         => $logs,
            'summary'      => $summary,
            'settings'     => $settings,
            'filters'      => $this->filters,
            'selectedUser' => $selectedUser,
        ];

        $pdf = Pdf::loadView('backend.reports.pdf.audit_report_pdf', $data)
            ->setPaper('a4', 'landscape');

        $tempDir = storage_path('app/temp_reports');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Pre-generation purge: delete previous audit_report PDFs for this user
        $pattern = $tempDir . "/audit_report_u{$this->userId}_*.pdf";
        foreach (glob($pattern) as $oldFile) {
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        $timestamp = now()->format('Ymd_His');
        $filename = "audit_report_u{$this->userId}_{$timestamp}.pdf";
        $filePath = "{$tempDir}/{$filename}";
        $pdf->save($filePath);

        $filterLabel = '';
        if (!empty($this->filters['start_date']) || !empty($this->filters['end_date'])) {
            $filterLabel .= ($this->filters['start_date'] ?? 'Start') . '→' . ($this->filters['end_date'] ?? 'End');
        }
        if (!empty($this->filters['module'])) {
            $filterLabel .= ' ' . ucfirst($this->filters['module']);
        }
        if ($selectedUser) {
            $filterLabel .= ' ' . $selectedUser->name;
        }
        $filterLabel = trim($filterLabel) ?: 'All Records';

        $this->addCacheNotification($this->userId, [
            'type'      => 'pdf_ready',
            'title'     => 'Audit Report Ready',
            'desc'      => "Audit Log Report ({$filterLabel}) is ready.",
            'url'       => route('admin.reports.audit.pdf.download', ['file' => $filename]),
            'icon'      => 'fas fa-clipboard-check',
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
