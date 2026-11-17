<?php

use App\Models\ChartOfAccount;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $parent = ChartOfAccount::where('account_code', '5000')->first();

        ChartOfAccount::firstOrCreate(
            ['account_code' => '5025'],
            [
                'account_name'   => 'Packaging & Stationery Supplies Expense',
                'account_type'   => 'expense',
                'normal_balance' => 'debit',
                'is_group'       => false,
                'parent_id'      => $parent ? $parent->id : null,
                'is_active'      => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        ChartOfAccount::where('account_code', '5025')->delete();
    }
};
