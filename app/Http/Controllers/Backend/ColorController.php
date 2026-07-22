<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ColorDataTable;
use App\Http\Controllers\Controller;
use App\Models\Color;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ColorDataTable $dataTable)
    {
        return $dataTable->render('backend.color.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.color.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:colors,name|max:255',
            'hex_code' => 'nullable|string|max:7',
            'status' => 'required|boolean'
        ]);

        Color::create([
            'name' => $request->name,
            'hex_code' => $request->hex_code,
            'status' => $request->status
        ]);

        Toastr::success('Color Created Successfully!');
        return redirect()->route('admin.colors.index');
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
        $color = Color::findOrFail($id);
        return view('backend.color.edit', compact('color'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|max:255|unique:colors,name,' . $id,
            'hex_code' => 'nullable|string|max:7',
            'status' => 'required|boolean'
        ]);

        $color = Color::findOrFail($id);
        $color->update([
            'name' => $request->name,
            'hex_code' => $request->hex_code,
            'status' => $request->status
        ]);

        Toastr::success('Color Updated Successfully!');
        return redirect()->route('admin.colors.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $color = Color::findOrFail($id);
        $color->delete();
        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    public function changeStatus(Request $request)
    {
        $color = Color::findOrFail($request->id);
        $color->status = $request->status == 'true' ? 1 : 0;
        $color->save();
        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }
}
