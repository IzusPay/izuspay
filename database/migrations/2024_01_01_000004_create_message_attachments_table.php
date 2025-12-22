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
        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->string('file_name'); // Nome original do arquivo
            $table->string('file_path'); // Caminho do arquivo no storage
            $table->string('file_type'); // image, video, document
            $table->string('mime_type'); // Tipo MIME do arquivo
            $table->bigInteger('file_size'); // Tamanho em bytes
            $table->json('metadata')->nullable(); // Metadados específicos (dimensões, duração, etc.)
            $table->string('thumbnail_path')->nullable(); // Caminho da thumbnail (para vídeos e imagens)
            $table->timestamps();

            $table->index(['message_id', 'file_type']);
            $table->index('file_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_attachments');
    }
};
