<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\GeneralSetting;
use App\Models\JournalEntryLine;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class GenerateFinancialReportPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $reportType;
    public array $filters;
    public int $userId;
    public int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(string $reportType, array $filters, int $userId)
    {
        $this->reportType = $reportType;
        $this->filters = $filters;
        $this->userId = $userId;
    }

    /**
     * Execute the job in background queue worker.
     */
    public function handle(): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(600);

        $company = Company::first();
        $settings = GeneralSetting::first();
        $user = \App\Models\User::find($this->userId);
        $generatedBy = $user?->name ?? 'Administrator';

        $tempDir = storage_path('app/temp_reports');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        switch ($this->reportType) {
            case 'general_ledger':
                $this->generateGeneralLedger($company, $settings, $tempDir, $generatedBy);
                break;
            case 'trial_balance':
                $this->generateTrialBalance($company, $settings, $tempDir, $generatedBy);
                break;
            case 'profit_loss':
                $this->generateProfitLoss($company, $settings, $tempDir, $generatedBy);
                break;
            case 'balance_sheet':
                $this->generateBalanceSheet($company, $settings, $tempDir, $generatedBy);
                break;
            default:
                Log::error("Unknown financial report type: {$this->reportType}");
                break;
        }
    }

    /**
     * Generate General Ledger PDF
     */
    private function generateGeneralLedger(?Company $company, ?GeneralSetting $settings, string $tempDir, string $generatedBy): void
    {
        $dateFrom = $this->filters['date_from'] ?? date('Y-01-01');
        $dateTo   = $this->filters['date_to'] ?? date('Y-m-d');
        $selectedAccountId = $this->filters['account_id'] ?? null;

        $query = JournalEntryLine::query()
            ->with(['account', 'journalEntry'])
            ->whereHas('journalEntry', function ($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) $q->whereDate('entry_date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('entry_date', '<=', $dateTo);
            });

        if ($selectedAccountId) {
            $query->where('account_id', $selectedAccountId);
        }

        $lines = $query->orderBy('id', 'asc')->get();
        $totalDebit  = (float) $lines->sum('debit');
        $totalCredit = (float) $lines->sum('credit');

        $selectedAccount = $selectedAccountId ? ChartOfAccount::find($selectedAccountId) : null;
        $accountLabel = $selectedAccount 
            ? "{$selectedAccount->account_code} - {$selectedAccount->account_name}" 
            : 'All Accounts';

        $dateRangeLabel = ($dateFrom || $dateTo) 
            ? (($dateFrom ?: 'Beginning') . ' to ' . ($dateTo ?: 'Today')) 
            : 'Full History';

        $data = compact('lines', 'totalDebit', 'totalCredit', 'selectedAccount', 'company', 'settings', 'dateFrom', 'dateTo', 'accountLabel', 'dateRangeLabel', 'generatedBy');

        $this->saveAndNotify(
            'backend.reports.financial.pdf.general_ledger_pdf',
            $data,
            'general_ledger',
            'General Ledger (' . $accountLabel . ')',
            'Period: ' . $dateRangeLabel,
            $tempDir,
            'a4',
            'landscape'
        );
    }

    /**
     * Generate Trial Balance PDF
     */
    private function generateTrialBalance(?Company $company, ?GeneralSetting $settings, string $tempDir, string $generatedBy): void
    {
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo   = $this->filters['date_to'] ?? null;

        $accounts   = ChartOfAccount::where('is_group', false)->orderBy('account_code')->get();
        $aggregates = $this->buildAggregate($dateFrom, $dateTo);

        $reportData     = [];
        $totalDebitSum  = 0.0;
        $totalCreditSum = 0.0;

        foreach ($accounts as $acc) {
            $debitSum  = $aggregates[$acc->id]['debit']  ?? 0.0;
            $creditSum = $aggregates[$acc->id]['credit'] ?? 0.0;

            $netDebit = $netCredit = 0.0;

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

        $isBalanced = abs($totalDebitSum - $totalCreditSum) < 0.01;
        $dateRangeLabel = ($dateFrom || $dateTo) 
            ? (($dateFrom ?: 'Beginning') . ' to ' . ($dateTo ?: 'Today')) 
            : 'All Time Balance';

        $data = compact('reportData', 'totalDebitSum', 'totalCreditSum', 'isBalanced', 'company', 'settings', 'dateFrom', 'dateTo', 'dateRangeLabel', 'generatedBy');

        $this->saveAndNotify(
            'backend.reports.financial.pdf.trial_balance_pdf',
            $data,
            'trial_balance',
            'Trial Balance Statement',
            'Period: ' . $dateRangeLabel,
            $tempDir,
            'a4',
            'portrait'
        );
    }

    /**
     * Generate Profit & Loss PDF
     */
    private function generateProfitLoss(?Company $company, ?GeneralSetting $settings, string $tempDir, string $generatedBy): void
    {
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo   = $this->filters['date_to'] ?? null;

        $aggregates = $this->buildAggregate($dateFrom, $dateTo);

        $revenueAccounts = ChartOfAccount::where('account_type', 'revenue')->where('is_group', false)->get();
        $revenueData = [];
        $totalRevenue = 0.0;

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
        $totalExpense = 0.0;

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
        $marginPercent = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0.0;

        $dateRangeLabel = ($dateFrom || $dateTo) 
            ? (($dateFrom ?: 'Beginning') . ' to ' . ($dateTo ?: 'Today')) 
            : 'All Time History';

        $data = compact('revenueData', 'expenseData', 'totalRevenue', 'totalExpense', 'netProfit', 'marginPercent', 'company', 'settings', 'dateFrom', 'dateTo', 'dateRangeLabel', 'generatedBy');

        $this->saveAndNotify(
            'backend.reports.financial.pdf.profit_loss_pdf',
            $data,
            'profit_loss',
            'Profit & Loss Statement',
            'Period: ' . $dateRangeLabel . ' | Net: ' . ($settings->currency_icon ?? 'kr.') . ' ' . number_format($netProfit, 2),
            $tempDir,
            'a4',
            'portrait'
        );
    }

    /**
     * Generate Balance Sheet PDF
     */
    private function generateBalanceSheet(?Company $company, ?GeneralSetting $settings, string $tempDir, string $generatedBy): void
    {
        $asOfDate = $this->filters['as_of_date'] ?? date('Y-m-d');
        $aggregates = $this->buildAggregate(null, null, $asOfDate);

        // Assets
        $assetsData  = [];
        $totalAssets = 0.0;
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
        $totalLiabilities = 0.0;
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
        $totalEquity = 0.0;
        foreach (ChartOfAccount::where('account_type', 'equity')->where('is_group', false)->get() as $acc) {
            $credit = $aggregates[$acc->id]['credit'] ?? 0.0;
            $debit  = $aggregates[$acc->id]['debit']  ?? 0.0;
            $val    = $credit - $debit;
            if ($val != 0) {
                $equityData[] = ['name' => $acc->account_name, 'code' => $acc->account_code, 'amount' => $val];
                $totalEquity  += $val;
            }
        }

        $totalLiabAndEquity = $totalLiabilities + $totalEquity;
        $isBalanced = abs($totalAssets - $totalLiabAndEquity) < 0.01;
        $dateRangeLabel = 'As of ' . date('d M Y', strtotime($asOfDate));

        $data = compact('assetsData', 'liabilitiesData', 'equityData', 'totalAssets', 'totalLiabilities', 'totalEquity', 'totalLiabAndEquity', 'isBalanced', 'company', 'settings', 'asOfDate', 'dateRangeLabel', 'generatedBy');

        $this->saveAndNotify(
            'backend.reports.financial.pdf.balance_sheet_pdf',
            $data,
            'balance_sheet',
            'Balance Sheet Statement',
            $dateRangeLabel,
            $tempDir,
            'a4',
            'portrait'
        );
    }

    /**
     * Purge old files, render PDF, save to disk, and dispatch navbar notification.
     */
    private function saveAndNotify(
        string $view,
        array $data,
        string $filePrefix,
        string $title,
        string $desc,
        string $tempDir,
        string $paper = 'a4',
        string $orientation = 'portrait'
    ): void {
        // 1. Dynamic Purge: Delete previous temporary files for this user & prefix
        $pattern = $tempDir . "/{$filePrefix}_u{$this->userId}_*.pdf";
        foreach (glob($pattern) as $oldFile) {
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        // 2. Generate new PDF with live data
        $timestamp = now()->format('Ymd_His');
        $storedFileName = "{$filePrefix}_u{$this->userId}_{$timestamp}.pdf";
        $filePath = "{$tempDir}/{$storedFileName}";

        $pdf = Pdf::loadView($view, $data)->setPaper($paper, $orientation);
        $pdf->save($filePath);

        // 3. Add to user notification cache (notification bell icon shows PDF ready)
        $this->addCacheNotification([
            'type' => 'pdf_ready',
            'title' => 'Financial Report: ' . $title,
            'desc' => $desc,
            'url' => route('admin.reports.financial.download', ['file' => $storedFileName]),
            'icon' => 'fas fa-file-pdf',
            'class' => 'bg-success',
            'timestamp' => now()->timestamp,
        ]);
    }

    /**
     * Push PDF notification to user notification cache.
     */
    private function addCacheNotification(array $data): void
    {
        $key = 'user_pdf_notifications_' . $this->userId;
        $notifications = Cache::get($key, []);
        $data['time'] = now()->diffForHumans();
        $data['is_unread'] = true;
        $data['is_out_of_stock'] = false;
        $notifications[] = $data;
        $notifications = array_slice($notifications, -20);
        Cache::put($key, $notifications, now()->addDays(7));
    }

    /**
     * Build a date-filtered aggregate: account_id => [debit, credit]
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
}
