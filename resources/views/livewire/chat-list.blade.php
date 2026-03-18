<div class="flex h-full flex-col">
    {{-- Header --}}
    <div class="flex items-center gap-2 border-b border-gray-200 p-3 dark:border-gray-700">
        <div class="flex-1">
            <x-filament::input.wrapper>
                <x-filament::input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search conversations..."
                />
            </x-filament::input.wrapper>
        </div>

        @if ($this->canCreateConversation())
            {{ $this->newConversationAction }}
        @endif
    </div>

    {{-- Conversation List --}}
    <div class="flex-1 overflow-y-auto">
        @forelse ($this->conversations as $conversation)
            <x-filament-chat::conversation-item
                :conversation="$conversation"
                :selected="$selectedConversationId === $conversation->id"
                :source="$this->getSource()"
            />
        @empty
            <div class="flex flex-col items-center justify-center gap-3 p-8 text-sm text-gray-500 dark:text-gray-400">
                <p>No conversations yet.</p>
                @if ($this->canCreateConversation())
                    <p class="text-xs">Click the + button to start one.</p>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Action modals --}}
    <x-filament-actions::modals />
</div>
