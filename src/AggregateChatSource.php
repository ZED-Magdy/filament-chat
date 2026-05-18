<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat;

abstract class AggregateChatSource
{
    abstract public function getKey(): string;

    abstract public function getLabel(): string;

    abstract public function getIcon(): string;

    abstract public function getPageClass(): string;

    /**
     * @return array<int, string>
     */
    abstract public function getSourceKeys(): array;

    public function getSlug(): string
    {
        return 'chat/'.$this->getKey();
    }

    public function getNavigationGroup(): ?string
    {
        return 'Chat';
    }

    public function getNavigationSort(): ?int
    {
        return null;
    }
}
