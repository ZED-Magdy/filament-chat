<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class ChatSource
{
    abstract public function getKey(): string;

    abstract public function getLabel(): string;

    abstract public function getIcon(): string;

    abstract public function getParticipantModel(): string;

    abstract public function getPageClass(): string;

    public function getSlug(): string
    {
        return 'chat/'.$this->getKey();
    }

    public function modifyQuery(Builder $query): Builder
    {
        return $query;
    }

    public function getAvailableParticipantsQuery(): Builder
    {
        return $this->getParticipantModel()::query();
    }

    public function allowsNewConversations(): bool
    {
        return true;
    }

    public function allowsGroupChats(): bool
    {
        return false;
    }

    public function getNavigationGroup(): ?string
    {
        return 'Chat';
    }

    public function getNavigationSort(): ?int
    {
        return null;
    }

    public function getParticipantDisplayName(Model $participant): string
    {
        return $participant->name ?? (string) $participant->getKey();
    }

    public function getParticipantAvatarUrl(Model $participant): ?string
    {
        return null;
    }
}
