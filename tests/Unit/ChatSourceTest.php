<?php

declare(strict_types=1);

use ZEDMagdy\FilamentChat\ChatSource;
use ZEDMagdy\FilamentChat\Tests\Fixtures\User;

it('requires implementation of abstract methods', function (): void {
    $source = new class extends ChatSource
    {
        public function getKey(): string
        {
            return 'test';
        }

        public function getLabel(): string
        {
            return 'Test Chat';
        }

        public function getIcon(): string
        {
            return 'heroicon-o-chat-bubble-left';
        }

        public function getParticipantModel(): string
        {
            return User::class;
        }

        public function getPageClass(): string
        {
            return 'TestPage';
        }
    };

    expect($source->getKey())->toBe('test')
        ->and($source->getLabel())->toBe('Test Chat')
        ->and($source->getIcon())->toBe('heroicon-o-chat-bubble-left')
        ->and($source->getSlug())->toBe('chat/test')
        ->and($source->allowsGroupChats())->toBeFalse()
        ->and($source->getNavigationGroup())->toBe('Chat');
});

it('provides default participant display name', function (): void {
    $source = new class extends ChatSource
    {
        public function getKey(): string
        {
            return 'test';
        }

        public function getLabel(): string
        {
            return 'Test';
        }

        public function getIcon(): string
        {
            return 'heroicon-o-chat-bubble-left';
        }

        public function getParticipantModel(): string
        {
            return User::class;
        }

        public function getPageClass(): string
        {
            return 'TestPage';
        }
    };

    $user = new User(['name' => 'John Doe']);

    expect($source->getParticipantDisplayName($user))->toBe('John Doe');
});
