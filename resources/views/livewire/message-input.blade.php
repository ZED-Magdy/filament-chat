<div class="p-3" x-on:message-sent.window="$el.scrollIntoView({ behavior: 'smooth' })">
    <form wire:submit="sendMessage" class="space-y-2">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" size="sm">
                Send
            </x-filament::button>
        </div>
    </form>
</div>
