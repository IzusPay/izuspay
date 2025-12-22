<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Torna a coluna bank_account_id opcional (NULL) para permitir saques via PIX sem conta bancária
        DB::statement('ALTER TABLE withdrawals MODIFY bank_account_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        // Reverte para NOT NULL
        DB::statement('ALTER TABLE withdrawals MODIFY bank_account_id BIGINT UNSIGNED NOT NULL');
    }
};
