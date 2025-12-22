<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ConversationController extends Controller
{
    /**
     * Lista todas as conversas do usuário autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $conversations = Conversation::forUser($user->id)
            ->with([
                'lastMessage.user',
                'lastMessage.attachments',
                'participants' => function ($query) use ($user) {
                    $query->where('user_id', '!=', $user->id);
                },
            ])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        $conversationsData = $conversations->map(function ($conversation) use ($user) {
            $otherParticipant = $conversation->participants->first();

            return [
                'id' => $conversation->id,
                'type' => $conversation->type,
                'title' => $conversation->type === 'group' ? $conversation->title : $otherParticipant?->name,
                'image' => $conversation->type === 'group' ? $conversation->image : null,
                'last_message' => $conversation->lastMessage ? [
                    'content' => $conversation->lastMessage->formatted_content,
                    'type' => $conversation->lastMessage->type,
                    'created_at' => $conversation->lastMessage->created_at,
                    'user_name' => $conversation->lastMessage->user->name,
                    'is_own' => $conversation->lastMessage->user_id === $user->id,
                ] : null,
                'unread_count' => $conversation->getUnreadCountForUser($user->id),
                'participants_count' => $conversation->participants->count(),
                'other_participant' => $otherParticipant ? [
                    'id' => $otherParticipant->id,
                    'name' => $otherParticipant->name,
                    'username' => $otherParticipant->username ?? null,
                    'avatar' => $otherParticipant->avatar ?? null,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $conversationsData,
            'pagination' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    /**
     * Exibe uma conversa específica
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = Auth::user();

        $conversation = Conversation::with([
            'participants',
            'messages' => function ($query) {
                $query->with(['user', 'attachments', 'replyTo.user'])
                    ->orderBy('created_at', 'asc');
            },
        ])->find($id);

        if (! $conversation || ! $conversation->hasParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Conversa não encontrada ou acesso negado',
            ], 404);
        }

        // Marca mensagens como lidas
        $conversation->markAsReadForUser($user->id);

        $conversationData = [
            'id' => $conversation->id,
            'type' => $conversation->type,
            'title' => $conversation->title,
            'description' => $conversation->description,
            'image' => $conversation->image,
            'created_at' => $conversation->created_at,
            'participants' => $conversation->participants->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'username' => $participant->username ?? null,
                    'avatar' => $participant->avatar ?? null,
                    'role' => $participant->pivot->role,
                    'joined_at' => $participant->pivot->joined_at,
                ];
            }),
            'messages' => $conversation->messages->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'content' => $message->formatted_content,
                    'type' => $message->type,
                    'created_at' => $message->created_at,
                    'is_edited' => $message->is_edited,
                    'edited_at' => $message->edited_at,
                    'is_own' => $message->user_id === $user->id,
                    'user' => [
                        'id' => $message->user->id,
                        'name' => $message->user->name,
                        'avatar' => $message->user->avatar ?? null,
                    ],
                    'attachments' => $message->attachment_info,
                    'reply_to' => $message->replyTo ? [
                        'id' => $message->replyTo->id,
                        'content' => $message->replyTo->formatted_content,
                        'user_name' => $message->replyTo->user->name,
                    ] : null,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $conversationData,
        ]);
    }

    /**
     * Cria uma nova conversa
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:private,group',
            'title' => 'required_if:type,group|string|max:255',
            'description' => 'nullable|string|max:1000',
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $participants = $request->participants;

        // Para conversas privadas, verifica se já existe uma conversa entre os usuários
        if ($request->type === 'private' && count($participants) === 1) {
            $existingConversation = Conversation::findOrCreatePrivateConversation(
                $user->id,
                $participants[0]
            );

            return response()->json([
                'success' => true,
                'data' => ['id' => $existingConversation->id],
                'message' => 'Conversa encontrada ou criada com sucesso',
            ]);
        }

        // Cria nova conversa
        $conversation = Conversation::create([
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => $user->id,
        ]);

        // Adiciona o criador como participante
        $conversation->addParticipant($user->id, 'admin');

        // Adiciona outros participantes
        foreach ($participants as $participantId) {
            if ($participantId != $user->id) {
                $conversation->addParticipant($participantId);
            }
        }

        return response()->json([
            'success' => true,
            'data' => ['id' => $conversation->id],
            'message' => 'Conversa criada com sucesso',
        ], 201);
    }

    /**
     * Atualiza uma conversa (apenas grupos)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();

        $conversation = Conversation::find($id);

        if (! $conversation || ! $conversation->hasParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Conversa não encontrada ou acesso negado',
            ], 404);
        }

        if ($conversation->type !== 'group') {
            return response()->json([
                'success' => false,
                'message' => 'Apenas conversas em grupo podem ser editadas',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $conversation->update($request->only(['title', 'description']));

        return response()->json([
            'success' => true,
            'message' => 'Conversa atualizada com sucesso',
        ]);
    }

    /**
     * Adiciona participantes a uma conversa em grupo
     */
    public function addParticipants(Request $request, $id): JsonResponse
    {
        $user = Auth::user();

        $conversation = Conversation::find($id);

        if (! $conversation || ! $conversation->hasParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Conversa não encontrada ou acesso negado',
            ], 404);
        }

        if ($conversation->type !== 'group') {
            return response()->json([
                'success' => false,
                'message' => 'Apenas conversas em grupo permitem adicionar participantes',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $addedCount = 0;
        foreach ($request->participants as $participantId) {
            if (! $conversation->hasParticipant($participantId)) {
                $conversation->addParticipant($participantId);
                $addedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$addedCount} participante(s) adicionado(s) com sucesso",
        ]);
    }

    /**
     * Remove um participante da conversa
     */
    public function removeParticipant(Request $request, $id, $participantId): JsonResponse
    {
        $user = Auth::user();

        $conversation = Conversation::find($id);

        if (! $conversation || ! $conversation->hasParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Conversa não encontrada ou acesso negado',
            ], 404);
        }

        if ($conversation->type !== 'group') {
            return response()->json([
                'success' => false,
                'message' => 'Apenas conversas em grupo permitem remover participantes',
            ], 400);
        }

        $conversation->removeParticipant($participantId);

        return response()->json([
            'success' => true,
            'message' => 'Participante removido com sucesso',
        ]);
    }

    /**
     * Sai de uma conversa
     */
    public function leave(Request $request, $id): JsonResponse
    {
        $user = Auth::user();

        $conversation = Conversation::find($id);

        if (! $conversation || ! $conversation->hasParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Conversa não encontrada ou acesso negado',
            ], 404);
        }

        $conversation->removeParticipant($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Você saiu da conversa',
        ]);
    }

    /**
     * Busca usuários para iniciar conversa
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $user = Auth::user();

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $users = User::where('users.id', '!=', $user->id) // Prefira usar 'users.id' para evitar ambiguidade.
            ->join('creator_profiles', 'users.id', '=', 'creator_profiles.user_id')
            ->where(function ($q) use ($query) {
                $q->where('users.name', 'LIKE', "%{$query}%")
                    ->orWhere('creator_profiles.username', 'LIKE', "%{$query}%")
                    ->orWhere('users.email', 'LIKE', "%{$query}%");
            })
            ->select([
                'users.id',
                'users.name',
                'creator_profiles.username', // Selecione o username da tabela creator_profiles
                'users.email',
            ])
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }
}
