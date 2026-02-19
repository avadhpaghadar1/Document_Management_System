<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_audits', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->integer('id', true);
            $table->integer('document_id');
            $table->integer('document_type_id');
            $table->integer('user_id');
            $table->string('action', 100);
            $table->date('created_at');
            $table->date('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_audits');
    }
};
