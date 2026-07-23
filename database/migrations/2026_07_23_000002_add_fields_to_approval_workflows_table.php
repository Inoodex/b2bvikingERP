<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_workflows', function (Blueprint $table) {
            if (!Schema::hasColumn('approval_workflows', 'model_type')) {
                $table->string('model_type')->nullable()->after('name');
            }
            if (!Schema::hasColumn('approval_workflows', 'min_amount')) {
                $table->double('min_amount')->default(0)->after('model_type');
            }
            if (!Schema::hasColumn('approval_workflows', 'max_amount')) {
                $table->double('max_amount')->nullable()->after('min_amount');
            }
            if (!Schema::hasColumn('approval_workflows', 'status')) {
                $table->boolean('status')->default(true)->after('max_amount');
            }
        });

        Schema::table('approval_steps', function (Blueprint $table) {
            if (!Schema::hasColumn('approval_steps', 'step_name')) {
                $table->string('step_name')->nullable()->after('approval_workflow_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('approval_workflows', function (Blueprint $table) {
            $table->dropColumn(['model_type', 'min_amount', 'max_amount', 'status']);
        });

        Schema::table('approval_steps', function (Blueprint $table) {
            $table->dropColumn(['step_name']);
        });
    }
};
