<div class="flex h-full w-full flex-col">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-2.5 dark:border-gray-700">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
            Recent Interactions
        </p>

        @if ($this->canCreateConversation())
            {{ $this->newConversationAction }}
        @endif
    </div>

    {{-- Conversation List --}}
    <div class="flex-1 overflow-y-auto" role="list" aria-label="Conversations">
        @forelse ($this->conversations as $conversation)
            <div role="listitem">
                <x-filament-chat::conversation-item
                    :conversation="$conversation"
                    :selected="$selectedConversationId === $conversation->id"
                    :source="$this->getSource()"
                />
            </div>
        @empty
            <div class="flex flex-col items-center justify-center gap-2 p-8 text-sm text-gray-500 dark:text-gray-400" role="status">
                <p>No conversations yet.</p>
                @if ($this->canCreateConversation())
                    <p class="text-xs">Click + to start one.</p>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Action modals --}}
    <x-filament-actions::modals />
</div>
