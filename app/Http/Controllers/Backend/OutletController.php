<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\OutletDataTable;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Outlet;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\Outlet\StoreOutletRequest;
use App\Http\Requests\Backend\Outlet\UpdateOutletRequest;
use App\Services\Outlet\OutletService;

class OutletController extends Controller
{
    protected $outletService;

    public function __construct(OutletService $outletService)
    {
        $this->outletService = $outletService;
    }
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

    public function store(StoreOutletRequest $request)
    {
        try {
            $this->outletService->createOutlet($request->validated());
            Toastr::success('Outlet / Warehouse Created Successfully!');
            return redirect()->route('admin.master.outlets.index');
        } catch (\Throwable $e) {
            Toastr::error('Failed to create outlet: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function edit(string $id)
    {
        $outlet = Outlet::findOrFail($id);
        $companies = Company::active()->get();
        $users = User::where('status', 1)->get();
        return view('backend.master.outlets.edit', compact('outlet', 'companies', 'users'));
    }

    public function update(UpdateOutletRequest $request, string $id)
    {
        try {
            $outlet = Outlet::findOrFail($id);
            $this->outletService->updateOutlet($outlet, $request->validated());
            Toastr::success('Outlet / Warehouse Updated Successfully!');
            return redirect()->route('admin.master.outlets.index');
        } catch (\Throwable $e) {
            Toastr::error('Failed to update outlet: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(string $id)
    {
        try {
            $outlet = Outlet::findOrFail($id);
            $this->outletService->deleteOutlet($outlet);
            return response(['status' => 'success', 'message' => 'Outlet / Warehouse Deleted Successfully!']);
        } catch (\Throwable $e) {
            return response(['status' => 'error', 'message' => 'Failed to delete outlet.']);
        }
    }
}
