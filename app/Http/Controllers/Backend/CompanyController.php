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

class CompanyController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function index(CompanyDataTable $dataTable)
    {
        return $dataTable->render('backend.master.companies.index');
    }

    public function create()
    {
        $currencies = Currency::active()->get();
        return view('backend.master.companies.create', compact('currencies'));
    }

    public function store(StoreCompanyRequest $request)
    {
        try {
            $this->companyService->createCompany($request->validated());
            Toastr::success('Company Created Successfully!');
            return redirect()->route('admin.master.companies.index');
        } catch (\Throwable $e) {
            Toastr::error('Failed to create company: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function edit(string $id)
    {
        $company = Company::findOrFail($id);
        $currencies = Currency::active()->get();
        return view('backend.master.companies.edit', compact('company', 'currencies'));
    }

    public function update(UpdateCompanyRequest $request, string $id)
    {
        try {
            $company = Company::findOrFail($id);
            $this->companyService->updateCompany($company, $request->validated());
            Toastr::success('Company Updated Successfully!');
            return redirect()->route('admin.master.companies.index');
        } catch (\Throwable $e) {
            Toastr::error('Failed to update company: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(string $id)
    {
        try {
            $company = Company::findOrFail($id);
            $this->companyService->deleteCompany($company);
            return response(['status' => 'success', 'message' => 'Company Deleted Successfully!']);
        } catch (\Throwable $e) {
            return response(['status' => 'error', 'message' => 'Failed to delete company.']);
        }
    }
}
