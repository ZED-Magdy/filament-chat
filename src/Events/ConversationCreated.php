<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use ZEDMagdy\FilamentChat\Models\Conversation;

class ConversationCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Conversation $conversation) {}
}
