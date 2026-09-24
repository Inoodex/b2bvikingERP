<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\GeneralSetting;
use App\Models\SalesQuotation;
use App\Support\PdfImageHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class GenerateBuyerCatalogPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $quotationId;
    public int $userId;
    public array $options;
    public int $timeout = 3600;

    public function __construct(int $quotationId, int $userId, array $options = [])
    {
        $this->quotationId = $quotationId;
        $this->userId = $userId;
        $this->options = $options;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $salesQuotation = SalesQuotation::with([
            'customer',
            'currency',
            'tax',
            'creator',
            'items.product',
            'items.variant.color',
            'items.variant.size'
        ])->findOrFail($this->quotationId);

        $settings = GeneralSetting::first();

        // Memory-safe pre-optimization of all product thumbnails via PdfImageHelper
        foreach ($salesQuotation->items as $item) {
            $product = $item->product;
            if ($product && !empty($product->thumb_image)) {
                $product->pdf_optimized_image = PdfImageHelper::optimize(
                    $product->thumb_image,
                    width: 120,
                    height: 120,
                    quality: 75
                );
            } else {
                $product->pdf_optimized_image = null;
            }
        }

        $data = [
            'salesQuotation' => $salesQuotation,
            'settings'       => $settings,
            'isLookbook'     => true,
            'options'        => $this->options,
            'generatedAt'    => now()->format('d M Y, h:i A'),
        ];

        $pdf = Pdf::loadView('backend.sales_quotation.sq_pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isPhpEnabled', true);

        $tempDir = storage_path('app/temp_reports');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Purge previous catalog PDFs for this user and quotation
        $pattern = $tempDir . "/catalog_sq{$this->quotationId}_u{$this->userId}_*.pdf";
        foreach (glob($pattern) as $oldFile) {
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        $timestamp = now()->format('Ymd_His');
        $filename = "catalog_sq{$this->quotationId}_u{$this->userId}_{$timestamp}.pdf";
        $filePath = "{$tempDir}/{$filename}";
        $pdf->save($filePath);

        $this->addCacheNotification($this->userId, [
            'type'      => 'pdf_ready',
            'title'     => 'Buyer Catalog & Lookbook PDF Ready',
            'desc'      => "Visual Lookbook for Quotation #{$salesQuotation->quotation_no} has been generated.",
            'url'       => route('admin.sales-quotations.catalog-pdf.download', ['file' => $filename]),
            'icon'      => 'fas fa-book-open',
            'class'     => 'bg-primary',
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
