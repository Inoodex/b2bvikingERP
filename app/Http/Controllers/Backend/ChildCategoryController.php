<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\DataTables\ChildCategoryDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChildCategory\ChildCategoryCreateRequest;
use App\Http\Requests\ChildCategory\ChildCategoryToggleStatusRequest;
use App\Http\Requests\ChildCategory\ChildCategoryUpdateRequest;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\SubCategory;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChildCategoryController extends Controller
{
    /**
     * Display a listing of child categories via DataTable.
     */
    public function index(ChildCategoryDataTable $dataTable): JsonResponse|View
    {
        return $dataTable->render('backend.child-category.index');
    }

    /**
     * Show the form for creating a new child category.
     */
    public function create(): View
    {
        $categories = Category::select(['name', 'status', 'id'])->get();

        return view('backend.child-category.create', compact('categories'));
    }

    /**
     * Get active subcategories based on category ID for cascading dropdowns.
     */
    public function getSubCategories(Request $request): JsonResponse
    {
        $categoryId = (int) $request->query('id', $request->input('id'));
        $subCategories = SubCategory::where('category_id', $categoryId)
            ->where('status', 1)
            ->select(['id', 'name'])
            ->get();

        return response()->json($subCategories);
    }

    /**
     * Get active child categories based on subcategory ID for cascading dropdowns.
     */
    public function getChildCategories(Request $request): JsonResponse
    {
        $subCategoryId = (int) $request->query('id', $request->input('id'));
        $childCategories = ChildCategory::where('sub_category_id', $subCategoryId)
            ->where('status', 1)
            ->select(['id', 'name'])
            ->get();

        return response()->json($childCategories);
    }

    /**
     * Store a newly created child category in storage.
     */
    public function store(ChildCategoryCreateRequest $request): RedirectResponse
    {
        ChildCategory::create([
            'category_id' => (int) $request->validated('category'),
            'sub_category_id' => (int) $request->validated('sub_category'),
            'name' => $request->validated('name'),
            'slug' => Str::slug($request->validated('name')),
            'status' => (bool) $request->validated('status'),
        ]);

        Toastr::success(__('Child Category Created Successfully!'));

        return redirect()->route('admin.child-category.index');
    }

    /**
     * Show the form for editing the specified child category.
     */
    public function edit(ChildCategory $childCategory): View
    {
        $categories = Category::select(['name', 'status', 'id'])->get();
        $subCategories = SubCategory::where('category_id', $childCategory->category_id)->get();

        return view('backend.child-category.edit', compact('childCategory', 'categories', 'subCategories'));
    }

    /**
     * Update the specified child category in storage.
     */
    public function update(ChildCategoryUpdateRequest $request, ChildCategory $childCategory): RedirectResponse
    {
        $childCategory->update([
            'category_id' => (int) $request->validated('category'),
            'sub_category_id' => (int) $request->validated('sub_category'),
            'name' => $request->validated('name'),
            'slug' => Str::slug($request->validated('name')),
            'status' => (bool) $request->validated('status'),
        ]);

        Toastr::success(__('Child Category Updated Successfully!'));

        return redirect()->route('admin.child-category.index');
    }

    /**
     * Remove the specified child category from storage with product association check.
     */
    public function destroy(ChildCategory $childCategory): JsonResponse
    {
        if (Product::where('child_category_id', $childCategory->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => __('This child category has active products. Please delete them first!'),
            ], 422);
        }

        $childCategory->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('Deleted Successfully!'),
        ]);
    }

    /**
     * Toggle publication status of a child category.
     */
    public function changeStatus(ChildCategoryToggleStatusRequest $request): JsonResponse
    {
        $childCategory = ChildCategory::findOrFail((int) $request->validated('id'));
        $status = filter_var($request->validated('status'), FILTER_VALIDATE_BOOLEAN);

        $childCategory->update(['status' => $status]);

        return response()->json([
            'status' => 'success',
            'message' => __('Status Updated Successfully!'),
        ]);
    }
}
