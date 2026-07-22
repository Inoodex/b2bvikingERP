<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\SizeDataTable;
use App\Http\Controllers\Controller;
use App\Models\Size;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SizeDataTable $dataTable)
    {
        return $dataTable->render('backend.size.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.size.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:sizes,name|max:255',
            'status' => 'required|boolean'
        ]);

        Size::create([
            'name' => $request->name,
            'status' => $request->status
        ]);

        Toastr::success('Size Created Successfully!');
        return redirect()->route('admin.sizes.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $size = Size::findOrFail($id);
        return view('backend.size.edit', compact('size'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|max:255|unique:sizes,name,' . $id,
            'status' => 'required|boolean'
        ]);

        $size = Size::findOrFail($id);
        $size->update([
            'name' => $request->name,
            'status' => $request->status
        ]);

        Toastr::success('Size Updated Successfully!');
        return redirect()->route('admin.sizes.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $size = Size::findOrFail($id);
        $size->delete();
        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    public function changeStatus(Request $request)
    {
        $size = Size::findOrFail($request->id);
        $size->status = $request->status == 'true' ? 1 : 0;
        $size->save();
        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }
}
