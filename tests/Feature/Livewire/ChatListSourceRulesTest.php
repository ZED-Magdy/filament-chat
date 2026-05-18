<?php

declare(strict_types=1);

use ZEDMagdy\FilamentChat\Livewire\ChatList;

it('never allows creating conversations when spanning multiple sources', function (): void {
    $list = new ChatList;
    $list->sourceKeys = ['staff', 'support'];

    expect($list->canCreateConversation())->toBeFalse();
});

it('does not allow creating conversations with no sources', function (): void {
    $list = new ChatList;
    $list->sourceKeys = [];

    expect($list->canCreateConversation())->toBeFalse();
});
