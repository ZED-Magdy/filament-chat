<?php

declare(strict_types=1);

use ZEDMagdy\FilamentChat\Models\Conversation;
use ZEDMagdy\FilamentChat\Models\Message;
use ZEDMagdy\FilamentChat\Models\Participant;
use ZEDMagdy\FilamentChat\Tests\Fixtures\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

it('can create a direct conversation', function (): void {
    $conversation = Conversation::factory()->create([
        'source' => 'staff',
        'type' => 'direct',
    ]);

    expect($conversation->source)->toBe('staff')
        ->and($conversation->type)->toBe('direct')
        ->and($conversation->isDirect())->toBeTrue()
        ->and($conversation->isGroup())->toBeFalse();
});

it('can create a group conversation', function (): void {
    $conversation = Conversation::factory()->group('Test Group')->create();

    expect($conversation->isGroup())->toBeTrue()
        ->and($conversation->name)->toBe('Test Group');
});

it('can scope by source', function (): void {
    Conversation::factory()->forSource('staff')->create();
    Conversation::factory()->forSource('patient')->create();
    Conversation::factory()->forSource('staff')->create();

    expect(Conversation::forSource('staff')->count())->toBe(2)
        ->and(Conversation::forSource('patient')->count())->toBe(1);
});

it('can scope by participant', function (): void {
    $conversation = Conversation::factory()->create();
    Participant::factory()->create([
        'conversation_id' => $conversation->id,
        'participantable_id' => $this->user->id,
        'participantable_type' => $this->user->getMorphClass(),
    ]);

    $otherConversation = Conversation::factory()->create();
    Participant::factory()->create([
        'conversation_id' => $otherConversation->id,
        'participantable_id' => $this->otherUser->id,
        'participantable_type' => $this->otherUser->getMorphClass(),
    ]);

    expect(Conversation::forParticipant($this->user)->count())->toBe(1)
        ->and(Conversation::forParticipant($this->user)->first()->id)->toBe($conversation->id);
});

it('can count unread messages', function (): void {
    $conversation = Conversation::factory()->create();

    Participant::factory()->create([
        'conversation_id' => $conversation->id,
        'participantable_id' => $this->user->id,
        'participantable_type' => $this->user->getMorphClass(),
        'last_read_at' => now()->subMinutes(5),
    ]);

    // Create messages: 2 before last_read_at, 3 after
    Message::factory()->count(2)->create([
        'conversation_id' => $conversation->id,
        'senderable_id' => $this->otherUser->id,
        'senderable_type' => $this->otherUser->getMorphClass(),
        'created_at' => now()->subMinutes(10),
    ]);

    Message::factory()->count(3)->create([
        'conversation_id' => $conversation->id,
        'senderable_id' => $this->otherUser->id,
        'senderable_type' => $this->otherUser->getMorphClass(),
        'created_at' => now(),
    ]);

    $result = Conversation::withUnreadCount($this->user)
        ->find($conversation->id);

    expect($result->unread_count)->toBe(3);
});

it('has messages relationship', function (): void {
    $conversation = Conversation::factory()->create();
    Message::factory()->count(3)->create([
        'conversation_id' => $conversation->id,
        'senderable_id' => $this->user->id,
        'senderable_type' => $this->user->getMorphClass(),
    ]);

    expect($conversation->messages)->toHaveCount(3);
});

it('has participants relationship', function (): void {
    $conversation = Conversation::factory()->create();
    Participant::factory()->create([
        'conversation_id' => $conversation->id,
        'participantable_id' => $this->user->id,
        'participantable_type' => $this->user->getMorphClass(),
    ]);
    Participant::factory()->create([
        'conversation_id' => $conversation->id,
        'participantable_id' => $this->otherUser->id,
        'participantable_type' => $this->otherUser->getMorphClass(),
    ]);

    expect($conversation->participants)->toHaveCount(2);
});

it('can get other participant in direct chat', function (): void {
    $conversation = Conversation::factory()->create(['type' => 'direct']);

    Participant::factory()->create([
        'conversation_id' => $conversation->id,
        'participantable_id' => $this->user->id,
        'participantable_type' => $this->user->getMorphClass(),
    ]);
    Participant::factory()->create([
        'conversation_id' => $conversation->id,
        'participantable_id' => $this->otherUser->id,
        'participantable_type' => $this->otherUser->getMorphClass(),
    ]);

    $conversation->load('participants');
    $other = $conversation->getOtherParticipant($this->user);

    expect($other->participantable_id)->toBe($this->otherUser->id);
});
