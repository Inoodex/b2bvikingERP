<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approvals', function (Blueprint $table) {
            if (Schema::hasColumn('approvals', 'step_order')) {
                $table->dropColumn('step_order');
            }
            if (Schema::hasColumn('approvals', 'approver_id')) {
                $table->dropForeign(['approver_id']);
                $table->dropColumn('approver_id');
            }
            if (Schema::hasColumn('approvals', 'remarks')) {
                $table->dropColumn('remarks');
            }
            if (Schema::hasColumn('approvals', 'acted_at')) {
                $table->dropColumn('acted_at');
            }
            
            if (!Schema::hasColumn('approvals', 'approval_step_id')) {
                $table->foreignId('approval_step_id')->nullable()->constrained('approval_steps')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('approvals', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('approvals', 'comments')) {
                $table->text('comments')->nullable();
            }
        });
    }

    public function down(): void
    {
        // 
    }
};
