<?php

namespace App\Jobs;

use App\Models\GeneralSetting;
use App\Models\Purchase;
use App\Support\PdfImageHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePurchasePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $purchaseId;
    public $userId;
    public $timeout = 3600; // 1 hour timeout

    public function __construct($purchaseId, $userId = null)
    {
        $this->purchaseId = $purchaseId;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0); // Infinite time limit for CLI process

        $purchase = Purchase::with(['vendor', 'user', 'details.product', 'attachments'])->find($this->purchaseId);
        if (!$purchase) {
            return;
        }

        $settings = GeneralSetting::first();

        $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
        // $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 180, 46);
        $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 480, 120, 95);

        foreach ($purchase->details as $detail) {
            if ($detail->product && $detail->product->thumb_image) {
                // $detail->product->optimized_image = PdfImageHelper::optimize($detail->product->thumb_image, 60, 60);
                $detail->product->optimized_image = PdfImageHelper::optimize($detail->product->thumb_image, 400, 400, 95);
            }
        }

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
            'enable_remote' => false,
        ])->loadView('backend.purchase.print_pdf', compact('purchase', 'settings'));

        $path = 'purchases/purchase_' . $purchase->invoice_no . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        // Notify user via Cache
        if ($purchase->user_id) {
            $this->addCacheNotification($purchase->user_id, [
                'type' => 'pdf_ready',
                'title' => 'Purchase PDF Ready',
                'desc' => "PDF for Purchase #{$purchase->invoice_no} is ready.",
                'url' => route('admin.purchases.download-pdf', $purchase->id),
                'icon' => 'fas fa-file-invoice',
                'class' => 'bg-info',
                'timestamp' => now()->timestamp,
            ]);
        }
    }

    private function addCacheNotification($userId, $data)
    {
        $key = 'user_pdf_notifications_' . $userId;
        $notifications = \Illuminate\Support\Facades\Cache::get($key, []);

        $data['time'] = now()->diffForHumans();
        $data['is_unread'] = true;
        $data['is_out_of_stock'] = false;

        $notifications[] = $data;
        $notifications = array_slice($notifications, -20);

        \Illuminate\Support\Facades\Cache::put($key, $notifications, now()->addDays(7));
    }
}
