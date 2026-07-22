<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CompanyDataTable;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Currency;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(CompanyDataTable $dataTable)
    {
        return $dataTable->render('backend.master.companies.index');
    }

    public function create()
    {
        $currencies = Currency::active()->get();
        return view('backend.master.companies.create', compact('currencies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'vat_number' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'currency_id' => 'nullable|exists:currencies,id',
            'status' => 'required|boolean',
        ]);

        Company::create($data);

        Toastr::success('Company Created Successfully!');
        return redirect()->route('admin.master.companies.index');
    }

    public function edit(string $id)
    {
        $company = Company::findOrFail($id);
        $currencies = Currency::active()->get();
        return view('backend.master.companies.edit', compact('company', 'currencies'));
    }

    public function update(Request $request, string $id)
    {
        $company = Company::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code,' . $company->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'vat_number' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'currency_id' => 'nullable|exists:currencies,id',
            'status' => 'required|boolean',
        ]);

        $company->update($data);

        Toastr::success('Company Updated Successfully!');
        return redirect()->route('admin.master.companies.index');
    }

    public function destroy(string $id)
    {
        $company = Company::findOrFail($id);
        $company->delete();
        return response(['status' => 'success', 'message' => 'Company Deleted Successfully!']);
    }
}
