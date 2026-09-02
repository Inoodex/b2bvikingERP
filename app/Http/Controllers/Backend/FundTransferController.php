<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\FundTransfer;
use App\Services\Accounting\JournalEntryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FundTransferController extends Controller
{
    protected JournalEntryService $journalService;

    public function __construct(JournalEntryService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function index()
    {
        $transfers = FundTransfer::with(['fromAccount', 'toAccount'])->latest()->paginate(20);
        $bankAccounts = BankAccount::where('status', true)->get();
        $totalTransferred = (float) FundTransfer::sum('amount');

        return view('backend.accounts.fund_transfers.index', compact('transfers', 'bankAccounts', 'totalTransferred'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:bank_accounts,id|different:to_account_id',
            'to_account_id'   => 'required|exists:bank_accounts,id',
            'amount'          => 'required|numeric|min:0.01',
            'transfer_date'   => 'required|date',
            'notes'           => 'nullable|string|max:255',
        ]);

        $amount = (float) $validated['amount'];
        $fromAccount = BankAccount::with('glAccount')->findOrFail($validated['from_account_id']);
        $toAccount = BankAccount::with('glAccount')->findOrFail($validated['to_account_id']);

        if ($fromAccount->current_balance < $amount) {
            toastr()->error("Insufficient balance in {$fromAccount->account_name}! Available: kr. " . number_format($fromAccount->current_balance, 2));
            return redirect()->back()->withInput();
        }

        DB::transaction(function () use ($validated, $amount, $fromAccount, $toAccount) {
            $transfer = FundTransfer::create([
                'from_account_id' => $fromAccount->id,
                'to_account_id'   => $toAccount->id,
                'amount'          => $amount,
                'transfer_date'   => $validated['transfer_date'],
            ]);

            // Adjust bank current balances
            $fromAccount->decrement('current_balance', $amount);
            $toAccount->increment('current_balance', $amount);

            // Double-Entry Contra Voucher Posting
            $fromGlCode = $fromAccount->glAccount ? $fromAccount->glAccount->account_code : '1020';
            $toGlCode = $toAccount->glAccount ? $toAccount->glAccount->account_code : '1020';

            $lines = [
                ['account_code' => $toGlCode, 'debit' => $amount, 'credit' => 0],
                ['account_code' => $fromGlCode, 'debit' => 0, 'credit' => $amount],
            ];

            $this->journalService->recordEntry(
                event: 'fund_transfer',
                sourceModel: $transfer,
                lines: $lines,
                entryDate: $validated['transfer_date'],
                narration: "Contra Fund Transfer: kr. {$amount} from {$fromAccount->account_name} to {$toAccount->account_name}"
            );
        });

        toastr()->success('Fund Transfer (Contra Voucher) recorded and posted successfully!');
        return redirect()->route('admin.fund-transfers.index');
    }
}
