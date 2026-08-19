<?php

namespace App\Jobs;

use App\Models\GeneralSetting;
use App\Models\Order;
use App\Support\PdfImageHelper;
use App\Support\PiInfoSupport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;
    public $type;
    public $userId;
    public $timeout = 3600; // 1 hour timeout for large PDFs

    /**
     * Create a new job instance.
     */
    public function __construct($orderId, $type, $userId = null)
    {
        $this->orderId = $orderId;
        $this->type = $type;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0); // Infinite time limit for CLI process

        $order = Order::find($this->orderId);
        if (!$order) {
            return;
        }

        $settings = GeneralSetting::first();

        if ($this->type === 'invoice') {
            $this->generateInvoice($order, $settings);
        } elseif ($this->type === 'pi_invoice') {
            $this->generatePiInvoice($order, $settings);
        } elseif ($this->type === 'customer_invoice') {
            $this->generateCustomerInvoice($order, $settings);
        }

        // Notify the user via Cache without using database tables
        $notifyUserId = $this->userId ?: ($order ? $order->user_id : null);
        
        if ($notifyUserId) {
            $title = 'PDF Ready';
            $message = 'Your PDF is ready to download.';
            $link = '#';

            if ($this->type === 'invoice') {
                $title = 'Invoice Ready';
                $message = "Invoice for Order #{$order->order_no} is ready.";
                $link = route('admin.orders.download-invoice', $order->id);
            } elseif ($this->type === 'pi_invoice') {
                $title = 'PI Invoice Ready';
                $message = "PI Invoice for Order #{$order->order_no} is ready.";
                $link = route('admin.orders.pi-invoice.download', $order->id); 
            } elseif ($this->type === 'customer_invoice') {
                $title = 'Customer Invoice Ready';
                $message = "Customer Invoice for Order #{$order->order_no} is ready.";
                $link = route('admin.orders.download-customer-invoice', $order->id);
            }

            $this->addCacheNotification($notifyUserId, [
                'type' => 'pdf_ready',
                'title' => $title,
                'desc' => $message,
                'url' => $link,
                'icon' => 'fas fa-file-pdf',
                'class' => 'bg-success',
                'timestamp' => now()->timestamp,
            ]);
        }
    }

    private function generateInvoice(Order $order, $settings)
    {
        $order->load(['items.product', 'items.variant.color', 'items.variant.size', 'user']);
        
        $issuedItems = $this->getIssuedItems($order);
        $itemCount = $issuedItems->count();

        $piInfo = PiInfoSupport::prepare($order->pi_info, $issuedItems, 'quantity');
        
        // Optimize images in PI Info blocks for faster PDF rendering
        if (isset($piInfo['blocks']) && is_array($piInfo['blocks'])) {
            foreach ($piInfo['blocks'] as &$block) {
                if (!empty($block['image'])) {
                    // $block['optimized_image'] = PdfImageHelper::optimize($block['image'], 80, 80);
                    $block['optimized_image'] = PdfImageHelper::optimize($block['image'], 400, 400, 95);
                }
            }
        }

        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($order->pi_info);

        $processed = 0;
        foreach ($issuedItems as $item) {
            // $item->optimized_image = PdfImageHelper::optimize($item->product_image, 80, 80);
            $item->optimized_image = PdfImageHelper::optimize($item->product_image, 400, 400, 95);
            $processed++;
            if ($processed % 500 === 0) {
                // \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Processed {$processed}/{$itemCount} items for Invoice Order #{$order->order_no}");
            }
        }
        // \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Rendering DOMPDF for Invoice Order #{$order->order_no}...");

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
            'enable_remote' => false,
        ])->loadView('backend.orders.print_pdf', compact('order', 'settings', 'piInfo', 'piTotals', 'hasSavedPiInfo', 'itemCount', 'issuedItems'));

        $path = 'invoices/invoice-' . $order->order_no . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());
    }

    private function generatePiInvoice(Order $order, $settings)
    {
        $order->load([
            'items.product.category',
            'items.product.subCategory',
            'items.product.childCategory',
            'items.product.brand',
            'items.product.vendor',
            'items.product.unit',
            'items.product.productType',
            'items.variant.color',
            'items.variant.size',
            'user',
        ]);
        
        $issuedItems = $this->getIssuedItems($order);
        $itemCount = $issuedItems->count();

        $piInfo = PiInfoSupport::prepare($order->pi_info, $issuedItems, 'quantity');
        
        // Optimize images in PI Info blocks for faster PDF rendering
        if (isset($piInfo['blocks']) && is_array($piInfo['blocks'])) {
            foreach ($piInfo['blocks'] as &$block) {
                if (!empty($block['image'])) {
                    // $block['optimized_image'] = PdfImageHelper::optimize($block['image'], 80, 80);
                    $block['optimized_image'] = PdfImageHelper::optimize($block['image'], 400, 400, 95);
                }
            }
        }

        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($order->pi_info);

        $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
       // $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 160, 40);
       $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 480, 400, 95);

        // \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Processing {$itemCount} items for PI Invoice Order #{$order->order_no}");
        $processed = 0;
        foreach ($issuedItems as $item) {
            // $item->optimized_image = PdfImageHelper::optimize($item->product_image, 80, 80);
            $item->optimized_image = PdfImageHelper::optimize($item->product_image, 400, 400, 95);
            $processed++;
            if ($processed % 500 === 0) {
                // \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Processed {$processed}/{$itemCount} items for PI Invoice Order #{$order->order_no}");
            }
        }
        // \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Rendering DOMPDF for PI Invoice Order #{$order->order_no}...");

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
            'enable_remote' => false,
        ])->loadView('backend.orders.pi_invoice', compact('order', 'settings', 'piInfo', 'piTotals', 'hasSavedPiInfo', 'itemCount', 'issuedItems') + ['isPdf' => true]);

        $path = 'invoices/pi-invoice-' . $order->order_no . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());
    }

    private function generateCustomerInvoice(Order $order, $settings)
    {
        $order->load(['items.product', 'items.variant.color', 'items.variant.size', 'user']);
        
        $issuedItems = $this->getIssuedItems($order);
        $itemCount = $issuedItems->count();

        $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
        // $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 120, 30);
        $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 480, 120, 95);

        // \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Processing {$itemCount} items for Customer Invoice Order #{$order->order_no}");
        $processed = 0;
        foreach ($issuedItems as $item) {
            // $item->optimized_image = PdfImageHelper::optimize($item->product_image, 60, 60);
            $item->optimized_image = PdfImageHelper::optimize($item->product_image, 400, 400, 95);
            $processed++;
            if ($processed % 500 === 0) {
                // \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Processed {$processed}/{$itemCount} items for Customer Invoice Order #{$order->order_no}");
            }
        }
        // \Illuminate\Support\Facades\Log::info("GeneratePdfJob: Rendering DOMPDF for Customer Invoice Order #{$order->order_no}...");

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
            'enable_remote' => false,
        ])->loadView('backend.orders.customer_invoice', compact('order', 'settings', 'itemCount', 'issuedItems'));
        
        $path = 'invoices/customer-invoice-' . $order->order_no . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        /*
        $order = Order::find($this->orderId);
        if ($order && $order->user_id) {
            $this->addCacheNotification($order->user_id, [
                'type' => 'pdf_failed',
                'title' => 'PDF Generation Failed',
                'desc' => "Failed to generate PDF for Order #{$order->order_no}. Please try again.",
                'url' => '#',
                'icon' => 'fas fa-times-circle',
                'class' => 'bg-danger',
            ]);
        }
        */
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
        
        \Illuminate\Support\Facades\Cache::put($key, $notifications, now()->addDays(7));
        // \Illuminate\Support\Facades\Log::info("Notification pushed to cache for user {$userId}: " . json_encode($data));
    }

    private function getIssuedItems(Order $order)
    {
        return $order->items;
    }
}
