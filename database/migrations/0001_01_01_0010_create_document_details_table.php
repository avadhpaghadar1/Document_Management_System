<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_details', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->integer('document_id');
            $table->string('field_name', 150);
            $table->string('field_type', 150);
            $table->string('field_value', 150)->nullable();

            $table->index('document_id', 'document_detail_relationship');
            $table
                ->foreign('document_id', 'document_detail_relationship')
                ->references('id')
                ->on('document_mains')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_details');
    }
};
