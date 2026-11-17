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
        Schema::create('stationery_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_no', 60)->unique();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('vendor_name_custom', 190)->nullable();
            $table->date('purchase_date');
            $table->string('invoice_no', 100)->nullable();
            $table->decimal('total_amount', 14, 2)->default(0.00);
            $table->string('payment_status', 30)->default('paid'); // paid, partial, unpaid
            $table->decimal('paid_amount', 14, 2)->default(0.00);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('purchase_date');
        });

        Schema::create('stationery_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stationery_purchase_id')->constrained('stationery_purchases')->cascadeOnDelete();
            $table->foreignId('stationery_item_id')->constrained('stationery_items')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2)->default(1.00);
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->decimal('subtotal', 14, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stationery_purchase_items');
        Schema::dropIfExists('stationery_purchases');
    }
};

