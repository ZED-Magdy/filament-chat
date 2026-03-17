<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use ZEDMagdy\FilamentChat\FilamentChat;

/**
 * @property int $id
 * @property int $conversation_id
 * @property int $participantable_id
 * @property string $participantable_type
 * @property string $role
 * @property Carbon|null $last_read_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Conversation $conversation
 * @property-read Model $participantable
 */
class Participant extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'last_read_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return FilamentChat::getTablePrefix().'participants';
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(FilamentChat::getConversationModel());
    }

    public function participantable(): MorphTo
    {
        return $this->morphTo();
    }

    public function markAsRead(): void
    {
        $this->update(['last_read_at' => now()]);
    }
}
