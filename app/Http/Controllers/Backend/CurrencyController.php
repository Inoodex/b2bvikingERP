<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CurrencyDataTable;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Http\Requests\Backend\Currency\StoreCurrencyRequest;
use App\Http\Requests\Backend\Currency\UpdateCurrencyRequest;
use App\Services\Currency\CurrencyService;

class CurrencyController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }
    public function index(CurrencyDataTable $dataTable)
    {
        return $dataTable->render('backend.master.currencies.index');
    }

    public function create()
    {
        return view('backend.master.currencies.create');
    }

    public function store(StoreCurrencyRequest $request)
    {
        try {
            $this->currencyService->createCurrency($request->validated());
            Toastr::success('Currency Created Successfully!');
            return redirect()->route('admin.master.currencies.index');
        } catch (\Throwable $e) {
            Toastr::error('Failed to create currency: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function edit(string $id)
    {
        $currency = Currency::findOrFail($id);
        return view('backend.master.currencies.edit', compact('currency'));
    }

    public function update(UpdateCurrencyRequest $request, string $id)
    {
        try {
            $currency = Currency::findOrFail($id);
            $this->currencyService->updateCurrency($currency, $request->validated());
            Toastr::success('Currency Updated Successfully!');
            return redirect()->route('admin.master.currencies.index');
        } catch (\Throwable $e) {
            Toastr::error('Failed to update currency: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(string $id)
    {
        try {
            $currency = Currency::findOrFail($id);
            $this->currencyService->deleteCurrency($currency);
            return response(['status' => 'success', 'message' => 'Currency Deleted Successfully!']);
        } catch (\Throwable $e) {
            return response(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
