@props(['message', 'isSent' => false, 'source' => null, 'isRead' => false])

@if ($message->isSystemMessage())
    <div class="flex justify-center" role="status">
        <p class="rounded-full bg-white px-3 py-1 text-xs italic text-gray-500 shadow-sm dark:bg-gray-800 dark:text-gray-400">
            {{ $message->body }}
        </p>
    </div>
@else
    <div class="flex flex-col {{ $isSent ? 'items-end' : 'items-start' }}">
        @if (! $isSent && $message->senderable)
            <p class="mb-1 px-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                {{ $source?->getParticipantDisplayName($message->senderable) ?? $message->senderable->name ?? 'Unknown' }}
            </p>
        @endif

        @if ($isSent)
            <div
                class="max-w-[70%] rounded-2xl px-4 py-2.5 text-white shadow-sm"
                style="background-color: var(--primary-600);"
                aria-label="Sent message"
            >
                @if ($message->body)
                    <p class="text-sm whitespace-pre-wrap break-words">{{ $message->body }}</p>
                @endif

                @if ($message->media->count() > 0)
                    <div class="{{ $message->body ? 'mt-1.5' : '' }} space-y-1">
                        @foreach ($message->media as $media)
                            <x-filament-chat::attachment-preview :media="$media" />
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <div
                class="max-w-[70%] rounded-2xl bg-white px-4 py-2.5 text-gray-900 shadow-sm dark:bg-gray-800 dark:text-gray-100"
                aria-label="Received message"
            >
                @if ($message->body)
                    <p class="text-sm whitespace-pre-wrap break-words">{{ $message->body }}</p>
                @endif

                @if ($message->media->count() > 0)
                    <div class="{{ $message->body ? 'mt-1.5' : '' }} space-y-1">
                        @foreach ($message->media as $media)
                            <x-filament-chat::attachment-preview :media="$media" />
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <p class="mt-1 px-1 text-[10px] text-gray-400 dark:text-gray-500 flex items-center gap-0.5">
            <time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('g:i A') }}</time>
            @if ($isSent)
                @if ($isRead)
                    {{-- Double check: read --}}
                    <svg class="h-3.5 w-3.5" viewBox="0 0 16 16" fill="currentColor" aria-label="Read" role="img">
                        <path d="M1 8.5L5.5 13 15 3.5M4 8.5L8.5 13 18 3.5" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                @else
                    {{-- Single check: sent --}}
                    <svg class="h-3.5 w-3.5" viewBox="0 0 16 16" fill="none" aria-label="Sent" role="img">
                        <path d="M2 8.5L6.5 13 14 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                @endif
            @endif
        </p>
    </div>
@endif
