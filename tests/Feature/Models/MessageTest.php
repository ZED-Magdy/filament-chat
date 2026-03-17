<?php

declare(strict_types=1);

use ZEDMagdy\FilamentChat\Models\Conversation;
use ZEDMagdy\FilamentChat\Models\Message;
use ZEDMagdy\FilamentChat\Tests\Fixtures\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->conversation = Conversation::factory()->create();
});

it('can create a message', function (): void {
    $message = Message::factory()->create([
        'conversation_id' => $this->conversation->id,
        'senderable_id' => $this->user->id,
        'senderable_type' => $this->user->getMorphClass(),
        'body' => 'Hello world',
    ]);

    expect($message->body)->toBe('Hello world')
        ->and($message->conversation_id)->toBe($this->conversation->id);
});

it('belongs to a conversation', function (): void {
    $message = Message::factory()->create([
        'conversation_id' => $this->conversation->id,
        'senderable_id' => $this->user->id,
        'senderable_type' => $this->user->getMorphClass(),
    ]);

    expect($message->conversation->id)->toBe($this->conversation->id);
});

it('has a senderable morph', function (): void {
    $message = Message::factory()->create([
        'conversation_id' => $this->conversation->id,
        'senderable_id' => $this->user->id,
        'senderable_type' => $this->user->getMorphClass(),
    ]);

    expect($message->senderable->id)->toBe($this->user->id);
});

it('can detect if sent by user', function (): void {
    $otherUser = User::factory()->create();

    $message = Message::factory()->create([
        'conversation_id' => $this->conversation->id,
        'senderable_id' => $this->user->id,
        'senderable_type' => $this->user->getMorphClass(),
    ]);

    expect($message->isSentBy($this->user))->toBeTrue()
        ->and($message->isSentBy($otherUser))->toBeFalse();
});

it('can detect system messages', function (): void {
    $message = Message::factory()->system()->create([
        'conversation_id' => $this->conversation->id,
        'body' => 'User joined the conversation',
    ]);

    expect($message->isSystemMessage())->toBeTrue();
});

it('casts metadata to array', function (): void {
    $message = Message::factory()->create([
        'conversation_id' => $this->conversation->id,
        'senderable_id' => $this->user->id,
        'senderable_type' => $this->user->getMorphClass(),
        'metadata' => ['type' => 'notification'],
    ]);

    $message->refresh();
    expect($message->metadata)->toBe(['type' => 'notification']);
});
