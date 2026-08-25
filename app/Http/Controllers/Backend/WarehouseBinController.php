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

    public function destroy(WarehouseBin $warehouseBin)
    {
        $warehouseBin->delete();
        Toastr::success('Warehouse Bin deleted successfully.', 'Success');
        return redirect()->route('admin.warehouse-bins.index');
    }
}
