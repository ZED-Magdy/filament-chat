<x-filament-panels::page :fullHeight="true">
    <div class="filament-chat-container flex h-full flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        {{-- Top Search Bar --}}
        <div class="shrink-0 border-b border-gray-200 px-4 py-3 dark:border-gray-700">
            <livewire:filament-chat::chat-search :source-key="$this->getChatSourceKey()" />
        </div>

        {{-- Main Content --}}
        <div class="flex min-h-0 flex-1">
            {{-- Sidebar --}}
            <div class="w-80 shrink-0 overflow-hidden border-e border-gray-200 dark:border-gray-700">
                <livewire:filament-chat::chat-list :source-key="$this->getChatSourceKey()" />
            </div>

            {{-- Chat Window --}}
            <div class="min-w-0 flex-1 overflow-hidden">
                <livewire:filament-chat::chat-window :source-key="$this->getChatSourceKey()" />
            </div>
        </div>
    </div>
</x-filament-panels::page>
