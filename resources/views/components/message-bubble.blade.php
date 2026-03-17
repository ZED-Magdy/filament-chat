@props(['message', 'isSent' => false, 'source' => null])

<div @class([
    'flex',
    'justify-end' => $isSent,
    'justify-start' => ! $isSent,
])>
    <div @class([
        'max-w-[70%] rounded-2xl px-4 py-2',
        'bg-primary-500 text-white' => $isSent,
        'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-100' => ! $isSent,
    ])>
        @if (! $isSent && $message->senderable)
            <p class="mb-1 text-xs font-semibold opacity-70">
                {{ $source?->getParticipantDisplayName($message->senderable) ?? $message->senderable->name ?? 'Unknown' }}
            </p>
        @endif

        @if ($message->isSystemMessage())
            <p class="text-center text-xs italic text-gray-500 dark:text-gray-400">
                {{ $message->body }}
            </p>
        @else
            @if ($message->body)
                <p class="text-sm whitespace-pre-wrap">{{ $message->body }}</p>
            @endif

            @if ($message->media->count() > 0)
                <div class="mt-2 space-y-1">
                    @foreach ($message->media as $media)
                        <x-filament-chat::attachment-preview :media="$media" />
                    @endforeach
                </div>
            @endif
        @endif

        <p @class([
            'mt-1 text-[10px]',
            'text-white/70' => $isSent,
            'text-gray-400 dark:text-gray-500' => ! $isSent,
        ])>
            {{ $message->created_at->format('g:i A') }}
        </p>
    </div>
</div>
