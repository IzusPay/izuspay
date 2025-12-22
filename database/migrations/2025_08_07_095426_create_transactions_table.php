<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->string('transaction_id')->nullable()->unique(); // ID externo do gateway de pagamento
            $table->decimal('amount', 8, 2);
            $table->string('payment_method');
            $table->string('status'); // 'created', 'processing', 'approved', 'rejected'
            $table->text('details')->nullable(); // Detalhes da resposta do gateway (JSON)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
