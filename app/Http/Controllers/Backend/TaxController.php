<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\TaxDataTable;
use App\Http\Controllers\Controller;
use App\Models\Tax;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TaxDataTable $dataTable)
    {
        return $dataTable->render('backend.tax.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.tax.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:taxes,name',
            'type' => 'required|in:flat,percent',
            'value' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'status' => 'required|boolean',
        ]);

        if ($data['type'] === 'percent' && (float) $data['value'] > 100) {
            return back()->withErrors(['value' => 'Percent tax cannot be greater than 100.'])->withInput();
        }

        $isDefault = (bool) ($data['is_default'] ?? false);
        $status = (bool) $data['status'];
        if ($isDefault) {
            $status = true;
        }

        DB::transaction(function () use ($data, $isDefault, $status) {
            if ($isDefault) {
                Tax::query()->update(['is_default' => false]);
            }

            Tax::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'value' => $data['value'],
                'is_default' => $isDefault,
                'status' => $status,
            ]);
        });

        Toastr::success('Tax / VAT Created Successfully!');
        return redirect()->route('admin.taxes.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('admin.taxes.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $tax = Tax::findOrFail($id);
        return view('backend.tax.edit', compact('tax'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tax = Tax::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:taxes,name,' . $tax->id,
            'type' => 'required|in:flat,percent',
            'value' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'status' => 'required|boolean',
        ]);

        if ($data['type'] === 'percent' && (float) $data['value'] > 100) {
            return back()->withErrors(['value' => 'Percent tax cannot be greater than 100.'])->withInput();
        }

        $isDefault = (bool) ($data['is_default'] ?? false);
        $status = (bool) $data['status'];
        if ($isDefault) {
            $status = true;
        }

        DB::transaction(function () use ($tax, $data, $isDefault, $status) {
            if ($isDefault) {
                Tax::query()->where('id', '!=', $tax->id)->update(['is_default' => false]);
            }

            $tax->update([
                'name' => $data['name'],
                'type' => $data['type'],
                'value' => $data['value'],
                'is_default' => $isDefault,
                'status' => $status,
            ]);
        });

        Toastr::success('Tax / VAT Updated Successfully!');
        return redirect()->route('admin.taxes.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tax = Tax::findOrFail($id);

        if ($tax->is_default) {
            return response([
                'status' => 'error',
                'message' => 'Default tax cannot be deleted. Set another default first.',
            ], 422);
        }

        $tax->delete();

        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    public function changeStatus(Request $request)
    {
        $tax = Tax::findOrFail($request->id);
        $newStatus = $request->status == 'true' ? 1 : 0;

        if ($tax->is_default && $newStatus === 0) {
            return response([
                'status' => 'error',
                'message' => 'Default tax cannot be inactive. Set another default first.',
            ], 422);
        }

        $tax->status = $newStatus;
        $tax->save();

        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }

    public function setDefault(Request $request)
    {
        $tax = Tax::findOrFail($request->id);

        DB::transaction(function () use ($tax) {
            Tax::query()->update(['is_default' => false]);
            $tax->is_default = true;
            $tax->status = true;
            $tax->save();
        });

        return response(['status' => 'success', 'message' => 'Default Tax Updated Successfully!']);
    }
}
