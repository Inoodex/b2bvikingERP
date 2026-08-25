<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\MonthEndSnapshot;
use App\DataTables\MonthEndSnapshotDataTable;
use Illuminate\Http\Request;

class MonthEndSnapshotController extends Controller
{
    public function index(MonthEndSnapshotDataTable $dataTable)
    {
        $periods = MonthEndSnapshot::query()
            ->select('period')
            ->distinct()
            ->orderByDesc('period')
            ->pluck('period');

        $products = Product::query()
            ->select('products.id', 'products.name')
            ->whereIn('products.id', MonthEndSnapshot::query()->select('product_id')->distinct())
            ->orderByDesc('products.id')
            ->get();

        return $dataTable->render('backend.month_end_snapshots.index', compact('periods', 'products'));
    }
}
