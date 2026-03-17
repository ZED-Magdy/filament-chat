@props(['name' => '', 'url' => null])

@php
    $initials = collect(explode(' ', $name))
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->take(2)
        ->join('');

    $colors = ['bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500'];
    $colorIndex = abs(crc32($name)) % count($colors);
    $bgColor = $colors[$colorIndex];
@endphp

@if ($url)
    <img
        src="{{ $url }}"
        alt="{{ $name }}"
        {{ $attributes->merge(['class' => 'rounded-full object-cover']) }}
    />
@else
    <div {{ $attributes->merge(['class' => "flex items-center justify-center rounded-full text-xs font-semibold text-white {$bgColor}"]) }}>
        {{ $initials ?: '?' }}
    </div>
@endif
