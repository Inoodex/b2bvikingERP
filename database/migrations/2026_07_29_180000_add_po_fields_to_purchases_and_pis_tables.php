<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'po_no')) {
                $table->string('po_no', 50)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('purchases', 'milestone_status')) {
                $table->enum('milestone_status', ['draft', 'pending_approval', 'approved', 'po_sent', 'pi_attached', 'lc_opened', 'cancelled'])
                      ->default('draft')->after('approval_status');
            }
        });

        Schema::table('proforma_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('proforma_invoices', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('status');
            }
            if (!Schema::hasColumn('proforma_invoices', 'remarks')) {
                $table->text('remarks')->nullable()->after('attachment_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'po_no')) {
                $table->dropColumn('po_no');
            }
            if (Schema::hasColumn('purchases', 'milestone_status')) {
                $table->dropColumn('milestone_status');
            }
        });

        Schema::table('proforma_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('proforma_invoices', 'attachment_path')) {
                $table->dropColumn('attachment_path');
            }
            if (Schema::hasColumn('proforma_invoices', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};
