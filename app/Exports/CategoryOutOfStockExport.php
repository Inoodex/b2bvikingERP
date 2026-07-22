<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CategoryOutOfStockExport implements FromQuery, WithHeadings, WithMapping
{
    protected $categoryId;

    public function __construct($categoryId = null)
    {
        $this->categoryId = $categoryId;
    }

    public function query()
    {
        set_time_limit(300);
        $query = Product::with(['category', 'vendor', 'inventoryStocks'])
            ->withSum('inventoryStocks', 'quantity')
            ->where('status', 1)
            ->havingRaw('inventory_stocks_sum_quantity <= 0 OR inventory_stocks_sum_quantity IS NULL');
            
        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }

        return $query->orderBy('category_id');
    }

    public function headings(): array
    {
        return [
            'Category',
            'Company/Vendor Name',
            'Item Code',
            'Photo URL',
            'Qty',
            'Buying Price',
            'Total Buying Price',
        ];
    }

    public function map($product): array
    {
        $qty = $product->inventory_stocks_sum_quantity ?? 0;
        $buyingPrice = $product->purchase_price ?? 0;
        $total = $qty * $buyingPrice;

        return [
            $product->category?->name ?? 'N/A',
            $product->vendor?->shop_name ?? 'N/A',
            $product->sku ?? $product->product_number ?? 'N/A',
            $product->thumb_image ? asset($product->thumb_image) : 'No Image',
            $qty,
            number_format($buyingPrice, 2, '.', ''),
            number_format($total, 2, '.', ''),
        ];
    }
}
