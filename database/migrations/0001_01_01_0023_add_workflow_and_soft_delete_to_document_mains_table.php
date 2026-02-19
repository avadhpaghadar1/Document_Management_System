<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_mains', function (Blueprint $table) {
            if (!Schema::hasColumn('document_mains', 'approval_status')) {
                $table->string('approval_status', 20)->default('draft')->after('note');
                $table->index('approval_status', 'document_mains_approval_status_idx');
            }

            if (!Schema::hasColumn('document_mains', 'approved_by')) {
                $table->unsignedInteger('approved_by')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('document_mains', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('document_mains', 'rejected_reason')) {
                $table->string('rejected_reason', 255)->nullable()->after('approved_at');
            }

            if (!Schema::hasColumn('document_mains', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
                $table->index('deleted_at', 'document_mains_deleted_at_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_mains', function (Blueprint $table) {
            foreach (['document_mains_approval_status_idx', 'document_mains_deleted_at_idx'] as $idx) {
                try {
                    $table->dropIndex($idx);
                } catch (\Throwable) {
                }
            }

            foreach (['approval_status', 'approved_by', 'approved_at', 'rejected_reason', 'deleted_at'] as $column) {
                if (Schema::hasColumn('document_mains', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
