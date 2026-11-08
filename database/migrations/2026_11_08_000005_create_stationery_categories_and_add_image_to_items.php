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
        Schema::create('stationery_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->string('icon', 60)->default('fas fa-boxes');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::table('stationery_items', function (Blueprint $table) {
            $table->string('image', 255)->nullable()->after('name');
            $table->foreignId('stationery_category_id')->nullable()->after('category')->constrained('stationery_categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stationery_items', function (Blueprint $table) {
            $table->dropForeign(['stationery_category_id']);
            $table->dropColumn(['image', 'stationery_category_id']);
        });

        Schema::dropIfExists('stationery_categories');
    }
};

