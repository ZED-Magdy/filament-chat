<div class="flex h-full flex-col">
    {{-- Search --}}
    <div class="border-b border-gray-200 p-3 dark:border-gray-700">
        <x-filament::input.wrapper>
            <x-filament::input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search conversations..."
            />
        </x-filament::input.wrapper>
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
            <div class="flex items-center justify-center p-8 text-sm text-gray-500 dark:text-gray-400">
                No conversations yet.
            </div>
        @endforelse
    </div>
</div>
