<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Relacionamento com a mensagem
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Relacionamento com o usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para leituras de um usuário específico
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para leituras de uma mensagem específica
     */
    public function scopeForMessage($query, $messageId)
    {
        return $query->where('message_id', $messageId);
    }

    /**
     * Scope para leituras em um período específico
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('read_at', [$startDate, $endDate]);
    }

    /**
     * Marca múltiplas mensagens como lidas por um usuário
     */
    public static function markMessagesAsRead(array $messageIds, int $userId): void
    {
        $existingReads = self::whereIn('message_id', $messageIds)
            ->where('user_id', $userId)
            ->pluck('message_id')
            ->toArray();

        $newMessageIds = array_diff($messageIds, $existingReads);

        if (! empty($newMessageIds)) {
            $reads = collect($newMessageIds)->map(function ($messageId) use ($userId) {
                return [
                    'message_id' => $messageId,
                    'user_id' => $userId,
                    'read_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            self::insert($reads);
        }
    }

    /**
     * Obtém estatísticas de leitura para uma conversa
     */
    public static function getConversationReadStats(int $conversationId): array
    {
        $messageIds = Message::where('conversation_id', $conversationId)->pluck('id');

        $totalMessages = $messageIds->count();
        $totalReads = self::whereIn('message_id', $messageIds)->count();

        return [
            'total_messages' => $totalMessages,
            'total_reads' => $totalReads,
            'read_percentage' => $totalMessages > 0 ? round(($totalReads / $totalMessages) * 100, 2) : 0,
        ];
    }

    /**
     * Obtém usuários que ainda não leram uma mensagem específica
     */
    public static function getUnreadUsers(int $messageId, int $conversationId): array
    {
        $message = Message::find($messageId);

        if (! $message) {
            return [];
        }

        $conversationParticipants = $message->conversation->participants()->pluck('users.id');
        $usersWhoRead = self::where('message_id', $messageId)->pluck('user_id');

        return $conversationParticipants->diff($usersWhoRead)->values()->toArray();
    }
}
