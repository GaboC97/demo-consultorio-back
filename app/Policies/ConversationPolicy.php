<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * ¿Puede ver/listar una conversación?
     */
    public function view(User $user, Conversation $conversation): bool
    {
        // Requiere relación users() en Conversation (belongsToMany)
        return $conversation->users()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * ¿Puede enviar mensajes en esa conversación?
     */
    public function send(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    /**
     * ¿Puede marcar como leído?
     */
    public function markAsRead(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
