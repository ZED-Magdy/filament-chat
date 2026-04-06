<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use ZEDMagdy\FilamentChat\ChatSource;
use ZEDMagdy\FilamentChat\FilamentChat;
use ZEDMagdy\FilamentChat\FilamentChatPlugin;

class ChatWindow extends Component
{
    public string $sourceKey = '';

    public ?int $conversationId = null;

    public int $page = 1;

    #[On('conversation-selected')]
    public function loadConversation(int $conversationId): void
    {
        $this->conversationId = $conversationId;
        $this->page = 1;
        $this->markAsRead();
    }

    public function loadMore(): void
    {
        $this->page++;
    }

    #[On('message-sent')]
    public function refreshMessages(): void
    {
        unset($this->messages);
        unset($this->othersLastReadAt);
        $this->markAsRead();
    }

    #[Computed]
    public function conversation(): ?Model
    {
        if (! $this->conversationId) {
            return null;
        }

        return FilamentChat::getConversationModel()::with('participants.participantable')
            ->find($this->conversationId);
    }

    #[Computed]
    public function messages(): Collection
    {
        if (! $this->conversationId) {
            return new Collection;
        }

        $perPage = config('filament-chat.messages_per_page', 50);

        return FilamentChat::getMessageModel()::query()
            ->where('conversation_id', $this->conversationId)
            ->with(['senderable', 'media'])
            ->latest()
            ->limit($perPage * $this->page)
            ->get()
            ->reverse()
            ->values();
    }

    #[Computed]
    public function othersLastReadAt(): ?Carbon
    {
        if (! $this->conversationId) {
            return null;
        }

        $user = filament()->auth()->user();

        $minLastRead = FilamentChat::getParticipantModel()::query()
            ->where('conversation_id', $this->conversationId)
            ->where(function ($q) use ($user): void {
                $q->where('participantable_id', '!=', $user->getKey())
                    ->orWhere('participantable_type', '!=', $user->getMorphClass());
            })
            ->whereNotNull('last_read_at')
            ->min('last_read_at');

        return $minLastRead ? Carbon::parse($minLastRead) : null;
    }

    public function markAsRead(): void
    {
        if (! $this->conversationId) {
            return;
        }

        $user = filament()->auth()->user();

        FilamentChat::getParticipantModel()::query()
            ->where('conversation_id', $this->conversationId)
            ->where('participantable_id', $user->getKey())
            ->where('participantable_type', $user->getMorphClass())
            ->update(['last_read_at' => now()]);
    }

    public function getSource(): ?ChatSource
    {
        return FilamentChatPlugin::get()->getSource($this->sourceKey);
    }

    public function getPollingInterval(): string
    {
        return FilamentChat::getPollingInterval();
    }

    public function isPollingMode(): bool
    {
        return FilamentChat::isPollingMode();
    }

    public function render(): View
    {
        return view('filament-chat::livewire.chat-window'); // @phpstan-ignore argument.type
    }
}
