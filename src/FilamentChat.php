<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat;

class FilamentChat
{
    public static function getConversationModel(): string
    {
        return config('filament-chat.models.conversation', Models\Conversation::class);
    }

    public static function getMessageModel(): string
    {
        return config('filament-chat.models.message', Models\Message::class);
    }

    public static function getParticipantModel(): string
    {
        return config('filament-chat.models.participant', Models\Participant::class);
    }

    public static function getTablePrefix(): string
    {
        return config('filament-chat.table_prefix', 'chat_');
    }

    public static function isPollingMode(): bool
    {
        return config('filament-chat.realtime.mode', 'polling') === 'polling';
    }

    public static function isBroadcastingMode(): bool
    {
        return config('filament-chat.realtime.mode') === 'broadcasting';
    }

    public static function getPollingInterval(): string
    {
        return config('filament-chat.realtime.polling_interval', '5s');
    }
}
