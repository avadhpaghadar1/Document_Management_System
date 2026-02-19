<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_mains', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->integer('id', true);
            $table->integer('user_id');
            $table->integer('document_type_id');
            $table->date('expiry');
            $table->string('note', 200)->nullable();
            $table->date('created_at');
            $table->date('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_mains');
    }
};
