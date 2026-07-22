<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\DiscountDataTable;
use App\Http\Controllers\Controller;
use App\Models\Discount;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(DiscountDataTable $dataTable)
    {
        return $dataTable->render('backend.discount.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.discount.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:discounts,name',
            'type' => 'required|in:flat,percent',
            'value' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'status' => 'required|boolean',
        ]);

        if ($data['type'] === 'percent' && (float) $data['value'] > 100) {
            return back()->withErrors(['value' => 'Percent discount cannot be greater than 100.'])->withInput();
        }

        $isDefault = (bool) ($data['is_default'] ?? false);
        $status = (bool) $data['status'];
        if ($isDefault) {
            $status = true;
        }

        DB::transaction(function () use ($data, $isDefault, $status) {
            if ($isDefault) {
                Discount::query()->update(['is_default' => false]);
            }

            Discount::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'value' => $data['value'],
                'is_default' => $isDefault,
                'status' => $status,
            ]);
        });

        Toastr::success('Discount Rule Created Successfully!');
        return redirect()->route('admin.discounts.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('admin.discounts.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $discount = Discount::findOrFail($id);
        return view('backend.discount.edit', compact('discount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $discount = Discount::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:discounts,name,' . $discount->id,
            'type' => 'required|in:flat,percent',
            'value' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'status' => 'required|boolean',
        ]);

        if ($data['type'] === 'percent' && (float) $data['value'] > 100) {
            return back()->withErrors(['value' => 'Percent discount cannot be greater than 100.'])->withInput();
        }

        $isDefault = (bool) ($data['is_default'] ?? false);
        $status = (bool) $data['status'];
        if ($isDefault) {
            $status = true;
        }

        DB::transaction(function () use ($discount, $data, $isDefault, $status) {
            if ($isDefault) {
                Discount::query()->where('id', '!=', $discount->id)->update(['is_default' => false]);
            }

            $discount->update([
                'name' => $data['name'],
                'type' => $data['type'],
                'value' => $data['value'],
                'is_default' => $isDefault,
                'status' => $status,
            ]);
        });

        Toastr::success('Discount Rule Updated Successfully!');
        return redirect()->route('admin.discounts.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $discount = Discount::findOrFail($id);

        if ($discount->is_default) {
            return response([
                'status' => 'error',
                'message' => 'Default discount cannot be deleted. Set another default first.',
            ], 422);
        }

        $discount->delete();

        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    public function changeStatus(Request $request)
    {
        $discount = Discount::findOrFail($request->id);
        $newStatus = $request->status == 'true' ? 1 : 0;

        if ($discount->is_default && $newStatus === 0) {
            return response([
                'status' => 'error',
                'message' => 'Default discount cannot be inactive. Set another default first.',
            ], 422);
        }

        $discount->status = $newStatus;
        $discount->save();

        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }

    public function setDefault(Request $request)
    {
        $discount = Discount::findOrFail($request->id);

        DB::transaction(function () use ($discount) {
            Discount::query()->update(['is_default' => false]);
            $discount->is_default = true;
            $discount->status = true;
            $discount->save();
        });

        return response(['status' => 'success', 'message' => 'Default Discount Updated Successfully!']);
    }
}
