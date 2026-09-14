<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\DataTables\SubCategoryDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubCategory\SubCategoryCreateRequest;
use App\Http\Requests\SubCategory\SubCategoryToggleStatusRequest;
use App\Http\Requests\SubCategory\SubCategoryUpdateRequest;
use App\Models\Category;
use App\Models\SubCategory;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of subcategories via DataTable.
     */
    public function index(SubCategoryDataTable $dataTable): JsonResponse|View
    {
        return $dataTable->render('backend.sub-category.index');
    }

    /**
     * Show the form for creating a new subcategory.
     */
    public function create(): View
    {
        $categories = Category::select('id', 'name', 'status')->get();

        return view('backend.sub-category.create', compact('categories'));
    }

    /**
     * Store a newly created subcategory in storage.
     */
    public function store(SubCategoryCreateRequest $request): RedirectResponse
    {
        SubCategory::create([
            'category_id' => (int) $request->validated('category'),
            'name' => $request->validated('name'),
            'slug' => Str::slug($request->validated('name')),
            'status' => (bool) $request->validated('status'),
        ]);

        Toastr::success(__('Sub Category Created Successfully!'));

        return redirect()->route('admin.sub-category.index');
    }

    /**
     * Show the form for editing the specified subcategory.
     */
    public function edit(SubCategory $subCategory): View
    {
        $categories = Category::select('id', 'name', 'status')->get();

        return view('backend.sub-category.edit', compact('subCategory', 'categories'));
    }

    /**
     * Update the specified subcategory in storage.
     */
    public function update(SubCategoryUpdateRequest $request, SubCategory $subCategory): RedirectResponse
    {
        $subCategory->update([
            'category_id' => (int) $request->validated('category'),
            'name' => $request->validated('name'),
            'slug' => Str::slug($request->validated('name')),
            'status' => (bool) $request->validated('status'),
        ]);

        Toastr::success(__('Sub Category Updated Successfully!'));

        return redirect()->route('admin.sub-category.index');
    }

    /**
     * Remove the specified subcategory from storage with child dependencies check.
     */
    public function destroy(SubCategory $subCategory): JsonResponse
    {
        if ($subCategory->childCategories()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => __('This sub category has child categories. Please delete them first!'),
            ], 422);
        }

        $subCategory->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('Deleted Successfully!'),
        ]);
    }

    /**
     * Toggle publication status of a subcategory.
     */
    public function changeStatus(SubCategoryToggleStatusRequest $request): JsonResponse
    {
        $subCategory = SubCategory::findOrFail((int) $request->validated('id'));
        $status = filter_var($request->validated('status'), FILTER_VALIDATE_BOOLEAN);

        $subCategory->update(['status' => $status]);

        return response()->json([
            'status' => 'success',
            'message' => __('Status Updated Successfully!'),
        ]);
    }
}
