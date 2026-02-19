<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_group', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->integer('group_id');
            $table->unsignedInteger('user_id');

            $table->index('group_id', 'group_relation');
            $table->index('user_id', 'user_relation');

            $table
                ->foreign('group_id', 'group_relation')
                ->references('id')
                ->on('groups')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table
                ->foreign('user_id', 'user_relation')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_group');
    }
};
