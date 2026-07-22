<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\DepartmentDataTable;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(DepartmentDataTable $dataTable)
    {
        return $dataTable->render('backend.master.departments.index');
    }

    public function create()
    {
        $companies = Company::active()->get();
        $users = User::where('status', 1)->get();
        return view('backend.master.departments.create', compact('companies', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'required|boolean',
        ]);

        Department::create($data);

        Toastr::success('Department Created Successfully!');
        return redirect()->route('admin.master.departments.index');
    }

    public function edit(string $id)
    {
        $department = Department::findOrFail($id);
        $companies = Company::active()->get();
        $users = User::where('status', 1)->get();
        return view('backend.master.departments.edit', compact('department', 'companies', 'users'));
    }

    public function update(Request $request, string $id)
    {
        $department = Department::findOrFail($id);

        $data = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code,' . $department->id,
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'required|boolean',
        ]);

        $department->update($data);

        Toastr::success('Department Updated Successfully!');
        return redirect()->route('admin.master.departments.index');
    }

    public function destroy(string $id)
    {
        $department = Department::findOrFail($id);
        $department->delete();
        return response(['status' => 'success', 'message' => 'Department Deleted Successfully!']);
    }
}
