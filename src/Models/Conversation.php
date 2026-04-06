<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use ZEDMagdy\FilamentChat\FilamentChat;

/**
 * @property int $id
 * @property string $source
 * @property string $type
 * @property string|null $name
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Message> $messages
 * @property-read Collection<int, Participant> $participants
 * @property-read Collection<int, Message> $latestMessage
 */
class Conversation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function getTable(): string
    {
        return FilamentChat::getTablePrefix().'conversations';
    }

    public function messages(): HasMany
    {
        return $this->hasMany(FilamentChat::getMessageModel());
    }

    public function participants(): HasMany
    {
        return $this->hasMany(FilamentChat::getParticipantModel());
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(FilamentChat::getMessageModel())->latest()->limit(1);
    }

    public function scopeForSource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    public function scopeForParticipant(Builder $query, Model $user): Builder
    {
        return $query->whereHas('participants', function (Builder $q) use ($user): void {
            $q->where('participantable_id', $user->getKey())
                ->where('participantable_type', $user->getMorphClass());
        });
    }

    public function scopeWithUnreadCount(Builder $query, Model $user): Builder
    {
        $messagesTable = (new (FilamentChat::getMessageModel()))->getTable();
        $participantsTable = (new (FilamentChat::getParticipantModel()))->getTable();

        return $query->withCount([
            'messages as unread_count' => function (Builder $q) use ($user, $messagesTable, $participantsTable): void {
                $q->whereHas('conversation.participants', function (Builder $pq) use ($user): void {
                    $pq->where('participantable_id', $user->getKey())
                        ->where('participantable_type', $user->getMorphClass());
                })->where(function (Builder $q) use ($user, $messagesTable, $participantsTable): void {
                    $q->whereRaw(
                        "{$messagesTable}.created_at > (SELECT last_read_at FROM {$participantsTable} WHERE conversation_id = {$messagesTable}.conversation_id AND participantable_id = ? AND participantable_type = ? LIMIT 1)",
                        [$user->getKey(), $user->getMorphClass()],
                    )
                        ->orWhereRaw(
                            "(SELECT last_read_at FROM {$participantsTable} WHERE conversation_id = {$messagesTable}.conversation_id AND participantable_id = ? AND participantable_type = ? LIMIT 1) IS NULL",
                            [$user->getKey(), $user->getMorphClass()],
                        );
                });
            },
        ]);
    }

    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    public function isDirect(): bool
    {
        return $this->type === 'direct';
    }

    public function getOtherParticipant(Model $user): ?Participant
    {
        /** @var Participant|null */
        return $this->participants
            ->first(function (Model $p) use ($user): bool {
                /** @var Participant $p */
                return $p->participantable_id !== $user->getKey()
                    || $p->participantable_type !== $user->getMorphClass();
            });
    }
}
