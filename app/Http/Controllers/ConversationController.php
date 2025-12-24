<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\ConversationService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(
        private ConversationService $conversationService
    ) {}

    /**
     * Inbox del usuario autenticado
     */
public function index(Request $request)
{
    $userId = $request->user()->id;

    $conversations = Conversation::query()
        ->forUser($userId)
        ->with([
            'users:id,name,email',
            'lastMessage.sender:id,name',
        ])
        ->withCount([
            'messages as unread_count' => function ($q) use ($userId) {
                $q->where('sender_id', '!=', $userId)
                  ->where(function ($qq) use ($userId) {
                      $qq->whereRaw(
                          "messages.created_at > COALESCE(
                              (SELECT cu.last_read_at
                               FROM conversation_user cu
                               WHERE cu.conversation_id = messages.conversation_id
                                 AND cu.user_id = ?),
                              '1970-01-01 00:00:00'
                          )",
                          [$userId]
                      );
                  });
            }
        ])
        ->orderByDesc('last_message_at')
        ->get();

    return response()->json(['data' => $conversations]);
}


    /**
     * Obtener o crear conversación directa
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id']
        ]);

        $conversation = $this->conversationService
            ->getOrCreateDirect(
                $request->user()->id,
                $request->user_id
            );

        return response()->json([
            'data' => $conversation->load('users:id,name,email')
        ], 201);
    }

    /**
     * Ver una conversación específica
     */
public function show(Request $request, $id)
{
    $conversation = Conversation::with(['users:id,name,email', 'lastMessage.sender:id,name'])
        ->findOrFail($id);

    $this->authorize('view', $conversation);

    return response()->json(['data' => $conversation]);
}

public function markAsRead(Request $request, $id)
{
    $userId = $request->user()->id;

    $conversation = Conversation::query()
        ->forUser($userId)
        ->findOrFail($id);

    $this->authorize('view', $conversation);

    // actualiza pivot last_read_at
    $conversation->users()->updateExistingPivot($userId, [
        'last_read_at' => now(),
    ]);

    return response()->json([
        'ok' => true,
        'data' => [
            'conversation_id' => (int) $conversation->id,
            'user_id' => (int) $userId,
            'last_read_at' => now()->toISOString(),
        ],
    ]);
}


}
