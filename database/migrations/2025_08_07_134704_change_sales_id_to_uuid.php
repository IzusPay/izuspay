<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop the foreign key from the 'transactions' table
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
        });

        // 2. Modify the 'sales' table
        Schema::table('sales', function (Blueprint $table) {
            $table->string('id')->change();
        });

        // 3. Remove the existing primary key and define a new one
        Schema::table('sales', function (Blueprint $table) {
            $table->dropPrimary();
            $table->uuid('id')->change();
            $table->primary('id');
        });

        // 4. Re-add the foreign key to the 'transactions' table
        Schema::table('transactions', function (Blueprint $table) {
            $table->uuid('sale_id')->change();
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Revertimos as mudanças na ordem inversa

        // 1. Drop the foreign key from 'transactions'
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
        });

        // 2. Modify the 'sales' table back to auto-incrementing big integer
        Schema::table('sales', function (Blueprint $table) {
            $table->dropPrimary();
            $table->bigIncrements('id')->change();
        });

        // 3. Re-add the foreign key to 'transactions'
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_id')->change();
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
        });
    }
};
