<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_owners', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->integer('document_main_id');
            $table->unsignedInteger('user_id')->nullable();

            $table->index('document_main_id', 'owner_relatinship');
            $table->index('user_id', 'user_owner_relationship');

            $table
                ->foreign('document_main_id', 'owner_relatinship')
                ->references('id')
                ->on('document_mains')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table
                ->foreign('user_id', 'user_owner_relationship')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_owners');
    }
};
