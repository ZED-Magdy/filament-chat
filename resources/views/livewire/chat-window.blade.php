<div class="flex h-full w-full flex-col">
    @if ($this->conversation)
        @php
            $user = filament()->auth()->user();
            $source = $this->getSource();
            $otherParticipant = $this->conversation->getOtherParticipant($user);
            $displayName = $this->conversation->isGroup()
                ? $this->conversation->name
                : ($otherParticipant
                    ? $source->getParticipantDisplayName($otherParticipant->participantable)
                    : 'Unknown');
            $avatarUrl = (! $this->conversation->isGroup() && $otherParticipant)
                ? $source->getParticipantAvatarUrl($otherParticipant->participantable)
                : null;
        @endphp

        {{-- Header --}}
        <div class="flex shrink-0 items-center gap-3 border-b border-gray-200 px-5 py-3 dark:border-gray-700">
            <x-filament-chat::avatar :name="$displayName" :url="$avatarUrl" class="h-10 w-10" />
            <div class="min-w-0 flex-1">
                <h3 class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $displayName }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $source->getLabel() }}</p>
            </div>
        </div>

        {{-- Messages --}}
        <div
            class="flex-1 overflow-y-auto"
            style="background-image: radial-gradient(circle, rgb(209 213 219 / 0.6) 1px, transparent 1px); background-size: 16px 16px;"
            x-data="{
                scrollToBottom() {
                    this.$el.scrollTop = this.$el.scrollHeight;
                }
            }"
            x-init="scrollToBottom()"
            @message-sent.window="$nextTick(() => scrollToBottom())"
            @if ($this->isPollingMode())
                wire:poll.{{ $this->getPollingInterval() }}="refreshMessages"
            @endif
        >
            @if (\ZEDMagdy\FilamentChat\FilamentChat::isBroadcastingMode())
                <div
                    x-data
                    x-init="
                        Echo.private('chat.conversation.{{ $this->conversationId }}')
                            .listen('MessageSent', () => $wire.refreshMessages());
                    "
                    class="hidden"
                ></div>
            @endif

            <div class="space-y-4 p-5">
                @if ($this->messages->count() >= config('filament-chat.messages_per_page', 50) * $this->page)
                    <div class="text-center">
                        <button
                            wire:click="loadMore"
                            class="rounded-full bg-white px-3 py-1 text-xs text-gray-500 shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400"
                        >
                            Load earlier messages
                        </button>
                    </div>
                @endif

                {{-- Date separators + messages --}}
                @php $lastDate = null; @endphp
                @foreach ($this->messages as $message)
                    @php $messageDate = $message->created_at->format('Y-m-d'); @endphp
                    @if ($messageDate !== $lastDate)
                        <div class="flex items-center justify-center py-2">
                            <span class="rounded-full bg-white px-4 py-1 text-xs font-medium text-gray-500 shadow-sm dark:bg-gray-800 dark:text-gray-400">
                                {{ $message->created_at->isToday() ? 'Today' : ($message->created_at->isYesterday() ? 'Yesterday' : $message->created_at->format('M d, Y')) }}
                            </span>
                        </div>
                        @php $lastDate = $messageDate; @endphp
                    @endif

                    <x-filament-chat::message-bubble
                        :message="$message"
                        :is-sent="$message->isSentBy($user)"
                        :source="$source"
                    />
                @endforeach
            </div>
        </div>

        {{-- Message Input --}}
        <div class="shrink-0 border-t border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
            <livewire:filament-chat::message-input :conversation-id="$this->conversationId" :key="'input-' . $this->conversationId" />
        </div>
    @else
        {{-- Empty State --}}
        <div
            class="flex h-full w-full items-center justify-center"
            style="background-image: radial-gradient(circle, rgb(209 213 219 / 0.6) 1px, transparent 1px); background-size: 16px 16px;"
        >
            <div class="rounded-xl bg-white/90 px-8 py-6 text-center shadow-sm dark:bg-gray-800/90">
                <svg class="mx-auto mb-3 h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/></svg>
                <p class="text-sm text-gray-500 dark:text-gray-400">Select a conversation to start chatting</p>
            </div>
        </div>
    @endif
</div>
