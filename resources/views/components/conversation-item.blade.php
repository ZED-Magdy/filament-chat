@props(['conversation', 'selected' => false, 'source' => null])

@php
    $user = filament()->auth()->user();
    $otherParticipant = $conversation->getOtherParticipant($user);
    $displayName = $conversation->isGroup()
        ? $conversation->name
        : ($otherParticipant ? $source?->getParticipantDisplayName($otherParticipant->participantable) : 'Unknown');
    $lastMessage = $conversation->latestMessage->first();
    $unreadCount = $conversation->unread_count ?? 0;
    $avatarUrl = (! $conversation->isGroup() && $otherParticipant)
        ? $source?->getParticipantAvatarUrl($otherParticipant->participantable)
        : null;
@endphp

<button
    wire:click="selectConversation({{ $conversation->id }})"
    aria-label="Open conversation with {{ $displayName }}"
    aria-pressed="{{ $selected ? 'true' : 'false' }}"
    class="flex w-full items-center gap-3 px-4 py-3 text-start transition-colors hover:bg-gray-50 dark:hover:bg-gray-800 {{ $selected ? 'bg-gray-50 dark:bg-gray-800' : '' }}"
    style="{{ $selected ? 'border-inline-start: 4px solid var(--primary-600); padding-inline-start: 12px;' : 'border-inline-start: 4px solid transparent;' }}"
>
    {{-- Avatar with unread dot --}}
    <div class="relative shrink-0">
        <x-filament-chat::avatar :name="$displayName" :url="$avatarUrl" class="h-10 w-10" />
        @if ($unreadCount > 0)
            <span class="absolute -bottom-0.5 -start-0.5 h-2.5 w-2.5 rounded-full border-2 border-white dark:border-gray-900" style="background-color: var(--primary-500);"></span>
        @endif
    </div>

    {{-- Content --}}
    <div class="min-w-0 flex-1">
        <div class="flex items-center justify-between gap-2">
            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                {{ $displayName }}
            </p>
            @if ($lastMessage)
                <time datetime="{{ $lastMessage->created_at->toIso8601String() }}" class="shrink-0 text-xs text-gray-400 dark:text-gray-500">
                    {{ $lastMessage->created_at->shortRelativeDiffForHumans() }}
                </time>
            @endif
        </div>
        <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
            {{ $lastMessage?->body ?? 'No messages yet' }}
        </p>
        @if ($source)
            <span
                class="mt-1 inline-block rounded px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white"
                style="background-color: var(--primary-600);"
            >
                {{ $source->getLabel() }}
            </span>
        @endif
    </div>
</button>
