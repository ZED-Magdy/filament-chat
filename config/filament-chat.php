<?php

declare(strict_types=1);
use ZEDMagdy\FilamentChat\Models\Conversation;
use ZEDMagdy\FilamentChat\Models\Message;
use ZEDMagdy\FilamentChat\Models\Participant;

// config for ZEDMagdy/FilamentChat

return [
    'table_prefix' => 'chat_',

    'realtime' => [
        'mode' => env('FILAMENT_CHAT_REALTIME_MODE', 'polling'), // 'polling' | 'broadcasting'
        'polling_interval' => '5s',
    ],

    'attachments' => [
        'disk' => env('FILAMENT_CHAT_DISK', 'public'),
        'collection' => 'chat-attachments',
        'max_files' => 4,
        'max_file_size' => 10240, // KB
        'accepted_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
        ],
    ],

    'messages_per_page' => 50,
    'conversations_per_page' => 25,

    'theme' => [
        'sent_bg' => 'primary-500',
        'received_bg' => 'gray-100',
        'sent_text' => 'white',
        'received_text' => 'gray-900',
    ],

    'models' => [
        'conversation' => Conversation::class,
        'message' => Message::class,
        'participant' => Participant::class,
    ],
];
