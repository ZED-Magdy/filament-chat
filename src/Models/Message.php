<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use ZEDMagdy\FilamentChat\FilamentChat;

class Message extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function getTable(): string
    {
        return FilamentChat::getTablePrefix().'messages';
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(FilamentChat::getConversationModel());
    }

    public function senderable(): MorphTo
    {
        return $this->morphTo();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(config('filament-chat.attachments.collection', 'chat-attachments'))
            ->useDisk(config('filament-chat.attachments.disk', 'public'))
            ->acceptsMimeTypes(config('filament-chat.attachments.accepted_types', []))
            ->onlyKeepLatest(config('filament-chat.attachments.max_files', 4));
    }

    public function isSentBy(Model $user): bool
    {
        return $this->senderable_id === $user->getKey()
            && $this->senderable_type === $user->getMorphClass();
    }

    public function isSystemMessage(): bool
    {
        return $this->senderable_type === null;
    }
}
