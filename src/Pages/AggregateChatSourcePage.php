<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Pages;

use Filament\Pages\Page;
use Filament\Panel;
use ZEDMagdy\FilamentChat\AggregateChatSource;
use ZEDMagdy\FilamentChat\FilamentChatPlugin;

abstract class AggregateChatSourcePage extends Page
{
    protected string $view = 'filament-chat::pages.chat-source';

    protected static string $aggregateKey = '';

    public static function getAggregate(): AggregateChatSource
    {
        $aggregate = FilamentChatPlugin::get()->getAggregate(static::$aggregateKey);

        if ($aggregate === null) {
            throw new \LogicException('No aggregate registered for key ['.static::$aggregateKey.'].');
        }

        return $aggregate;
    }

    public static function getNavigationLabel(): string
    {
        return static::getAggregate()->getLabel();
    }

    public static function getNavigationIcon(): string
    {
        return static::getAggregate()->getIcon();
    }

    public static function getNavigationGroup(): ?string
    {
        return static::getAggregate()->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return static::getAggregate()->getNavigationSort();
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return static::getAggregate()->getSlug();
    }

    public function getTitle(): string
    {
        return static::getAggregate()->getLabel();
    }

    public function getAggregateKey(): string
    {
        return static::$aggregateKey;
    }

    /**
     * @return array<int, string>
     */
    public function getSourceKeys(): array
    {
        return static::getAggregate()->getSourceKeys();
    }
}
