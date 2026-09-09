<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Upgrade general_settings with Feature Toggles, PayPal, and COD configuration
        Schema::table('general_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('general_settings', 'feature_toggles')) {
                $table->json('feature_toggles')->nullable()->after('currency_rate');
            }
            if (!Schema::hasColumn('general_settings', 'paypal_enabled')) {
                $table->boolean('paypal_enabled')->default(true)->after('feature_toggles');
            }
            if (!Schema::hasColumn('general_settings', 'paypal_mode')) {
                $table->string('paypal_mode', 20)->default('sandbox')->after('paypal_enabled');
            }
            if (!Schema::hasColumn('general_settings', 'paypal_client_id')) {
                $table->string('paypal_client_id')->nullable()->after('paypal_mode');
            }
            if (!Schema::hasColumn('general_settings', 'paypal_client_secret')) {
                $table->text('paypal_client_secret')->nullable()->after('paypal_client_id');
            }
            if (!Schema::hasColumn('general_settings', 'paypal_currency')) {
                $table->string('paypal_currency', 10)->default('DKK')->after('paypal_client_secret');
            }
            if (!Schema::hasColumn('general_settings', 'cod_enabled')) {
                $table->boolean('cod_enabled')->default(true)->after('paypal_currency');
            }
            if (!Schema::hasColumn('general_settings', 'cod_max_limit')) {
                $table->decimal('cod_max_limit', 12, 2)->default(10000.00)->after('cod_enabled');
            }
            if (!Schema::hasColumn('general_settings', 'cod_instructions')) {
                $table->text('cod_instructions')->nullable()->after('cod_max_limit');
            }
            if (!Schema::hasColumn('general_settings', 'cod_deposit_account_id')) {
                $table->unsignedBigInteger('cod_deposit_account_id')->nullable()->after('cod_instructions');
            }
        });

        // 2. Payment Transactions (Audit record for online/offline gateway transactions)
        if (!Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_no', 64)->unique();
                $table->string('gateway', 32); // 'paypal', 'cod'
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->unsignedBigInteger('sales_invoice_id')->nullable()->index();
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->decimal('amount', 12, 2)->default(0.00);
                $table->string('currency', 10)->default('DKK');
                $table->string('external_reference', 128)->nullable()->index(); // PayPal Order/Capture ID, COD slip
                $table->string('status', 32)->default('pending'); // 'pending', 'captured', 'failed', 'refunded', 'cancelled'
                $table->json('payload')->nullable();
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
                $table->foreign('sales_invoice_id')->references('id')->on('sales_invoices')->nullOnDelete();
                $table->foreign('customer_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 3. Cash On Delivery (COD) Collections Lifecycle
        if (!Schema::hasTable('cod_collections')) {
            Schema::create('cod_collections', function (Blueprint $table) {
                $table->id();
                $table->string('collection_no', 64)->unique();
                $table->unsignedBigInteger('transaction_id')->nullable()->index();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->unsignedBigInteger('sales_invoice_id')->nullable()->index();
                $table->unsignedBigInteger('driver_id')->nullable()->index(); // User acting as delivery agent
                $table->string('courier_name', 128)->nullable();
                $table->decimal('expected_amount', 12, 2)->default(0.00);
                $table->decimal('collected_amount', 12, 2)->default(0.00);
                $table->decimal('difference_amount', 12, 2)->default(0.00);
                $table->string('status', 32)->default('pending_dispatch'); // 'pending_dispatch', 'out_for_delivery', 'collected', 'handed_over', 'failed'
                $table->timestamp('collected_at')->nullable();
                $table->unsignedBigInteger('handed_over_to_user_id')->nullable(); // Cashier / Accountant who verified cash
                $table->unsignedBigInteger('deposit_account_id')->nullable(); // Account 1010 Petty Cash
                $table->timestamp('handed_over_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
                $table->foreign('sales_invoice_id')->references('id')->on('sales_invoices')->nullOnDelete();
                $table->foreign('driver_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('handed_over_to_user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('deposit_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
            });
        }

        // 4. System Backup Logs (Admin backup registry)
        if (!Schema::hasTable('system_backup_logs')) {
            Schema::create('system_backup_logs', function (Blueprint $table) {
                $table->id();
                $table->string('file_name');
                $table->string('disk', 64)->default('local');
                $table->string('file_path');
                $table->bigInteger('file_size_bytes')->default(0);
                $table->string('backup_type', 32)->default('full'); // 'full', 'database_only', 'files_only'
                $table->string('status', 32)->default('successful'); // 'successful', 'failed'
                $table->unsignedBigInteger('triggered_by')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('triggered_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_backup_logs');
        Schema::dropIfExists('cod_collections');
        Schema::dropIfExists('payment_transactions');

        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn([
                'feature_toggles',
                'paypal_enabled',
                'paypal_mode',
                'paypal_client_id',
                'paypal_client_secret',
                'paypal_currency',
                'cod_enabled',
                'cod_max_limit',
                'cod_instructions',
                'cod_deposit_account_id',
            ]);
        });
    }
};
