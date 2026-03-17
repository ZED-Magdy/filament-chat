# Filament Chat

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zedmagdy/filament-chat.svg?style=flat-square)](https://packagist.org/packages/zedmagdy/filament-chat)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/ZED-Magdy/filament-chat/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/ZED-Magdy/filament-chat/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/zedmagdy/filament-chat.svg?style=flat-square)](https://packagist.org/packages/zedmagdy/filament-chat)

A chat plugin for Filament v4 that supports configurable chat sources, one-to-one and group conversations, text and file attachments (via Spatie Media Library), read/unread tracking, search, and real-time updates via polling or broadcasting (Reverb/Pusher).

## Requirements

- PHP ^8.3
- Laravel ^11.0 or ^12.0
- Filament ^4.3.1 or ^5.0
- Spatie Media Library (installed automatically)

## Installation

Install via Composer:

```bash
composer require zedmagdy/filament-chat
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="filament-chat-migrations"
php artisan migrate
```

If you haven't already, publish the Spatie Media Library migration as well:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan migrate
```

Optionally publish the config:

```bash
php artisan vendor:publish --tag="filament-chat-config"
```

Optionally publish the views for customization:

```bash
php artisan vendor:publish --tag="filament-chat-views"
```

## Full Integration Example

### 1. Add the `HasChats` trait to your User model

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use ZEDMagdy\FilamentChat\Traits\HasChats;

class User extends Authenticatable
{
    use HasChats;

    // ...
}
```

This gives your User model these relationships:

- `chatParticipations()` - all chat participations
- `conversations()` - all conversations the user is part of
- `sentMessages()` - all messages sent by the user

### 2. Create a Chat Source

A chat source defines a category of chat (e.g. staff-to-staff, patient support). Create one class per source:

```php
namespace App\Chat;

use App\Filament\Pages\StaffChatPage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use ZEDMagdy\FilamentChat\ChatSource;

class StaffChatSource extends ChatSource
{
    public function getKey(): string
    {
        return 'staff';
    }

    public function getLabel(): string
    {
        return 'Staff Chat';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-chat-bubble-left-right';
    }

    public function getParticipantModel(): string
    {
        return User::class;
    }

    public function getPageClass(): string
    {
        return StaffChatPage::class;
    }

    // Optional: filter which users can be added to conversations
    public function getAvailableParticipantsQuery(): Builder
    {
        return User::query()->where('role', 'staff');
    }

    // Optional: enable group chats for this source
    public function allowsGroupChats(): bool
    {
        return true;
    }

    // Optional: customize navigation
    public function getNavigationGroup(): ?string
    {
        return 'Communication';
    }

    public function getNavigationSort(): ?int
    {
        return 1;
    }

    // Optional: customize how participant names are displayed
    public function getParticipantDisplayName(\Illuminate\Database\Eloquent\Model $participant): string
    {
        return $participant->name;
    }

    // Optional: provide avatar URLs
    public function getParticipantAvatarUrl(\Illuminate\Database\Eloquent\Model $participant): ?string
    {
        return $participant->avatar_url;
    }
}
```

### 3. Create a Chat Page

Each chat source needs a thin Filament page class:

```php
namespace App\Filament\Pages;

use ZEDMagdy\FilamentChat\Pages\ChatSourcePage;

class StaffChatPage extends ChatSourcePage
{
    protected static string $chatSourceKey = 'staff';
}
```

That's it. The page inherits its navigation label, icon, group, sort, and slug from the chat source.

### 4. Register the Plugin in your Panel

```php
namespace App\Providers\Filament;

use App\Chat\StaffChatSource;
use Filament\Panel;
use Filament\PanelProvider;
use ZEDMagdy\FilamentChat\FilamentChatPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // ... other config
            ->plugin(
                FilamentChatPlugin::make()
                    ->sources([
                        StaffChatSource::class,
                    ])
            );
    }
}
```

### 5. Multiple Chat Sources

You can register multiple sources for different chat contexts:

```php
// app/Chat/PatientChatSource.php
namespace App\Chat;

use App\Filament\Pages\PatientChatPage;
use App\Models\Patient;
use ZEDMagdy\FilamentChat\ChatSource;

class PatientChatSource extends ChatSource
{
    public function getKey(): string
    {
        return 'patient';
    }

    public function getLabel(): string
    {
        return 'Patient Messages';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-heart';
    }

    public function getParticipantModel(): string
    {
        return Patient::class; // any model with HasChats trait
    }

    public function getPageClass(): string
    {
        return PatientChatPage::class;
    }
}
```

```php
// app/Filament/Pages/PatientChatPage.php
namespace App\Filament\Pages;

use ZEDMagdy\FilamentChat\Pages\ChatSourcePage;

class PatientChatPage extends ChatSourcePage
{
    protected static string $chatSourceKey = 'patient';
}
```

Register both in your panel:

```php
->plugin(
    FilamentChatPlugin::make()
        ->sources([
            StaffChatSource::class,
            PatientChatSource::class,
        ])
)
```

### 6. Creating Conversations Programmatically

```php
use ZEDMagdy\FilamentChat\Models\Conversation;
use ZEDMagdy\FilamentChat\Models\Participant;
use ZEDMagdy\FilamentChat\Models\Message;

// Create a direct conversation
$conversation = Conversation::create([
    'source' => 'staff',
    'type' => 'direct',
]);

// Add participants
Participant::create([
    'conversation_id' => $conversation->id,
    'participantable_id' => $user1->id,
    'participantable_type' => $user1->getMorphClass(),
]);

Participant::create([
    'conversation_id' => $conversation->id,
    'participantable_id' => $user2->id,
    'participantable_type' => $user2->getMorphClass(),
]);

// Send a message
$message = Message::create([
    'conversation_id' => $conversation->id,
    'senderable_id' => $user1->id,
    'senderable_type' => $user1->getMorphClass(),
    'body' => 'Hello!',
]);

// Create a group conversation
$group = Conversation::create([
    'source' => 'staff',
    'type' => 'group',
    'name' => 'Project Team',
]);

// Send a system message (no sender)
Message::create([
    'conversation_id' => $group->id,
    'body' => 'User1 created the group',
]);
```

### 7. Working with Attachments

The `Message` model uses Spatie Media Library. Attachments are stored in the `chat-attachments` media collection:

```php
$message = Message::create([
    'conversation_id' => $conversation->id,
    'senderable_id' => $user->id,
    'senderable_type' => $user->getMorphClass(),
    'body' => 'Check out this file',
]);

// Add an attachment
$message->addMedia($pathToFile)
    ->toMediaCollection('chat-attachments');

// Get attachments
$message->getMedia('chat-attachments');
```

In the UI, the `MessageInput` Livewire component uses Filament's `SpatieMediaLibraryFileUpload` for seamless file uploads.

## Real-time Updates

### Polling (default)

Out of the box, the chat window polls for new messages. Configure the interval in your `.env` or config:

```env
FILAMENT_CHAT_REALTIME_MODE=polling
```

```php
// config/filament-chat.php
'realtime' => [
    'mode' => 'polling',
    'polling_interval' => '5s',
],
```

### Broadcasting (Reverb / Pusher)

For real-time updates via WebSockets:

```env
FILAMENT_CHAT_REALTIME_MODE=broadcasting
```

The package broadcasts `MessageSent` and `MessagesRead` events on private channels (`chat.conversation.{id}`). Channel authorization is handled automatically - only conversation participants can listen.

Make sure your Laravel broadcasting is configured (Reverb, Pusher, etc.) and that your frontend includes the Echo setup.

## Configuration

```php
// config/filament-chat.php
return [
    'table_prefix' => 'chat_',

    'realtime' => [
        'mode' => env('FILAMENT_CHAT_REALTIME_MODE', 'polling'),
        'polling_interval' => '5s',
    ],

    'attachments' => [
        'disk' => env('FILAMENT_CHAT_DISK', 'public'),
        'collection' => 'chat-attachments',
        'max_files' => 4,
        'max_file_size' => 10240, // KB
        'accepted_types' => [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
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

    // Override with your own model classes
    'models' => [
        'conversation' => \ZEDMagdy\FilamentChat\Models\Conversation::class,
        'message' => \ZEDMagdy\FilamentChat\Models\Message::class,
        'participant' => \ZEDMagdy\FilamentChat\Models\Participant::class,
    ],
];
```

### Custom Models

You can extend the built-in models and register them in the config:

```php
namespace App\Models;

use ZEDMagdy\FilamentChat\Models\Conversation as BaseConversation;

class Conversation extends BaseConversation
{
    // Add your custom logic
}
```

```php
// config/filament-chat.php
'models' => [
    'conversation' => \App\Models\Conversation::class,
],
```

## Events

The package dispatches the following events:

| Event | Broadcasts | Description |
|---|---|---|
| `MessageSent` | Yes | Fired when a message is sent. Broadcasts on `chat.conversation.{id}`. |
| `ConversationCreated` | No | Fired when a new conversation is created. |
| `MessagesRead` | Yes | Fired when a user reads messages. Broadcasts read receipts. |

Listen to them in your `EventServiceProvider` or with `Event::listen()`:

```php
use ZEDMagdy\FilamentChat\Events\MessageSent;

Event::listen(MessageSent::class, function (MessageSent $event) {
    // Send a notification, update counters, etc.
    $event->message;
    $event->message->conversation;
    $event->message->senderable;
});
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [ZED-Magdy](https://github.com/ZED-Magdy)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
