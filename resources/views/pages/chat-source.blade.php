<x-filament-panels::page>
    <div class="flex h-[calc(100vh-12rem)] gap-4 mt-5">
        {{-- Chat List Sidebar --}}
        <div class="w-80 shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
            <livewire:filament-chat::chat-list :source-key="$this->getChatSourceKey()" />
        </div>

        {{-- Chat Window --}}
        <div class="flex-1 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
            <livewire:filament-chat::chat-window :source-key="$this->getChatSourceKey()" />
        </div>
    </div>
</x-filament-panels::page>
