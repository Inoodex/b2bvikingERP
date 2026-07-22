<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\SubCategoryDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubCategory\SubCategoryCreateRequest;
use App\Http\Requests\SubCategory\SubCategoryUpdateRequest;
use App\Models\Category;
use App\Models\SubCategory;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SubCategoryDataTable $dataTable)
    {
        return $dataTable->render('backend.sub-category.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::select('id', 'name', 'status')->get();
        return view('backend.sub-category.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubCategoryCreateRequest $request)
    {
        SubCategory::create([
            'category_id' => $request->category,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => $request->status,
        ]);
        Toastr::success('Sub Category Created Successfully!');
        return redirect()->route('admin.sub-category.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $subCategory = SubCategory::findOrFail($id);
        $categories = Category::select('id', 'name', 'status')->get();
        return view('backend.sub-category.edit', compact('subCategory', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubCategoryUpdateRequest $request, string $id)
    {
        $subCategory = SubCategory::findOrFail($id);
        $subCategory->update([
            'category_id' => $request->category,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => $request->status,
        ]);
        Toastr::success('Sub Category Updated Successfully!');
        return redirect()->route('admin.sub-category.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $subCategory = SubCategory::findOrFail($id);
        if ($subCategory->childCategories()->count() > 0) {
            return response(['status' => 'error', 'message' => 'This sub category has child categories. Please delete them first!']);
        }
        $subCategory->delete();

        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    /**
     * Change sub category status.
     */
    public function changeStatus(Request $request)
    {
        $subCategory = SubCategory::findOrFail($request->id);
        $subCategory->status = $request->status == 'true' ? 1 : 0;
        $subCategory->save();

        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }
}
