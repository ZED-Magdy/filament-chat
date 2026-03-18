<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use ZEDMagdy\FilamentChat\Events\ConversationCreated;
use ZEDMagdy\FilamentChat\Models\Conversation;
use ZEDMagdy\FilamentChat\Models\Participant;
use ZEDMagdy\FilamentChat\Tests\Fixtures\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

it('can create a direct conversation with participants', function (): void {
    Event::fake([ConversationCreated::class]);

    $conversation = Conversation::create([
        'source' => 'staff',
        'type' => 'direct',
    ]);

    Participant::create([
        'conversation_id' => $conversation->id,
        'participantable_id' => $this->user->id,
        'participantable_type' => $this->user->getMorphClass(),
        'role' => 'member',
    ]);

    Participant::create([
        'conversation_id' => $conversation->id,
        'participantable_id' => $this->otherUser->id,
        'participantable_type' => $this->otherUser->getMorphClass(),
        'role' => 'member',
    ]);

    event(new ConversationCreated($conversation));

    expect($conversation->participants)->toHaveCount(2)
        ->and($conversation->isDirect())->toBeTrue();

    Event::assertDispatched(ConversationCreated::class);
});

it('can create a group conversation with name and multiple participants', function (): void {
    $thirdUser = User::factory()->create();

    $conversation = Conversation::create([
        'source' => 'staff',
        'type' => 'group',
        'name' => 'Project Team',
    ]);

    Participant::create([
        'conversation_id' => $conversation->id,
        'participantable_id' => $this->user->id,
        'participantable_type' => $this->user->getMorphClass(),
        'role' => 'owner',
    ]);

    Participant::create([
        'conversation_id' => $conversation->id,
        'participantable_id' => $this->otherUser->id,
        'participantable_type' => $this->otherUser->getMorphClass(),
        'role' => 'member',
    ]);

    Participant::create([
        'conversation_id' => $conversation->id,
        'participantable_id' => $thirdUser->id,
        'participantable_type' => $thirdUser->getMorphClass(),
        'role' => 'member',
    ]);

    expect($conversation->isGroup())->toBeTrue()
        ->and($conversation->name)->toBe('Project Team')
        ->and($conversation->participants)->toHaveCount(3);
});

it('can find an existing direct conversation between two users', function (): void {
    $conversation = Conversation::create([
        'source' => 'staff',
        'type' => 'direct',
    ]);

    Participant::create([
        'conversation_id' => $conversation->id,
        'participantable_id' => $this->user->id,
        'participantable_type' => $this->user->getMorphClass(),
    ]);

    Participant::create([
        'conversation_id' => $conversation->id,
        'participantable_id' => $this->otherUser->id,
        'participantable_type' => $this->otherUser->getMorphClass(),
    ]);

    // Search for existing direct conversation
    $existing = Conversation::query()
        ->forSource('staff')
        ->where('type', 'direct')
        ->forParticipant($this->user)
        ->whereHas('participants', function ($q): void {
            $q->where('participantable_id', $this->otherUser->id)
                ->where('participantable_type', $this->otherUser->getMorphClass());
        })
        ->first();

    expect($existing)->not->toBeNull()
        ->and($existing->id)->toBe($conversation->id);
});

it('does not find a direct conversation when none exists', function (): void {
    $thirdUser = User::factory()->create();

    $conversation = Conversation::create([
        'source' => 'staff',
        'type' => 'direct',
    ]);

    Participant::create([
        'conversation_id' => $conversation->id,
        'participantable_id' => $this->user->id,
        'participantable_type' => $this->user->getMorphClass(),
    ]);

    Participant::create([
        'conversation_id' => $conversation->id,
        'participantable_id' => $this->otherUser->id,
        'participantable_type' => $this->otherUser->getMorphClass(),
    ]);

    // Search for conversation with a third user - should not find one
    $existing = Conversation::query()
        ->forSource('staff')
        ->where('type', 'direct')
        ->forParticipant($this->user)
        ->whereHas('participants', function ($q) use ($thirdUser): void {
            $q->where('participantable_id', $thirdUser->id)
                ->where('participantable_type', $thirdUser->getMorphClass());
        })
        ->first();

    expect($existing)->toBeNull();
});
