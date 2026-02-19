<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_uploads', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->integer('id', true);
            $table->unsignedInteger('user_id');
            $table->string('file_name', 150);

            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('sha256', 64)->nullable();
            $table->integer('pdf_page_count')->nullable();
            $table->integer('image_width')->nullable();
            $table->integer('image_height')->nullable();
            $table->json('exif')->nullable();
            $table->timestamp('analyzed_at')->nullable();

            $table->longText('ocr_text')->nullable();
            $table->string('ocr_error', 255)->nullable();
            $table->string('ocr_engine', 50)->nullable();
            $table->string('ocr_language', 20)->nullable();
            $table->timestamp('ocr_completed_at')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->unique(['user_id', 'file_name'], 'document_uploads_user_file_uq');
            $table->index('sha256', 'document_uploads_sha256_idx');

            $table
                ->foreign('user_id', 'document_uploads_user_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_uploads');
    }
};
