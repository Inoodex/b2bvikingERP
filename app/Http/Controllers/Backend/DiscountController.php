<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\DiscountDataTable;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Discount;
use App\Models\ProductType;
use App\Models\User;
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
        $categories = Category::where('status', 1)->orderBy('name')->get(['id', 'name']);
        $customers = User::orderBy('name')->get(['id', 'name', 'outlet_name', 'email']);
        $productTypes = ProductType::where('status', 1)->orderBy('id')->get(['id', 'name']);
        return view('backend.discount.create', compact('categories', 'customers', 'productTypes'));
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
            'is_seasonal' => 'nullable|boolean',
            'product_type_id' => 'nullable|exists:product_types,id',
            'occasion' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        if ($data['type'] === 'percent' && (float) $data['value'] > 100) {
            return back()->withErrors(['value' => 'Percent discount cannot be greater than 100.'])->withInput();
        }

        $isDefault = (bool) ($data['is_default'] ?? false);
        $status = (bool) $data['status'];
        if ($isDefault) {
            $status = true;
        }

        $isSeasonal = (bool) ($request->is_seasonal ?? false);
        $productTypeId = !empty($data['product_type_id']) ? (int) $data['product_type_id'] : null;

        // Auto-resolve occasion label from ProductType if not explicitly typed
        $occasion = $data['occasion'] ?? null;
        if ($productTypeId && empty($occasion)) {
            $pt = ProductType::find($productTypeId);
            if ($pt) {
                $occasion = $pt->name;
            }
        }

        DB::transaction(function () use ($data, $isDefault, $status, $isSeasonal, $productTypeId, $occasion) {
            if ($isDefault) {
                Discount::query()->update(['is_default' => false]);
            }

            Discount::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'value' => $data['value'],
                'is_seasonal' => $isSeasonal,
                'product_type_id' => $isSeasonal ? $productTypeId : null,
                'occasion' => $isSeasonal ? $occasion : null,
                'start_date' => $isSeasonal ? ($data['start_date'] ?? null) : null,
                'end_date' => $isSeasonal ? ($data['end_date'] ?? null) : null,
                'category_ids' => ($isSeasonal && !empty($data['category_ids'])) ? array_values(array_map('intval', $data['category_ids'])) : null,
                'user_ids' => ($isSeasonal && !empty($data['user_ids'])) ? array_values(array_map('intval', $data['user_ids'])) : null,
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
        $categories = Category::where('status', 1)->orderBy('name')->get(['id', 'name']);
        $customers = User::orderBy('name')->get(['id', 'name', 'outlet_name', 'email']);
        $productTypes = ProductType::where('status', 1)->orderBy('id')->get(['id', 'name']);
        return view('backend.discount.edit', compact('discount', 'categories', 'customers', 'productTypes'));
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
            'is_seasonal' => 'nullable|boolean',
            'product_type_id' => 'nullable|exists:product_types,id',
            'occasion' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        if ($data['type'] === 'percent' && (float) $data['value'] > 100) {
            return back()->withErrors(['value' => 'Percent discount cannot be greater than 100.'])->withInput();
        }

        $isDefault = (bool) ($data['is_default'] ?? false);
        $status = (bool) $data['status'];
        if ($isDefault) {
            $status = true;
        }

        $isSeasonal = (bool) ($request->is_seasonal ?? false);
        $productTypeId = !empty($data['product_type_id']) ? (int) $data['product_type_id'] : null;

        // Auto-resolve occasion label from ProductType if not explicitly typed
        $occasion = $data['occasion'] ?? null;
        if ($productTypeId && empty($occasion)) {
            $pt = ProductType::find($productTypeId);
            if ($pt) {
                $occasion = $pt->name;
            }
        }

        DB::transaction(function () use ($discount, $data, $isDefault, $status, $isSeasonal, $productTypeId, $occasion) {
            if ($isDefault) {
                Discount::query()->where('id', '!=', $discount->id)->update(['is_default' => false]);
            }

            $discount->update([
                'name' => $data['name'],
                'type' => $data['type'],
                'value' => $data['value'],
                'is_seasonal' => $isSeasonal,
                'product_type_id' => $isSeasonal ? $productTypeId : null,
                'occasion' => $isSeasonal ? $occasion : null,
                'start_date' => $isSeasonal ? ($data['start_date'] ?? null) : null,
                'end_date' => $isSeasonal ? ($data['end_date'] ?? null) : null,
                'category_ids' => ($isSeasonal && !empty($data['category_ids'])) ? array_values(array_map('intval', $data['category_ids'])) : null,
                'user_ids' => ($isSeasonal && !empty($data['user_ids'])) ? array_values(array_map('intval', $data['user_ids'])) : null,
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
