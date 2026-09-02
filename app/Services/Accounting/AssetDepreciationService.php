<?php

namespace App\Services\Accounting;

use App\Models\Asset;
use App\Models\AssetDepreciation;
use App\Models\ChartOfAccount;
use Exception;
use Illuminate\Support\Facades\DB;

class AssetDepreciationService
{
    protected JournalEntryService $journalService;

    public function __construct(JournalEntryService $journalService)
    {
        $this->journalService = $journalService;
    }

    /**
     * Run monthly straight-line depreciation for all active assets for a given period (YYYY-MM).
     */
    public function runMonthlyDepreciation(string $period): array
    {
        $assets = Asset::where('status', 'active')
            ->where('purchase_date', '<=', $period . '-28')
            ->get();

        $processedCount = 0;
        $totalDepreciation = 0;

        // Ensure GL accounts exist
        ChartOfAccount::firstOrCreate(['account_code' => '5030'], [
            'account_name'   => 'Depreciation Expense',
            'account_type'   => 'expense',
            'normal_balance' => 'debit',
            'is_group'       => false,
            'is_active'      => true,
        ]);

        ChartOfAccount::firstOrCreate(['account_code' => '1080'], [
            'account_name'   => 'Accumulated Depreciation',
            'account_type'   => 'asset',
            'normal_balance' => 'credit',
            'is_group'       => false,
            'is_active'      => true,
        ]);

        DB::transaction(function () use ($assets, $period, &$processedCount, &$totalDepreciation) {
            foreach ($assets as $asset) {
                // Check if already depreciated for this period
                $alreadyDone = AssetDepreciation::where('asset_id', $asset->id)
                    ->where('period', $period)
                    ->exists();

                if ($alreadyDone) {
                    continue;
                }

                $usefulLifeYears = $asset->useful_life_years ?: 5;
                $currentBook = $asset->current_book_value;
                if ($currentBook <= 0) {
                    continue;
                }

                // Support both straight_line and reducing_balance (declining balance) methods
                if ($asset->depreciation_method === 'reducing_balance') {
                    // Reducing Balance Formula (IFRS/GAAP Double Declining Balance: 2 / usefulLifeYears)
                    $annualRate = min(0.50, 2.0 / $usefulLifeYears);
                    $monthlyAmount = round(($currentBook * $annualRate) / 12, 2);
                } else {
                    // Standard Straight-Line Formula: Purchase Cost / (Useful Life * 12)
                    $monthlyAmount = round((float) $asset->purchase_value / ($usefulLifeYears * 12), 2);
                }

                if ($monthlyAmount <= 0) {
                    continue;
                }

                $depreciating = min($monthlyAmount, $currentBook);

                $deprec = AssetDepreciation::create([
                    'asset_id' => $asset->id,
                    'period'   => $period,
                    'amount'   => $depreciating,
                ]);

                // Post GL double-entry: DR 5030 Depreciation Expense / CR 1080 Accumulated Depreciation
                $lines = [
                    ['account_code' => '5030', 'debit' => $depreciating, 'credit' => 0],
                    ['account_code' => '1080', 'debit' => 0, 'credit' => $depreciating],
                ];

                $this->journalService->recordEntry(
                    event: 'asset_depreciation',
                    sourceModel: $deprec,
                    lines: $lines,
                    entryDate: $period . '-28',
                    narration: "Monthly Depreciation for Asset {$asset->asset_code} ({$asset->name}) — Period: {$period}"
                );

                $processedCount++;
                $totalDepreciation += $depreciating;
            }
        });

        return [
            'processed_count'    => $processedCount,
            'total_depreciation' => $totalDepreciation,
            'period'             => $period,
        ];
    }
}
