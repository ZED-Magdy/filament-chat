<?php

declare(strict_types=1);

use ZEDMagdy\FilamentChat\AggregateChatSource;

it('exposes identity and defaults', function (): void {
    $aggregate = new class extends AggregateChatSource
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
    };

    expect($aggregate->getKey())->toBe('all')
        ->and($aggregate->getLabel())->toBe('All Messages')
        ->and($aggregate->getIcon())->toBe('heroicon-o-inbox-stack')
        ->and($aggregate->getSourceKeys())->toBe(['staff', 'support'])
        ->and($aggregate->getSlug())->toBe('chat/all')
        ->and($aggregate->getNavigationGroup())->toBe('Chat')
        ->and($aggregate->getNavigationSort())->toBeNull();
});
