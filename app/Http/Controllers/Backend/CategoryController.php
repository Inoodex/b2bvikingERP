<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CategoryDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryCreateRequest;
use App\Http\Requests\Category\CategoryUpdateRequest;
use App\Models\Category;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CategoryDataTable $dataTable)
    {
        return $dataTable->render('backend.category.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryCreateRequest $request)
    {
        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => $request->status,
            'frontend_show' => $request->boolean('frontend_show'),
        ]);

        Toastr::success('Category Created Successfully!');
        return redirect()->route('admin.category.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::findOrFail($id);
        return view('backend.category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryUpdateRequest $request, string $id)
    {
        // dd($request->all());
        $category = Category::findOrFail($id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->status = $request->status;
        $category->frontend_show = $request->boolean('frontend_show');
        $category->save();

        Toastr::success('Category Updated Successfully!');
        return redirect()->route('admin.category.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        // Check if there are subcategories before deleting
        if ($category->subCategories()->count() > 0) {
            return response(['status' => 'error', 'message' => 'This category has subcategories. Please delete them first!']);
        }
        $category->delete();
        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    /**
     * Change category status.
     */
    public function changeStatus(Request $request)
    {
        $category = Category::findOrFail($request->id);
        $category->status = $request->status == 'true' ? 1 : 0;
        $category->save();

        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }

    /**
     * Change category frontend show status.
     */
    public function changeFrontendShow(Request $request)
    {
        $category = Category::findOrFail($request->id);
        $category->frontend_show = $request->frontend_show == 'true' ? 1 : 0;
        $category->save();

        return response(['status' => 'success', 'message' => 'Frontend Show Updated Successfully!']);
    }
}
