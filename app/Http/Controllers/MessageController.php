<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Services\ConversationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    public function __construct(
        private ConversationService $conversationService
    ) {}

    /**
     * Listar mensajes de una conversación
     */

    public function index(Request $request, $conversationId)
    {
        $userId = $request->user()->id;

        $conversation = Conversation::query()
            ->forUser($userId)
            ->findOrFail($conversationId);

        $this->authorize('view', $conversation);

        $messages = Message::query()
            ->where('conversation_id', $conversation->id)
            ->with(['sender:id,name', 'attachments'])
            ->latest()
            ->paginate(30);

        return response()->json($messages);
    }

    public function store(Request $request, $conversationId)
    {
        $userId = $request->user()->id;

        $conversation = Conversation::query()
            ->forUser($userId)
            ->findOrFail($conversationId);

        $this->authorize('view', $conversation);

        $request->validate([
            'body' => ['nullable', 'string'],
            'files' => ['nullable', 'array', 'max:6'],
            'files.*' => ['file', 'max:10240'],                     
        ]);

        $body = trim((string) $request->input('body', ''));

        if ($body === '' && !$request->hasFile('files')) {
            throw ValidationException::withMessages([
                'body' => 'El mensaje no puede estar vacío si no adjuntás archivos.',
            ]);
        }

        $disk = 'public';

        $message = DB::transaction(function () use ($conversation, $userId, $body, $request, $disk) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $userId,
                'body' => $body,
                'delivered_at' => now(),
                'read_at' => null,
            ]);

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store("chats/{$conversation->id}", $disk);

                    MessageAttachment::create([
                        'message_id' => $message->id,
                        'disk' => $disk,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                        'sha1' => sha1_file($file->getRealPath()),
                    ]);
                }
            }

            $conversation->update(['last_message_at' => $message->created_at]);

            return $message;
        });

        return response()->json([
            'data' => $message->load(['sender:id,name', 'attachments'])
        ], 201);
    }


public function markAsRead(Request $request, $conversationId)
{
    $conversation = Conversation::findOrFail($conversationId);

    $this->authorize('markAsRead', $conversation);

    $this->conversationService->markAsRead($conversation->id, $request->user()->id);

    return response()->json(['message' => 'Conversación marcada como leída']);
}

}
