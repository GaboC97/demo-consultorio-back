<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * ¿Puede ver un mensaje?
     * (si pertenece a la conversación)
     */
    public function view(User $user, Message $message): bool
    {
        return $message->conversation
            ->users()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * ¿Puede crear/enviar mensajes en una conversación?
     * Nota: el "create" en policy recibe User y opcionalmente datos.
     * Nosotros chequeamos esto en ConversationPolicy::send.
     */
    public function create(User $user): bool
    {
        return true; // la validación real la hace ConversationPolicy::send
    }

    /**
     * (Opcional) ¿Puede borrar un mensaje?
     * Ejemplo: solo el autor y dentro de X minutos.
     */
    public function delete(User $user, Message $message): bool
    {
        // Si no querés borrado por ahora, dejalo false.
        return $message->sender_id === $user->id;
    }
}
