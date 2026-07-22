<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ChildCategoryDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChildCategory\ChildCategoryCreateRequest;
use App\Http\Requests\ChildCategory\ChildCategoryUpdateRequest;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChildCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ChildCategoryDataTable $dataTable)
    {
        return $dataTable->render('backend.child-category.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::select(['name', 'status', 'id'])->get();
        return view('backend.child-category.create', compact('categories'));
    }

    /**
     * Get sub categories based on category.
     */
    public function getSubCategories(Request $request)
    {
        $subCategories = SubCategory::where('category_id', $request->id)
            ->where('status', 1)
            ->select(['id', 'name'])
            ->get();
        return $subCategories;
    }

    /**
     * Get child categories based on sub category.
     */
    public function getChildCategories(Request $request)
    {
        $childCategories = ChildCategory::where('sub_category_id', $request->id)
            ->where('status', 1)
            ->select(['id', 'name'])
            ->get();
        return $childCategories;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ChildCategoryCreateRequest $request)
    {
        ChildCategory::create([
            'category_id' => $request->category,
            'sub_category_id' => $request->sub_category,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => $request->status,
        ]);
        Toastr::success('Child Category Created Successfully!');
        return redirect()->route('admin.child-category.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $childCategory = ChildCategory::findOrFail($id);
        $categories = Category::select(['name', 'status', 'id'])->get();
        $subCategories = SubCategory::where('category_id', $childCategory->category_id)->get();
        return view('backend.child-category.edit', compact('childCategory', 'categories', 'subCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ChildCategoryUpdateRequest $request, string $id)
    {
        $childCategory = ChildCategory::findOrFail($id);
        $childCategory->update([
            'category_id' => $request->category,
            'sub_category_id' => $request->sub_category,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => $request->status,
        ]);

        Toastr::success('Child Category Updated Successfully!');
        return redirect()->route('admin.child-category.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $childCategory = ChildCategory::findOrFail($id);
        $childCategory->delete();

        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    /**
     * Change child category status.
     */
    public function changeStatus(Request $request)
    {
        $childCategory = ChildCategory::findOrFail($request->id);
        $childCategory->status = $request->status == 'true' ? 1 : 0;
        $childCategory->save();

        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }
}
