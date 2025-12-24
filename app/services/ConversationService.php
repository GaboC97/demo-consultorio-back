<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use App\Models\MessageAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ConversationService
{

    public function getOrCreateDirect(int $userAId, int $userBId): Conversation
    {
        if ($userAId === $userBId) {
            throw new \InvalidArgumentException('No se puede crear conversación con el mismo usuario.');
        }

        return DB::transaction(function () use ($userAId, $userBId) {
            $existing = Conversation::query()
                ->direct()
                ->whereHas('users', fn($q) => $q->where('users.id', $userAId))
                ->whereHas('users', fn($q) => $q->where('users.id', $userBId))
                ->withCount('users')
                ->having('users_count', '=', 2)
                ->first();

            if ($existing) {
                return $existing;
            }

            $conversation = Conversation::create([
                'type' => 'direct',
                'title' => null,
                'last_message_at' => null,
            ]);

            $conversation->users()->attach([
                $userAId => ['role' => 'member', 'last_read_at' => null],
                $userBId => ['role' => 'member', 'last_read_at' => null],
            ]);

            return $conversation->fresh(['users']);
        });
    }

    public function sendMessage(int $conversationId, int $senderId, string $body): Message
    {
        $body = trim($body);
        if ($body === '') {
            throw new \InvalidArgumentException('El mensaje no puede estar vacío.');
        }

        return DB::transaction(function () use ($conversationId, $senderId, $body) {

            $message = Message::create([
                'conversation_id' => $conversationId,
                'sender_id' => $senderId,
                'body' => $body,
                'delivered_at' => now(),
                'read_at' => null,
            ]);

            Conversation::where('id', $conversationId)
                ->update(['last_message_at' => $message->created_at]);

            return $message->fresh(['sender']);
        });
    }

    public function markAsRead(int $conversationId, int $userId): void
    {
        DB::table('conversation_user')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->update(['last_read_at' => now(), 'updated_at' => now()]);
    }

    public function unreadCount(int $conversationId, int $userId): int
    {
        $lastReadAt = DB::table('conversation_user')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->value('last_read_at');

        return Message::query()
            ->where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->when($lastReadAt, fn($q) => $q->where('created_at', '>', $lastReadAt))
            ->count();
    }

    public function sendMessageWithAttachments(
        int $conversationId,
        int $senderId,
        ?string $body,
        array $files
    ): Message {
        return DB::transaction(function () use ($conversationId, $senderId, $body, $files) {

            $message = Message::create([
                'conversation_id' => $conversationId,
                'sender_id' => $senderId,
                'body' => $body,
                'delivered_at' => now(),
                'read_at' => null,
            ]);

            foreach ($files as $file) {
                /** @var UploadedFile $file */
                $uuid = (string) Str::uuid();
                $safeName = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $file->getClientOriginalName());
                $path = "chats/{$conversationId}/{$uuid}-{$safeName}";

                $stored = Storage::disk('public')->putFileAs(
                    dirname($path),
                    $file,
                    basename($path)
                );

                MessageAttachment::create([
                    'message_id' => $message->id,
                    'disk' => 'public',
                    'file_path' => $stored,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize() ?? 0,
                    'sha1' => sha1_file($file->getRealPath()),
                ]);
            }

            Conversation::where('id', $conversationId)
                ->update(['last_message_at' => now()]);

            return $message->fresh([
                'sender:id,name',
                'attachments'
            ]);
        });
    }
}
