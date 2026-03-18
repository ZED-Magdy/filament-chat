<div class="w-full">
    <x-filament::input.wrapper>
        <x-slot name="prefix">
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-5 w-5 text-gray-400" />
        </x-slot>
        <x-filament::input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search conversations..."
        />
    </x-filament::input.wrapper>
</div>
