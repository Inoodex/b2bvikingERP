<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\OutletDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Outlet\StoreOutletRequest;
use App\Http\Requests\Backend\Outlet\UpdateOutletRequest;
use App\Models\Company;
use App\Models\Outlet;
use App\Models\User;
use App\Services\Outlet\OutletService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OutletController extends Controller
{
    protected OutletService $outletService;

    public function __construct(OutletService $outletService)
    {
        $this->outletService = $outletService;
    }

    public function index(OutletDataTable $dataTable)
    {
        return $dataTable->render('backend.master.outlets.index');
    }

    public function create(): View
    {
        $companies = Company::active()->get();
        $users = User::staff()->where('status', 1)->get();
        return view('backend.master.outlets.create', compact('companies', 'users'));
    }

    public function store(StoreOutletRequest $request): RedirectResponse
    {
        $this->outletService->createOutlet($request->validated());
        Toastr::success('Outlet / Warehouse Created Successfully!');
        return redirect()->route('admin.master.outlets.index');
    }

    public function edit(string $id): View
    {
        $outlet = Outlet::findOrFail($id);
        $companies = Company::active()->get();
        $users = User::staff()->where('status', 1)->get();
        return view('backend.master.outlets.edit', compact('outlet', 'companies', 'users'));
    }

    public function update(UpdateOutletRequest $request, string $id): RedirectResponse
    {
        $outlet = Outlet::findOrFail($id);
        $this->outletService->updateOutlet($outlet, $request->validated());
        Toastr::success('Outlet / Warehouse Updated Successfully!');
        return redirect()->route('admin.master.outlets.index');
    }

    public function destroy(string $id): JsonResponse
    {
        $outlet = Outlet::findOrFail($id);
        $this->outletService->deleteOutlet($outlet);
        return response()->json(['status' => 'success', 'message' => 'Outlet / Warehouse Deleted Successfully!']);
    }
}
