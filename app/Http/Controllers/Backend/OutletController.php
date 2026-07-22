<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\OutletDataTable;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Outlet;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function index(OutletDataTable $dataTable)
    {
        return $dataTable->render('backend.master.outlets.index');
    }

    public function create()
    {
        $companies = Company::active()->get();
        $users = User::where('status', 1)->get();
        return view('backend.master.outlets.create', compact('companies', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:outlets,code',
            'type' => 'required|in:warehouse,retail,wholesale',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'required|boolean',
        ]);

        Outlet::create($data);

        Toastr::success('Outlet / Warehouse Created Successfully!');
        return redirect()->route('admin.master.outlets.index');
    }

    public function edit(string $id)
    {
        $outlet = Outlet::findOrFail($id);
        $companies = Company::active()->get();
        $users = User::where('status', 1)->get();
        return view('backend.master.outlets.edit', compact('outlet', 'companies', 'users'));
    }

    public function update(Request $request, string $id)
    {
        $outlet = Outlet::findOrFail($id);

        $data = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:outlets,code,' . $outlet->id,
            'type' => 'required|in:warehouse,retail,wholesale',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'required|boolean',
        ]);

        $outlet->update($data);

        Toastr::success('Outlet / Warehouse Updated Successfully!');
        return redirect()->route('admin.master.outlets.index');
    }

    public function destroy(string $id)
    {
        $outlet = Outlet::findOrFail($id);
        $outlet->delete();
        return response(['status' => 'success', 'message' => 'Outlet / Warehouse Deleted Successfully!']);
    }
}
