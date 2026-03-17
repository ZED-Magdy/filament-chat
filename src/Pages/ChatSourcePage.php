<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Pages;

use Filament\Pages\Page;
use Filament\Panel;
use ZEDMagdy\FilamentChat\ChatSource;
use ZEDMagdy\FilamentChat\FilamentChatPlugin;

abstract class ChatSourcePage extends Page
{
    protected string $view = 'filament-chat::pages.chat-source';

    protected static string $chatSourceKey = '';

    public static function getChatSource(): ChatSource
    {
        return FilamentChatPlugin::get()->getSource(static::$chatSourceKey);
    }

    public static function getNavigationLabel(): string
    {
        return static::getChatSource()->getLabel();
    }

    public static function getNavigationIcon(): string
    {
        return static::getChatSource()->getIcon();
    }

    public static function getNavigationGroup(): ?string
    {
        return static::getChatSource()->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return static::getChatSource()->getNavigationSort();
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return static::getChatSource()->getSlug();
    }

    public function getTitle(): string
    {
        return static::getChatSource()->getLabel();
    }

    public function getChatSourceKey(): string
    {
        return static::$chatSourceKey;
    }
}
