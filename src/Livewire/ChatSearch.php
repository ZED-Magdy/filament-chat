<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ChatSearch extends Component
{
    public string $sourceKey = '';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->dispatch('chat-search-updated', search: $this->search);
    }

    public function render(): View
    {
        return view('filament-chat::livewire.chat-search'); // @phpstan-ignore argument.type
    }
}
