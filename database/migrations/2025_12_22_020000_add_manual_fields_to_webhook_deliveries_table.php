<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webhook_deliveries', function (Blueprint $table) {
            if (! Schema::hasColumn('webhook_deliveries', 'is_manual')) {
                $table->boolean('is_manual')->default(false)->after('status');
            }
            if (! Schema::hasColumn('webhook_deliveries', 'moderation_reason')) {
                $table->string('moderation_reason')->nullable()->after('error_message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('webhook_deliveries', function (Blueprint $table) {
            if (Schema::hasColumn('webhook_deliveries', 'is_manual')) {
                $table->dropColumn('is_manual');
            }
            if (Schema::hasColumn('webhook_deliveries', 'moderation_reason')) {
                $table->dropColumn('moderation_reason');
            }
        });
    }
};
