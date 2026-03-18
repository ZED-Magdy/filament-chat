@props(['message', 'isSent' => false, 'source' => null])

@if ($message->isSystemMessage())
    <div class="flex justify-center">
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
                style="background-color: rgb(var(--primary-600));"
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
            <div class="max-w-[70%] rounded-2xl bg-white px-4 py-2.5 text-gray-900 shadow-sm dark:bg-gray-800 dark:text-gray-100">
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

        <p class="mt-1 px-1 text-[10px] text-gray-400 dark:text-gray-500">
            {{ $message->created_at->format('g:i A') }}
        </p>
    </div>
@endif
