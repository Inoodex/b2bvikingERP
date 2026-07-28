<?php

namespace App\Jobs;

use App\Models\ComparisonStatement;
use App\Models\VendorQuotation;
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

class GenerateCsPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $csId;
    public $userId;
    public $timeout = 3600;

    public function __construct($csId, $userId = null)
    {
        $this->csId = $csId;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $cs = ComparisonStatement::with([
            'rfq.items.product.unit',
            'recommendedVendor',
            'items.product.unit',
            'items.selectedQuotationItem.quotation.vendor',
            'approvals.step.approverRole',
            'approvals.user'
        ])->find($this->csId);

        if (!$cs) {
            return;
        }

        $quotations = VendorQuotation::with(['vendor', 'currency', 'items'])
            ->where('rfq_id', $cs->rfq_id)
            ->get();

        $settings = GeneralSetting::first();
        if ($settings && $settings->site_logo) {
            $settings->optimized_logo = PdfImageHelper::optimize($settings->site_logo, 480, 120, 95);
        }

        foreach ($cs->rfq->items as $item) {
            if ($item->product && $item->product->thumb_image) {
                $item->product->optimized_image = PdfImageHelper::optimize($item->product->thumb_image, 400, 400, 95);
            }
        }

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
        ])->loadView('backend.rfq.cs_pdf', compact('cs', 'quotations', 'settings'));

        $path = 'cs/cs_' . $cs->id . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        if ($this->userId) {
            $this->addCacheNotification($this->userId, [
                'type' => 'pdf_ready',
                'title' => 'CS PDF Ready',
                'desc' => "PDF for CS Ref #{$cs->cs_no} is generated.",
                'url' => route('admin.rfqs.cs.pdf.view', ['rfq' => $cs->rfq_id, 'cs' => $cs->id]),
                'icon' => 'fas fa-balance-scale',
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
