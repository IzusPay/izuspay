<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            if (! Schema::hasColumn('withdrawals', 'pix_key')) {
                $table->string('pix_key')->nullable()->after('bank_account_id');
            }
            if (! Schema::hasColumn('withdrawals', 'pix_key_type')) {
                $table->string('pix_key_type')->nullable()->after('pix_key');
            }
            if (! Schema::hasColumn('withdrawals', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            if (Schema::hasColumn('withdrawals', 'pix_key')) {
                $table->dropColumn('pix_key');
            }
            if (Schema::hasColumn('withdrawals', 'pix_key_type')) {
                $table->dropColumn('pix_key_type');
            }
            // Não remove 'notes' se já existir por outra migration
        });
    }
};
