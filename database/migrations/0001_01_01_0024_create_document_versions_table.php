<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->integer('id', true);
            $table->integer('document_id');
            $table->integer('version');
            $table->unsignedInteger('created_by')->nullable();
            $table->json('snapshot');
            $table->timestamp('created_at')->nullable();

            $table->unique(['document_id', 'version'], 'document_versions_doc_ver_uq');
            $table->index('document_id', 'document_versions_doc_idx');

            $table
                ->foreign('document_id', 'document_versions_document_fk')
                ->references('id')
                ->on('document_mains')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table
                ->foreign('created_by', 'document_versions_created_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
