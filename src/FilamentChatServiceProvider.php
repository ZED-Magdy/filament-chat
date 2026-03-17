<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat;

use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use ZEDMagdy\FilamentChat\Commands\MakeChatSourceCommand;
use ZEDMagdy\FilamentChat\Livewire\ChatList;
use ZEDMagdy\FilamentChat\Livewire\ChatWindow;
use ZEDMagdy\FilamentChat\Livewire\MessageInput;

class FilamentChatServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-chat';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(MakeChatSourceCommand::class)
            ->hasMigrations([
                'create_chat_conversations_table',
                'create_chat_participants_table',
                'create_chat_messages_table',
            ]);
    }

    public function packageBooted(): void
    {
        Livewire::component('filament-chat::chat-list', ChatList::class);
        Livewire::component('filament-chat::chat-window', ChatWindow::class);
        Livewire::component('filament-chat::message-input', MessageInput::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/channels.php');
    }
}
