<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('associations', function (Blueprint $table) {
            $table->id();

            // Dados básicos
            $table->string('nome');
            $table->enum('tipo', ['pf', 'cnpj']);
            $table->string('documento')->unique(); // CPF ou CNPJ
            $table->string('email')->unique();
            $table->string('telefone');

            // Endereço
            $table->string('endereco');
            $table->string('numero');
            $table->string('complemento')->nullable();
            $table->string('bairro');
            $table->string('cidade');
            $table->string('estado', 2);
            $table->string('cep', 9);

            // Dados específicos
            $table->date('data_fundacao')->nullable();
            $table->text('descricao')->nullable();
            $table->string('site')->nullable();
            $table->string('logo')->nullable();

            // Para CNPJ - Representante Legal
            $table->string('representante_nome')->nullable();
            $table->string('representante_cpf')->nullable();
            $table->string('representante_email')->nullable();
            $table->string('representante_telefone')->nullable();

            // Status e controle
            $table->enum('status', ['pendente', 'ativa', 'inativa', 'suspensa'])->default('pendente');
            $table->unsignedBigInteger('plano_id')->nullable();
            $table->decimal('mensalidade', 10, 2)->nullable();
            $table->date('data_vencimento')->nullable();

            // Dados adicionais
            $table->integer('numero_membros')->default(0);
            $table->json('configuracoes')->nullable();
            $table->text('observacoes')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['status', 'tipo']);
            $table->index('documento');
            $table->index('email');
        });
    }

    public function down()
    {
        Schema::dropIfExists('associations');
    }
};
