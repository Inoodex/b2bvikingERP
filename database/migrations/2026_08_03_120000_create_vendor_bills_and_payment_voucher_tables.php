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
        // 1. Upgrade purchase_payments table with enterprise voucher and multi-currency fields
        if (Schema::hasTable('purchase_payments')) {
            Schema::table('purchase_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_payments', 'payment_no')) {
                    $table->string('payment_no')->unique()->nullable()->after('id');
                }
                if (!Schema::hasColumn('purchase_payments', 'payment_date')) {
                    $table->date('payment_date')->nullable()->after('payment_no');
                }
                if (!Schema::hasColumn('purchase_payments', 'currency_id')) {
                    $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete()->after('vendor_id');
                }
                if (!Schema::hasColumn('purchase_payments', 'exchange_rate')) {
                    $table->decimal('exchange_rate', 15, 6)->default(1.000000)->after('amount');
                }
                if (!Schema::hasColumn('purchase_payments', 'base_amount')) {
                    $table->decimal('base_amount', 15, 2)->default(0.00)->after('exchange_rate');
                }
                if (!Schema::hasColumn('purchase_payments', 'bank_name')) {
                    $table->string('bank_name')->nullable()->after('payment_method');
                }
                if (!Schema::hasColumn('purchase_payments', 'cheque_no')) {
                    $table->string('cheque_no')->nullable()->after('bank_name');
                }
                if (!Schema::hasColumn('purchase_payments', 'status')) {
                    $table->enum('status', ['pending', 'approved', 'cancelled'])->default('approved')->after('base_amount');
                }
                if (!Schema::hasColumn('purchase_payments', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
                }
            });
        }

        // 2. Create vendor_bills table for 3-way matching invoice processing
        if (!Schema::hasTable('vendor_bills')) {
            Schema::create('vendor_bills', function (Blueprint $table) {
                $table->id();
                $table->string('bill_no')->unique();
                $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->foreignId('goods_receipt_id')->nullable()->constrained('goods_receipts')->nullOnDelete();
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->date('bill_date');
                $table->date('due_date')->nullable();
                $table->decimal('subtotal', 15, 2)->default(0.00);
                $table->decimal('tax_amount', 15, 2)->default(0.00);
                $table->decimal('discount_amount', 15, 2)->default(0.00);
                $table->decimal('debit_note_adjustment', 15, 2)->default(0.00);
                $table->decimal('grand_total', 15, 2)->default(0.00);
                $table->decimal('paid_amount', 15, 2)->default(0.00);
                $table->decimal('due_amount', 15, 2)->default(0.00);
                $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'overpaid'])->default('unpaid');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 3. Create vendor_bill_items table for line-item bill details
        if (!Schema::hasTable('vendor_bill_items')) {
            Schema::create('vendor_bill_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_bill_id')->constrained('vendor_bills')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->string('description')->nullable();
                $table->decimal('qty', 15, 4)->default(0.0000);
                $table->decimal('unit_price', 15, 2)->default(0.00);
                $table->decimal('landed_cost', 15, 2)->default(0.00);
                $table->decimal('line_total', 15, 2)->default(0.00);
                $table->timestamps();
            });
        }

        // 4. Create debit_note_settlements table for Debit Note credit adjustments against bills
        if (!Schema::hasTable('debit_note_settlements')) {
            Schema::create('debit_note_settlements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_return_id')->constrained('vendor_returns')->cascadeOnDelete();
                $table->foreignId('vendor_bill_id')->constrained('vendor_bills')->cascadeOnDelete();
                $table->decimal('settled_amount', 15, 2);
                $table->timestamp('settlement_date');
                $table->text('notes')->nullable();
                $table->foreignId('settled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debit_note_settlements');
        Schema::dropIfExists('vendor_bill_items');
        Schema::dropIfExists('vendor_bills');

        if (Schema::hasTable('purchase_payments')) {
            Schema::table('purchase_payments', function (Blueprint $table) {
                $columnsToDrop = [];
                if (Schema::hasColumn('purchase_payments', 'payment_no')) $columnsToDrop[] = 'payment_no';
                if (Schema::hasColumn('purchase_payments', 'payment_date')) $columnsToDrop[] = 'payment_date';
                if (Schema::hasColumn('purchase_payments', 'currency_id')) $columnsToDrop[] = 'currency_id';
                if (Schema::hasColumn('purchase_payments', 'exchange_rate')) $columnsToDrop[] = 'exchange_rate';
                if (Schema::hasColumn('purchase_payments', 'base_amount')) $columnsToDrop[] = 'base_amount';
                if (Schema::hasColumn('purchase_payments', 'bank_name')) $columnsToDrop[] = 'bank_name';
                if (Schema::hasColumn('purchase_payments', 'cheque_no')) $columnsToDrop[] = 'cheque_no';
                if (Schema::hasColumn('purchase_payments', 'status')) $columnsToDrop[] = 'status';
                if (Schema::hasColumn('purchase_payments', 'created_by')) $columnsToDrop[] = 'created_by';

                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
