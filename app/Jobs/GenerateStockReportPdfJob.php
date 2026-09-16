<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class GenerateStockReportPdfJob implements ShouldQueue
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
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $query = Product::with(['category:id,name', 'brand:id,name'])
            ->withSum('inventoryStocks', 'quantity')
            ->where('status', 1);

        if (!empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }

        if (!empty($this->filters['brand_id'])) {
            $query->where('brand_id', $this->filters['brand_id']);
        }

        $products = $query->orderBy('name', 'asc')->get();

        $totalQty = 0;
        $totalValue = 0.0;
        $potentialRevenue = 0.0;

        $defaultPlaceholder = public_path('uploads/no-image.svg');
        if (!file_exists($defaultPlaceholder)) {
            $defaultPlaceholder = public_path('uploads/default.png');
        }

        foreach ($products as $product) {
            $qty = (float) ($product->inventory_stocks_sum_quantity ?? 0);
            $purchasePrice = (float) ($product->purchase_price ?? 0);
            $sellingPrice = (float) ($product->price ?? 0);

            $assetVal = $qty * $purchasePrice;
            $revenueVal = $qty * $sellingPrice;
            $profitVal = $revenueVal - $assetVal;

            $product->calc_qty = $qty;
            $product->calc_asset_value = $assetVal;
            $product->calc_potential_revenue = $revenueVal;
            $product->calc_potential_profit = $profitVal;

            $totalQty += $qty;
            $totalValue += $assetVal;
            $potentialRevenue += $revenueVal;

            // Resolve local image path safely for DomPDF without HTTP overhead
            $imgPath = null;
            if (!empty($product->thumb_image)) {
                if (file_exists(public_path('storage/' . $product->thumb_image))) {
                    $imgPath = public_path('storage/' . $product->thumb_image);
                } elseif (file_exists(public_path($product->thumb_image))) {
                    $imgPath = public_path($product->thumb_image);
                }
            }

            if (!$imgPath && file_exists($defaultPlaceholder)) {
                $imgPath = $defaultPlaceholder;
            }

            $product->pdf_image_path = $imgPath;
        }

        $potentialProfit = $potentialRevenue - $totalValue;
        $settings = GeneralSetting::first() ?? new GeneralSetting(['site_name' => 'B2B Viking ERP', 'currency_icon' => 'Kr.']);

        $categoryName = null;
        if (!empty($this->filters['category_id'])) {
            $categoryName = Category::where('id', $this->filters['category_id'])->value('name');
        }

        $brandName = null;
        if (!empty($this->filters['brand_id'])) {
            $brandName = Brand::where('id', $this->filters['brand_id'])->value('name');
        }

        $data = [
            'products'         => $products,
            'totalQty'         => $totalQty,
            'totalValue'       => $totalValue,
            'potentialRevenue' => $potentialRevenue,
            'potentialProfit'  => $potentialProfit,
            'settings'         => $settings,
            'filters'          => $this->filters,
            'categoryName'     => $categoryName,
            'brandName'        => $brandName,
            'generatedAt'      => now()->format('d M Y, h:i A'),
        ];

        $pdf = Pdf::loadView('backend.reports.stock_pdf', $data)
            ->setPaper('a4', 'landscape');

        $tempDir = storage_path('app/temp_reports');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Pre-generation purge: delete previous stock_valuation_report PDFs for this user
        $pattern = $tempDir . "/stock_valuation_report_u{$this->userId}_*.pdf";
        foreach (glob($pattern) as $oldFile) {
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        $timestamp = now()->format('Ymd_His');
        $filename = "stock_valuation_report_u{$this->userId}_{$timestamp}.pdf";
        $filePath = "{$tempDir}/{$filename}";
        $pdf->save($filePath);

        $filterDesc = [];
        if ($categoryName) {
            $filterDesc[] = "Category: {$categoryName}";
        }
        if ($brandName) {
            $filterDesc[] = "Brand: {$brandName}";
        }
        $filterLabel = !empty($filterDesc) ? implode(', ', $filterDesc) : 'All Products';

        $this->addCacheNotification($this->userId, [
            'type'      => 'pdf_ready',
            'title'     => 'Stock Valuation Report Ready',
            'desc'      => "Stock Valuation Report ({$filterLabel}) is ready.",
            'url'       => route('admin.reports.stock.pdf.download', ['file' => $filename]),
            'icon'      => 'fas fa-boxes',
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
