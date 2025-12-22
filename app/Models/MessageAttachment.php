<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'metadata',
        'thumbnail_path',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
    ];

    /**
     * Relacionamento com a mensagem
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Scope para anexos de imagem
     */
    public function scopeImages($query)
    {
        return $query->where('file_type', 'image');
    }

    /**
     * Scope para anexos de vídeo
     */
    public function scopeVideos($query)
    {
        return $query->where('file_type', 'video');
    }

    /**
     * Scope para documentos
     */
    public function scopeDocuments($query)
    {
        return $query->where('file_type', 'document');
    }

    /**
     * Obtém a URL completa do arquivo
     */
    public function getFileUrl(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Obtém a URL da thumbnail (se existir)
     */
    public function getThumbnailUrl(): ?string
    {
        return $this->thumbnail_path ? Storage::url($this->thumbnail_path) : null;
    }

    /**
     * Verifica se o arquivo é uma imagem
     */
    public function isImage(): bool
    {
        return $this->file_type === 'image';
    }

    /**
     * Verifica se o arquivo é um vídeo
     */
    public function isVideo(): bool
    {
        return $this->file_type === 'video';
    }

    /**
     * Verifica se o arquivo é um documento
     */
    public function isDocument(): bool
    {
        return $this->file_type === 'document';
    }

    /**
     * Obtém o tamanho formatado do arquivo
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Obtém as dimensões da imagem (se for imagem)
     */
    public function getDimensions(): ?array
    {
        if ($this->isImage() && isset($this->metadata['dimensions'])) {
            return $this->metadata['dimensions'];
        }

        return null;
    }

    /**
     * Obtém a duração do vídeo (se for vídeo)
     */
    public function getDuration(): ?int
    {
        if ($this->isVideo() && isset($this->metadata['duration'])) {
            return $this->metadata['duration'];
        }

        return null;
    }

    /**
     * Obtém a duração formatada do vídeo
     */
    public function getFormattedDurationAttribute(): ?string
    {
        $duration = $this->getDuration();

        if (! $duration) {
            return null;
        }

        $minutes = floor($duration / 60);
        $seconds = $duration % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Deleta o arquivo físico do storage
     */
    public function deleteFile(): bool
    {
        $deleted = true;

        // Deleta o arquivo principal
        if (Storage::exists($this->file_path)) {
            $deleted = Storage::delete($this->file_path);
        }

        // Deleta a thumbnail se existir
        if ($this->thumbnail_path && Storage::exists($this->thumbnail_path)) {
            Storage::delete($this->thumbnail_path);
        }

        return $deleted;
    }

    /**
     * Determina o tipo de arquivo baseado no MIME type
     */
    public static function determineFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        return 'document';
    }

    /**
     * Valida se o tipo de arquivo é permitido
     */
    public static function isAllowedMimeType(string $mimeType): bool
    {
        $allowedTypes = [
            // Imagens
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',

            // Vídeos
            'video/mp4',
            'video/mpeg',
            'video/quicktime',
            'video/webm',

            // Documentos
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
        ];

        return in_array($mimeType, $allowedTypes);
    }

    /**
     * Obtém o tamanho máximo permitido para o tipo de arquivo (em bytes)
     */
    public static function getMaxFileSize(string $fileType): int
    {
        return match ($fileType) {
            'image' => 10 * 1024 * 1024, // 10MB
            'video' => 100 * 1024 * 1024, // 100MB
            'document' => 25 * 1024 * 1024, // 25MB
            default => 5 * 1024 * 1024 // 5MB
        };
    }

    /**
     * Boot method para eventos do modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Deleta arquivos físicos quando o anexo é deletado
        static::deleting(function ($attachment) {
            $attachment->deleteFile();
        });
    }
}
