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
        Schema::table('news', function (Blueprint $table) {
            // Adicionar coluna creator_profile_id (nullable para compatibilidade com dados existentes)
            $table->foreignId('creator_profile_id')->nullable()->after('user_id')->constrained('creator_profiles')->onDelete('set null');

            // Adicionar coluna category se não existir
            if (! Schema::hasColumn('news', 'category')) {
                $table->string('category')->nullable()->after('tags');
            }

            // Adicionar índices para melhor performance
            $table->index('creator_profile_id');
            $table->index(['status', 'published_at']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            // Remover índices
            $table->dropIndex(['creator_profile_id']);
            $table->dropIndex(['status', 'published_at']);

            if (Schema::hasColumn('news', 'category')) {
                $table->dropIndex(['category']);
                $table->dropColumn('category');
            }

            // Remover foreign key e coluna
            $table->dropForeign(['creator_profile_id']);
            $table->dropColumn('creator_profile_id');
        });
    }
};
