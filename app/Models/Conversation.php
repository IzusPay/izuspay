<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'description',
        'image',
        'created_by',
        'last_message_at',
        'is_active',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relacionamento com o usuário criador da conversa
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relacionamento com as mensagens da conversa
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Relacionamento com a última mensagem
     */
    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Relacionamento com os participantes da conversa
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['role', 'joined_at', 'last_read_at', 'is_muted', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Relacionamento com participantes ativos
     */
    public function activeParticipants(): BelongsToMany
    {
        return $this->participants()->wherePivot('is_active', true);
    }

    /**
     * Scope para conversas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para conversas privadas
     */
    public function scopePrivate($query)
    {
        return $query->where('type', 'private');
    }

    /**
     * Scope para conversas em grupo
     */
    public function scopeGroup($query)
    {
        return $query->where('type', 'group');
    }

    /**
     * Scope para conversas de um usuário específico
     */
    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('is_active', true);
        });
    }

    /**
     * Verifica se um usuário é participante da conversa
     */
    public function hasParticipant($userId): bool
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }

    /**
     * Adiciona um participante à conversa
     */
    public function addParticipant($userId, $role = 'member'): void
    {
        $this->participants()->attach($userId, [
            'role' => $role,
            'joined_at' => now(),
            'is_active' => true,
        ]);
    }

    /**
     * Remove um participante da conversa
     */
    public function removeParticipant($userId): void
    {
        $this->participants()->updateExistingPivot($userId, ['is_active' => false]);
    }

    /**
     * Marca mensagens como lidas para um usuário
     */
    public function markAsReadForUser($userId): void
    {
        $this->participants()->updateExistingPivot($userId, [
            'last_read_at' => now(),
        ]);
    }

    /**
     * Conta mensagens não lidas para um usuário
     */
    public function getUnreadCountForUser($userId): int
    {
        $participant = $this->participants()->where('user_id', $userId)->first();

        if (! $participant || ! $participant->pivot->last_read_at) {
            return $this->messages()->count();
        }

        return $this->messages()
            ->where('created_at', '>', $participant->pivot->last_read_at)
            ->where('user_id', '!=', $userId)
            ->count();
    }

    /**
     * Atualiza o timestamp da última mensagem
     */
    public function updateLastMessageTime(): void
    {
        $this->update(['last_message_at' => now()]);
    }

    /**
     * Busca ou cria uma conversa privada entre dois usuários
     */
    public static function findOrCreatePrivateConversation($user1Id, $user2Id): self
    {
        // Busca conversa existente
        $conversation = self::private()
            ->whereHas('participants', function ($q) use ($user1Id) {
                $q->where('user_id', $user1Id);
            })
            ->whereHas('participants', function ($q) use ($user2Id) {
                $q->where('user_id', $user2Id);
            })
            ->first();

        if ($conversation) {
            return $conversation;
        }

        // Cria nova conversa
        $conversation = self::create([
            'type' => 'private',
            'created_by' => $user1Id,
            'is_active' => true,
        ]);

        // Adiciona participantes
        $conversation->addParticipant($user1Id);
        $conversation->addParticipant($user2Id);

        return $conversation;
    }
}
