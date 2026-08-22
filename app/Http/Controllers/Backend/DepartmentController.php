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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    protected DepartmentService $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public function index(DepartmentDataTable $dataTable)
    {
        return $dataTable->render('backend.master.departments.index');
    }

    public function create(): View
    {
        $companies = Company::active()->get();
        $users = User::staff()->where('status', 1)->get();
        return view('backend.master.departments.create', compact('companies', 'users'));
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $this->departmentService->createDepartment($request->validated());
        Toastr::success('Department Created Successfully!');
        return redirect()->route('admin.master.departments.index');
    }

    public function edit(string $id): View
    {
        $department = Department::findOrFail($id);
        $companies = Company::active()->get();
        $users = User::staff()->where('status', 1)->get();
        return view('backend.master.departments.edit', compact('department', 'companies', 'users'));
    }

    public function update(UpdateDepartmentRequest $request, string $id): RedirectResponse
    {
        $department = Department::findOrFail($id);
        $this->departmentService->updateDepartment($department, $request->validated());
        Toastr::success('Department Updated Successfully!');
        return redirect()->route('admin.master.departments.index');
    }

    public function destroy(string $id): JsonResponse
    {
        $department = Department::findOrFail($id);
        $this->departmentService->deleteDepartment($department);
        return response()->json(['status' => 'success', 'message' => 'Department Deleted Successfully!']);
    }
}
