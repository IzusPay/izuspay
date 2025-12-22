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
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('association_id')->constrained()->onDelete('cascade');
            $table->string('payment_method'); // 'credit_card', 'pix', 'boleto'
            $table->decimal('percentage_fee', 5, 2)->default(0); // Taxa em porcentagem
            $table->decimal('fixed_fee', 8, 2)->default(0);      // Taxa fixa em R$
            $table->timestamps();

            $table->unique(['association_id', 'payment_method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
