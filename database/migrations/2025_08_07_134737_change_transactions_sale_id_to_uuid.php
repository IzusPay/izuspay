<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Remove a chave estrangeira antiga
            $table->dropForeign(['sale_id']);

            // Altera o tipo da coluna para uuid
            $table->uuid('sale_id')->change();

            // Adiciona a nova chave estrangeira
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->bigIncrements('sale_id')->change();
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
        });
    }
};
