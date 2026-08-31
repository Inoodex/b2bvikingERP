<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\WarehouseBin;
use App\Models\WarehouseZone;
use App\DataTables\WarehouseBinDataTable;
use App\Services\Inventory\BarcodeGeneratorService;
use App\Http\Requests\Backend\WarehouseBin\StoreRequest;
use App\Http\Requests\Backend\WarehouseBin\UpdateRequest;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class WarehouseBinController extends Controller
{
    public function index(WarehouseBinDataTable $dataTable)
    {
        return $dataTable->render('backend.warehouse_bins.index');
    }

    public function create()
    {
        $zones = WarehouseZone::where('status', 1)->get();
        return view('backend.warehouse_bins.create', compact('zones'));
    }

    public function store(StoreRequest $request, BarcodeGeneratorService $barcodeService)
    {
        $bin = new WarehouseBin($request->validated());
        $bin->barcode = 'PENDING';
        $bin->save();

        // Generate barcode using standard format BIN-OUTLET-ZONE-BIN
        $bin->barcode = $barcodeService->generateBinBarcode($bin);
        $bin->save();

        Toastr::success('Warehouse Bin created successfully.', 'Success');
        return redirect()->route('admin.warehouse-bins.index');
    }

    public function edit(WarehouseBin $warehouseBin)
    {
        $zones = WarehouseZone::where('status', 1)->get();
        return view('backend.warehouse_bins.edit', compact('warehouseBin', 'zones'));
    }

    public function update(UpdateRequest $request, WarehouseBin $warehouseBin)
    {
        $warehouseBin->update($request->validated());
        Toastr::success('Warehouse Bin updated successfully.', 'Success');
        return redirect()->route('admin.warehouse-bins.index');
    }

    public function show(WarehouseBin $warehouseBin)
    {
        // This is used for printing the barcode sticker
        return view('backend.warehouse_bins.print_barcode', compact('warehouseBin'));
    }

    public function stocks(Request $request, WarehouseBin $warehouseBin)
    {
        if ($request->ajax()) {
            if ($request->get('type') === 'batches') {
                $query = \App\Models\StockBatch::with(['product.category', 'variant.color', 'variant.size'])
                    ->where('bin_id', $warehouseBin->id)
                    ->where('qty_remaining', '>', 0)
                    ->select('stock_batches.*');

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('batch_code', function($row){
                        return '<span class="badge badge-light border text-dark font-weight-bold"><i class="fas fa-barcode text-primary mr-1"></i>' . e($row->batch_no) . '</span>';
                    })
                    ->addColumn('product_name', function($row){
                        return e($row->product?->name ?? 'N/A');
                    })
                    ->filterColumn('product_name', function($query, $keyword) {
                        $query->whereHas('product', function($q) use ($keyword) {
                            $q->where('name', 'like', "%{$keyword}%");
                        });
                    })
                    ->addColumn('variant_details', function($row){
                        if ($row->variant) {
                            $name = e($row->variant->name);
                            $color = e($row->variant->color?->name ?? $row->variant->color);
                            $size = e($row->variant->size?->name ?? $row->variant->size);
                            $badges = '<strong class="text-primary font-weight-bold d-block">' . $name . '</strong>';
                            if ($color) $badges .= '<span class="badge badge-info mt-1 mr-1"><i class="fas fa-palette mr-1"></i>' . $color . '</span>';
                            if ($size) $badges .= '<span class="badge badge-secondary mt-1 mr-1"><i class="fas fa-ruler-combined mr-1"></i>' . $size . '</span>';
                            return $badges;
                        }
                        return '<span class="badge badge-light border text-muted">Standard (No Variant)</span>';
                    })
                    ->filterColumn('variant_details', function($query, $keyword) {
                        $query->whereHas('variant', function($q) use ($keyword) {
                            $q->where('name', 'like', "%{$keyword}%");
                        });
                    })
                    ->addColumn('received_date', function($row){
                        return $row->received_date ? \Carbon\Carbon::parse($row->received_date)->format('Y-m-d') : 'N/A';
                    })
                    ->addColumn('unit_cost', function($row){
                        return 'kr. ' . number_format($row->unit_cost, 2);
                    })
                    ->addColumn('qty_remaining', function($row){
                        return '<strong class="text-primary font-weight-bold">' . number_format($row->qty_remaining, 0) . ' Pcs</strong>';
                    })
                    ->addColumn('total_value', function($row){
                        return '<strong class="text-success font-weight-bold">kr. ' . number_format($row->qty_remaining * $row->unit_cost, 2) . '</strong>';
                    })
                    ->rawColumns(['batch_code', 'variant_details', 'qty_remaining', 'total_value'])
                    ->make(true);
            }

            // Default AJAX: inventory stocks
            $query = \App\Models\InventoryStock::with(['product.category', 'variant.color', 'variant.size'])
                ->where('bin_id', $warehouseBin->id)
                ->where('quantity', '>', 0)
                ->select('inventory_stocks.*');

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('image', function($row){
                    $url = $row->product && $row->product->thumb_image ? asset('storage/'.$row->product->thumb_image) : asset('uploads/default.jpg');
                    return '<img src="'.$url.'" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" class="border shadow-sm">';
                })
                ->addColumn('product_name', function($row){
                    $name = e($row->product?->name ?? 'N/A');
                    $cat = $row->product?->category ? '<br><small class="text-muted"><i class="fas fa-folder mr-1"></i>' . e($row->product->category->name) . '</small>' : '';
                    return '<strong class="text-dark">' . $name . '</strong>' . $cat;
                })
                ->filterColumn('product_name', function($query, $keyword) {
                    $query->whereHas('product', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('variant_details', function($row){
                    if ($row->variant) {
                        $name = e($row->variant->name);
                        $color = e($row->variant->color?->name ?? $row->variant->color);
                        $size = e($row->variant->size?->name ?? $row->variant->size);
                        $badges = '<strong class="text-primary font-weight-bold d-block">' . $name . '</strong>';
                        if ($color) $badges .= '<span class="badge badge-info mt-1 mr-1"><i class="fas fa-palette mr-1"></i>' . $color . '</span>';
                        if ($size) $badges .= '<span class="badge badge-secondary mt-1 mr-1"><i class="fas fa-ruler-combined mr-1"></i>' . $size . '</span>';
                        return $badges;
                    }
                    return '<span class="badge badge-light border text-muted"><i class="fas fa-box mr-1"></i> Standard (No Variant)</span>';
                })
                ->filterColumn('variant_details', function($query, $keyword) {
                    $query->whereHas('variant', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('sku', function($row){
                    $code = e($row->product?->product_number ?? $row->product?->sku ?? 'N/A');
                    return '<span class="badge badge-light border text-dark font-weight-bold">' . $code . '</span>';
                })
                ->addColumn('unit_price', function($row){
                    return 'kr. ' . number_format($row->product?->purchase_price ?? 0, 2);
                })
                ->addColumn('quantity', function($row){
                    return '<strong class="text-success font-weight-bold" style="font-size: 15px;">' . number_format($row->quantity, 0) . ' Pcs</strong>';
                })
                ->rawColumns(['image', 'product_name', 'variant_details', 'sku', 'quantity'])
                ->make(true);
        }

        $warehouseBin->load(['zone.outlet']);

        // Fast aggregated counts for summary cards (No full object loading!)
        $totalStoredProducts = \App\Models\InventoryStock::where('bin_id', $warehouseBin->id)->where('quantity', '>', 0)->count();
        $totalStockQty = \App\Models\InventoryStock::where('bin_id', $warehouseBin->id)->sum('quantity');
        $activeBatchesCount = \App\Models\StockBatch::where('bin_id', $warehouseBin->id)->where('qty_remaining', '>', 0)->count();
        $totalValuation = \App\Models\StockBatch::where('bin_id', $warehouseBin->id)->where('qty_remaining', '>', 0)->selectRaw('SUM(qty_remaining * unit_cost) as total')->value('total') ?? 0;

        return view('backend.warehouse_bins.stocks', compact('warehouseBin', 'totalStoredProducts', 'totalStockQty', 'activeBatchesCount', 'totalValuation'));
    }

    public function destroy(WarehouseBin $warehouseBin)
    {
        $warehouseBin->delete();
        Toastr::success('Warehouse Bin deleted successfully.', 'Success');
        return redirect()->route('admin.warehouse-bins.index');
    }
}
