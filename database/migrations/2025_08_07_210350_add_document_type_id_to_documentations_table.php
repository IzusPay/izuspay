<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentations', function (Blueprint $table) {
            // Remove a coluna 'document_type' de texto e adiciona a chave estrangeira
            $table->dropColumn('document_type');
            $table->foreignId('document_type_id')->after('user_id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('documentations', function (Blueprint $table) {
            $table->dropForeign(['document_type_id']);
            $table->dropColumn('document_type_id');
            // Recria a coluna original
            $table->string('document_type')->after('user_id');
        });
    }
};
