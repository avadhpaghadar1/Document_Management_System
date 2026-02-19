<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_type_fields', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->integer('id', true);
            $table->integer('document_type_id');
            $table->string('field_name', 150);
            $table->string('field_type', 150);

            $table->index('document_type_id', 'document_type_relationship');
            $table
                ->foreign('document_type_id', 'document_type_relationship')
                ->references('id')
                ->on('document_types')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_type_fields');
    }
};
