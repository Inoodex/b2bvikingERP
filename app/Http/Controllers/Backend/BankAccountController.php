<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankAccountController extends Controller
{
    public function index()
    {
        $bankAccounts = BankAccount::with(['glAccount', 'currency', 'company'])->latest()->get();
        $currencies = Currency::all();
        $glAccounts = ChartOfAccount::where('account_type', 'asset')
            ->where('is_group', false)
            ->get();

        $totalBankLiquidity = $bankAccounts->where('status', true)->sum('current_balance');
        $activeBanksCount = $bankAccounts->where('status', true)->count();

        return view('backend.accounts.banking.index', compact(
            'bankAccounts',
            'currencies',
            'glAccounts',
            'totalBankLiquidity',
            'activeBanksCount'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name'    => 'required|string|max:255',
            'bank_name'       => 'required|string|max:255',
            'account_number'  => 'required|string|max:100|unique:bank_accounts,account_number',
            'currency_id'     => 'nullable|exists:currencies,id',
            'gl_account_id'   => 'nullable|exists:chart_of_accounts,id',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        $opening = (float) ($validated['opening_balance'] ?? 0);

        BankAccount::create([
            'company_id'      => 1,
            'account_name'    => $validated['account_name'],
            'bank_name'       => $validated['bank_name'],
            'account_number'  => $validated['account_number'],
            'currency_id'     => $validated['currency_id'] ?? null,
            'gl_account_id'   => $validated['gl_account_id'] ?? null,
            'opening_balance' => $opening,
            'current_balance' => $opening,
            'status'          => true,
        ]);

        toastr()->success('Bank Account created successfully!');
        return redirect()->route('admin.bank-accounts.index');
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'account_name'   => 'required|string|max:255',
            'bank_name'      => 'required|string|max:255',
            'account_number' => 'required|string|max:100|unique:bank_accounts,account_number,' . $bankAccount->id,
            'currency_id'    => 'nullable|exists:currencies,id',
            'gl_account_id'  => 'nullable|exists:chart_of_accounts,id',
        ]);

        $bankAccount->update($validated);

        toastr()->success('Bank Account updated successfully!');
        return redirect()->route('admin.bank-accounts.index');
    }

    public function toggleStatus(BankAccount $bankAccount)
    {
        $bankAccount->status = !$bankAccount->status;
        $bankAccount->save();

        toastr()->success('Bank Account status updated!');
        return redirect()->route('admin.bank-accounts.index');
    }
}
