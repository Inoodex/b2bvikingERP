<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\VendorDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\VendorStoreRequest;
use App\Http\Requests\Vendor\VendorUpdateRequest;
use App\Models\Vendor;
use App\Models\Currency;
use App\Support\AuditLogSupport;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(VendorDataTable $dataTable)
    {
        return $dataTable->render('backend.vendor.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currencies = Currency::where('status', 1)->get();
        return view('backend.vendor.create', compact('currencies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VendorStoreRequest $request)
    {
        $validated = $request->validated();
        $vendor = Vendor::create($validated);

        AuditLogSupport::log([
            'vendor_id' => $vendor->id,
            'module' => 'vendors',
            'action' => 'vendor_created',
            'entity_type' => 'vendor',
            'entity_id' => $vendor->id,
            'reference_no' => $vendor->shop_name,
            'description' => 'Vendor created.',
            'new_values' => $vendor->toArray(),
        ]);

        Toastr::success('Vendor Created Successfully');
        return redirect()->route('admin.vendor.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $vendor = Vendor::findOrFail($id);
        $currencies = Currency::where('status', 1)->get();
        return view('backend.vendor.edit', compact('vendor', 'currencies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VendorUpdateRequest $request, string $id)
    {
        $vendor = Vendor::findOrFail($id);
        $validated = $request->validated();
        $before = $vendor->only(array_keys($validated));
        $vendor->update($validated);

        AuditLogSupport::log([
            'vendor_id' => $vendor->id,
            'module' => 'vendors',
            'action' => 'vendor_updated',
            'entity_type' => 'vendor',
            'entity_id' => $vendor->id,
            'reference_no' => $vendor->shop_name,
            'description' => 'Vendor updated.',
            'old_values' => $before,
            'new_values' => $vendor->only(array_keys($validated)),
        ]);

        Toastr::success('Vendor Updated Successfully');
        return redirect()->route('admin.vendor.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vendor = Vendor::findOrFail($id);

        AuditLogSupport::log([
            'vendor_id' => $vendor->id,
            'module' => 'vendors',
            'action' => 'vendor_deleted',
            'entity_type' => 'vendor',
            'entity_id' => $vendor->id,
            'reference_no' => $vendor->shop_name,
            'description' => 'Vendor deleted.',
            'old_values' => $vendor->toArray(),
        ]);

        $vendor->delete();
        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    public function changeStatus(Request $request) {
        $vendor = Vendor::findOrFail($request->id);
        $beforeStatus = (int) $vendor->status;
        $vendor->status = $request->status == 'true' ? 1 : 0;
        $vendor->save();

        AuditLogSupport::log([
            'vendor_id' => $vendor->id,
            'module' => 'vendors',
            'action' => 'vendor_status_changed',
            'entity_type' => 'vendor',
            'entity_id' => $vendor->id,
            'reference_no' => $vendor->shop_name,
            'description' => 'Vendor status changed.',
            'old_values' => ['status' => $beforeStatus],
            'new_values' => ['status' => (int) $vendor->status],
        ]);

        return response(['message' => 'Status has been updated!']);
    }

    public function getVendorDetails(Request $request)
    {
        $vendor = Vendor::findOrFail($request->id);
        return response()->json($vendor);
    }
}
