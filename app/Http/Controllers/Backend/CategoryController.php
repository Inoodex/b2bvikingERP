<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\DataTables\CategoryDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryCreateRequest;
use App\Http\Requests\Category\CategoryToggleFrontendShowRequest;
use App\Http\Requests\Category\CategoryToggleStatusRequest;
use App\Http\Requests\Category\CategoryUpdateRequest;
use App\Models\Category;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories via DataTable.
     */
    public function index(CategoryDataTable $dataTable): JsonResponse|View
    {
        return $dataTable->render('backend.category.index');
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        return view('backend.category.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(CategoryCreateRequest $request): RedirectResponse
    {
        Category::create([
            'name' => $request->validated('name'),
            'slug' => Str::slug($request->validated('name')),
            'status' => (bool) $request->validated('status'),
            'frontend_show' => (bool) $request->validated('frontend_show', false),
        ]);

        Toastr::success(__('Category Created Successfully!'));

        return redirect()->route('admin.category.index');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        return view('backend.category.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(CategoryUpdateRequest $request, Category $category): RedirectResponse
    {
        $category->update([
            'name' => $request->validated('name'),
            'slug' => Str::slug($request->validated('name')),
            'status' => (bool) $request->validated('status'),
            'frontend_show' => (bool) $request->validated('frontend_show', false),
        ]);

        Toastr::success(__('Category Updated Successfully!'));

        return redirect()->route('admin.category.index');
    }

    /**
     * Remove the specified category from storage with cascading child safety check.
     */
    public function destroy(Category $category): JsonResponse
    {
        if ($category->subCategories()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => __('This category has subcategories. Please delete them first!'),
            ], 422);
        }

        if ($category->products()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => __('This category has active products. Please delete them first!'),
            ], 422);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('Deleted Successfully!'),
        ]);
    }

    /**
     * Toggle category publication status.
     */
    public function changeStatus(CategoryToggleStatusRequest $request): JsonResponse
    {
        $category = Category::findOrFail((int) $request->validated('id'));
        $status = filter_var($request->validated('status'), FILTER_VALIDATE_BOOLEAN);

        $category->update(['status' => $status]);

        return response()->json([
            'status' => 'success',
            'message' => __('Status Updated Successfully!'),
        ]);
    }

    /**
     * Toggle category frontend visibility.
     */
    public function changeFrontendShow(CategoryToggleFrontendShowRequest $request): JsonResponse
    {
        $category = Category::findOrFail((int) $request->validated('id'));
        $frontendShow = filter_var($request->validated('frontend_show'), FILTER_VALIDATE_BOOLEAN);

        $category->update(['frontend_show' => $frontendShow]);

        return response()->json([
            'status' => 'success',
            'message' => __('Frontend Show Updated Successfully!'),
        ]);
    }
}
