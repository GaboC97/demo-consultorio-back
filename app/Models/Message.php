<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at'      => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments()
    {
        return $this->hasMany(\App\Models\MessageAttachment::class);
    }


    public function scopeInConversation(Builder $query, int $conversationId): Builder
    {
        return $query->where('conversation_id', $conversationId);
    }

    public function scopeUnreadForUser(Builder $query, int $userId): Builder
    {
        return $query
            ->where('sender_id', '!=', $userId)
            ->whereHas('conversation.users', function ($q) use ($userId) {
                $q->where('users.id', $userId)
                    ->where(function ($q2) {
                        $q2->whereNull('conversation_user.last_read_at')
                            ->orWhereColumn(
                                'messages.created_at',
                                '>',
                                'conversation_user.last_read_at'
                            );
                    });
            });
    }
}
