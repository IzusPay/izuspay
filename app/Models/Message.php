<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'content',
        'type',
        'metadata',
        'reply_to_id',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relacionamento com a conversa
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Relacionamento com o usuário remetente
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com anexos da mensagem
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    /**
     * Relacionamento com a mensagem que está sendo respondida
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    /**
     * Relacionamento com as respostas desta mensagem
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }

    /**
     * Relacionamento com as leituras da mensagem
     */
    public function reads(): HasMany
    {
        return $this->hasMany(MessageRead::class);
    }

    /**
     * Scope para mensagens de texto
     */
    public function scopeText($query)
    {
        return $query->where('type', 'text');
    }

    /**
     * Scope para mensagens com imagem
     */
    public function scopeImage($query)
    {
        return $query->where('type', 'image');
    }

    /**
     * Scope para mensagens com vídeo
     */
    public function scopeVideo($query)
    {
        return $query->where('type', 'video');
    }

    /**
     * Scope para mensagens de sistema
     */
    public function scopeSystem($query)
    {
        return $query->where('type', 'system');
    }

    /**
     * Scope para mensagens não deletadas
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope para mensagens de uma conversa específica
     */
    public function scopeInConversation($query, $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    /**
     * Verifica se a mensagem foi lida por um usuário específico
     */
    public function isReadBy($userId): bool
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }

    /**
     * Marca a mensagem como lida por um usuário
     */
    public function markAsReadBy($userId): void
    {
        $this->reads()->firstOrCreate([
            'user_id' => $userId,
            'read_at' => now(),
        ]);
    }

    /**
     * Obtém usuários que leram a mensagem
     */
    public function getReadByUsers()
    {
        return User::whereIn('id', $this->reads()->pluck('user_id'))->get();
    }

    /**
     * Verifica se a mensagem tem anexos
     */
    public function hasAttachments(): bool
    {
        return $this->attachments()->exists();
    }

    /**
     * Verifica se a mensagem é uma resposta
     */
    public function isReply(): bool
    {
        return ! is_null($this->reply_to_id);
    }

    /**
     * Verifica se a mensagem foi editada
     */
    public function wasEdited(): bool
    {
        return $this->is_edited;
    }

    /**
     * Edita o conteúdo da mensagem
     */
    public function editContent($newContent): void
    {
        $this->update([
            'content' => $newContent,
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }

    /**
     * Deleta a mensagem (soft delete)
     */
    public function softDeleteMessage(): void
    {
        $this->update([
            'is_deleted' => true,
            'deleted_at' => now(),
        ]);
    }

    /**
     * Obtém o conteúdo formatado da mensagem
     */
    public function getFormattedContentAttribute(): string
    {
        if ($this->is_deleted) {
            return 'Esta mensagem foi deletada';
        }

        if ($this->type === 'system') {
            return $this->content;
        }

        return $this->content ?? '';
    }

    /**
     * Obtém informações de anexos formatadas
     */
    public function getAttachmentInfoAttribute(): array
    {
        return $this->attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'name' => $attachment->file_name,
                'type' => $attachment->file_type,
                'size' => $attachment->file_size,
                'url' => $attachment->getFileUrl(),
                'thumbnail' => $attachment->getThumbnailUrl(),
            ];
        })->toArray();
    }

    /**
     * Boot method para eventos do modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Atualiza timestamp da conversa quando uma mensagem é criada
        static::created(function ($message) {
            $message->conversation->updateLastMessageTime();
        });
    }
}
