<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('association_id')->nullable()->after('id');
            $table->enum('tipo', ['admin', 'cliente', 'membro'])->default('cliente')->after('association_id');
            $table->string('telefone')->nullable()->after('email');
            $table->string('documento')->nullable()->after('telefone'); // CPF
            $table->enum('status', ['ativo', 'inativo', 'pendente'])->default('ativo')->after('documento');

            $table->foreign('association_id')->references('id')->on('associations')->onDelete('cascade');
            $table->index(['association_id', 'tipo']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['association_id']);
            $table->dropColumn(['association_id', 'tipo', 'telefone', 'documento', 'status']);
        });
    }
};
