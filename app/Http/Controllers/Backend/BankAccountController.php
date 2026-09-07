<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\Currency;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 12);
        $perPage = min(50, max(2, $perPage));

        $bankAccounts = BankAccount::with(['glAccount', 'currency', 'company'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $currencies = Currency::all();
        $glAccounts = ChartOfAccount::where('account_type', 'asset')
            ->where('is_group', false)
            ->get();

        $totalBankLiquidity = BankAccount::where('status', true)->sum('current_balance');
        $activeBanksCount = BankAccount::where('status', true)->count();

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
            'company_id'      => auth()->user()->company_id ?? 1,
            'account_name'    => $validated['account_name'],
            'bank_name'       => $validated['bank_name'],
            'account_number'  => $validated['account_number'],
            'currency_id'     => $validated['currency_id'] ?? null,
            'gl_account_id'   => $validated['gl_account_id'] ?? null,
            'opening_balance' => $opening,
            'current_balance' => $opening,
            'status'          => true,
        ]);

        Toastr::success('Bank Account created successfully!');
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

        Toastr::success('Bank Account updated successfully!');
        return redirect()->route('admin.bank-accounts.index');
    }

    public function toggleStatus(BankAccount $bankAccount)
    {
        $bankAccount->status = !$bankAccount->status;
        $bankAccount->save();

        Toastr::success('Bank Account status updated!');
        return redirect()->route('admin.bank-accounts.index');
    }

    public function destroy(Request $request, BankAccount $bankAccount)
    {
        // Enterprise Rule 1: Balance Guard — Strictly block deletion if balance != 0
        if (abs((float) $bankAccount->current_balance) > 0.0001) {
            $formattedBal = number_format((float) $bankAccount->current_balance, 2);
            $msg = "Cannot delete: This account has an active balance of kr. {$formattedBal}. In accordance with accounting compliance, the balance must be transferred to 0.00 first, or you can deactivate the account instead.";
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg]);
            }
            Toastr::error($msg);
            return redirect()->route('admin.bank-accounts.index');
        }

        // Enterprise Rule 2: Transaction History Guard — Block deletion if past transactions or reconciliations exist
        $hasTransactions = DB::table('bank_transactions')->where('bank_account_id', $bankAccount->id)->exists();
        $hasReconciliations = DB::table('bank_reconciliations')->where('bank_account_id', $bankAccount->id)->exists();
        $hasTransfers = DB::table('fund_transfers')
            ->where('from_account_id', $bankAccount->id)
            ->orWhere('to_account_id', $bankAccount->id)
            ->exists();

        if ($hasTransactions || $hasReconciliations || $hasTransfers) {
            $msg = 'Cannot delete: This bank account has linked financial transactions or reconciliations. Please deactivate it to preserve audit history.';
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg]);
            }
            Toastr::error($msg);
            return redirect()->route('admin.bank-accounts.index');
        }

        // Enterprise Rule 3: Zero-Balance & Zero-Transaction Safe Deletion
        $bankAccount->delete();
        $msg = 'Bank Account deleted successfully!';
        if ($request->ajax()) {
            return response()->json(['status' => 'success', 'message' => $msg]);
        }
        Toastr::success($msg);
        return redirect()->route('admin.bank-accounts.index');
    }
}
