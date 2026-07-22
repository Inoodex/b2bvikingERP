<?php

namespace App\Jobs;

use App\Models\GeneralSetting;
use App\Models\Issue;
use App\Support\PdfImageHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateIssuePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $issueId;
    public $userId;
    public $timeout = 3600; // 1 hour timeout for large invoices

    /**
     * Create a new job instance.
     */
    public function __construct($issueId, $userId = null)
    {
        $this->issueId = $issueId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $issue = Issue::find($this->issueId);
        if (!$issue) {
            return;
        }

        $issue->load(['items.product', 'items.variant.color', 'items.variant.size', 'outlet', 'productRequest']);
        $settings = GeneralSetting::first();

        // Optimize logo
        $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
        // $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 160, 38);
        $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 480, 114, 95);

        // Optimize product images
        foreach ($issue->items as $item) {
            if ($item->product && $item->product->thumb_image) {
                // $item->product->optimized_image = PdfImageHelper::optimize($item->product->thumb_image, 60, 60);
                $item->product->optimized_image = PdfImageHelper::optimize($item->product->thumb_image, 400, 400, 95);
            }
        }

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
        ])->loadView('backend.pdf.issue-invoice', array_merge(compact('issue', 'settings'), ['is_pdf' => true]));

        $fileName = 'issue_invoice_' . $issue->issue_no . '.pdf';
        $path = 'invoices/' . $fileName;
        
        Storage::disk('public')->put($path, $pdf->output());
        $issue->update(['invoice_path' => $path]);

        // Notify user via cache
        $notifyUserId = $this->userId;
        if ($notifyUserId) {
            $this->addCacheNotification($notifyUserId, [
                'type' => 'pdf_ready',
                'title' => 'Issue Invoice Ready',
                'desc' => "Issue Invoice for Issue #{$issue->issue_no} is ready.",
                'url' => route('admin.issues.view-invoice', $issue->id),
                'icon' => 'fas fa-file-pdf',
                'class' => 'bg-success',
                'timestamp' => now()->timestamp,
            ]);
        }
    }

    /**
     * Add notification to cache
     */
    private function addCacheNotification($userId, $data)
    {
        $key = 'user_pdf_notifications_' . $userId;
        $notifications = \Illuminate\Support\Facades\Cache::get($key, []);
        
        $data['time'] = now()->diffForHumans();
        $data['is_unread'] = true;
        $data['is_out_of_stock'] = false;
        
        $notifications[] = $data;
        
        // Keep only the latest 20 notifications in cache
        $notifications = array_slice($notifications, -20);
        
        \Illuminate\Support\Facades\Cache::put($key, $notifications, now()->addDays(3));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Log the failure if needed
        \Illuminate\Support\Facades\Log::error('GenerateIssuePdfJob failed: ' . $exception->getMessage());
    }
}
