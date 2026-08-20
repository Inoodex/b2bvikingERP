<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CurrencyDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Currency\StoreCurrencyRequest;
use App\Http\Requests\Backend\Currency\UpdateCurrencyRequest;
use App\Models\Currency;
use App\Services\Currency\CurrencyService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function index(CurrencyDataTable $dataTable)
    {
        return $dataTable->render('backend.master.currencies.index');
    }

    public function create(): View
    {
        return view('backend.master.currencies.create');
    }

    public function store(StoreCurrencyRequest $request): RedirectResponse
    {
        $this->currencyService->createCurrency($request->validated());
        Toastr::success('Currency Created Successfully!');
        return redirect()->route('admin.master.currencies.index');
    }

    public function edit(string $id): View
    {
        $currency = Currency::findOrFail($id);
        return view('backend.master.currencies.edit', compact('currency'));
    }

    public function update(UpdateCurrencyRequest $request, string $id): RedirectResponse
    {
        $currency = Currency::findOrFail($id);
        $this->currencyService->updateCurrency($currency, $request->validated());
        Toastr::success('Currency Updated Successfully!');
        return redirect()->route('admin.master.currencies.index');
    }

    public function destroy(string $id): JsonResponse
    {
        $currency = Currency::findOrFail($id);
        $this->currencyService->deleteCurrency($currency);
        return response()->json(['status' => 'success', 'message' => 'Currency Deleted Successfully!']);
    }
}
