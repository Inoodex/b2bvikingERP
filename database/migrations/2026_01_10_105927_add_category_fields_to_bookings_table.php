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
        Schema::table('bookings', function (Blueprint $table) {
            // Add category fields
            $table->foreignId('category_id')->nullable()->after('product_id')->constrained('categories')->onDelete('set null');
            $table->foreignId('sub_category_id')->nullable()->after('category_id')->constrained('sub_categories')->onDelete('set null');
            $table->foreignId('child_category_id')->nullable()->after('sub_category_id')->constrained('child_categories')->onDelete('set null');
            
            // Change status from boolean to enum
            $table->dropColumn('status');
        });
        
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending')->after('custom_fields');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['sub_category_id']);
            $table->dropForeign(['child_category_id']);
            $table->dropColumn(['category_id', 'sub_category_id', 'child_category_id']);
            $table->dropColumn('status');
        });
        
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('status')->default(1);
        });
    }
};
