<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Vendor;
use App\Models\Currency;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure foreign key exists (optional, but good practice if not already there)
        // We'll skip adding currency_id since it already exists.

        // 2. Data Migration: Map existing currency_name to currency_id
        $currencyList = collect(config('settings.currency_list'));
        
        // We use chunk to handle large datasets safely
        Vendor::chunk(100, function ($vendors) use ($currencyList) {
            foreach ($vendors as $vendor) {
                if (!empty($vendor->currency_name)) {
                    // Try to find the currency details from config
                    $configCurrency = $currencyList->firstWhere('code', $vendor->currency_name);
                    
                    if ($configCurrency) {
                        // Ensure it exists in the database
                        $dbCurrency = Currency::firstOrCreate(
                            ['code' => $configCurrency['code']],
                            [
                                'name' => $configCurrency['name'],
                                'symbol' => $configCurrency['symbol'],
                                'exchange_rate' => 1.0, // default placeholder
                                'is_base' => false,
                                'status' => true,
                            ]
                        );
                        
                        // Link the vendor to this currency
                        $vendor->currency_id = $dbCurrency->id;
                        $vendor->save();
                    }
                }
            }
        });

        // 3. Drop old text columns
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['currency_name', 'currency_icon']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Add columns back
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('currency_name')->nullable()->after('country');
            $table->string('currency_icon')->nullable()->after('currency_name');
        });

        // 2. Reverse Data Migration
        Vendor::chunk(100, function ($vendors) {
            foreach ($vendors as $vendor) {
                if ($vendor->currency_id) {
                    $currency = Currency::find($vendor->currency_id);
                    if ($currency) {
                        $vendor->currency_name = $currency->code;
                        $vendor->currency_icon = $currency->symbol;
                        $vendor->save();
                    }
                }
            }
        });

        // 3. Do not drop currency_id as it existed before
    }
};
