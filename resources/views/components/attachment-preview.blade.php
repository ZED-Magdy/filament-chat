@props(['media'])

@php
    $isImage = str_starts_with($media->mime_type, 'image/');
@endphp

@if ($isImage)
    <a href="{{ $media->getUrl() }}" target="_blank" class="block">
        <img
            src="{{ $media->getUrl() }}"
            alt="{{ $media->file_name }}"
            class="max-h-48 rounded-lg object-cover"
            loading="lazy"
        />
    </a>
@else
    <a
        href="{{ $media->getUrl() }}"
        target="_blank"
        class="flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 text-xs hover:bg-white/20 transition"
    >
        <x-filament::icon icon="heroicon-o-document" class="h-5 w-5 shrink-0" />
        <span class="truncate">{{ $media->file_name }}</span>
        <span class="shrink-0 text-[10px] opacity-70">{{ $media->human_readable_size }}</span>
    </a>
@endif
