<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalEntryService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalVoucherController extends Controller
{
    public function __construct(protected JournalEntryService $journalService) {}

    /**
     * List all manual journal vouchers.
     */
    public function index()
    {
        $vouchers = JournalEntry::where('event', 'manual_jv')
            ->with('lines.account')
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(30);

        return view('backend.accounts.journal_vouchers.index', compact('vouchers'));
    }

    /**
     * Show the manual JV creation form.
     */
    public function create()
    {
        $accounts = ChartOfAccount::where('is_group', false)
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        return view('backend.accounts.journal_vouchers.create', compact('accounts'));
    }

    /**
     * Store the manual JV — validates debit == credit then posts via JournalEntryService.
     */
    public function store(Request $request)
    {
        $request->validate([
            'entry_date'              => 'required|date',
            'narration'               => 'required|string|max:500',
            'lines'                   => 'required|array|min:2',
            'lines.*.account_id'      => 'required|exists:chart_of_accounts,id',
            'lines.*.debit'           => 'required|numeric|min:0',
            'lines.*.credit'          => 'required|numeric|min:0',
        ]);

        $lines = $request->input('lines');

        // Client-side guard: at least one debit > 0 and one credit > 0
        $totalDebit  = collect($lines)->sum('debit');
        $totalCredit = collect($lines)->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.001) {
            return back()->withInput()->withErrors(['lines' => "Imbalance: Total Debit (kr. " . number_format($totalDebit, 2) . ") ≠ Total Credit (kr. " . number_format($totalCredit, 2) . "). Please balance your journal entry."]);
        }

        if ($totalDebit <= 0) {
            return back()->withInput()->withErrors(['lines' => 'Journal entry must have at least one debit and one credit line.']);
        }

        try {
            DB::transaction(function () use ($request, $lines) {
                // Build lines using account codes for JournalEntryService
                $accountMap = ChartOfAccount::whereIn('id', collect($lines)->pluck('account_id'))
                    ->pluck('account_code', 'id');

                $journalLines = array_map(function ($line) use ($accountMap) {
                    return [
                        'account_code' => $accountMap[$line['account_id']],
                        'debit'        => (float) $line['debit'],
                        'credit'       => (float) $line['credit'],
                        'description'  => $line['description'] ?? null,
                    ];
                }, $lines);

                // Post via the central JournalEntryService (fiscal lock + balance validation)
                $this->journalService->postManualJournal(
                    narration:  $request->narration,
                    lines:      $journalLines,
                    entryDate:  $request->entry_date,
                    createdBy:  Auth::id(),
                );
            });

            Toastr::success('Manual Journal Voucher posted successfully to General Ledger.');
            return redirect()->route('admin.journal-vouchers.index');

        } catch (\Throwable $e) {
            Toastr::error('GL Posting Failed: ' . $e->getMessage());
            return back()->withInput();
        }
    }
}
