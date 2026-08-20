<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CompanyDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\Currency;
use App\Services\Company\CompanyService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompanyController extends Controller
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function index(CompanyDataTable $dataTable)
    {
        return $dataTable->render('backend.master.companies.index');
    }

    public function create(): View
    {
        $currencies = Currency::active()->get();
        return view('backend.master.companies.create', compact('currencies'));
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $this->companyService->createCompany($request->validated());
        Toastr::success('Company Created Successfully!');
        return redirect()->route('admin.master.companies.index');
    }

    public function edit(string $id): View
    {
        $company = Company::findOrFail($id);
        $currencies = Currency::active()->get();
        return view('backend.master.companies.edit', compact('company', 'currencies'));
    }

    public function update(UpdateCompanyRequest $request, string $id): RedirectResponse
    {
        $company = Company::findOrFail($id);
        $this->companyService->updateCompany($company, $request->validated());
        Toastr::success('Company Updated Successfully!');
        return redirect()->route('admin.master.companies.index');
    }

    public function destroy(string $id): JsonResponse
    {
        $company = Company::findOrFail($id);
        $this->companyService->deleteCompany($company);
        return response()->json(['status' => 'success', 'message' => 'Company Deleted Successfully!']);
    }
}
