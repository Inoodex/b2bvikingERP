<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\GeneralLedgerDataTable;
use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    /**
     * General Ledger Report using Yajra DataTables
     */
    public function generalLedger(GeneralLedgerDataTable $dataTable, Request $request)
    {
        $accounts = ChartOfAccount::where('is_group', false)->where('is_active', true)->orderBy('account_code')->get();

        $selectedAccountId = $request->get('account_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = JournalEntryLine::query();

        if ($dateFrom || $dateTo) {
            $query->whereHas('journalEntry', function ($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) $q->whereDate('entry_date', '>=', $dateFrom);
                if ($dateTo) $q->whereDate('entry_date', '<=', $dateTo);
            });
        }

        if ($selectedAccountId) {
            $query->where('account_id', $selectedAccountId);
        }

        $totalDebit = (clone $query)->sum('debit');
        $totalCredit = (clone $query)->sum('credit');

        return $dataTable->render('backend.reports.financial.general_ledger', compact('accounts', 'selectedAccountId', 'dateFrom', 'dateTo', 'totalDebit', 'totalCredit'));
    }

    /**
     * Trial Balance Report
     */
    public function trialBalance(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $accounts = ChartOfAccount::where('is_group', false)->orderBy('account_code')->get();

        $reportData = [];
        $totalDebitSum = 0;
        $totalCreditSum = 0;

        foreach ($accounts as $acc) {
            $query = JournalEntryLine::where('account_id', $acc->id);

            if ($dateFrom || $dateTo) {
                $query->whereHas('journalEntry', function ($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $q->whereDate('entry_date', '>=', $dateFrom);
                    if ($dateTo) $q->whereDate('entry_date', '<=', $dateTo);
                });
            }

            $debitSum = (float) (clone $query)->sum('debit');
            $creditSum = (float) (clone $query)->sum('credit');

            $netDebit = 0;
            $netCredit = 0;

            if ($acc->normal_balance === 'debit') {
                $diff = $debitSum - $creditSum;
                if ($diff >= 0) $netDebit = $diff;
                else $netCredit = abs($diff);
            } else {
                $diff = $creditSum - $debitSum;
                if ($diff >= 0) $netCredit = $diff;
                else $netDebit = abs($diff);
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

                $totalDebitSum += $netDebit;
                $totalCreditSum += $netCredit;
            }
        }

        return view('backend.reports.financial.trial_balance', compact('reportData', 'totalDebitSum', 'totalCreditSum', 'dateFrom', 'dateTo'));
    }

    /**
     * Profit & Loss (Income Statement) Report
     */
    public function profitAndLoss(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Revenue Accounts
        $revenueAccounts = ChartOfAccount::where('account_type', 'revenue')->where('is_group', false)->get();
        $revenueData = [];
        $totalRevenue = 0;

        foreach ($revenueAccounts as $acc) {
            $query = JournalEntryLine::where('account_id', $acc->id);
            if ($dateFrom || $dateTo) {
                $query->whereHas('journalEntry', function ($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $q->whereDate('entry_date', '>=', $dateFrom);
                    if ($dateTo) $q->whereDate('entry_date', '<=', $dateTo);
                });
            }

            $credit = (float) (clone $query)->sum('credit');
            $debit = (float) (clone $query)->sum('debit');

            $amount = $credit - $debit;
            if ($amount != 0) {
                $revenueData[] = ['name' => $acc->account_name, 'code' => $acc->account_code, 'amount' => $amount];
                $totalRevenue += $amount;
            }
        }

        // Expense Accounts
        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')->where('is_group', false)->get();
        $expenseData = [];
        $totalExpense = 0;

        foreach ($expenseAccounts as $acc) {
            $query = JournalEntryLine::where('account_id', $acc->id);
            if ($dateFrom || $dateTo) {
                $query->whereHas('journalEntry', function ($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $q->whereDate('entry_date', '>=', $dateFrom);
                    if ($dateTo) $q->whereDate('entry_date', '<=', $dateTo);
                });
            }

            $debit = (float) (clone $query)->sum('debit');
            $credit = (float) (clone $query)->sum('credit');

            $amount = $debit - $credit;
            if ($amount != 0) {
                $expenseData[] = ['name' => $acc->account_name, 'code' => $acc->account_code, 'amount' => $amount];
                $totalExpense += $amount;
            }
        }

        $netProfit = $totalRevenue - $totalExpense;

        return view('backend.reports.financial.profit_loss', compact('revenueData', 'expenseData', 'totalRevenue', 'totalExpense', 'netProfit', 'dateFrom', 'dateTo'));
    }

    /**
     * Balance Sheet Report
     */
    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->get('as_of_date');

        // Asset Accounts
        $assetAccounts = ChartOfAccount::where('account_type', 'asset')->where('is_group', false)->get();
        $assetsData = [];
        $totalAssets = 0;

        foreach ($assetAccounts as $acc) {
            $query = JournalEntryLine::where('account_id', $acc->id);
            if ($asOfDate) {
                $query->whereHas('journalEntry', fn($q) => $q->whereDate('entry_date', '<=', $asOfDate));
            }

            $debit = (float) (clone $query)->sum('debit');
            $credit = (float) (clone $query)->sum('credit');

            $val = $acc->normal_balance === 'credit' ? ($credit - $debit) : ($debit - $credit);
            if ($val != 0) {
                $assetsData[] = ['name' => $acc->account_name, 'code' => $acc->account_code, 'amount' => $val];
                $totalAssets += $val;
            }
        }

        // Liability Accounts
        $liabilityAccounts = ChartOfAccount::where('account_type', 'liability')->where('is_group', false)->get();
        $liabilitiesData = [];
        $totalLiabilities = 0;

        foreach ($liabilityAccounts as $acc) {
            $query = JournalEntryLine::where('account_id', $acc->id);
            if ($asOfDate) {
                $query->whereHas('journalEntry', fn($q) => $q->whereDate('entry_date', '<=', $asOfDate));
            }

            $credit = (float) (clone $query)->sum('credit');
            $debit = (float) (clone $query)->sum('debit');

            $val = $credit - $debit;
            if ($val != 0) {
                $liabilitiesData[] = ['name' => $acc->account_name, 'code' => $acc->account_code, 'amount' => $val];
                $totalLiabilities += $val;
            }
        }

        // Equity Accounts
        $equityAccounts = ChartOfAccount::where('account_type', 'equity')->where('is_group', false)->get();
        $equityData = [];
        $totalEquity = 0;

        foreach ($equityAccounts as $acc) {
            $query = JournalEntryLine::where('account_id', $acc->id);
            if ($asOfDate) {
                $query->whereHas('journalEntry', fn($q) => $q->whereDate('entry_date', '<=', $asOfDate));
            }

            $credit = (float) (clone $query)->sum('credit');
            $debit = (float) (clone $query)->sum('debit');

            $val = $credit - $debit;
            if ($val != 0) {
                $equityData[] = ['name' => $acc->account_name, 'code' => $acc->account_code, 'amount' => $val];
                $totalEquity += $val;
            }
        }

        return view('backend.reports.financial.balance_sheet', compact('assetsData', 'liabilitiesData', 'equityData', 'totalAssets', 'totalLiabilities', 'totalEquity', 'asOfDate'));
    }
}
