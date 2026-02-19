<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->unsignedInteger('user_id')->nullable();
            $table->integer('permission_id');

            $table->index('user_id', 'user_relationship');
            $table->index('permission_id', 'permission_relationship');

            $table
                ->foreign('user_id', 'user_relationship')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table
                ->foreign('permission_id', 'permission_relationship')
                ->references('id')
                ->on('permissions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
