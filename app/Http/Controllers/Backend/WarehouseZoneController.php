<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\WarehouseZone;
use App\Models\Outlet;
use App\DataTables\WarehouseZoneDataTable;
use App\Http\Requests\Backend\WarehouseZone\StoreRequest;
use App\Http\Requests\Backend\WarehouseZone\UpdateRequest;
use Brian2694\Toastr\Facades\Toastr;

class WarehouseZoneController extends Controller
{
    public function index(WarehouseZoneDataTable $dataTable)
    {
        return $dataTable->render('backend.warehouse_zones.index');
    }

    public function create()
    {
        $outlets = Outlet::where('status', 1)->get();
        return view('backend.warehouse_zones.create', compact('outlets'));
    }

    public function store(StoreRequest $request)
    {
        WarehouseZone::create($request->validated());
        Toastr::success('Warehouse Zone created successfully.', 'Success');
        return redirect()->route('admin.warehouse-zones.index');
    }

    public function edit(WarehouseZone $warehouseZone)
    {
        $outlets = Outlet::where('status', 1)->get();
        return view('backend.warehouse_zones.edit', compact('warehouseZone', 'outlets'));
    }

    public function update(UpdateRequest $request, WarehouseZone $warehouseZone)
    {
        $warehouseZone->update($request->validated());
        Toastr::success('Warehouse Zone updated successfully.', 'Success');
        return redirect()->route('admin.warehouse-zones.index');
    }

    public function destroy(WarehouseZone $warehouseZone)
    {
        $warehouseZone->delete();
        Toastr::success('Warehouse Zone deleted successfully.', 'Success');
        return redirect()->route('admin.warehouse-zones.index');
    }
}
