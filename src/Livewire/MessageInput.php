<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Livewire;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use ZEDMagdy\FilamentChat\Events\MessageSent;
use ZEDMagdy\FilamentChat\FilamentChat;

class MessageInput extends Component implements HasForms
{
    use InteractsWithForms;

    public ?int $conversationId = null;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('body')
                    ->hiddenLabel()
                    ->placeholder('Type a message...')
                    ->rows(1)
                    ->autosize()
                    ->extraInputAttributes([
                        'onkeydown' => "if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); \$wire.sendMessage(); }",
                    ]),
                SpatieMediaLibraryFileUpload::make('attachments')
                    ->hiddenLabel()
                    ->collection(config('filament-chat.attachments.collection', 'chat-attachments'))
                    ->disk(config('filament-chat.attachments.disk', 'public'))
                    ->multiple()
                    ->maxFiles(config('filament-chat.attachments.max_files', 4))
                    ->maxSize(config('filament-chat.attachments.max_file_size', 10240))
                    ->acceptedFileTypes(config('filament-chat.attachments.accepted_types', []))
                    ->reorderable(false)
                    ->openable()
                    ->previewable(),
            ])
            ->statePath('data');
    }

    public function sendMessage(): void
    {
        $data = $this->form->getState();

        if (blank($data['body'] ?? null) && empty($data['attachments'] ?? [])) {
            return;
        }

        if (! $this->conversationId) {
            return;
        }

        $user = filament()->auth()->user();

        $message = FilamentChat::getMessageModel()::create([
            'conversation_id' => $this->conversationId,
            'senderable_id' => $user->getKey(),
            'senderable_type' => $user->getMorphClass(),
            'body' => $data['body'] ?? null,
        ]);

        // Save media attachments
        $this->form->model($message)->saveRelationships();

        // Update conversation timestamp
        FilamentChat::getConversationModel()::where('id', $this->conversationId)
            ->update(['updated_at' => now()]);

        // Mark as read for sender
        FilamentChat::getParticipantModel()::query()
            ->where('conversation_id', $this->conversationId)
            ->where('participantable_id', $user->getKey())
            ->where('participantable_type', $user->getMorphClass())
            ->update(['last_read_at' => now()]);

        event(new MessageSent($message));

        $this->form->fill();
        $this->dispatch('message-sent');
    }

    public function render(): View
    {
        return view('filament-chat::livewire.message-input');
    }
}
