<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->nullableMorphs('source'); // Adds source_type, source_id
        });

        // Copy existing data gracefully to prevent data loss
        DB::table('rfqs')->whereNotNull('product_request_id')->update([
            'source_type' => 'App\Models\ProductRequest',
            'source_id' => DB::raw('product_request_id')
        ]);

        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropForeign(['product_request_id']);
            $table->dropColumn('product_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->foreignId('product_request_id')->nullable()->constrained('product_requests')->cascadeOnDelete();
        });

        DB::table('rfqs')->where('source_type', 'App\Models\ProductRequest')->update([
            'product_request_id' => DB::raw('source_id')
        ]);

        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropMorphs('source');
        });
    }
};
