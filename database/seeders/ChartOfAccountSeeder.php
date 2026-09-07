<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Seed standard enterprise Chart of Accounts (COA Tree) and current Fiscal Year.
     */
    public function run(): void
    {
        Artisan::call('accounting:seed-coa');
    }
}
