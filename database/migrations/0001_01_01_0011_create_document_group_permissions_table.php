<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_group_permissions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->integer('document_main_id');
            $table->integer('group_id');
            $table->boolean('view')->nullable()->default(false);
            $table->boolean('edit')->nullable()->default(false);
            $table->boolean('delete')->nullable()->default(false);

            $table->index('document_main_id', 'document_group_relationship');
            $table->index('group_id', 'group_permission_relatinoship');

            $table
                ->foreign('document_main_id', 'document_group_relationship')
                ->references('id')
                ->on('document_mains')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table
                ->foreign('group_id', 'group_permission_relatinoship')
                ->references('id')
                ->on('groups')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_group_permissions');
    }
};
