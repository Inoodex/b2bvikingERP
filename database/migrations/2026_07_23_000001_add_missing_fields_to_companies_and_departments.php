<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'email')) {
                $table->string('email')->nullable()->after('code');
            }
            if (!Schema::hasColumn('companies', 'phone')) {
                $table->string('phone', 50)->nullable()->after('email');
            }
            if (!Schema::hasColumn('companies', 'vat_number')) {
                $table->string('vat_number', 100)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('companies', 'currency_id')) {
                $table->foreignId('currency_id')->nullable()->after('vat_number')->constrained('currencies')->nullOnDelete();
            }
        });

        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'code')) {
                $table->string('code', 50)->nullable()->after('name');
            }
            if (!Schema::hasColumn('departments', 'manager_id')) {
                $table->foreignId('manager_id')->nullable()->after('code')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('outlets', function (Blueprint $table) {
            if (!Schema::hasColumn('outlets', 'phone')) {
                $table->string('phone', 50)->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('outlets', 'email')) {
                $table->string('email')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('outlets', 'manager_id')) {
                $table->foreignId('manager_id')->nullable()->after('email')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn(['email', 'phone', 'vat_number', 'currency_id']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['code', 'manager_id']);
        });

        Schema::table('outlets', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['phone', 'email', 'manager_id']);
        });
    }
};
