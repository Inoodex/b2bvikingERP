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
        Schema::table('discounts', function (Blueprint $table) {
            $table->boolean('is_seasonal')->default(false)->after('value');
            $table->string('occasion')->nullable()->after('is_seasonal');
            $table->date('start_date')->nullable()->after('occasion');
            $table->date('end_date')->nullable()->after('start_date');
            $table->json('category_ids')->nullable()->after('end_date');
            $table->json('user_ids')->nullable()->after('category_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn([
                'is_seasonal',
                'occasion',
                'start_date',
                'end_date',
                'category_ids',
                'user_ids',
            ]);
        });
    }
};
