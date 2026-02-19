<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_links', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->integer('id', true);
            $table->string('token', 64);
            $table->integer('document_id');
            $table->string('file_name', 150);
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique('token', 'shared_links_token_uq');
            $table->index(['document_id', 'file_name'], 'shared_links_doc_file_idx');

            $table
                ->foreign('document_id', 'shared_links_document_fk')
                ->references('id')
                ->on('document_mains')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table
                ->foreign('created_by', 'shared_links_created_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_links');
    }
};
