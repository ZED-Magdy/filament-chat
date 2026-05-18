<?php

declare(strict_types=1);

use ZEDMagdy\FilamentChat\AggregateChatSource;
use ZEDMagdy\FilamentChat\FilamentChatPlugin;

class AllMessagesAggregateFixture extends AggregateChatSource
{
    public function getKey(): string
    {
        return 'all';
    }

    public function getLabel(): string
    {
        return 'All Messages';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-inbox-stack';
    }

    public function getPageClass(): string
    {
        return 'AllChatPage';
    }

    public function getSourceKeys(): array
    {
        return ['staff', 'support'];
    }
}

it('registers and resolves aggregates', function (): void {
    $plugin = FilamentChatPlugin::make()
        ->aggregates([AllMessagesAggregateFixture::class]);

    expect($plugin->getAggregates())->toBe([AllMessagesAggregateFixture::class])
        ->and($plugin->getResolvedAggregates())->toHaveCount(1)
        ->and($plugin->getResolvedAggregates()[0])->toBeInstanceOf(AllMessagesAggregateFixture::class)
        ->and($plugin->getAggregate('all'))->toBeInstanceOf(AllMessagesAggregateFixture::class)
        ->and($plugin->getAggregate('missing'))->toBeNull();
});
