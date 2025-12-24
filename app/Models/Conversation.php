<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = [
        'type',          // direct | group
        'title',         // nullable
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /* =========================
     * Relationships
     * ========================= */

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_user')
            ->withPivot(['last_read_at', 'role'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->oldest();
    }

    // Útil para mostrar el último mensaje en el inbox
    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /* =========================
     * Scopes
     * ========================= */

    public function scopeDirect(Builder $query): Builder
    {
        return $query->where('type', 'direct');
    }

    public function scopeGroup(Builder $query): Builder
    {
        return $query->where('type', 'group');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas('users', fn ($q) => $q->where('users.id', $userId));
    }

    public function scopeInboxForUser(Builder $query, int $userId): Builder
    {
        return $query
            ->forUser($userId)
            ->orderByDesc('last_message_at')
            ->with(['lastMessage', 'users']);
    }

    /* =========================
     * Helpers
     * ========================= */

    /**
     * Devuelve el "otro participante" en chats directos.
     */
    public function otherParticipant(int $myUserId): ?User
    {
        if ($this->relationLoaded('users')) {
            return $this->users->firstWhere('id', '!=', $myUserId);
        }

        return $this->users()->where('users.id', '!=', $myUserId)->first();
    }
}
