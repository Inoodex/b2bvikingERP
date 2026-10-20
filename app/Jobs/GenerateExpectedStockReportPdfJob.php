<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class GenerateExpectedStockReportPdfJob implements ShouldQueue
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
        ini_set('memory_limit', '2048M');
        set_time_limit(0);

        $outletId = null;
        $outletName = null;
        $userName = null;

        if (!empty($this->filters['expected_outlet_id'])) {
            $outletId = (int) $this->filters['expected_outlet_id'];
            $outletName = Outlet::where('id', $outletId)->value('name');
        } elseif (!empty($this->filters['expected_user_id'])) {
            $user = User::find($this->filters['expected_user_id']);
            if ($user) {
                $outletId = $user->outlet_id;
                $userName = $user->name . ($user->outlet_name ? ' (' . $user->outlet_name . ')' : '');
            }
        }

        $query = Product::with([
            'category:id,name',
            'brand:id,name',
            'variants' => function ($vq) use ($outletId) {
                $vq->with(['color:id,name', 'size:id,name']);
                if ($outletId) {
                    $vq->withSum(['inventoryStocks' => function ($q) use ($outletId) {
                        $q->where('outlet_id', $outletId);
                    }], 'quantity');
                } else {
                    $vq->withSum('inventoryStocks', 'quantity');
                }
            }
        ])->where('status', 1);

        if ($outletId) {
            $query->whereHas('inventoryStocks', function ($q) use ($outletId) {
                $q->where('outlet_id', $outletId)->where('quantity', '>', 0);
            })->withSum(['inventoryStocks' => function ($q) use ($outletId) {
                $q->where('outlet_id', $outletId);
            }], 'quantity');
        } else {
            $query->whereHas('inventoryStocks', function ($q) {
                $q->where('quantity', '>', 0);
            })->withSum('inventoryStocks', 'quantity');
        }

        if (!empty($this->filters['expected_category_id'])) {
            $query->where('category_id', $this->filters['expected_category_id']);
        }

        if (!empty($this->filters['expected_brand_id'])) {
            $query->where('brand_id', $this->filters['expected_brand_id']);
        }

        $products = $query->orderBy('name', 'asc')->get();

        $totalQty = 0;
        $totalCost = 0.0;
        $totalWholesale = 0.0;
        $totalRetail = 0.0;

        $defaultPlaceholder = public_path('uploads/no-image.svg');
        if (!file_exists($defaultPlaceholder)) {
            $defaultPlaceholder = public_path('uploads/default.png');
        }

        foreach ($products as $product) {
            $qty = (float) ($product->inventory_stocks_sum_quantity ?? 0);
            $cost = (float) ($product->purchase_price ?? 0);
            $wholesale = (float) ($product->outlet_price ?? 0);
            $retail = (float) ($product->price ?? 0);

            $assetVal = $qty * $cost;
            $wholesaleVal = $qty * $wholesale;
            $retailVal = $qty * $retail;
            $marginVal = $retailVal - $assetVal;

            $product->calc_qty = $qty;
            $product->calc_asset_val = $assetVal;
            $product->calc_wholesale_val = $wholesaleVal;
            $product->calc_retail_val = $retailVal;
            $product->calc_margin_val = $marginVal;

            $totalQty += $qty;
            $totalCost += $assetVal;
            $totalWholesale += $wholesaleVal;
            $totalRetail += $retailVal;

            // Resolve image path safely for DomPDF
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

        $totalMargin = $totalRetail - $totalCost;
        $settings = GeneralSetting::first() ?? new GeneralSetting(['site_name' => 'B2B Viking ERP', 'currency_icon' => 'Kr.']);

        $categoryName = null;
        if (!empty($this->filters['expected_category_id'])) {
            $categoryName = Category::where('id', $this->filters['expected_category_id'])->value('name');
        }

        $brandName = null;
        if (!empty($this->filters['expected_brand_id'])) {
            $brandName = Brand::where('id', $this->filters['expected_brand_id'])->value('name');
        }

        $data = [
            'products'       => $products,
            'totalQty'       => $totalQty,
            'totalCost'      => $totalCost,
            'totalWholesale' => $totalWholesale,
            'totalRetail'    => $totalRetail,
            'totalMargin'    => $totalMargin,
            'settings'       => $settings,
            'filters'        => $this->filters,
            'outletName'     => $outletName,
            'userName'       => $userName,
            'categoryName'   => $categoryName,
            'brandName'      => $brandName,
            'generatedAt'    => now()->format('d M Y, h:i A'),
        ];

        $pdf = Pdf::loadView('backend.reports.expected_stock_pdf', $data)
            ->setPaper('a4', 'landscape');

        $tempDir = storage_path('app/temp_reports');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Pre-generation purge: delete previous expected_stock_report PDFs for this user
        $pattern = $tempDir . "/expected_stock_report_u{$this->userId}_*.pdf";
        foreach (glob($pattern) as $oldFile) {
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        $timestamp = now()->format('Ymd_His');
        $filename = "expected_stock_report_u{$this->userId}_{$timestamp}.pdf";
        $filePath = "{$tempDir}/{$filename}";
        $pdf->save($filePath);

        $filterDesc = [];
        if ($outletName) {
            $filterDesc[] = "Outlet: {$outletName}";
        }
        if ($userName) {
            $filterDesc[] = "Customer: {$userName}";
        }
        if ($categoryName) {
            $filterDesc[] = "Category: {$categoryName}";
        }
        if ($brandName) {
            $filterDesc[] = "Brand: {$brandName}";
        }
        $filterLabel = !empty($filterDesc) ? implode(', ', $filterDesc) : 'All Inventory';

        $this->addCacheNotification($this->userId, [
            'type'      => 'pdf_ready',
            'title'     => 'Expected Retail Report Ready',
            'desc'      => "Expected Retail Stock Report ({$filterLabel}) is ready.",
            'url'       => route('admin.reports.stock.expected.pdf.download', ['file' => $filename]),
            'icon'      => 'fas fa-tags',
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
