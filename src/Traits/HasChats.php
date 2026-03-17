<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Traits;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use ZEDMagdy\FilamentChat\FilamentChat;

trait HasChats
{
    public function chatParticipations(): MorphMany
    {
        return $this->morphMany(FilamentChat::getParticipantModel(), 'participantable');
    }

    public function sentMessages(): MorphMany
    {
        return $this->morphMany(FilamentChat::getMessageModel(), 'senderable');
    }

    public function conversations(): HasManyThrough
    {
        return $this->hasManyThrough(
            FilamentChat::getConversationModel(),
            FilamentChat::getParticipantModel(),
            'participantable_id',
            'id',
            'id',
            'conversation_id',
        )->where(
            FilamentChat::getTablePrefix().'participants.participantable_type',
            $this->getMorphClass(),
        );
    }
}
