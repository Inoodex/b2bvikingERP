<?php

namespace App\Jobs;

use App\Models\Rfq;
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

class GenerateRfqPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $rfqId;
    public $userId;
    public $timeout = 3600;

    public function __construct($rfqId, $userId = null)
    {
        $this->rfqId = $rfqId;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $rfq = Rfq::with(['items.product.unit', 'items.variant', 'vendors.vendor'])->find($this->rfqId);
        if (!$rfq) {
            return;
        }

        $settings = GeneralSetting::first();
        if ($settings && $settings->site_logo) {
            $settings->optimized_logo = PdfImageHelper::optimize($settings->site_logo, 480, 120, 95);
        }

        foreach ($rfq->items as $item) {
            if ($item->product && $item->product->thumb_image) {
                $item->product->optimized_image = PdfImageHelper::optimize($item->product->thumb_image, 400, 400, 95);
            }
        }

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
        ])->loadView('backend.rfq.pdf', compact('rfq', 'settings'));

        $path = 'rfqs/rfq_' . $rfq->id . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        if ($this->userId) {
            $this->addCacheNotification($this->userId, [
                'type' => 'pdf_ready',
                'title' => 'RFQ PDF Ready',
                'desc' => "PDF for RFQ #{$rfq->rfq_no} is generated.",
                'url' => route('admin.rfqs.pdf.view', $rfq->id),
                'icon' => 'fas fa-file-pdf',
                'class' => 'bg-info',
                'timestamp' => now()->timestamp,
            ]);
        }
    }

    private function addCacheNotification($userId, $data)
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
