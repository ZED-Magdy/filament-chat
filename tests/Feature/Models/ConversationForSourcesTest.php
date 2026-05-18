<?php

declare(strict_types=1);

use ZEDMagdy\FilamentChat\Models\Conversation;

it('filters conversations to a set of sources', function (): void {
    $staff = Conversation::create(['source' => 'staff', 'type' => 'direct']);
    $support = Conversation::create(['source' => 'support', 'type' => 'direct']);
    Conversation::create(['source' => 'patients', 'type' => 'direct']);

    $ids = Conversation::query()
        ->forSources(['staff', 'support'])
        ->pluck('id')
        ->sort()
        ->values()
        ->all();

    expect($ids)->toBe([$staff->id, $support->id]);
});

it('returns nothing for an empty source set', function (): void {
    Conversation::create(['source' => 'staff', 'type' => 'direct']);

    expect(Conversation::query()->forSources([])->count())->toBe(0);
});
