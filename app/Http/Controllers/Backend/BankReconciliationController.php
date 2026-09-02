<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $bankAccounts = BankAccount::where('status', true)->get();
        $selectedBankId = $request->query('bank_account_id', $bankAccounts->first()?->id);
        $selectedBank = $selectedBankId ? BankAccount::with('glAccount')->find($selectedBankId) : null;

        $unreconciledTransactions = collect();
        $glBalance = 0;

        if ($selectedBank) {
            $unreconciledTransactions = BankTransaction::where('bank_account_id', $selectedBank->id)
                ->where('reconciled', false)
                ->latest('transaction_date')
                ->get();

            if ($selectedBank->glAccount) {
                $glBalance = $selectedBank->glAccount->balance;
            } else {
                $glBalance = $selectedBank->current_balance;
            }
        }

        $reconciliations = BankReconciliation::with(['bankAccount', 'creator'])->latest()->take(10)->get();

        return view('backend.accounts.banking.reconcile', compact(
            'bankAccounts',
            'selectedBank',
            'unreconciledTransactions',
            'glBalance',
            'reconciliations'
        ));
    }

    public function reconcile(Request $request)
    {
        $validated = $request->validate([
            'bank_account_id'   => 'required|exists:bank_accounts,id',
            'statement_date'    => 'required|date',
            'statement_balance' => 'required|numeric',
            'transaction_ids'   => 'nullable|array',
            'transaction_ids.*' => 'exists:bank_transactions,id',
        ]);

        $bank = BankAccount::with('glAccount')->findOrFail($validated['bank_account_id']);
        $glBalance = $bank->glAccount ? $bank->glAccount->balance : $bank->current_balance;

        DB::transaction(function () use ($validated, $glBalance) {
            if (!empty($validated['transaction_ids'])) {
                BankTransaction::whereIn('id', $validated['transaction_ids'])->update(['reconciled' => true]);
            }

            BankReconciliation::create([
                'bank_account_id'   => $validated['bank_account_id'],
                'statement_date'    => $validated['statement_date'],
                'statement_balance' => $validated['statement_balance'],
                'gl_balance'        => $glBalance,
                'status'            => abs((float)$validated['statement_balance'] - (float)$glBalance) < 0.01 ? 'reconciled' : 'discrepancy',
                'created_by'        => auth()->id() ?? 1,
            ]);
        });

        toastr()->success('Bank Reconciliation recorded successfully!');
        return redirect()->route('admin.bank-reconciliation.index', ['bank_account_id' => $validated['bank_account_id']]);
    }
}
