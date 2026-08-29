<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockBatch;
use App\DataTables\StockBatchDataTable;
use Illuminate\Http\Request;

class StockBatchController extends Controller
{
    public function index(StockBatchDataTable $dataTable)
    {
        $products = Product::query()
            ->select('products.id', 'products.name', 'products.product_number')
            ->where('status', 1)
            ->orderByDesc('products.id')
            ->get();

        return $dataTable->render('backend.stock_batches.index', compact('products'));
    }
}
