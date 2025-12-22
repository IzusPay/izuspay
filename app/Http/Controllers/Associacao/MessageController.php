<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    /**
     * Lista mensagens de uma conversa
     */
    public function index(Request $request, $conversationId): JsonResponse
    {
        $user = Auth::user();

        $conversation = Conversation::find($conversationId);

        if (! $conversation || ! $conversation->hasParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Conversa não encontrada ou acesso negado',
            ], 404);
        }

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 50);

        $messages = Message::inConversation($conversationId)
            ->with(['user', 'attachments', 'replyTo.user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $messagesData = $messages->map(function ($message) use ($user) {
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
                'is_read' => $message->isReadBy($user->id),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $messagesData->reverse()->values(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    /**
     * Envia uma nova mensagem
     */
    public function store(Request $request, $conversationId): JsonResponse
    {
        $user = Auth::user();

        $conversation = Conversation::find($conversationId);

        if (! $conversation || ! $conversation->hasParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Conversa não encontrada ou acesso negado',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required_without:attachments|string|max:5000',
            'type' => 'sometimes|in:text,image,video,file,gif',
            'reply_to_id' => 'sometimes|exists:messages,id',
            'attachments' => 'sometimes|array|max:5',
            'attachments.*' => 'file|max:102400', // 100MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Determina o tipo da mensagem
        $messageType = $request->get('type', 'text');
        if ($request->hasFile('attachments')) {
            $firstFile = $request->file('attachments')[0];
            $messageType = MessageAttachment::determineFileType($firstFile->getMimeType());
        }

        // Cria a mensagem
        $message = Message::create([
            'conversation_id' => $conversationId,
            'user_id' => $user->id,
            'content' => $request->content,
            'type' => $messageType,
            'reply_to_id' => $request->reply_to_id,
        ]);

        // Processa anexos se existirem
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $this->processAttachment($message, $file);
            }
        }

        // Marca como lida pelo remetente
        $message->markAsReadBy($user->id);

        // Carrega relacionamentos para resposta
        $message->load(['user', 'attachments', 'replyTo.user']);

        $messageData = [
            'id' => $message->id,
            'content' => $message->formatted_content,
            'type' => $message->type,
            'created_at' => $message->created_at,
            'is_own' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar ?? null,
            ],
            'attachments' => $message->attachment_info,
            'reply_to' => $message->replyTo ? [
                'id' => $message->replyTo->id,
                'content' => $message->replyTo->formatted_content,
                'user_name' => $message->replyTo->user->name,
            ] : null,
        ];

        return response()->json([
            'success' => true,
            'data' => $messageData,
            'message' => 'Mensagem enviada com sucesso',
        ], 201);
    }

    /**
     * Edita uma mensagem
     */
    public function update(Request $request, $conversationId, $messageId): JsonResponse
    {
        $user = Auth::user();

        $message = Message::inConversation($conversationId)->find($messageId);

        if (! $message || $message->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Mensagem não encontrada ou sem permissão para editar',
            ], 404);
        }

        if ($message->type !== 'text') {
            return response()->json([
                'success' => false,
                'message' => 'Apenas mensagens de texto podem ser editadas',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $message->editContent($request->content);

        return response()->json([
            'success' => true,
            'message' => 'Mensagem editada com sucesso',
        ]);
    }

    /**
     * Deleta uma mensagem
     */
    public function destroy(Request $request, $conversationId, $messageId): JsonResponse
    {
        $user = Auth::user();

        $message = Message::inConversation($conversationId)->find($messageId);

        if (! $message || $message->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Mensagem não encontrada ou sem permissão para deletar',
            ], 404);
        }

        $message->softDeleteMessage();

        return response()->json([
            'success' => true,
            'message' => 'Mensagem deletada com sucesso',
        ]);
    }

    /**
     * Marca mensagens como lidas
     */
    public function markAsRead(Request $request, $conversationId): JsonResponse
    {
        $user = Auth::user();

        $conversation = Conversation::find($conversationId);

        if (! $conversation || ! $conversation->hasParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Conversa não encontrada ou acesso negado',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'message_ids' => 'sometimes|array',
            'message_ids.*' => 'exists:messages,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->has('message_ids')) {
            // Marca mensagens específicas como lidas
            foreach ($request->message_ids as $messageId) {
                $message = Message::find($messageId);
                if ($message && $message->conversation_id == $conversationId) {
                    $message->markAsReadBy($user->id);
                }
            }
        } else {
            // Marca todas as mensagens da conversa como lidas
            $conversation->markAsReadForUser($user->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mensagens marcadas como lidas',
        ]);
    }

    /**
     * Busca mensagens na conversa
     */
    public function search(Request $request, $conversationId): JsonResponse
    {
        $user = Auth::user();

        $conversation = Conversation::find($conversationId);

        if (! $conversation || ! $conversation->hasParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Conversa não encontrada ou acesso negado',
            ], 404);
        }

        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $messages = Message::inConversation($conversationId)
            ->where('content', 'LIKE', "%{$query}%")
            ->where('type', 'text')
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $messagesData = $messages->map(function ($message) use ($user) {
            return [
                'id' => $message->id,
                'content' => $message->formatted_content,
                'created_at' => $message->created_at,
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                ],
                'is_own' => $message->user_id === $user->id,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $messagesData,
        ]);
    }

    /**
     * Processa um anexo de mensagem
     */
    private function processAttachment(Message $message, $file): MessageAttachment
    {
        $mimeType = $file->getMimeType();
        $fileType = MessageAttachment::determineFileType($mimeType);

        // Valida tipo de arquivo
        if (! MessageAttachment::isAllowedMimeType($mimeType)) {
            throw new \Exception('Tipo de arquivo não permitido');
        }

        // Valida tamanho do arquivo
        $maxSize = MessageAttachment::getMaxFileSize($fileType);
        if ($file->getSize() > $maxSize) {
            throw new \Exception('Arquivo muito grande');
        }

        // Gera nome único para o arquivo
        $fileName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $uniqueName = Str::uuid().'.'.$extension;

        // Define diretório baseado no tipo
        $directory = "messages/{$fileType}s/".date('Y/m');
        $filePath = $file->storeAs($directory, $uniqueName, 'public');

        // Prepara metadados
        $metadata = [];
        $thumbnailPath = null;

        if ($fileType === 'image') {
            $imageInfo = getimagesize($file->getPathname());
            if ($imageInfo) {
                $metadata['dimensions'] = [
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                ];
            }
        }

        // Cria registro do anexo
        return MessageAttachment::create([
            'message_id' => $message->id,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'mime_type' => $mimeType,
            'file_size' => $file->getSize(),
            'metadata' => $metadata,
            'thumbnail_path' => $thumbnailPath,
        ]);
    }
}
