<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_file_analyses', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->integer('id', true);
            $table->integer('document_id');
            $table->string('file_name', 150);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('sha256', 64)->nullable();

            $table->integer('pdf_page_count')->nullable();
            $table->integer('image_width')->nullable();
            $table->integer('image_height')->nullable();
            $table->json('exif')->nullable();

            $table->timestamp('analyzed_at')->nullable();

            $table->index(['document_id', 'file_name'], 'document_file_analyses_doc_file_idx');
            $table->index('sha256', 'document_file_analyses_sha256_idx');

            $table
                ->foreign('document_id', 'document_file_analyses_document_fk')
                ->references('id')
                ->on('document_mains')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_file_analyses');
    }
};
