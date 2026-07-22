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
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->after('user_id')->constrained('vendors')->nullOnDelete();
            $table->string('module', 50)->nullable()->after('vendor_id');
            $table->string('action', 100)->nullable()->after('module');
            $table->string('entity_type', 100)->nullable()->after('action');
            $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');
            $table->string('reference_no')->nullable()->after('entity_id');
            $table->text('description')->nullable()->after('reference_no');
            $table->json('old_values')->nullable()->after('description');
            $table->json('new_values')->nullable()->after('old_values');
            $table->string('ip_address', 45)->nullable()->after('new_values');
            $table->text('user_agent')->nullable()->after('ip_address');

            $table->index(['module', 'action']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('reference_no');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['vendor_id']);
            $table->dropIndex(['module', 'action']);
            $table->dropIndex(['entity_type', 'entity_id']);
            $table->dropIndex(['reference_no']);
            $table->dropIndex(['created_at']);
            $table->dropColumn([
                'user_id',
                'vendor_id',
                'module',
                'action',
                'entity_type',
                'entity_id',
                'reference_no',
                'description',
                'old_values',
                'new_values',
                'ip_address',
                'user_agent',
            ]);
        });
    }
};
