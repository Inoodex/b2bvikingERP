<?php
namespace App\Http\Controllers\Backend;

use App\DataTables\ProductTypeDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductType\ProductTypeCreateRequest;
use App\Http\Requests\ProductType\ProductTypeUpdateRequest;
use App\Models\ProductType;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ProductTypeDataTable $dataTable)
    {
        return $dataTable->render('backend.product-types.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.product-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductTypeCreateRequest $request)
    {
        ProductType::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => $request->status,
        ]);

        Toastr::success('Product Type Created Successfully!');
        return redirect()->route('admin.product-types.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $productType = ProductType::findOrFail($id);
        return view('backend.product-types.edit', compact('productType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductTypeUpdateRequest $request, string $id)
    {
        $productType = ProductType::findOrFail($id);
        $productType->name = $request->name;
        $productType->slug = Str::slug($request->name);
        $productType->status = $request->status;
        $productType->save();

        Toastr::success('Product Type Updated Successfully!');
        return redirect()->route('admin.product-types.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productType = ProductType::findOrFail($id);
        
        // Optional: Check if products are assigned to this type
        if ($productType->products()->count() > 0) {
            return response(['status' => 'error', 'message' => 'This type has products assigned. Please reassign them first!']);
        }
        
        $productType->delete();
        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    /**
     * Change product type status.
     */
    public function changeStatus(Request $request)
    {
        $productType = ProductType::findOrFail($request->id);
        $productType->status = $request->status == 'true' ? 1 : 0;
        $productType->save();

        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }
}
