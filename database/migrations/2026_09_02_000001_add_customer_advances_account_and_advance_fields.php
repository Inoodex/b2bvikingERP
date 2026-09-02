<?php

use App\Models\ChartOfAccount;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add unallocated_amount and is_advance to customer_payments table
        if (Schema::hasTable('customer_payments')) {
            Schema::table('customer_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('customer_payments', 'unallocated_amount')) {
                    $table->decimal('unallocated_amount', 15, 2)->default(0.00)->after('amount');
                }
                if (!Schema::hasColumn('customer_payments', 'is_advance')) {
                    $table->boolean('is_advance')->default(false)->after('unallocated_amount');
                }
            });
        }

        // 2. Ensure 2040 Customer Advances & Deposits account exists under 2000 Liabilities
        $parentLiab = ChartOfAccount::where('account_code', '2000')->first();
        if (!ChartOfAccount::where('account_code', '2040')->exists()) {
            ChartOfAccount::create([
                'company_id'     => 1,
                'account_code'   => '2040',
                'account_name'   => 'Customer Advances & Deposits',
                'account_type'   => 'liability',
                'normal_balance' => 'credit',
                'parent_id'      => $parentLiab?->id,
                'is_group'       => false,
                'is_active'      => true,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customer_payments')) {
            Schema::table('customer_payments', function (Blueprint $table) {
                if (Schema::hasColumn('customer_payments', 'unallocated_amount')) {
                    $table->dropColumn('unallocated_amount');
                }
                if (Schema::hasColumn('customer_payments', 'is_advance')) {
                    $table->dropColumn('is_advance');
                }
            });
        }
    }
};
