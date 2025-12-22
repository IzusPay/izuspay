<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->tinyInteger('tipo_produto')->nullable()->after('association_id');
            $table->tinyInteger('entrega_produto')->nullable()->after('tipo_produto');
            $table->unsignedBigInteger('categoria_produto')->nullable()->after('entrega_produto');
            $table->string('url_venda')->nullable()->after('categoria_produto');
            $table->string('nome_sac')->nullable()->after('url_venda');
            $table->string('email_sac')->nullable()->after('nome_sac');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_produto',
                'entrega_produto',
                'categoria_produto',
                'url_venda',
                'nome_sac',
                'email_sac',
            ]);
        });
    }
};
