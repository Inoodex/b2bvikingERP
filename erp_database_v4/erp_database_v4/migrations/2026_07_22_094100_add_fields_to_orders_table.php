<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('quotation_id')->nullable()->after('user_id')->constrained('sales_quotations')->nullOnDelete();
            $table->foreignId('salesperson_id')->nullable()->after('quotation_id')->constrained('users')->nullOnDelete();
            $table->string('incoterm', 20)->nullable()->after('salesperson_id');
            $table->foreignId('coupon_id')->nullable()->after('incoterm')->constrained('coupons')->nullOnDelete();
            $table->enum('approval_status', ['pending','level1_approved','approved','rejected'])->default('approved')->after('coupon_id');  // default 'approved' so existing historical orders aren't retroactively blocked
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['quotation_id']);
            $table->dropForeign(['salesperson_id']);
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['quotation_id', 'salesperson_id', 'coupon_id', 'incoterm', 'approval_status']);
        });
    }
};
