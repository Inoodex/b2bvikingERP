<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\GeneralLedgerDataTable;
use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialReportController extends Controller
{
    /**
     * Build a date-filtered aggregate: account_id => [debit, credit]
     * Single SQL query — eliminates N+1 across all reports.
     */
    private function buildAggregate(?string $dateFrom, ?string $dateTo, ?string $asOfDate = null): array
    {
        $query = JournalEntryLine::select(
                'account_id',
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(credit) as total_credit')
            )
            ->groupBy('account_id');

        if ($dateFrom || $dateTo || $asOfDate) {
            $query->whereHas('journalEntry', function ($q) use ($dateFrom, $dateTo, $asOfDate) {
                if ($dateFrom)  $q->whereDate('entry_date', '>=', $dateFrom);
                if ($dateTo)    $q->whereDate('entry_date', '<=', $dateTo);
                if ($asOfDate)  $q->whereDate('entry_date', '<=', $asOfDate);
            });
        }

        $results = [];
        foreach ($query->get() as $row) {
            $results[$row->account_id] = [
                'debit'  => (float) $row->total_debit,
                'credit' => (float) $row->total_credit,
            ];
        }
        return $results;
    }

    /**
     * General Ledger Report using Yajra DataTables
     */
    public function generalLedger(GeneralLedgerDataTable $dataTable, Request $request)
    {
        $accounts = ChartOfAccount::where('is_group', false)->where('is_active', true)->orderBy('account_code')->get();

        $selectedAccountId = $request->get('account_id');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        $query = JournalEntryLine::query();

        if ($dateFrom || $dateTo) {
            $query->whereHas('journalEntry', function ($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) $q->whereDate('entry_date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('entry_date', '<=', $dateTo);
            });
        }

        if ($selectedAccountId) {
            $query->where('account_id', $selectedAccountId);
        }

        $totalDebit  = (clone $query)->sum('debit');
        $totalCredit = (clone $query)->sum('credit');

        return $dataTable->render('backend.reports.financial.general_ledger', compact(
            'accounts', 'selectedAccountId', 'dateFrom', 'dateTo', 'totalDebit', 'totalCredit'
        ));
    }

    /**
     * Trial Balance Report — single aggregate query (no N+1)
     */
    public function trialBalance(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        $accounts   = ChartOfAccount::where('is_group', false)->orderBy('account_code')->get();
        $aggregates = $this->buildAggregate($dateFrom, $dateTo);

        $reportData     = [];
        $totalDebitSum  = 0;
        $totalCreditSum = 0;

        foreach ($accounts as $acc) {
            $debitSum  = $aggregates[$acc->id]['debit']  ?? 0.0;
            $creditSum = $aggregates[$acc->id]['credit'] ?? 0.0;

            $netDebit = $netCredit = 0;

            if ($acc->normal_balance === 'debit') {
                $diff = $debitSum - $creditSum;
                if ($diff >= 0) $netDebit = $diff;
                else            $netCredit = abs($diff);
            } else {
                $diff = $creditSum - $debitSum;
                if ($diff >= 0) $netCredit = $diff;
                else            $netDebit = abs($diff);
            }

            if ($debitSum > 0 || $creditSum > 0) {
                $reportData[] = [
                    'account_code'   => $acc->account_code,
                    'account_name'   => $acc->account_name,
                    'account_type'   => $acc->account_type,
                    'normal_balance' => $acc->normal_balance,
                    'debit'          => $netDebit,
                    'credit'         => $netCredit,
                ];

                $totalDebitSum  += $netDebit;
                $totalCreditSum += $netCredit;
            }
        }

        return view('backend.reports.financial.trial_balance', compact(
            'reportData', 'totalDebitSum', 'totalCreditSum', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Profit & Loss (Income Statement) — single aggregate query (no N+1)
     */
    public function profitAndLoss(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        // Single aggregate for all revenue + expense accounts
        $aggregates = $this->buildAggregate($dateFrom, $dateTo);

        $revenueAccounts = ChartOfAccount::where('account_type', 'revenue')->where('is_group', false)->get();
        $revenueData = [];
        $totalRevenue = 0;

        foreach ($revenueAccounts as $acc) {
            $credit = $aggregates[$acc->id]['credit'] ?? 0.0;
            $debit  = $aggregates[$acc->id]['debit']  ?? 0.0;
            $amount = $credit - $debit;
            if ($amount != 0) {
                $revenueData[] = ['name' => $acc->account_name, 'code' => $acc->account_code, 'amount' => $amount];
                $totalRevenue  += $amount;
            }
        }

        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')->where('is_group', false)->get();
        $expenseData = [];
        $totalExpense = 0;

        foreach ($expenseAccounts as $acc) {
            $debit  = $aggregates[$acc->id]['debit']  ?? 0.0;
            $credit = $aggregates[$acc->id]['credit'] ?? 0.0;
            $amount = $debit - $credit;
            if ($amount != 0) {
                $expenseData[] = ['name' => $acc->account_name, 'code' => $acc->account_code, 'amount' => $amount];
                $totalExpense  += $amount;
            }
        }

        $netProfit = $totalRevenue - $totalExpense;

        return view('backend.reports.financial.profit_loss', compact(
            'revenueData', 'expenseData', 'totalRevenue', 'totalExpense', 'netProfit', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Balance Sheet Report — single aggregate query (no N+1)
     */
    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->get('as_of_date');

        // Single aggregate for all asset, liability, equity accounts
        $aggregates = $this->buildAggregate(null, null, $asOfDate);

        // Assets
        $assetsData  = [];
        $totalAssets = 0;
        foreach (ChartOfAccount::where('account_type', 'asset')->where('is_group', false)->get() as $acc) {
            $debit  = $aggregates[$acc->id]['debit']  ?? 0.0;
            $credit = $aggregates[$acc->id]['credit'] ?? 0.0;
            $val    = $acc->normal_balance === 'credit' ? ($credit - $debit) : ($debit - $credit);
            if ($val != 0) {
                $assetsData[] = ['name' => $acc->account_name, 'code' => $acc->account_code, 'amount' => $val];
                $totalAssets  += $val;
            }
        }

        // Liabilities
        $liabilitiesData  = [];
        $totalLiabilities = 0;
        foreach (ChartOfAccount::where('account_type', 'liability')->where('is_group', false)->get() as $acc) {
            $credit = $aggregates[$acc->id]['credit'] ?? 0.0;
            $debit  = $aggregates[$acc->id]['debit']  ?? 0.0;
            $val    = $credit - $debit;
            if ($val != 0) {
                $liabilitiesData[] = ['name' => $acc->account_name, 'code' => $acc->account_code, 'amount' => $val];
                $totalLiabilities  += $val;
            }
        }

        // Equity
        $equityData  = [];
        $totalEquity = 0;
        foreach (ChartOfAccount::where('account_type', 'equity')->where('is_group', false)->get() as $acc) {
            $credit = $aggregates[$acc->id]['credit'] ?? 0.0;
            $debit  = $aggregates[$acc->id]['debit']  ?? 0.0;
            $val    = $credit - $debit;
            if ($val != 0) {
                $equityData[] = ['name' => $acc->account_name, 'code' => $acc->account_code, 'amount' => $val];
                $totalEquity  += $val;
            }
        }

        return view('backend.reports.financial.balance_sheet', compact(
            'assetsData', 'liabilitiesData', 'equityData',
            'totalAssets', 'totalLiabilities', 'totalEquity', 'asOfDate'
        ));
    }
}
