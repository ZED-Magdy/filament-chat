@props(['name' => '', 'url' => null])

@php
    $initials = collect(explode(' ', $name))
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->take(2)
        ->join('');

    $colors = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#06b6d4', '#3b82f6', '#8b5cf6', '#ec4899'];
    $avatarColor = $colors[abs(crc32($name)) % count($colors)];
@endphp

@if ($url)
    <img
        src="{{ $url }}"
        alt="{{ $name }}"
        {{ $attributes->merge(['class' => 'rounded-full object-cover']) }}
    />
@else
    <div
        {{ $attributes->merge(['class' => 'flex items-center justify-center rounded-full text-xs font-bold text-white']) }}
        style="background-color: {{ $avatarColor }};"
        aria-label="{{ $name }}"
        role="img"
    >
        {{ $initials ?: '?' }}
    </div>
@endif
