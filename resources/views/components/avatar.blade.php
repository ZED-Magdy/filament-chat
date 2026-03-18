@props(['name' => '', 'url' => null])

@php
    $initials = collect(explode(' ', $name))
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->take(2)
        ->join('');
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
        style="background-color: rgb(var(--primary-600));"
    >
        {{ $initials ?: '?' }}
    </div>
@endif
