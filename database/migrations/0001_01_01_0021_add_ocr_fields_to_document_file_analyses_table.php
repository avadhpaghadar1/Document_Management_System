<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_file_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('document_file_analyses', 'ocr_text')) {
                $table->longText('ocr_text')->nullable()->after('exif');
            }
            if (!Schema::hasColumn('document_file_analyses', 'ocr_error')) {
                $table->string('ocr_error', 255)->nullable()->after('ocr_text');
            }
            if (!Schema::hasColumn('document_file_analyses', 'ocr_engine')) {
                $table->string('ocr_engine', 50)->nullable()->after('ocr_error');
            }
            if (!Schema::hasColumn('document_file_analyses', 'ocr_language')) {
                $table->string('ocr_language', 20)->nullable()->after('ocr_engine');
            }
            if (!Schema::hasColumn('document_file_analyses', 'ocr_completed_at')) {
                $table->timestamp('ocr_completed_at')->nullable()->after('ocr_language');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_file_analyses', function (Blueprint $table) {
            foreach (['ocr_text', 'ocr_error', 'ocr_engine', 'ocr_language', 'ocr_completed_at'] as $column) {
                if (Schema::hasColumn('document_file_analyses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
