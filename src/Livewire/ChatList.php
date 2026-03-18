<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use ZEDMagdy\FilamentChat\ChatSource;
use ZEDMagdy\FilamentChat\Events\ConversationCreated;
use ZEDMagdy\FilamentChat\FilamentChat;
use ZEDMagdy\FilamentChat\FilamentChatPlugin;

class ChatList extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public string $sourceKey = '';

    public string $search = '';

    public ?int $selectedConversationId = null;

    #[On('chat-search-updated')]
    public function updateSearch(string $search): void
    {
        $this->search = $search;
        unset($this->conversations);
    }

    public function selectConversation(int $conversationId): void
    {
        $this->selectedConversationId = $conversationId;
        $this->dispatch('conversation-selected', conversationId: $conversationId);
    }

    public function newConversationAction(): Action
    {
        $source = $this->getSource();

        return Action::make('newConversation')
            ->label('New Chat')
            ->icon('heroicon-o-plus')
            ->iconButton()
            ->size('md')
            ->tooltip('New Chat')
            ->modalHeading('Start a Conversation')
            ->modalWidth('md')
            ->schema(function () use ($source): array {
                $user = filament()->auth()->user();
                $participantOptions = $source->getAvailableParticipantsQuery()
                    ->where(function ($q) use ($user): void {
                        $q->where('id', '!=', $user->getKey());
                    })
                    ->get()
                    ->mapWithKeys(fn ($p) => [
                        $p->getKey() => $source->getParticipantDisplayName($p),
                    ])
                    ->toArray();

                $fields = [];

                if ($source->allowsGroupChats()) {
                    $fields[] = Select::make('type')
                        ->label('Conversation Type')
                        ->options([
                            'direct' => 'Direct Message',
                            'group' => 'Group Chat',
                        ])
                        ->default('direct')
                        ->required()
                        ->live();

                    $fields[] = TextInput::make('name')
                        ->label('Group Name')
                        ->visible(fn (Get $get): bool => $get('type') === 'group')
                        ->required(fn (Get $get): bool => $get('type') === 'group')
                        ->maxLength(255);
                }

                $fields[] = Select::make('participants')
                    ->label($source->allowsGroupChats() ? 'Participants' : 'Participant')
                    ->options($participantOptions)
                    ->searchable()
                    ->required()
                    ->multiple(fn (Get $get): bool => $source->allowsGroupChats() && $get('type') === 'group')
                    ->preload();

                return $fields;
            })
            ->action(function (array $data) use ($source): void {
                $this->createConversation($data, $source);
            });
    }

    protected function createConversation(array $data, ChatSource $source): void
    {
        $user = filament()->auth()->user();
        $type = $data['type'] ?? 'direct';
        $participantIds = (array) $data['participants'];
        $participantModel = $source->getParticipantModel();

        // For direct chats, check if a conversation already exists between these two users
        if ($type === 'direct' && count($participantIds) === 1) {
            $existingConversation = $this->findExistingDirectConversation(
                $source->getKey(),
                $user,
                $participantIds[0],
                $participantModel,
            );

            if ($existingConversation) {
                $this->selectConversation((int) $existingConversation->getKey());

                return;
            }
        }

        $conversationModel = FilamentChat::getConversationModel();
        $conversation = $conversationModel::create([
            'source' => $source->getKey(),
            'type' => $type,
            'name' => $data['name'] ?? null,
        ]);

        $participantModelClass = FilamentChat::getParticipantModel();

        // Add current user as participant
        $participantModelClass::create([
            'conversation_id' => $conversation->id,
            'participantable_id' => $user->getKey(),
            'participantable_type' => $user->getMorphClass(),
            'role' => $type === 'group' ? 'owner' : 'member',
        ]);

        // Add selected participants
        foreach ($participantIds as $participantId) {
            $participant = $participantModel::find($participantId);

            if ($participant) {
                $participantModelClass::create([
                    'conversation_id' => $conversation->id,
                    'participantable_id' => $participant->getKey(),
                    'participantable_type' => $participant->getMorphClass(),
                    'role' => 'member',
                ]);
            }
        }

        event(new ConversationCreated($conversation));

        unset($this->conversations);
        $this->selectConversation($conversation->id);
    }

    protected function findExistingDirectConversation(
        string $sourceKey,
        Model $user,
        int|string $otherParticipantId,
        string $participantModel,
    ): ?Model {
        $other = $participantModel::find($otherParticipantId);

        if (! $other) {
            return null;
        }

        $conversationModel = FilamentChat::getConversationModel();

        return $conversationModel::query()
            ->forSource($sourceKey)
            ->where('type', 'direct')
            ->forParticipant($user)
            ->whereHas('participants', function ($q) use ($other): void {
                $q->where('participantable_id', $other->getKey())
                    ->where('participantable_type', $other->getMorphClass());
            })
            ->first();
    }

    #[Computed]
    public function conversations(): Collection
    {
        $user = filament()->auth()->user();
        $conversationModel = FilamentChat::getConversationModel();

        $query = $conversationModel::query()
            ->forSource($this->sourceKey)
            ->forParticipant($user)
            ->withUnreadCount($user)
            ->with(['participants.participantable', 'latestMessage'])
            ->latest('updated_at');

        if (filled($this->search)) {
            $source = FilamentChatPlugin::get()->getSource($this->sourceKey);

            $query->whereHas('participants', function ($q) use ($user, $source): void {
                $q->where(function ($q) use ($user): void {
                    $q->where('participantable_id', '!=', $user->getKey())
                        ->orWhere('participantable_type', '!=', $user->getMorphClass());
                })->whereHasMorph('participantable', [$source->getParticipantModel()], function ($q): void {
                    $q->where('name', 'like', "%{$this->search}%");
                });
            });
        }

        return $query->limit(config('filament-chat.conversations_per_page', 25))->get();
    }

    public function getSource(): ?ChatSource
    {
        return FilamentChatPlugin::get()->getSource($this->sourceKey);
    }

    public function canCreateConversation(): bool
    {
        $source = $this->getSource();

        return $source !== null && $source->allowsNewConversations();
    }

    public function render(): View
    {
        return view('filament-chat::livewire.chat-list'); // @phpstan-ignore argument.type
    }
}
