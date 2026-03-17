<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;
use ZEDMagdy\FilamentChat\Broadcasting\ChatConversationChannel;

Broadcast::channel('chat.conversation.{conversationId}', ChatConversationChannel::class);
