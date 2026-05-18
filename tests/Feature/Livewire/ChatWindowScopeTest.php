<?php

declare(strict_types=1);

use ZEDMagdy\FilamentChat\Livewire\ChatWindow;
use ZEDMagdy\FilamentChat\Models\Conversation;

it('treats a conversation as in scope when its source is among the keys', function (): void {
    $conversation = Conversation::create(['source' => 'support', 'type' => 'direct']);

    $window = new ChatWindow;
    $window->sourceKeys = ['staff', 'support'];

    expect($window->isConversationInScope($conversation))->toBeTrue();
});

it('treats a conversation as out of scope when its source is not among the keys', function (): void {
    $conversation = Conversation::create(['source' => 'patients', 'type' => 'direct']);

    $window = new ChatWindow;
    $window->sourceKeys = ['staff', 'support'];

    expect($window->isConversationInScope($conversation))->toBeFalse();
});

it('treats every conversation as in scope when no source keys are set', function (): void {
    $conversation = Conversation::create(['source' => 'anything', 'type' => 'direct']);

    $window = new ChatWindow;
    $window->sourceKeys = [];

    expect($window->isConversationInScope($conversation))->toBeTrue();
});
