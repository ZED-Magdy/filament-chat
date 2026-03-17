<div class="flex h-full flex-col">
    @if ($this->conversation)
        {{-- Header --}}
        <div class="flex items-center gap-3 border-b border-gray-200 px-4 py-3 dark:border-gray-700">
            @php
                $user = filament()->auth()->user();
                $source = $this->getSource();
                $displayName = $this->conversation->isGroup()
                    ? $this->conversation->name
                    : ($this->conversation->getOtherParticipant($user)
                        ? $source->getParticipantDisplayName($this->conversation->getOtherParticipant($user)->participantable)
                        : 'Unknown');
            @endphp
            <x-filament-chat::avatar :name="$displayName" class="h-10 w-10" />
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $displayName }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $this->conversation->participants->count() }} participant(s)
                </p>
            </div>
        </div>

        {{-- Messages --}}
        <div
            class="flex-1 overflow-y-auto p-4 space-y-3"
            x-data="{ init() { this.$el.scrollTop = this.$el.scrollHeight; } }"
            @if ($this->isPollingMode())
                wire:poll.{{ $this->getPollingInterval() }}="refreshMessages"
            @endif
            x-init="
                @if (\ZEDMagdy\FilamentChat\FilamentChat::isBroadcastingMode())
                    Echo.private('chat.conversation.{{ $this->conversationId }}')
                        .listen('MessageSent', () => $wire.refreshMessages());
                @endif
            "
        >
            @if ($this->messages->count() >= config('filament-chat.messages_per_page', 50) * $this->page)
                <div class="text-center">
                    <x-filament::button size="xs" color="gray" wire:click="loadMore">
                        Load earlier messages
                    </x-filament::button>
                </div>
            @endif

            @foreach ($this->messages as $message)
                <x-filament-chat::message-bubble
                    :message="$message"
                    :is-sent="$message->isSentBy(filament()->auth()->user())"
                    :source="$source"
                />
            @endforeach
        </div>

        {{-- Message Input --}}
        <div class="border-t border-gray-200 dark:border-gray-700">
            <livewire:filament-chat::message-input :conversation-id="$this->conversationId" :key="'input-' . $this->conversationId" />
        </div>
    @else
        {{-- Empty State --}}
        <div class="flex flex-1 items-center justify-center text-gray-400 dark:text-gray-500">
            <div class="text-center">
                <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="mx-auto mb-4 h-12 w-12" />
                <p>Select a conversation to start chatting</p>
            </div>
        </div>
    @endif
</div>
