<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryStock;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Support\PdfImageHelper;

class InventoryReportController extends Controller
{
    // public function index()
    // {
    //     // Consolidate duplicates: Group by Product+Variant and Sum Quantity
    //     $rawStocks = InventoryStock::with(['product', 'variant'])->get();
        
    //     $stocks = $rawStocks->groupBy(function($item) {
    //         return $item->product_id . '-' . $item->variant_id . '-' . $item->outlet_id;
    //     })->map(function($group) {
    //         $first = $group->first();
    //         $first->quantity = $group->sum('quantity');
    //         return $first;
    //     })->values();
        
    //     return view('backend.inventory_report.index', compact('stocks'));

    //     //if show all then use this : 
    //     // $stocks = InventoryStock::with(['product', 'variant'])->orderBy('product_id')->get();
    //     // return view('backend.inventory_report.index', compact('stocks'));
    // }
//     public function index(Request $request)
// {
//     $query = InventoryStock::with(['product.category', 'variant'])
//         ->select(
//             DB::raw('MAX(id) as id'),
//             'product_id',
//             'variant_id',
//             'outlet_id',
//             DB::raw('SUM(quantity) as quantity')
//         )
//         ->groupBy('product_id', 'variant_id', 'outlet_id')
//         ->orderBy('id', 'desc');

//     if ($request->filled('search')) {
//         $search = $request->search;

//         $query->whereHas('product', function ($q) use ($search) {
//             $q->where('name', 'like', "%{$search}%")
//               ->orWhere('product_number', 'like', "%{$search}%")
//               ->orWhereHas('category', function ($c) use ($search) {
//                   $c->where('name', 'like', "%{$search}%");
//               });
//         })
//         ->orWhereHas('variant', function ($q) use ($search) {
//             $q->where('name', 'like', "%{$search}%");
//         });
//     }

//     $stocks = $query->paginate(10)->withQueryString();

//     return view('backend.inventory_report.index', compact('stocks'));
// }

public function index(Request $request)
{
    // Fetch all active categories for the filter dropdown
    $categories = Category::where('status', 1)->orderBy('name')->get();

    $subCategories = collect();
    if ($request->filled('category_id')) {
        $subCategories = SubCategory::where('category_id', $request->category_id)
            ->where('status', 1)
            ->orderBy('name')
            ->get();
    }

    $childCategories = collect();
    if ($request->filled('sub_category_id')) {
        $childCategories = ChildCategory::where('sub_category_id', $request->sub_category_id)
            ->where('status', 1)
            ->orderBy('name')
            ->get();
    }

    // 🔹 Step 1 — Build grouped subquery
    $groupedQuery = InventoryStock::query()
        ->select(
            DB::raw('MAX(id) as id'),
            'product_id',
            'variant_id',
            'outlet_id',
            DB::raw('SUM(quantity) as quantity')
        )
        ->groupBy('product_id', 'variant_id', 'outlet_id');

    // 🔹 Step 2 — Advanced search and category/subcategory/childcategory filter
    if ($request->filled('search') || $request->filled('category_id') || $request->filled('sub_category_id') || $request->filled('child_category_id')) {
        $search = $request->search;
        $categoryId = $request->category_id;
        $subCategoryId = $request->sub_category_id;
        $childCategoryId = $request->child_category_id;

        $groupedQuery->where(function ($q) use ($search, $categoryId, $subCategoryId, $childCategoryId) {
            if ($categoryId || $subCategoryId || $childCategoryId) {
                $q->whereHas('product', function ($p) use ($categoryId, $subCategoryId, $childCategoryId) {
                    if ($categoryId) {
                        $p->where('category_id', $categoryId);
                    }
                    if ($subCategoryId) {
                        $p->where('sub_category_id', $subCategoryId);
                    }
                    if ($childCategoryId) {
                        $p->where('child_category_id', $childCategoryId);
                    }
                });
            }

            if ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->whereHas('product', function ($p) use ($search) {
                        $p->where('name', 'like', "%{$search}%")
                          ->orWhere('product_number', 'like', "%{$search}%")
                          ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                          ->orWhereHas('subCategory', fn ($sc) => $sc->where('name', 'like', "%{$search}%"))
                          ->orWhereHas('childCategory', fn ($cc) => $cc->where('name', 'like', "%{$search}%"));
                    })
                    ->orWhereHas('variant', function ($v) use ($search) {
                        $v->where('name', 'like', "%{$search}%");
                    });
                });
            }
        });
    }

    // 🔹 Step 3 — Wrap grouped query (advanced DB aggregation)
    $wrapped = DB::query()->fromSub($groupedQuery, 'stocks');

    // 👉 Total quantity (ALL pages)
    $totalQuantity = $wrapped->sum('quantity');

    // 🔹 Step 4 — Pagination query with eager loading
    $stocks = InventoryStock::fromSub($groupedQuery, 'stocks')
        ->with(['product.category', 'product.subCategory', 'product.childCategory', 'variant'])
        ->orderByDesc('id')
        ->paginate(10)
        ->withQueryString();

    // 👉 Current page quantity
    $pageQuantity = $stocks->sum('quantity');

    if ($request->ajax()) {
        return response()->json([
            'html' => view('backend.inventory_report.partials.table', compact(
                'stocks',
                'totalQuantity',
                'pageQuantity'
            ))->render(),
            'totalQuantity' => $totalQuantity,
            'pageQuantity' => $pageQuantity,
            'total' => $stocks->total(),
        ]);
    }

    return view('backend.inventory_report.index', compact(
        'stocks',
        'totalQuantity',
        'pageQuantity',
        'categories',
        'subCategories',
        'childCategories'
    ));
}

public function exportPdf(Request $request)
{
    ini_set('memory_limit', '1024M');
    set_time_limit(300);
    // 🔹 Step 1 — Build grouped subquery (Same logic as index)
    $groupedQuery = InventoryStock::query()
        ->select(
            DB::raw('MAX(id) as id'),
            'product_id',
            'variant_id',
            'outlet_id',
            DB::raw('SUM(quantity) as quantity')
        )
        ->groupBy('product_id', 'variant_id', 'outlet_id');

    // 🔹 Step 2 — Advanced search and category/subcategory/childcategory filter (Same logic as index)
    if ($request->filled('search') || $request->filled('category_id') || $request->filled('sub_category_id') || $request->filled('child_category_id')) {
        $search = $request->search;
        $categoryId = $request->category_id;
        $subCategoryId = $request->sub_category_id;
        $childCategoryId = $request->child_category_id;

        $groupedQuery->where(function ($q) use ($search, $categoryId, $subCategoryId, $childCategoryId) {
            if ($categoryId || $subCategoryId || $childCategoryId) {
                $q->whereHas('product', function ($p) use ($categoryId, $subCategoryId, $childCategoryId) {
                    if ($categoryId) {
                        $p->where('category_id', $categoryId);
                    }
                    if ($subCategoryId) {
                        $p->where('sub_category_id', $subCategoryId);
                    }
                    if ($childCategoryId) {
                        $p->where('child_category_id', $childCategoryId);
                    }
                });
            }

            if ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->whereHas('product', function ($p) use ($search) {
                        $p->where('name', 'like', "%{$search}%")
                          ->orWhere('product_number', 'like', "%{$search}%")
                          ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                          ->orWhereHas('subCategory', fn ($sc) => $sc->where('name', 'like', "%{$search}%"))
                          ->orWhereHas('childCategory', fn ($cc) => $cc->where('name', 'like', "%{$search}%"));
                    })
                    ->orWhereHas('variant', function ($v) use ($search) {
                        $v->where('name', 'like', "%{$search}%");
                    });
                });
            }
        });
    }

    // 🔹 Step 3 — Get all stocks without pagination
    $stocks = InventoryStock::fromSub($groupedQuery, 'stocks')
        ->with(['product.category', 'product.subCategory', 'product.childCategory', 'variant'])
        ->orderByDesc('id')
        ->get();

    // 🔹 Step 4 — Image Optimization (Compress for PDF)
    foreach ($stocks as $stock) {
        if ($stock->product && $stock->product->thumb_image) {
            $stock->optimized_image = PdfImageHelper::optimize($stock->product->thumb_image, 50, 50);
        }
    }

    $totalQuantity = $stocks->sum('quantity');

    // Generate PDF
    $pdf = Pdf::loadView('backend.inventory_report.export_pdf', compact('stocks', 'totalQuantity'));
    
    // Set paper size if needed
    $pdf->setPaper('a4', 'portrait');

    return $pdf->download('inventory-report-' . date('Y-m-d') . '.pdf');
}

}
