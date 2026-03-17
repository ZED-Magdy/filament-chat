<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Broadcasting;

use Illuminate\Database\Eloquent\Model;
use ZEDMagdy\FilamentChat\FilamentChat;

class ChatConversationChannel
{
    public function join(Model $user, int $conversationId): bool
    {
        return FilamentChat::getParticipantModel()::query()
            ->where('conversation_id', $conversationId)
            ->where('participantable_id', $user->getKey())
            ->where('participantable_type', $user->getMorphClass())
            ->exists();
    }
}
