<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\User;
use Illuminate\Http\Request;

class StockLedgerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = StockLedger::with(['product', 'variant', 'outlet'])->select('stock_ledgers.*');

            if ($request->filled('product_id')) {
                $data->where('product_id', $request->integer('product_id'));
            }

            if ($request->filled('variant_id')) {
                $data->where('variant_id', $request->integer('variant_id'));
            }

            if ($request->filled('reference_type')) {
                $data->where('reference_type', $request->string('reference_type')->toString());
            }

            if ($request->filled('movement_type')) {
                $movementType = $request->string('movement_type')->toString();

                if ($movementType === 'in') {
                    $data->where('in_qty', '>', 0);
                }

                if ($movementType === 'out') {
                    $data->where('out_qty', '>', 0);
                }
            }

            if ($request->filled('date_from')) {
                $data->whereDate('created_at', '>=', $request->date('date_from')->toDateString());
            }

            if ($request->filled('date_to')) {
                $data->whereDate('created_at', '<=', $request->date('date_to')->toDateString());
            }

            if ($request->filled('user_id')) {
                $data->where('outlet_id', $request->integer('user_id'));
            }

            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function($row){
                    return \Carbon\Carbon::parse($row->created_at)->format('Y-m-d h:i A');
                })
                ->addColumn('image', function($row){
                    $url = $row->product && $row->product->thumb_image ? asset('storage/'.$row->product->thumb_image) : asset('uploads/default.jpg');
                    return '<img src="'.$url.'" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">';
                })
                ->addColumn('product_name', function($row){
                    return $row->product->name ?? 'Deleted';
                })
                ->filterColumn('product_name', function($query, $keyword) {
                    $query->whereHas('product', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('variant_name', function($row){
                    return $row->variant ? $row->variant->name : '-';
                })
                ->filterColumn('variant_name', function($query, $keyword) {
                    $query->whereHas('variant', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('reference', function($row){
                    return $row->reference_type . ' #' . $row->reference_id;
                })
                ->filterColumn('reference', function($query, $keyword) {
                    $query->where('reference_id', 'like', "%{$keyword}%")
                          ->orWhere('reference_type', 'like', "%{$keyword}%");
                })
                ->addColumn('outlet', function($row){
                    if ($row->outlet) {
                        $name = $row->outlet->name;
                        $outletName = $row->outlet->outlet_name;
                        $label = $name . ($outletName ? " ({$outletName})" : '');
                        return '<span class="badge badge-secondary">' . e($label) . '</span>';
                    }
                    return '<span class="badge badge-dark">Main Warehouse</span>';
                })
                ->filterColumn('outlet', function($query, $keyword) {
                    $query->whereHas('outlet', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('type', function($row){
                    if($row->in_qty > 0)
                        return '<div class="badge badge-success">IN</div>';
                    else
                        return '<div class="badge badge-danger">OUT</div>';
                })
                ->rawColumns(['image', 'type', 'outlet'])
                ->make(true);
        }

        $products = Product::query()
            ->select('products.id', 'products.name')
            ->where('status', 1)
            ->with(['variants' => function ($query) {
                $query->select('id', 'product_id', 'name', 'color', 'size');
            }])
            ->orderByDesc('products.id')
            ->get();

        $ledgerProducts = $products->mapWithKeys(function ($product) {
            return [
                (string) $product->id => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'label' => $variant->name ?: trim(collect([$variant->color, $variant->size])->filter()->implode(' ')) ?: 'Variant #' . $variant->id,
                    ];
                })->values()->all(),
            ];
        })->toArray();

        $referenceTypes = StockLedger::query()
            ->whereNotNull('reference_type')
            ->distinct()
            ->orderBy('reference_type')
            ->pluck('reference_type');

        $users = User::role(['Outlet User', 'User'])->get(['id', 'name', 'outlet_name']);

        return view('backend.stock_ledger.index', compact('products', 'ledgerProducts', 'referenceTypes', 'users'));
    }
}
