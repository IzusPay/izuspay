<?php

// database/migrations/xxxx_xx_xx_create_ledger_entries_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('association_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('related_type')->nullable(); // Ex: App\Models\Sale, App\Models\Withdrawal
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('type'); // Ex: 'sale_revenue', 'mdr_cost', 'platform_fee', 'withdrawal'
            $table->string('description');
            $table->decimal('amount', 15, 2); // Positivo para entradas, negativo para saídas
            $table->timestamps();

            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
