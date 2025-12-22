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
        Schema::table('plans', function (Blueprint $table) {
            // Preço do plano em centavos (INTEGER para evitar problemas de ponto flutuante)
            $table->integer('price')->after('recurrence')->nullable();
            // Hash da oferta na Goat Payments
            $table->string('offer_hash')->after('price')->nullable();
            // Hash do produto na Goat Payments
            $table->string('product_hash')->after('offer_hash')->nullable();
            // Duração do plano em meses para cálculo da assinatura
            $table->integer('duration_in_months')->after('product_hash')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'price',
                'offer_hash',
                'product_hash',
                'duration_in_months',
            ]);
        });
    }
};
