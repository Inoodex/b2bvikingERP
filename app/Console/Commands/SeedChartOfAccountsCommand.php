<?php

namespace App\Console\Commands;

use App\Models\ChartOfAccount;
use App\Models\FiscalYear;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedChartOfAccountsCommand extends Command
{
    protected $signature = 'accounting:seed-coa';

    protected $description = 'Seed standard enterprise Chart of Accounts (COA Tree) and current Fiscal Year.';

    public function handle()
    {
        $this->info('Seeding Standard Enterprise Chart of Accounts & Fiscal Year...');

        DB::transaction(function () {
            // 1. Current Fiscal Year
            FiscalYear::firstOrCreate([
                'name' => 'FY 2026-2027',
            ], [
                'start_date' => '2026-01-01',
                'end_date'   => '2026-12-31',
                'is_closed'  => false,
            ]);

            // 2. Standard Master COA Tree
            $accounts = [
                // Assets (1000)
                ['code' => '1000', 'name' => 'Assets', 'type' => 'asset', 'balance' => 'debit', 'is_group' => true, 'parent' => null],
                ['code' => '1010', 'name' => 'Cash on Hand', 'type' => 'asset', 'balance' => 'debit', 'is_group' => false, 'parent' => '1000'],
                ['code' => '1020', 'name' => 'Bank Account', 'type' => 'asset', 'balance' => 'debit', 'is_group' => false, 'parent' => '1000'],
                ['code' => '1030', 'name' => 'Accounts Receivable (Clients)', 'type' => 'asset', 'balance' => 'debit', 'is_group' => false, 'parent' => '1000'],
                ['code' => '1050', 'name' => 'Inventory in Warehouse', 'type' => 'asset', 'balance' => 'debit', 'is_group' => false, 'parent' => '1000'],
                ['code' => '1090', 'name' => 'Accumulated Depreciation', 'type' => 'asset', 'balance' => 'credit', 'is_group' => false, 'parent' => '1000'],

                // Liabilities (2000)
                ['code' => '2000', 'name' => 'Liabilities', 'type' => 'liability', 'balance' => 'credit', 'is_group' => true, 'parent' => null],
                ['code' => '2010', 'name' => 'Accounts Payable (Vendors)', 'type' => 'liability', 'balance' => 'credit', 'is_group' => false, 'parent' => '2000'],
                ['code' => '2020', 'name' => 'GRN Accrued Clearing', 'type' => 'liability', 'balance' => 'credit', 'is_group' => false, 'parent' => '2000'],
                ['code' => '2030', 'name' => 'Sales Tax / VAT Payable', 'type' => 'liability', 'balance' => 'credit', 'is_group' => false, 'parent' => '2000'],

                // Equity (3000)
                ['code' => '3000', 'name' => 'Equity', 'type' => 'equity', 'balance' => 'credit', 'is_group' => true, 'parent' => null],
                ['code' => '3010', 'name' => "Owner's Capital", 'type' => 'equity', 'balance' => 'credit', 'is_group' => false, 'parent' => '3000'],
                ['code' => '3020', 'name' => 'Retained Earnings', 'type' => 'equity', 'balance' => 'credit', 'is_group' => false, 'parent' => '3000'],

                // Revenue (4000)
                ['code' => '4000', 'name' => 'Revenue', 'type' => 'revenue', 'balance' => 'credit', 'is_group' => true, 'parent' => null],
                ['code' => '4010', 'name' => 'Sales Revenue', 'type' => 'revenue', 'balance' => 'credit', 'is_group' => false, 'parent' => '4000'],
                ['code' => '4020', 'name' => 'Service & Shipping Revenue', 'type' => 'revenue', 'balance' => 'credit', 'is_group' => false, 'parent' => '4000'],

                // Expenses (5000)
                ['code' => '5000', 'name' => 'Expenses', 'type' => 'expense', 'balance' => 'debit', 'is_group' => true, 'parent' => null],
                ['code' => '5010', 'name' => 'Cost of Goods Sold (COGS)', 'type' => 'expense', 'balance' => 'debit', 'is_group' => false, 'parent' => '5000'],
                ['code' => '5020', 'name' => 'Operating Expenses', 'type' => 'expense', 'balance' => 'debit', 'is_group' => false, 'parent' => '5000'],
                ['code' => '5030', 'name' => 'Freight & Shipping Expense', 'type' => 'expense', 'balance' => 'debit', 'is_group' => false, 'parent' => '5000'],
                ['code' => '5080', 'name' => 'Depreciation Expense', 'type' => 'expense', 'balance' => 'debit', 'is_group' => false, 'parent' => '5000'],
            ];

            $codeToIdMap = [];

            foreach ($accounts as $acc) {
                $parentId = $acc['parent'] && isset($codeToIdMap[$acc['parent']]) ? $codeToIdMap[$acc['parent']] : null;

                $account = ChartOfAccount::firstOrCreate([
                    'account_code' => $acc['code'],
                ], [
                    'account_name'   => $acc['name'],
                    'account_type'   => $acc['type'],
                    'normal_balance' => $acc['balance'],
                    'is_group'       => $acc['is_group'],
                    'parent_id'      => $parentId,
                    'is_active'      => true,
                ]);

                $codeToIdMap[$acc['code']] = $account->id;
            }
        });

        $this->info('Successfully seeded Chart of Accounts and Fiscal Year!');
    }
}
