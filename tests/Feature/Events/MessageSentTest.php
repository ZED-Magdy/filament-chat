<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use ZEDMagdy\FilamentChat\Events\MessageSent;
use ZEDMagdy\FilamentChat\Models\Conversation;
use ZEDMagdy\FilamentChat\Models\Message;
use ZEDMagdy\FilamentChat\Tests\Fixtures\User;

it('broadcasts on the correct private channel', function (): void {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();

    $message = Message::factory()->create([
        'conversation_id' => $conversation->id,
        'senderable_id' => $user->id,
        'senderable_type' => $user->getMorphClass(),
        'body' => 'Test message',
    ]);

    $event = new MessageSent($message);
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1)
        ->and($channels[0]->name)->toBe('private-chat.conversation.'.$conversation->id);
});

it('includes correct data in broadcast payload', function (): void {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();

    $message = Message::factory()->create([
        'conversation_id' => $conversation->id,
        'senderable_id' => $user->id,
        'senderable_type' => $user->getMorphClass(),
        'body' => 'Hello!',
    ]);

    $event = new MessageSent($message);
    $payload = $event->broadcastWith();

    expect($payload)
        ->toHaveKey('message_id', $message->id)
        ->toHaveKey('conversation_id', $conversation->id)
        ->toHaveKey('body', 'Hello!');
});

it('can be dispatched', function (): void {
    Event::fake([MessageSent::class]);

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();

    $message = Message::factory()->create([
        'conversation_id' => $conversation->id,
        'senderable_id' => $user->id,
        'senderable_type' => $user->getMorphClass(),
    ]);

    event(new MessageSent($message));

    Event::assertDispatched(MessageSent::class, function (MessageSent $event) use ($message): bool {
        return $event->message->id === $message->id;
    });
});
