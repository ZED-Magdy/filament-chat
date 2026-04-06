<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Livewire;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use ZEDMagdy\FilamentChat\Events\MessageSent;
use ZEDMagdy\FilamentChat\FilamentChat;

/**
 * @property Schema $form
 */
class MessageInput extends Component implements HasForms
{
    use InteractsWithForms;

    public ?int $conversationId = null;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public bool $showAttachments = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function toggleAttachments(): void
    {
        $this->showAttachments = ! $this->showAttachments;
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('body')
                    ->hiddenLabel()
                    ->placeholder('Type a message...')
                    ->extraInputAttributes([
                        'class' => 'border-0 bg-transparent ring-0 focus:ring-0 shadow-none',
                        'data-chat-body' => 'true',
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
                    ->previewable()
                    ->panelLayout('compact')
                    ->visible(fn (): bool => $this->showAttachments),
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

        try {
            DB::transaction(function () use ($data, $user): void {
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
            });
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Failed to send message')
                ->body('Something went wrong. Please try again.')
                ->danger()
                ->send();

            return;
        }

        $this->form->fill();
        $this->dispatch('message-sent');
    }

    public function render(): View
    {
        return view('filament-chat::livewire.message-input'); // @phpstan-ignore argument.type
    }
}
