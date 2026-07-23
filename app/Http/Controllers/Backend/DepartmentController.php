<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\DepartmentDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use App\Services\Department\DepartmentService;
use Brian2694\Toastr\Facades\Toastr;

class DepartmentController extends Controller
{
    protected $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

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

    public function store(StoreDepartmentRequest $request)
    {
        try {
            $this->departmentService->createDepartment($request->validated());
            Toastr::success('Department Created Successfully!');
            return redirect()->route('admin.master.departments.index');
        } catch (\Throwable $e) {
            Toastr::error('Failed to create department: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function edit(string $id)
    {
        $department = Department::findOrFail($id);
        $companies = Company::active()->get();
        $users = User::where('status', 1)->get();
        return view('backend.master.departments.edit', compact('department', 'companies', 'users'));
    }

    public function update(UpdateDepartmentRequest $request, string $id)
    {
        try {
            $department = Department::findOrFail($id);
            $this->departmentService->updateDepartment($department, $request->validated());
            Toastr::success('Department Updated Successfully!');
            return redirect()->route('admin.master.departments.index');
        } catch (\Throwable $e) {
            Toastr::error('Failed to update department: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(string $id)
    {
        try {
            $department = Department::findOrFail($id);
            $this->departmentService->deleteDepartment($department);
            return response(['status' => 'success', 'message' => 'Department Deleted Successfully!']);
        } catch (\Throwable $e) {
            return response(['status' => 'error', 'message' => 'Failed to delete department.']);
        }
    }
}
