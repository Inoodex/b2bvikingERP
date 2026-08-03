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
        // 1. Add settlement tracking columns to vendor_returns table if missing
        Schema::table('vendor_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_returns', 'settlement_type')) {
                $table->string('settlement_type')->default('pending')->after('status');
            }
            if (!Schema::hasColumn('vendor_returns', 'replacement_product_id')) {
                $table->foreignId('replacement_product_id')->nullable()->after('settlement_type')->constrained('products')->nullOnDelete();
            }
            if (!Schema::hasColumn('vendor_returns', 'replacement_variant_id')) {
                $table->foreignId('replacement_variant_id')->nullable()->after('replacement_product_id')->constrained('product_variants')->nullOnDelete();
            }
            if (!Schema::hasColumn('vendor_returns', 'replacement_qty')) {
                $table->decimal('replacement_qty', 15, 4)->nullable()->after('replacement_variant_id');
            }
            if (!Schema::hasColumn('vendor_returns', 'settled_at')) {
                $table->timestamp('settled_at')->nullable()->after('replacement_qty');
            }
        });

        // 2. Create debit_note_refunds table for direct bank/cash refunds
        if (!Schema::hasTable('debit_note_refunds')) {
            Schema::create('debit_note_refunds', function (Blueprint $table) {
                $table->id();
                $table->string('refund_no')->unique();
                $table->foreignId('vendor_return_id')->constrained('vendor_returns')->onDelete('cascade');
                $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
                $table->decimal('amount', 15, 2);
                $table->date('refund_date');
                $table->string('payment_method')->default('bank_transfer');
                $table->string('bank_name')->nullable();
                $table->string('cheque_no')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debit_note_refunds');

        Schema::table('vendor_returns', function (Blueprint $table) {
            $table->dropForeign(['replacement_product_id']);
            $table->dropForeign(['replacement_variant_id']);
            $table->dropColumn([
                'settlement_type',
                'replacement_product_id',
                'replacement_variant_id',
                'replacement_qty',
                'settled_at',
            ]);
        });
    }
};
