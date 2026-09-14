<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductTypeDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductType\ProductTypeCreateRequest;
use App\Http\Requests\ProductType\ProductTypeToggleStatusRequest;
use App\Http\Requests\ProductType\ProductTypeUpdateRequest;
use App\Models\ProductType;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ProductTypeController extends Controller
{
    /**
     * Display a listing of product types via DataTable.
     */
    public function index(ProductTypeDataTable $dataTable): JsonResponse|View
    {
        return $dataTable->render('backend.product-types.index');
    }

    /**
     * Show the form for creating a new product type.
     */
    public function create(): View
    {
        return view('backend.product-types.create');
    }

    /**
     * Store a newly created product type in storage.
     */
    public function store(ProductTypeCreateRequest $request): RedirectResponse
    {
        ProductType::create([
            'name' => $request->validated('name'),
            'slug' => Str::slug($request->validated('name')),
            'status' => (bool) $request->validated('status'),
        ]);

        Toastr::success(__('Product Type Created Successfully!'));

        return redirect()->route('admin.product-types.index');
    }

    /**
     * Show the form for editing the specified product type.
     */
    public function edit(ProductType $productType): View
    {
        return view('backend.product-types.edit', compact('productType'));
    }

    /**
     * Update the specified product type in storage.
     */
    public function update(ProductTypeUpdateRequest $request, ProductType $productType): RedirectResponse
    {
        $productType->update([
            'name' => $request->validated('name'),
            'slug' => Str::slug($request->validated('name')),
            'status' => (bool) $request->validated('status'),
        ]);

        Toastr::success(__('Product Type Updated Successfully!'));

        return redirect()->route('admin.product-types.index');
    }

    /**
     * Remove the specified product type from storage with relationship safety guard.
     */
    public function destroy(ProductType $productType): JsonResponse
    {
        if ($productType->products()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => __('This type has products assigned. Please reassign them first!'),
            ], 422);
        }

        $productType->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('Deleted Successfully!'),
        ]);
    }

    /**
     * Toggle publication status of a product type.
     */
    public function changeStatus(ProductTypeToggleStatusRequest $request): JsonResponse
    {
        $productType = ProductType::findOrFail((int) $request->validated('id'));
        $status = filter_var($request->validated('status'), FILTER_VALIDATE_BOOLEAN);

        $productType->update(['status' => $status]);

        return response()->json([
            'status' => 'success',
            'message' => __('Status Updated Successfully!'),
        ]);
    }
}
