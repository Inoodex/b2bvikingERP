<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\FiscalYear;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class JournalEntryService
{
    /**
     * Post an automated balanced Journal Entry with strict Enterprise Governance.
     *
     * @param string $event
     * @param Model $sourceModel
     * @param array $lines Array of ['account_code' => '1010', 'debit' => 100, 'credit' => 0]
     * @param string|null $date
     * @param string|null $narration
     * @return JournalEntry
     * @throws Exception
     */
    public function recordEntry(string $event, Model $sourceModel, array $lines, ?string $entryDate = null, ?string $narration = null): JournalEntry
    {
        return $this->postJournal($event, $sourceModel, $lines, $entryDate, $narration);
    }

    public function postJournal(string $event, Model $sourceModel, array $lines, ?string $date = null, ?string $narration = null): JournalEntry
    {
        $entryDate = $date ? date('Y-m-d', strtotime($date)) : now()->toDateString();

        // 1. Fiscal Period Lock Enforcement
        $closedFiscalYear = FiscalYear::where('is_closed', true)
            ->whereDate('start_date', '<=', $entryDate)
            ->whereDate('end_date', '>=', $entryDate)
            ->first();

        if ($closedFiscalYear) {
            throw new Exception("Posting Blocked: Fiscal Year '{$closedFiscalYear->name}' for date {$entryDate} is CLOSED. No transactions can be posted.");
        }

        // 2. Validate Zero-Imbalance Invariant: SUM(Debit) == SUM(Credit)
        $totalDebit = round((float)collect($lines)->sum('debit'), 2);
        $totalCredit = round((float)collect($lines)->sum('credit'), 2);

        if (abs($totalDebit - $totalCredit) > 0.001) {
            throw new Exception("Journal Entry Imbalance Exception: Total Debit ({$totalDebit}) does not match Total Credit ({$totalCredit})!");
        }

        return DB::transaction(function () use ($event, $sourceModel, $lines, $entryDate, $narration) {
            // Generate unique JV entry number: JV-YYYYMM-XXXXX (Atomic & Collision-Proof)
            $prefix = 'JV-' . date('Ym', strtotime($entryDate)) . '-';
            $count = JournalEntry::where('entry_no', 'like', $prefix . '%')->lockForUpdate()->count() + 1;
            $entryNo = $prefix . str_pad($count, 5, '0', STR_PAD_LEFT);
            if (JournalEntry::where('entry_no', $entryNo)->exists()) {
                $entryNo = $prefix . strtoupper(bin2hex(random_bytes(3)));
            }

            $entry = JournalEntry::create([
                'entry_no'       => $entryNo,
                'entry_date'     => $entryDate,
                'reference_type' => $sourceModel->getMorphClass(),
                'reference_id'   => $sourceModel->id,
                'narration'      => $narration ?? "Automated journal posting for {$event} #{$sourceModel->id}",
                'created_by'     => auth()->id() ?? 1,
            ]);

            foreach ($lines as $line) {
                $debit = (float)($line['debit'] ?? 0);
                $credit = (float)($line['credit'] ?? 0);

                if ($debit == 0 && $credit == 0) {
                    continue;
                }

                $account = ChartOfAccount::where('account_code', $line['account_code'])->first();

                if (!$account) {
                    throw new Exception("Chart of Account with code '{$line['account_code']}' not found in system!");
                }

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $account->id,
                    'debit'            => $debit,
                    'credit'           => $credit,
                ]);
            }

            return $entry;
        });
    }

    /**
     * Post a Manual Journal Voucher (JV) entered directly by an accountant.
     * Lines are passed with account_code (same format as recordEntry).
     * Enforces Fiscal Lock + Zero-Imbalance — same governance as automated postings.
     *
     * @param string $narration  Description of the JV
     * @param array  $lines      [['account_code' => '1010', 'debit' => 100, 'credit' => 0], ...]
     * @param string $entryDate  Date in Y-m-d format
     * @param int|null $createdBy  User ID
     * @return JournalEntry
     */
    public function postManualJournal(string $narration, array $lines, string $entryDate, ?int $createdBy = null): JournalEntry
    {
        $entryDate = date('Y-m-d', strtotime($entryDate));

        // Fiscal Period Lock
        $closedFiscalYear = FiscalYear::where('is_closed', true)
            ->whereDate('start_date', '<=', $entryDate)
            ->whereDate('end_date', '>=', $entryDate)
            ->first();

        if ($closedFiscalYear) {
            throw new Exception("Posting Blocked: Fiscal Year '{$closedFiscalYear->name}' is CLOSED.");
        }

        // Zero-Imbalance Invariant
        $totalDebit  = round((float) collect($lines)->sum('debit'), 2);
        $totalCredit = round((float) collect($lines)->sum('credit'), 2);

        if (abs($totalDebit - $totalCredit) > 0.001) {
            throw new Exception("Journal Imbalance: Debit ({$totalDebit}) ≠ Credit ({$totalCredit})!");
        }

        return DB::transaction(function () use ($narration, $lines, $entryDate, $createdBy) {
            $prefix = 'MJV-' . date('Ym', strtotime($entryDate)) . '-';
            $count  = JournalEntry::where('entry_no', 'like', $prefix . '%')->lockForUpdate()->count() + 1;
            $entryNo = $prefix . str_pad($count, 5, '0', STR_PAD_LEFT);

            $entry = JournalEntry::create([
                'entry_no'       => $entryNo,
                'entry_date'     => $entryDate,
                'reference_type' => 'manual_jv',
                'reference_id'   => null,
                'narration'      => $narration,
                'created_by'     => $createdBy ?? auth()->id() ?? 1,
            ]);

            foreach ($lines as $line) {
                $debit  = (float) ($line['debit']  ?? 0);
                $credit = (float) ($line['credit'] ?? 0);
                if ($debit == 0 && $credit == 0) continue;

                // Support both account_code and account_id
                if (!empty($line['account_code'])) {
                    $account = ChartOfAccount::where('account_code', $line['account_code'])->first();
                } else {
                    $account = ChartOfAccount::find($line['account_id'] ?? null);
                }

                if (!$account) {
                    throw new Exception("GL Account not found for line: " . ($line['account_code'] ?? $line['account_id'] ?? '?'));
                }

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $account->id,
                    'debit'            => $debit,
                    'credit'           => $credit,
                ]);
            }

            return $entry;
        });
    }
}
