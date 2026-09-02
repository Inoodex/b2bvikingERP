<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\PettyCashTransaction;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PettyCashController extends Controller
{
    protected JournalEntryService $journalService;

    public function __construct(JournalEntryService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function index()
    {
        $transactions = PettyCashTransaction::with('creator')->latest()->paginate(20);
        $totalIn = (float) PettyCashTransaction::where('type', 'in')->sum('amount');
        $totalOut = (float) PettyCashTransaction::where('type', 'out')->sum('amount');
        $currentFloat = $totalIn - $totalOut;

        $todayOut = (float) PettyCashTransaction::where('type', 'out')
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount');

        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')
            ->where('is_group', false)
            ->get();

        return view('backend.accounts.petty_cash.index', compact(
            'transactions',
            'currentFloat',
            'todayOut',
            'totalIn',
            'totalOut',
            'expenseAccounts'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'        => 'required|in:in,out',
            'amount'      => 'required|numeric|min:0.01',
            'purpose'     => 'required|string|max:255',
            'expense_acc' => 'nullable|string',
        ]);

        $amount = (float) $validated['amount'];
        $type = $validated['type'];

        DB::transaction(function () use ($validated, $amount, $type) {
            $tx = PettyCashTransaction::create([
                'type'       => $type,
                'amount'     => $amount,
                'purpose'    => $validated['purpose'],
                'created_by' => auth()->id() ?? 1,
            ]);

            // Auto GL Double-Entry Posting
            $lines = [];
            if ($type === 'out') {
                // DR 5010 General Expenses (or specific selected expense) / CR 1010 Cash on Hand
                $expCode = $validated['expense_acc'] ?? '5010';
                $lines = [
                    ['account_code' => $expCode, 'debit' => $amount, 'credit' => 0],
                    ['account_code' => '1010', 'debit' => 0, 'credit' => $amount],
                ];
            } else {
                // DR 1010 Cash on Hand / CR 1020 Bank Account (Replenishment)
                $lines = [
                    ['account_code' => '1010', 'debit' => $amount, 'credit' => 0],
                    ['account_code' => '1020', 'debit' => 0, 'credit' => $amount],
                ];
            }

            $this->journalService->recordEntry(
                event: 'petty_cash_' . $type,
                sourceModel: $tx,
                lines: $lines,
                entryDate: now()->toDateString(),
                narration: "Petty Cash Voucher: {$validated['purpose']}"
            );
        });

        toastr()->success('Petty Cash transaction recorded and posted to GL successfully!');
        return redirect()->route('admin.petty-cash.index');
    }
}
