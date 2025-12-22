<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            // Relacionamento com o gateway escolhido pelo cliente
            $table->foreignId('gateway_id')->nullable()->after('association_id')->constrained('gateways')->nullOnDelete();

            // Armazena as credenciais (ex: access_token, private_key) de forma criptografada
            $table->text('gateway_credentials')->nullable()->after('gateway_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropForeign(['gateway_id']);
            $table->dropColumn(['gateway_id', 'gateway_credentials']);
        });
    }
};
