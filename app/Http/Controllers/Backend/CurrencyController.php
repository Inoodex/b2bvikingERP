<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CurrencyDataTable;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(CurrencyDataTable $dataTable)
    {
        return $dataTable->render('backend.master.currencies.index');
    }

    public function create()
    {
        return view('backend.master.currencies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:10|unique:currencies,code',
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.00001',
            'is_base' => 'nullable|boolean',
            'status' => 'required|boolean',
        ]);

        $isBase = (bool) ($data['is_base'] ?? false);

        if ($isBase) {
            Currency::query()->update(['is_base' => false]);
            $data['exchange_rate'] = 1.0000;
        }

        Currency::create([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'symbol' => $data['symbol'],
            'exchange_rate' => $data['exchange_rate'],
            'is_base' => $isBase,
            'status' => $data['status'],
        ]);

        Toastr::success('Currency Created Successfully!');
        return redirect()->route('admin.master.currencies.index');
    }

    public function edit(string $id)
    {
        $currency = Currency::findOrFail($id);
        return view('backend.master.currencies.edit', compact('currency'));
    }

    public function update(Request $request, string $id)
    {
        $currency = Currency::findOrFail($id);

        $data = $request->validate([
            'code' => 'required|string|max:10|unique:currencies,code,' . $currency->id,
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.00001',
            'is_base' => 'nullable|boolean',
            'status' => 'required|boolean',
        ]);

        $isBase = (bool) ($data['is_base'] ?? false);

        if ($isBase) {
            Currency::query()->where('id', '!=', $currency->id)->update(['is_base' => false]);
            $data['exchange_rate'] = 1.0000;
        }

        $currency->update([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'symbol' => $data['symbol'],
            'exchange_rate' => $data['exchange_rate'],
            'is_base' => $isBase,
            'status' => $data['status'],
        ]);

        Toastr::success('Currency Updated Successfully!');
        return redirect()->route('admin.master.currencies.index');
    }

    public function destroy(string $id)
    {
        $currency = Currency::findOrFail($id);
        if ($currency->is_base) {
            return response(['status' => 'error', 'message' => 'Cannot delete base currency!']);
        }
        $currency->delete();
        return response(['status' => 'success', 'message' => 'Currency Deleted Successfully!']);
    }
}
