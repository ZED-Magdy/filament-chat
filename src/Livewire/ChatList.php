<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use ZEDMagdy\FilamentChat\ChatSource;
use ZEDMagdy\FilamentChat\FilamentChat;
use ZEDMagdy\FilamentChat\FilamentChatPlugin;

class ChatList extends Component
{
    public string $sourceKey = '';

    public string $search = '';

    public ?int $selectedConversationId = null;

    public function selectConversation(int $conversationId): void
    {
        $this->selectedConversationId = $conversationId;
        $this->dispatch('conversation-selected', conversationId: $conversationId);
    }

    #[Computed]
    public function conversations(): Collection
    {
        $user = filament()->auth()->user();
        $conversationModel = FilamentChat::getConversationModel();

        $query = $conversationModel::query()
            ->forSource($this->sourceKey)
            ->forParticipant($user)
            ->withUnreadCount($user)
            ->with(['participants.participantable', 'latestMessage'])
            ->latest('updated_at');

        if (filled($this->search)) {
            $source = FilamentChatPlugin::get()->getSource($this->sourceKey);

            $query->whereHas('participants', function ($q) use ($user, $source): void {
                $q->where(function ($q) use ($user): void {
                    $q->where('participantable_id', '!=', $user->getKey())
                        ->orWhere('participantable_type', '!=', $user->getMorphClass());
                })->whereHasMorph('participantable', [$source->getParticipantModel()], function ($q): void {
                    $q->where('name', 'like', "%{$this->search}%");
                });
            });
        }

        return $query->limit(config('filament-chat.conversations_per_page', 25))->get();
    }

    public function getSource(): ?ChatSource
    {
        return FilamentChatPlugin::get()->getSource($this->sourceKey);
    }

    public function render(): View
    {
        return view('filament-chat::livewire.chat-list');
    }
}
