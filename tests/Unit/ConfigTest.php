<?php

declare(strict_types=1);

use ZEDMagdy\FilamentChat\FilamentChat;
use ZEDMagdy\FilamentChat\Models\Conversation;
use ZEDMagdy\FilamentChat\Models\Message;
use ZEDMagdy\FilamentChat\Models\Participant;

it('resolves model classes from config', function (): void {
    expect(FilamentChat::getConversationModel())->toBe(Conversation::class)
        ->and(FilamentChat::getMessageModel())->toBe(Message::class)
        ->and(FilamentChat::getParticipantModel())->toBe(Participant::class);
});

it('resolves table prefix from config', function (): void {
    expect(FilamentChat::getTablePrefix())->toBe('chat_');
});

it('resolves realtime mode from config', function (): void {
    config()->set('filament-chat.realtime.mode', 'polling');
    expect(FilamentChat::isPollingMode())->toBeTrue()
        ->and(FilamentChat::isBroadcastingMode())->toBeFalse();

    config()->set('filament-chat.realtime.mode', 'broadcasting');
    expect(FilamentChat::isPollingMode())->toBeFalse()
        ->and(FilamentChat::isBroadcastingMode())->toBeTrue();
});

it('resolves polling interval from config', function (): void {
    expect(FilamentChat::getPollingInterval())->toBe('5s');

    config()->set('filament-chat.realtime.polling_interval', '10s');
    expect(FilamentChat::getPollingInterval())->toBe('10s');
});
