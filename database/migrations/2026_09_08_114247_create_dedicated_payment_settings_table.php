<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('payment_settings')) {
            Schema::create('payment_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 50)->unique();
                $table->string('name', 100);
                $table->string('status', 20)->default('disable'); // enable | disable
                $table->string('mode', 20)->default('sandbox');   // sandbox | live
                $table->string('country_name', 100)->nullable();
                $table->string('currency_name', 100)->nullable();
                $table->decimal('currency_rate', 12, 4)->default(1.0000);
                $table->text('client_id')->nullable();
                $table->text('client_secret')->nullable();
                $table->text('instructions')->nullable();
                $table->foreignId('deposit_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
                $table->json('additional_config')->nullable();
                $table->timestamps();
            });

            // Seed default gateway configurations
            DB::table('payment_settings')->insert([
                [
                    'key'                => 'paypal',
                    'name'               => 'PayPal Express',
                    'status'             => 'disable',
                    'mode'               => 'sandbox',
                    'country_name'       => 'Denmark',
                    'currency_name'      => 'DKK',
                    'currency_rate'      => 1.0000,
                    'client_id'          => null,
                    'client_secret'      => null,
                    'instructions'       => null,
                    'deposit_account_id' => null,
                    'additional_config'  => null,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ],
                [
                    'key'                => 'cod',
                    'name'               => 'Cash On Delivery',
                    'status'             => 'enable',
                    'mode'               => 'live',
                    'country_name'       => 'Denmark',
                    'currency_name'      => 'DKK',
                    'currency_rate'      => 1.0000,
                    'client_id'          => null,
                    'client_secret'      => null,
                    'instructions'       => 'Payment must be handed to delivery driver upon arrival.',
                    'deposit_account_id' => null,
                    'additional_config'  => null,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ],
                [
                    'key'                => 'mobile_pay',
                    'name'               => 'Mobile Pay',
                    'status'             => 'disable',
                    'mode'               => 'sandbox',
                    'country_name'       => 'Denmark',
                    'currency_name'      => 'DKK',
                    'currency_rate'      => 1.0000,
                    'client_id'          => null,
                    'client_secret'      => null,
                    'instructions'       => null,
                    'deposit_account_id' => null,
                    'additional_config'  => null,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
