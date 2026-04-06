<div
    class="w-full px-4 py-3"
    x-data="{
        send() {
            const input = this.$el.querySelector('[data-chat-body]');
            const body = input ? input.value.trim() : '';
            if (body) {
                this.$dispatch('optimistic-message', { body });
            }
            $wire.sendMessage();
        }
    }"
    @keydown.enter.prevent="
        const input = this.$el.querySelector('[data-chat-body]');
        if ($event.target === input && !$event.shiftKey) send();
    "
>
    <div class="flex items-end gap-3">
        {{-- Paperclip button --}}
        <button
            type="button"
            wire:click="toggleAttachments"
            aria-label="Toggle attachments"
            class="mb-2 shrink-0 transition-colors {{ $showAttachments ? 'opacity-100' : 'opacity-40 hover:opacity-70' }}"
            style="color: var(--primary-600);"
        >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" /></svg>
        </button>

        {{-- Form --}}
        <div class="min-w-0 flex-1">
            {{ $this->form }}
        </div>

        {{-- Send button --}}
        <button
            type="button"
            @click="send()"
            aria-label="Send message"
            class="mb-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-white shadow-sm transition hover:opacity-90"
            style="background-color: var(--primary-600);"
        >
            <svg class="h-5 w-5 rtl:-scale-x-100" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
        </button>
    </div>
</div>
