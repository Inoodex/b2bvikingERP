<?php

namespace App\Jobs;

use App\Models\Purchase;
use App\Models\GeneralSetting;
use App\Support\PdfImageHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeneratePoPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $poId;
    public ?int $userId;
    public int $timeout = 3600;

    public function __construct(int $poId, ?int $userId = null)
    {
        $this->poId   = $poId;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $po = Purchase::with([
            'vendor',
            'currency',
            'items.product',
            'items.variant',
        ])->find($this->poId);

        if (!$po) {
            Log::warning("GeneratePoPdfJob: Purchase #{$this->poId} not found.");
            return;
        }

        // Load and optimize company logo (same as Phase 1)
        $settings = GeneralSetting::first();
        if ($settings && $settings->site_logo) {
            $settings->optimized_logo = PdfImageHelper::optimize($settings->site_logo, 480, 120, 95);
        }

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false,
            'defaultFont'          => 'sans-serif',
        ])->loadView('backend.purchase.po_pdf', compact('po', 'settings'));

        $path = 'pos/po_' . $po->id . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        Log::info("GeneratePoPdfJob: PO PDF cached at {$path}");

        // Notify user (same cache-based notification as Phase 1)
        if ($this->userId) {
            $this->addCacheNotification($this->userId, [
                'type'      => 'pdf_ready',
                'title'     => 'PO PDF Ready',
                'desc'      => 'PDF for PO #' . ($po->po_no ?? $po->id) . ' is generated and ready to download.',
                'url'       => route('admin.purchase-orders.pdf.download', $po->id),
                'icon'      => 'fas fa-file-pdf',
                'class'     => 'bg-success',
                'timestamp' => now()->timestamp,
            ]);
        }
    }

    private function addCacheNotification(int $userId, array $data): void
    {
        $key           = 'user_pdf_notifications_' . $userId;
        $notifications = Cache::get($key, []);
        $data['time']           = now()->diffForHumans();
        $data['is_unread']      = true;
        $data['is_out_of_stock'] = false;
        $notifications[]        = $data;
        $notifications          = array_slice($notifications, -20);
        Cache::put($key, $notifications, now()->addDays(7));
    }
}
