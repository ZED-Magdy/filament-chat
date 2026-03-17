@props(['conversation', 'selected' => false, 'source' => null])

@php
    $user = filament()->auth()->user();
    $otherParticipant = $conversation->getOtherParticipant($user);
    $displayName = $conversation->isGroup()
        ? $conversation->name
        : ($otherParticipant ? $source?->getParticipantDisplayName($otherParticipant->participantable) : 'Unknown');
    $lastMessage = $conversation->latestMessage->first();
    $unreadCount = $conversation->unread_count ?? 0;
@endphp

<button
    wire:click="selectConversation({{ $conversation->id }})"
    @class([
        'flex w-full items-center gap-3 px-3 py-3 text-start transition hover:bg-gray-50 dark:hover:bg-gray-800',
        'bg-primary-50 dark:bg-primary-900/20' => $selected,
    ])
>
    <x-filament-chat::avatar :name="$displayName" class="h-10 w-10 shrink-0" />

    <div class="min-w-0 flex-1">
        <div class="flex items-center justify-between">
            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                {{ $displayName }}
            </p>
            @if ($lastMessage)
                <span class="shrink-0 text-[10px] text-gray-400 dark:text-gray-500">
                    {{ $lastMessage->created_at->shortRelativeDiffForHumans() }}
                </span>
            @endif
        </div>
        <div class="flex items-center justify-between">
            <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                {{ $lastMessage?->body ?? 'No messages yet' }}
            </p>
            @if ($unreadCount > 0)
                <span class="ml-2 inline-flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-primary-500 px-1 text-[10px] font-bold text-white">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
        </div>
    </div>
</button>
