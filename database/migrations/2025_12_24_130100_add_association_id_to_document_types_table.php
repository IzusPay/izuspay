<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            if (! Schema::hasColumn('document_types', 'association_id')) {
                $table->foreignId('association_id')
                    ->nullable()
                    ->constrained('associations')
                    ->cascadeOnDelete()
                    ->after('id');
                $table->index(['association_id', 'is_active']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            if (Schema::hasColumn('document_types', 'association_id')) {
                $table->dropConstrainedForeignId('association_id');
                $table->dropIndex(['association_id', 'is_active']);
            }
        });
    }
};
